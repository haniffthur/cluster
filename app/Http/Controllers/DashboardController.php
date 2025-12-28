<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resident;
use App\Models\Bill;
use App\Models\GateLog;
use App\Models\AccessCard;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Total Penghuni
        $totalResidents = Resident::count();

        // 2. Hitung Total Tunggakan (Status 'belum_bayar')
        // Menggunakan sum('jumlah_tagihan') atau 'amount' sesuai kolom di tabel bills
        $totalUnpaid = Bill::where('status', 'belum_bayar')->sum('jumlah_tagihan');

        // 3. Hitung Aktivitas Gate Hari Ini
        $todaysGateActivity = GateLog::whereDate('tapped_at', Carbon::today())->count();

        // 4. Hitung Kartu Aktif
        $activeCards = AccessCard::where('is_active', true)->count();

        // 5. Ambil 5 Log Terakhir untuk tabel mini
        $latestLogs = GateLog::latest('tapped_at')->take(5)->get();

        // Kirim semua variabel ke View
        return view('dashboard.index', compact(
            'totalResidents',
            'totalUnpaid',
            'todaysGateActivity',
            'activeCards',
            'latestLogs'
        ));
    }
}