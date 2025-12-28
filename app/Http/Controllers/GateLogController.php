<?php

namespace App\Http\Controllers;

use App\Models\GateLog;
use App\Models\Device; // Untuk dropdown filter Gate
use Illuminate\Http\Request;

class GateLogController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relasi 'card' dan 'card.resident' agar query ringan
        $query = GateLog::with(['card.resident'])->latest('tapped_at');

        // 1. Filter Pencarian (No Kartu / Nama Penghuni)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('card_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('card.resident', function($sub) use ($search) {
                      $sub->where('nama', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 2. Filter Gate (TermNo)
        if ($request->filled('termno')) {
            $query->where('termno', $request->termno);
        }

        // 3. Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tapped_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tapped_at', '<=', $request->end_date);
        }

        // Pagination 20 data per halaman
        $logs = $query->paginate(20);

        // Ambil data device untuk dropdown filter
        $gates = Device::select('termno', 'lokasi')->get();

        return view('logs.index', compact('logs', 'gates'));
    }

    /**
     * Hapus log (Opsional: Misal untuk bersih-bersih data lama)
     */
    public function destroy($id)
    {
        $log = GateLog::findOrFail($id);
        
        // Hapus file foto jika ada
        if ($log->snapshot_path && \Storage::disk('public')->exists($log->snapshot_path)) {
            \Storage::disk('public')->delete($log->snapshot_path);
        }

        $log->delete();

        return back()->with('success', 'Data log berhasil dihapus.');
    }
    public function getLogsAjax(Request $request)
{
    // Ambil log terbaru (misal 10 terakhir)
    $logs = GateLog::with(['card.resident'])->latest('tapped_at')->take(10)->get();

    // Kita render partial view (hanya bagian <tr>...</tr>)
    // agar javascript tinggal tempel HTML-nya
    $html = view('logs.partials.log-rows', compact('logs'))->render();

    return response()->json(['html' => $html]);
}
}