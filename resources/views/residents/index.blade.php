@extends('layouts.app')

@section('title', 'Data Penghuni')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Data Penghuni Cluster</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Warga</h6>
            <div>
                {{-- Tombol Import Baru --}}
                <a href="{{ route('residents.import') }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-csv"></i> Import CSV
                </a>

                <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Penghuni
                </a>
            </div>
        </div>
        <div class="card-body">

            {{-- Notifikasi Sukses/Error --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Form Pencarian --}}
            <form action="{{ route('residents.index') }}" method="GET" class="form-inline mb-3">
                <input type="text" name="search" class="form-control mr-2" placeholder="Cari Nama/No Pelanggan..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Cari</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No Pelanggan</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                       
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($residents as $resident)
                            <tr>
                                <td>{{ $resident->no_pelanggan }}<br><small class="text-muted">VA:
                                        {{ $resident->no_va ?? '-' }}</small></td>
                                <td class="font-weight-bold">{{ $resident->nama }}</td>
                                <td>{{Str::limit($resident->alamat, 50)}}</td>
                                <td>
                                    @if($resident->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('residents.show', $resident->id) }}" class="btn btn-info btn-sm"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('residents.edit', $resident->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- Tombol Delete dengan konfirmasi SweetAlert sederhana (inline) --}}
                                    <form action="{{ route('residents.destroy', $resident->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
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
                                <td colspan="6" class="text-center">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Link --}}
            <div class="d-flex justify-content-end">
                {{ $residents->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection