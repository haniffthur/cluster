<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\AccessCard;
use App\Models\GateLog;
use Illuminate\Support\Facades\Log;

class GateController extends Controller
{
    public function handleTap(Request $request)
    {
        // 1. Ambil Parameter
        $termno = $request->query('termno');
        $io = $request->query('IO'); // Pastikan url pakai IO huruf besar
        $cardNumber = $request->query('card');

        // Setup Variabel Default
        $currentDate = now()->format('d-m-Y H:i:s');
        $directionText = ($io == 1) ? 'In' : 'Out';

        // Validasi Parameter
        if (!$termno || !$cardNumber || is_null($io)) {
            return response()->json([
                'Status' => 0,
                'Nama' => '-',
                'Date' => $currentDate,
                'Direction' => '-',
                'Message' => 'Parameter tidak lengkap',
                'Cardno' => $cardNumber ?? '-'
            ], 400);
        }

        // 2. Cek Validitas Kartu (+ Load Relasi Resident)
        $card = AccessCard::with('resident')
                          ->where('card_number', $cardNumber)
                          ->where('is_active', true)
                          ->first();

        if (!$card) {
            $this->logActivity($termno, $cardNumber, $io, null);
            return response()->json([
                'Status' => 0, 
                'Nama' => 'Unknown',
                'Date' => $currentDate,
                'Direction' => $directionText,
                'Message' => 'DITOLAK: KARTU TIDAK DIKENAL',
                'Cardno' => $cardNumber,
            ], 403);
        }

        // Ambil Data Warga
        $resident = $card->resident;
        $namaWarga = $resident ? $resident->nama : 'Warga Tidak Dikenal';

        // =========================================================================
        // 2.5 CEK TUNGGAKAN (Hanya Cek Jika Mau Masuk / IO = 1)
        // =========================================================================
        
        if ($resident && $io == 1) {
            // Hitung total tagihan yang statusnya 'belum_bayar'
            $totalTunggakan = $resident->bills()
                                       ->where('status', 'belum_bayar') 
                                       ->sum('jumlah_tagihan');

            // Jika ada tunggakan lebih dari 0
            if ($totalTunggakan > 0) {
                // Log activity (Opsional, agar terekam history orang ditolak)
                $this->logActivity($termno, $cardNumber, $io, null);

                return response()->json([
                    'Status' => 0,
                    'Nama' => $namaWarga,
                    'Date' => $currentDate,
                    'Direction' => 'In',
                    'Message' => 'BELUM BAYAR',
                    'Cardno' => $cardNumber,
                ], 403);
            }
        }
        // =========================================================================

        // 3. Logika Anti-Passback
        $lastLog = GateLog::where('card_number', $cardNumber)->latest('tapped_at')->first();
        
        if ($io == 1 && $lastLog && $lastLog->io_status == 1) {
            return response()->json([
                'Status' => 0,
                'Nama' => $namaWarga,
                'Date' => $currentDate,
                'Direction' => 'In',
                'Message' => 'SUDAH MASUK',
                'Cardno' => $cardNumber,
            ], 403);
        }
        if ($io == 0 && $lastLog && $lastLog->io_status == 0) {
            return response()->json([
                'Status' => 0,
                'Nama' => $namaWarga,
                'Date' => $currentDate,
                'Direction' => 'Out',
                'Message' => 'SUDAH KLUAR',
                'Cardno' => $cardNumber,
            ], 403);
        }

        // 4. Logika Snapshot (Kamera)
        $snapshotPath = null;
        $device = Device::where('termno', $termno)->first();
        if ($device) {
            $configIo = $device->ios()->where('io_status', $io)->first();
            if ($configIo) {
                $snapshotPath = $this->captureSnapshotDynamic($configIo, $cardNumber);
            }
        }

        // 5. Simpan Log & SUKSES
        $this->logActivity($termno, $cardNumber, $io, $snapshotPath);

        return response()->json([
            'Status'    => 1,
            'Nama'      => $namaWarga,
            'Date'      => $currentDate,
            'Direction' => $directionText,
            'Message'   => 'Akses Diterima',
            'Cardno'    => $cardNumber,
        ]);
    }
    
    // -------------------------------------------------------------------------
    // FUNCTION PENDUKUNG (JANGAN DIHAPUS)
    // -------------------------------------------------------------------------

    private function captureSnapshotDynamic($configIo, $cardNo)
    {
        $ip = trim($configIo->cam_ip);
        $username = $configIo->cam_username ?? 'admin';
        $password = $configIo->cam_password ?? 'admin';

        $url = "http://{$ip}/onvifsnapshot/media_service/snapshot?channel=1&subtype=0";

        Log::info("📷 SNAPSHOT ATTEMPT", ['ip' => $ip]);

        $filename = "SNAP_{$cardNo}_" . now()->format('Ymd_His') . ".jpg";
        $relativePath = "snapshots/{$filename}";
        $storagePath = storage_path("app/public/{$relativePath}");

        if (!file_exists(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2, // Timeout cepat agar gate tidak nunggu lama
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_USERPWD => "{$username}:{$password}",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $image = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($http === 200 && str_contains($ctype ?? '', 'image') && strlen($image) > 1000) {
            // Compress Image Logic
            $img = imagecreatefromstring($image);
            if ($img !== false) {
                $width = imagesx($img);
                $height = imagesy($img);
                
                $maxWidth = 1280; $maxHeight = 720;
                
                if ($width > $maxWidth || $height > $maxHeight) {
                    $ratio = min($maxWidth / $width, $maxHeight / $height);
                    $newWidth = (int)($width * $ratio);
                    $newHeight = (int)($height * $ratio);
                    
                    $resized = imagescale($img, $newWidth, $newHeight, IMG_BICUBIC);
                    imagedestroy($img);
                    $img = $resized;
                }
                
                imagejpeg($img, $storagePath, 75);
                imagedestroy($img);
                Log::info("✅ SNAPSHOT OK", ['file' => $relativePath]);
                return $relativePath;
            }
            
            file_put_contents($storagePath, $image);
            return $relativePath;
        }

        Log::error("❌ SNAPSHOT GAGAL", ['http' => $http]);
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