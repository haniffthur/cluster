<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('termno', 'LIKE', "%{$search}%")
                  ->orWhere('lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('cam_ip', 'LIKE', "%{$search}%");
        }

        $devices = $query->latest()->paginate(10);

        return view('gates.index', compact('devices'));
    }

    public function create()
    {
        return view('gates.create');
    }

    public function store(Request $request)
{
    // Validasi HANYA TermNo dan Lokasi
    $request->validate([
        'termno' => 'required|unique:devices,termno|alpha_dash',
        'lokasi' => 'required|string',
    ]);

    // Simpan data dasar
    $device = Device::create([
        'termno' => $request->termno,
        'lokasi' => $request->lokasi,
    ]);

    // Redirect langsung ke halaman SHOW (Detail) agar user bisa langsung setting IO
    return redirect()->route('gates.show', $device->id)
                     ->with('success', 'Gate berhasil didaftarkan. Silakan tambah konfigurasi IO di bawah.');
}

    public function edit(Device $device)
    {
        return view('gates.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'termno' => 'required|alpha_dash|unique:devices,termno,' . $device->id,
            'lokasi' => 'required|string',
            // 'cam_ip' => 'required|ipv4',
            // 'cam_username' => 'required|string',
            // // Password boleh kosong jika tidak ingin diubah (opsional, tapi di sini kita wajibkan dulu biar aman)
            // 'cam_password' => 'required|string',
        ]);

        $device->update($request->all());

        return redirect()->route('gates.index')->with('success', 'Konfigurasi Gate berhasil diperbarui.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('gates.index')->with('success', 'Data Gate berhasil dihapus.');
    }

    public function show(Device $device)
{
    // Load data IO yang sudah ada
    $device->load('ios');
    return view('gates.show', compact('device')); // Kita butuh view show sekarang
}
}