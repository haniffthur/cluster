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
        // 1. AMBIL & VALIDASI PARAMETER
        $termno = $request->query('termno');
        $io = $request->query('IO'); 
        $cardNumber = $request->query('card');

        $currentDate = now()->format('d-m-Y H:i:s');
        $directionText = ($io == 1) ? 'In' : 'Out';

        if (!$termno || !$cardNumber || is_null($io)) {
            return response()->json([
                'Status' => 0, 'Nama' => '-', 'Date' => $currentDate, 'Direction' => '-',
                'Message' => 'Parameter tidak lengkap', 'Cardno' => $cardNumber ?? '-'
            ], 400);
        }

        // 2. CEK VALIDITAS KARTU
        $card = AccessCard::with('resident')
                          ->where('card_number', $cardNumber)
                          ->where('is_active', true)
                          ->first();

        if (!$card) {
            $this->logActivity($termno, $cardNumber, $io, null);
            return response()->json([
                'Status' => 0, 'Nama' => 'Unknown', 'Date' => $currentDate, 'Direction' => $directionText,
                'Message' => 'DITOLAK: KARTU TIDAK DIKENAL', 'Cardno' => $cardNumber,
            ]); 
        }

        // 3. TENTUKAN LABEL NAMA
        if ($card->kategori == 'penghuni' && $card->resident) {
            $namaWarga = $card->resident->nama;
        } elseif ($card->kategori == 'security') {
            $namaWarga = 'SECURITY / PATROLI';
        } else {
            $namaWarga = 'TAMU / UMUM';
        }

        // 4. CEK TUNGGAKAN (TETAP KHUSUS PENGHUNI)
        // Tamu tidak punya tagihan IPL, jadi logic ini di-skip untuk tamu.
        if ($card->kategori == 'penghuni' && $card->resident && $io == 1) {
            $totalTunggakan = $card->resident->bills()
                                           ->where('status', 'belum_bayar') 
                                           ->sum('jumlah_tagihan');

            if ($totalTunggakan > 0) {
                $this->logActivity($termno, $cardNumber, $io, null);
                return response()->json([
                    'Status' => 0, 'Nama' => $namaWarga, 'Date' => $currentDate, 'Direction' => 'In',
                    'Message' => 'BELUM BAYAR', 'Cardno' => $cardNumber,
                ]);
            }
        }

        // =========================================================================
        // 5. LOGIKA ANTI-PASSBACK (PENGHUNI + TAMU)
        // =========================================================================
        // Security tetap bebas (Skip logic ini).
        // Tamu sekarang ikut dicek agar tidak bisa tap masuk 2x berturut-turut.
        
        if ($card->kategori == 'penghuni' || $card->kategori == 'tamu') {
            
            $lastLog = GateLog::where('card_number', $cardNumber)->latest('tapped_at')->first();
            
            if ($io == 1 && $lastLog && $lastLog->io_status == 1) {
                return response()->json([
                    'Status' => 0,
                    'Nama' => $namaWarga,
                    'Date' => $currentDate,
                    'Direction' => 'In',
                    'Message' => 'SUDAH MASUK (ANTI-PASSBACK)',
                    'Cardno' => $cardNumber,
                ]);
            }
            if ($io == 0 && $lastLog && $lastLog->io_status == 0) {
                return response()->json([
                    'Status' => 0,
                    'Nama' => $namaWarga,
                    'Date' => $currentDate,
                    'Direction' => 'Out',
                    'Message' => 'SUDAH KELUAR (ANTI-PASSBACK)',
                    'Cardno' => $cardNumber,
                ]);
            }
        }

        // 6. SNAPSHOT KAMERA
        $snapshotPath = null;
        $device = Device::where('termno', $termno)->first();
        if ($device) {
            $configIo = $device->ios()->where('io_status', $io)->first();
            if ($configIo) {
                $snapshotPath = $this->captureSnapshotDynamic($configIo, $cardNumber);
            }
        }

        // 7. SUKSES - BUKA GATE
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

    // ... (Function captureSnapshotDynamic dan logActivity TETAP SAMA) ...
    // Copy paste bagian bawah dari kode sebelumnya
    
    private function captureSnapshotDynamic($configIo, $cardNo)
    {
        $ip = trim($configIo->cam_ip);
        $username = $configIo->cam_username ?? 'admin';
        $password = $configIo->cam_password ?? 'admin';

        $url = "http://{$ip}/onvifsnapshot/media_service/snapshot?channel=1&subtype=0";

        // Log::info("📷 SNAPSHOT ATTEMPT", ['ip' => $ip]);

        $filename = "SNAP_{$cardNo}_" . now()->format('Ymd_His') . ".jpg";
        $relativePath = "snapshots/{$filename}";
        $storagePath = storage_path("app/public/{$relativePath}");

        if (!file_exists(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
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
                return $relativePath;
            }
            
            file_put_contents($storagePath, $image);
            return $relativePath;
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