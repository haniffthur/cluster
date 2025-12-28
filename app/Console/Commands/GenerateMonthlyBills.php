<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resident;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyBills extends Command
{
    // Nama command yang akan dijalankan
    protected $signature = 'bills:generate';
    protected $description = 'Generate tagihan bulanan untuk semua penghuni aktif';

    public function handle()
    {
        $this->info('Memulai proses generate tagihan...');

        $bulanIni = Carbon::now()->format('m');
        $tahunIni = Carbon::now()->format('Y');

        // Ambil semua penghuni yang aktif dan punya iuran > 0
        $residents = Resident::where('is_active', true)
                             ->where('iuran_bulanan', '>', 0)
                             ->get();

        $count = 0;

        DB::beginTransaction(); // Pakai transaksi biar aman
        try {
            foreach ($residents as $resident) {
                // Cek apakah tagihan bulan ini sudah ada? (Supaya tidak dobel)
                $exists = Bill::where('resident_id', $resident->id)
                              ->where('bulan', $bulanIni)
                              ->where('tahun', $tahunIni)
                              ->exists();

                if (!$exists) {
                    Bill::create([
                        'resident_id' => $resident->id,
                        'bulan' => $bulanIni,
                        'tahun' => $tahunIni,
                        'jumlah_tagihan' => $resident->iuran_bulanan,
                        'status' => 'belum_bayar'
                    ]);
                    $count++;
                }
            }
            DB::commit();
            $this->info("Berhasil membuat tagihan untuk {$count} penghuni.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
        }
    }
}