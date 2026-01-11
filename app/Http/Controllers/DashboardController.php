<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resident;
// use App\Models\Bill; // Hapus atau biarkan jika chart masih butuh
use App\Models\Device; // <--- Tambahkan Model Device
use App\Models\GateLog;
use App\Models\AccessCard;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Stats Utama
        $totalResidents = Resident::count();
        
        // --- GANTI KE NON-MONEY ---
        $totalDevices = Device::count(); // Menghitung jumlah mesin gate
        // --------------------------

        $todaysGateActivity = GateLog::whereDate('tapped_at', Carbon::today())->count();
        $activeCards = AccessCard::where('is_active', true)->count();
        
        $latestLogs = GateLog::latest('tapped_at')->take(5)->get();

        // 2. Data Chart Traffic
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartValues[] = GateLog::whereDate('tapped_at', $date)->count();
        }

        // 3. Data Chart Keuangan (Jika chart pie mau tetap ada, biarkan. Jika tidak, hapus juga)
        // Kita biarkan chart-nya saja yg ada uangnya (opsional), tapi Card Stats kita ubah.
        $countLunas = \App\Models\Bill::where('status', 'lunas')->count();
        $countNunggak = \App\Models\Bill::where('status', 'belum_bayar')->count();

        return view('dashboard.index', compact(
            'totalResidents',
            'totalDevices', // Kirim variabel baru
            'todaysGateActivity',
            'activeCards',
            'latestLogs',
            'chartLabels',
            'chartValues',
            'countLunas',
            'countNunggak'
        ));
    }
}