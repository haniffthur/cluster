@extends('layouts.app')

@section('title', 'Data Gate')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Master Data Gate</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Mesin Gate</h6>
        <a href="{{ route('gates.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Gate
        </a>
    </div>
    <div class="card-body">
        
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>TermNo (ID Mesin)</th>
                        <th>Lokasi</th>
                        <th>Jml. Config IO</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td class="font-weight-bold text-primary">{{ $device->termno }}</td>
                        <td>{{ $device->lokasi }}</td>
                        <td>
                            {{-- Menghitung berapa IO yang sudah disetting --}}
                            <span class="badge badge-info">{{ $device->ios->count() }} Config</span>
                        </td>
                        <td>
                            {{-- TOMBOL SHOW (CONFIG) --}}
                            <a href="{{ route('gates.show', $device->id) }}" class="btn btn-info btn-sm" title="Konfigurasi IO & Kamera">
                                <i class="fas fa-cogs"></i> 
                            </a>

                            {{-- TOMBOL EDIT (Hanya edit nama/lokasi) --}}
                            <a href="{{ route('gates.edit', $device->id) }}" class="btn btn-warning btn-sm" title="Edit Nama">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- TOMBOL DELETE --}}
                            <form action="{{ route('gates.destroy', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus gate ini beserta semua settingannya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada gate terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection