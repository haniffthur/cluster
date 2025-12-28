<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Resident;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * Menampilkan Semua Tagihan (Menu: Tagihan IPL)
     */
    public function index(Request $request)
    {
        $query = Bill::with('resident')->latest();

        // Filter Pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('resident', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('no_pelanggan', 'LIKE', "%{$search}%");
            });
        }

        // Filter Bulan/Tahun
        if ($request->filled('bulan')) $query->where('bulan', $request->bulan);
        if ($request->filled('tahun')) $query->where('tahun', $request->tahun);
        if ($request->filled('status')) $query->where('status', $request->status);

        $bills = $query->paginate(10);
        $pageTitle = 'Riwayat Tagihan IPL';
        $isArrearsPage = false; // Penanda ini halaman riwayat

        return view('bills.index', compact('bills', 'pageTitle', 'isArrearsPage'));
    }

    /**
     * Menampilkan Khusus Tunggakan (Menu: Data Tunggakan)
     */
    public function arrears(Request $request)
    {
        // Hanya ambil yang statusnya belum_bayar
        $query = Bill::with('resident')
                     ->where('status', 'belum_bayar')
                     ->orderBy('id', 'asc'); // Urutkan dari yang terlama nunggaknya

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('resident', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%");
            });
        }

        $bills = $query->paginate(10);
        $pageTitle = 'Data Tunggakan Warga';
        $isArrearsPage = true; // Penanda ini halaman tunggakan

        // Kita gunakan view yang sama (bills/index) biar hemat file
        return view('bills.index', compact('bills', 'pageTitle', 'isArrearsPage'));
    }

    /**
     * Form Tambah Tagihan Manual (Single)
     */
    public function create()
    {
        $residents = Resident::where('is_active', true)->orderBy('nama')->get();
        return view('bills.create', compact('residents'));
    }

    /**
     * Simpan Tagihan Manual
     */
    public function store(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'bulan' => 'required',
            'tahun' => 'required',
            'jumlah_tagihan' => 'required|numeric|min:0',
        ]);

        // Cek duplikasi
        $exists = Bill::where('resident_id', $request->resident_id)
                      ->where('bulan', $request->bulan)
                      ->where('tahun', $request->tahun)
                      ->exists();

        if($exists) {
            return back()->with('error', 'Tagihan untuk periode tersebut sudah ada!');
        }

        Bill::create([
            'resident_id' => $request->resident_id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'jumlah_tagihan' => $request->jumlah_tagihan,
            'status' => 'belum_bayar'
        ]);

        return redirect()->route('ipl_bills.index')->with('success', 'Tagihan berhasil dibuat manual.');
    }

    /**
     * Proses Bayar Manual (Admin terima cash/transfer)
     */
    public function markAsPaid(Request $request, Bill $bill)
    {
        $bill->update([
            'status' => 'lunas',
            'tanggal_bayar' => now()
        ]);

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi. Status: LUNAS.');
    }

    /**
     * Trigger Generate Tagihan Bulan Ini (Massal)
     * Sama logikanya dengan Command Scheduler
     */
    public function generateBills()
    {
        $bulanIni = Carbon::now()->format('m');
        $tahunIni = Carbon::now()->format('Y');
        $residents = Resident::where('is_active', true)->where('iuran_bulanan', '>', 0)->get();
        $count = 0;

        foreach ($residents as $resident) {
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

        return back()->with('success', "Berhasil generate {$count} tagihan untuk periode $bulanIni-$tahunIni.");
    }
}