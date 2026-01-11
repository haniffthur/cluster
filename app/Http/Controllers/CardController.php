<?php

namespace App\Http\Controllers;

use App\Models\AccessCard;
use App\Models\Resident;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Menampilkan daftar kartu
     */
    public function index(Request $request)
    {
        // Ambil data kartu dengan relasi penghuni
        $query = AccessCard::with('resident');

        // Fitur Pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('card_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('resident', function($q) use ($search) {
                      $q->where('nama', 'LIKE', "%{$search}%");
                  });
        }

        $cards = $query->latest()->paginate(10);

        return view('cards.index', compact('cards'));
    }

    /**
     * Form tambah kartu baru
     */
    public function create()
    {
        // LOGIC KHUSUS: Hanya ambil penghuni yang BELUM punya kartu
        // Menggunakan 'doesntHave' untuk memfilter
        $residents = Resident::where('is_active', true)
                             ->doesntHave('accessCards') 
                             ->orderBy('nama')
                             ->get();

        return view('cards.create', compact('residents'));
    }

    /**
     * Simpan kartu baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_number' => 'required|unique:access_cards,card_number',
            'kategori'    => 'required|in:penghuni,tamu',
            'resident_id' => 'nullable|required_if:kategori,penghuni|exists:residents,id', 
            'is_active'   => 'required|boolean',
        ]);

        // VALIDASI GANDA: Pastikan penghuni ini benar-benar belum punya kartu
        if ($request->kategori == 'penghuni') {
            $exists = AccessCard::where('resident_id', $request->resident_id)->exists();
            if ($exists) {
                return back()->with('error', 'Penghuni ini sudah memiliki kartu! Satu penghuni hanya boleh punya satu kartu.')->withInput();
            }
        }

        AccessCard::create($request->all());

        return redirect()->route('cards.index')->with('success', 'Kartu akses berhasil didaftarkan.');
    }

    /**
     * Form edit kartu
     */
    public function edit(AccessCard $card)
    {
        // Untuk edit, kita tampilkan semua penghuni, 
        // tapi idealnya kita tandai mana yang sudah punya kartu.
        // Agar simpel, kita ambil semua yang aktif.
        $residents = Resident::where('is_active', true)->orderBy('nama')->get();
        
        return view('cards.edit', compact('card', 'residents'));
    }

    /**
     * Update data kartu
     */
    public function update(Request $request, AccessCard $card)
    {
        $request->validate([
            'card_number' => 'required|unique:access_cards,card_number,' . $card->id,
            'kategori'    => 'required|in:penghuni,tamu',
            'resident_id' => 'nullable|required_if:kategori,penghuni|exists:residents,id',
            'is_active'   => 'required|boolean',
        ]);

        // Cek jika user mencoba mengganti pemilik ke orang lain yang SUDAH punya kartu
        if ($request->kategori == 'penghuni' && $request->resident_id != $card->resident_id) {
            $isTaken = AccessCard::where('resident_id', $request->resident_id)->exists();
            if ($isTaken) {
                return back()->with('error', 'Penghuni yang Anda pilih sudah memiliki kartu lain.')->withInput();
            }
        }

        $card->update($request->all());

        return redirect()->route('cards.index')->with('success', 'Data kartu berhasil diperbarui.');
    }

    /**
     * Hapus kartu
     */
    public function destroy(AccessCard $card)
    {
        $card->delete();
        return redirect()->route('cards.index')->with('success', 'Kartu akses berhasil dihapus.');
    }
}