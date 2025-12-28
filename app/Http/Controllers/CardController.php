<?php

namespace App\Http\Controllers;

use App\Models\AccessCard;
use App\Models\Resident;
use Illuminate\Http\Request;

class CardController extends Controller
{
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

    public function create()
    {
        // Kita butuh daftar penghuni untuk dropdown
        $residents = Resident::where('is_active', true)->orderBy('nama')->get();
        return view('cards.create', compact('residents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_number' => 'required|unique:access_cards,card_number',
            'kategori'    => 'required|in:penghuni,tamu',
            // resident_id wajib diisi jika kategori = penghuni
            'resident_id' => 'nullable|required_if:kategori,penghuni|exists:residents,id', 
            'is_active'   => 'required|boolean',
        ]);

        AccessCard::create($request->all());

        return redirect()->route('cards.index')->with('success', 'Kartu akses berhasil didaftarkan.');
    }

    public function edit(AccessCard $card)
    {
        $residents = Resident::where('is_active', true)->orderBy('nama')->get();
        return view('cards.edit', compact('card', 'residents'));
    }

    public function update(Request $request, AccessCard $card)
    {
        $request->validate([
            'card_number' => 'required|unique:access_cards,card_number,' . $card->id,
            'kategori'    => 'required|in:penghuni,tamu',
            'resident_id' => 'nullable|required_if:kategori,penghuni|exists:residents,id',
            'is_active'   => 'required|boolean',
        ]);

        $card->update($request->all());

        return redirect()->route('cards.index')->with('success', 'Data kartu berhasil diperbarui.');
    }

    public function destroy(AccessCard $card)
    {
        $card->delete();
        return redirect()->route('cards.index')->with('success', 'Kartu akses berhasil dihapus.');
    }
}