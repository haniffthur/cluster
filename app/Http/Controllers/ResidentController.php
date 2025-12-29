<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
class ResidentController extends Controller
{
    /**
     * Tampilkan daftar penghuni (Read)
     */
    public function index(Request $request)
    {
        // Ambil data dengan fitur pencarian sederhana
        $query = Resident::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('no_pelanggan', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%");
        }

        // Paginate 10 data per halaman
        $residents = $query->latest()->paginate(10);

        return view('residents.index', compact('residents'));
    }

    /**
     * Tampilkan form tambah data (Create View)
     */
    public function create()
    {
        return view('residents.create');
    }

    /**
     * Simpan data baru ke database (Store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_pelanggan' => 'required|unique:residents,no_pelanggan',
            'nama' => 'required|string|max:255',
            'no_va' => 'nullable|numeric',
            'alamat' => 'required|string',
            'iuran_bulanan' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        Resident::create($request->all());

        return redirect()->route('residents.index')
                         ->with('success', 'Data penghuni berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit data (Edit View)
     */
    public function edit(Resident $resident)
    {
        return view('residents.edit', compact('resident'));
    }

    /**
     * Update data ke database (Update)
     */
    public function update(Request $request, Resident $resident)
    {
        $request->validate([
            // Ignore ID saat cek unique agar tidak error saat update diri sendiri
            'no_pelanggan' => 'required|unique:residents,no_pelanggan,' . $resident->id,
            'nama' => 'required|string|max:255',
            'no_va' => 'nullable|numeric',
            'alamat' => 'required|string',
            'iuran_bulanan' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $resident->update($request->all());

        return redirect()->route('residents.index')
                         ->with('success', 'Data penghuni berhasil diperbarui.');
    }

    /**
     * Hapus data (Delete)
     */
    public function destroy(Resident $resident)
    {
        try {
            $resident->delete();
            return redirect()->route('residents.index')->with('success', 'Data penghuni berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data. Mungkin data masih terikat dengan tagihan/kartu.');
        }
    }
    public function importForm()
    {
        return view('residents.import');
    }

    /**
     * Proses File CSV
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048' // Max 2MB
        ]);

        if (!$request->hasFile('file_csv') || !$request->file('file_csv')->isValid()) {
            return back()->with('error', 'File CSV korup atau gagal diupload.');
        }

        $file = $request->file('file_csv');
        $path = $file->getRealPath();

        // VALIDASI PATH KOSONG (Penyebab Error Sebelumnya)
        if (!$path) {
            return back()->with('error', 'Gagal membaca lokasi file temporary. Coba upload ulang.');
        }

        // Membaca CSV
        $data = array_map('str_getcsv', file($path));
        
        if (empty($data)) {
            return back()->with('error', 'File CSV kosong.');
        }

        $header = array_shift($data); // Skip baris pertama (Header)

        DB::beginTransaction();
        try {
            $countResident = 0;
            $countBill = 0;

            foreach ($data as $row) {
                // Index: 0=NO, 1=NO VA, 2=NO PELANGGAN, 3=Nama, 4=ALAMAT, 6=PERIODE, 10=TOTAL CHARGE
                
                // Pastikan baris memiliki data minimal
                if (count($row) < 5) continue;

                $noVa = $row[1] ?? null;
                $noPelanggan = $row[2];
                $nama = $row[3];
                $alamat = $row[4];
                
                // Bersihkan format angka
                $totalCharge = filter_var($row[10] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); 

                // 1. Simpan/Update Penghuni
                $resident = Resident::updateOrCreate(
                    ['no_pelanggan' => $noPelanggan], 
                    [
                        'nama' => $nama,
                        'no_va' => $noVa,
                        'alamat' => $alamat,
                        'iuran_bulanan' => 0, 
                        'is_active' => true
                    ]
                );
                $countResident++;

                // 2. Masukkan Tunggakan
                if ($totalCharge > 0) {
                    $billExists = Bill::where('resident_id', $resident->id)
                                    ->where('bulan', 'Total')
                                    ->where('tahun', 'Lama')
                                    ->exists();
                    
                    if (!$billExists) {
                        Bill::create([
                            'resident_id' => $resident->id,
                            'bulan' => 'Total',
                            'tahun' => 'Lama',
                            'jumlah_tagihan' => $totalCharge,
                            'status' => 'belum_bayar',
                            'created_at' => now(),
                        ]);
                        $countBill++;
                    }
                }
            }

            DB::commit();
            return redirect()->route('residents.index')->with('success', "Import Selesai! $countResident warga diproses, $countBill data tunggakan dibuat.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
    public function show(Resident $resident)
{
    // Ambil data penghuni beserta kartu akses dan tagihannya
    $resident->load(['accessCards', 'bills' => function($query) {
        $query->latest(); // Urutkan tagihan dari yang terbaru
    }]);

    return view('residents.show', compact('resident'));
}
}