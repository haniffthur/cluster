<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceIo;
use Illuminate\Http\Request;

class DeviceIoController extends Controller
{
    /**
     * Simpan Settingan IO Baru ke Mesin Tertentu
     */
    public function store(Request $request, Device $device)
    {
        // PERBAIKAN DI SINI: Ubah 'cam_user' jadi 'cam_username'
        $request->validate([
            'io_status'     => 'required|in:1,0',
            'label'         => 'required|string',
            'cam_ip'        => 'required|ipv4',
            'cam_username'  => 'required', // SEBELUMNYA: 'cam_user'
            'cam_password'  => 'required',
        ]);

        // Cek apakah IO ini sudah ada di mesin ini? (Biar gak dobel IO=1)
        $exists = DeviceIo::where('device_id', $device->id)
                          ->where('io_status', $request->io_status)
                          ->exists();
        
        if($exists) {
            return back()->with('error', 'IO ' . $request->io_status . ' sudah terdaftar di mesin ini.');
        }

        // Simpan via relasi
        // Karena nama input validasi sekarang 'cam_username', 
        // maka $request->all() akan berisi key 'cam_username' yang cocok dengan database.
        $device->ios()->create($request->all());

        return back()->with('success', 'Konfigurasi IO berhasil ditambahkan.');
    }

    /**
     * Hapus Settingan IO
     */
    public function destroy($id)
    {
        $io = DeviceIo::findOrFail($id);
        $io->delete();
        return back()->with('success', 'Konfigurasi IO berhasil dihapus.');
    }
}