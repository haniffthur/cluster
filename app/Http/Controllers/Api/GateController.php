<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceIo;
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
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
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
        
        // 🗜️ COMPRESS IMAGE
        $originalSize = strlen($image);
        
        // Load image
        $img = imagecreatefromstring($image);
        
        if ($img !== false) {
            // Get original dimensions
            $width = imagesx($img);
            $height = imagesy($img);
            
            // 📏 Option 1: Resize (lebih kecil = lebih ringan)
            $maxWidth = 1280;  // Ubah sesuai kebutuhan (640, 800, 1024, 1280)
            $maxHeight = 720;
            
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $resized = imagescale($img, $newWidth, $newHeight, IMG_BICUBIC);
                imagedestroy($img);
                $img = $resized;
            }
            
            // 🗜️ Option 2: Compress quality (0-100, makin kecil = makin ringan)
            $quality = 75; // 75 = balance antara size & quality (recommended: 70-85)
            
            // Save compressed image
            imagejpeg($img, $storagePath, $quality);
            imagedestroy($img);
            
            $compressedSize = filesize($storagePath);
            $reduction = round((1 - $compressedSize / $originalSize) * 100, 1);
            
            Log::info("✅ SNAPSHOT OK (COMPRESSED)", [
                'file' => $relativePath,
                'original_size' => $this->formatBytes($originalSize),
                'compressed_size' => $this->formatBytes($compressedSize),
                'reduction' => "{$reduction}%"
            ]);
            
            return $relativePath;
        }
        
        // Fallback: save tanpa compress jika gagal
        file_put_contents($storagePath, $image);
        Log::info("✅ SNAPSHOT OK (NO COMPRESSION)", ['file' => $relativePath]);
        return $relativePath;
    }

    Log::error("❌ SNAPSHOT GAGAL", ['http' => $http]);
    return null;
}

// Helper function untuk format size
private function formatBytes($bytes)
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
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