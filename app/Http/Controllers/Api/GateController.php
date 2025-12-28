<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\AccessCard;
use App\Models\GateLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GateController extends Controller
{
    public function handleTap(Request $request)
    {
        // 1. Ambil Parameter Dinamis dari Mesin
        $termno = $request->query('termno');
        $io = $request->query('io'); 
        $cardNumber = $request->query('card');

        // Validasi
        if (!$termno || !$cardNumber) {
            return response()->json(['status' => 'ERROR', 'message' => 'Parameter tidak lengkap'], 400);
        }

        // 2. Cek Validitas Kartu
        $card = AccessCard::where('card_number', $cardNumber)->where('is_active', true)->first();
        if (!$card) {
            $this->logActivity($termno, $cardNumber, $io, null);
            return response()->json(['status' => 'DENIED', 'message' => 'Kartu Tidak Terdaftar'], 403);
        }

        // 3. Logika Anti-Passback (Cek Status Terakhir)
        $lastLog = GateLog::where('card_number', $cardNumber)->latest('tapped_at')->first();
        
        if ($io == 1 && $lastLog && $lastLog->io_status == 1) {
            return response()->json(['status' => 'DENIED', 'message' => 'Kartu SUDAH DI DALAM.'], 403);
        }
        if ($io == 0 && $lastLog && $lastLog->io_status == 0) {
            return response()->json(['status' => 'DENIED', 'message' => 'Kartu SUDAH DI LUAR.'], 403);
        }

        // 4. Logika Snapshot DINAMIS
        $snapshotPath = null;
        $device = Device::where('termno', $termno)->first();

        if ($device) {
            // AMBIL CONFIG DARI DATABASE (Tabel device_ios)
            // Ini bagian kuncinya: Data diambil berdasarkan IO yang sedang aktif
            $configIo = $device->ios()->where('io_status', $io)->first();

            if ($configIo) {
                // Oper data config (IP/User/Pass) ke fungsi snapshot
                $snapshotPath = $this->captureSnapshotDynamic($configIo, $cardNumber);
            } else {
                Log::warning("IO {$io} belum disetting untuk Gate {$termno}");
            }
        }

        // 5. Simpan Log & Buka Gate
        $this->logActivity($termno, $cardNumber, $io, $snapshotPath);

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Silahkan Lewat',
            'gate_open' => true
        ]);
    }

    /**
     * Fungsi Snapshot Dinamis
     * Menggunakan IP, Username, & Password dari database ($ioConfig)
     */
    private function captureSnapshotDynamic($ioConfig, $cardNum)
    {
        try {
            // URL Dinamis: Menggunakan IP dari database
            $url = "http://{$ioConfig->cam_ip}/cgi-bin/snapshot.cgi?channel=1";

            // PERCOBAAN 1: Digest Auth (Default Dahua)
            // Menggunakan User/Pass dari database
            $response = Http::withDigestAuth($ioConfig->cam_username, $ioConfig->cam_password)
                ->timeout(5)
                ->get($url);

            // PERCOBAAN 2: Basic Auth (Fallback jika Digest gagal/Unauthorized)
            if ($response->status() == 401) {
                // Log info bahwa kita mencoba cara kedua
                Log::info("Digest Auth gagal untuk IP {$ioConfig->cam_ip}, mencoba Basic Auth.");
                
                $response = Http::withBasicAuth($ioConfig->cam_username, $ioConfig->cam_password)
                    ->timeout(5)
                    ->get($url);
            }

            if ($response->successful()) {
                $fileName = 'snapshots/' . date('Y-m-d') . '/' . $cardNum . '_' . time() . '.jpg';
                Storage::disk('public')->put($fileName, $response->body());
                return $fileName;
            } else {
                Log::error("Gagal Snapshot IP {$ioConfig->cam_ip}. Status: " . $response->status());
            }

        } catch (\Exception $e) {
            Log::error("Error Koneksi Kamera IO ID {$ioConfig->id}: " . $e->getMessage());
        }

        return null;
    }

    private function logActivity($termno, $card, $io, $path)
    {
        GateLog::create([
            'termno' => $termno,
            'card_number' => $card,
            'io_status' => $io ?? 0,
            'snapshot_path' => $path,
            'tapped_at' => now(),
        ]);
    }
}