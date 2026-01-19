@extends('layouts.app')

@section('title', 'Data Kartu Akses')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Manajemen Kartu RFID</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kartu Terdaftar</h6>
            <a href="{{ route('cards.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Registrasi Kartu Baru
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Search Form --}}
            <form action="{{ route('cards.index') }}" method="GET" class="form-inline mb-3">
                <input type="text" name="search" class="form-control mr-2" placeholder="Cari No Kartu / Pemilik..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Cari</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nomor Kartu (UID)</th>
                            <th>Kategori</th>
                            <th>Pemilik / Penghuni</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $card->card_number }}</td>
                                <td>
                                    @if($card->kategori == 'penghuni')
                                        <span class="badge badge-info">Penghuni</span>
                                    @elseif($card->kategori == 'security')
                                    <span class="badge badge-dark">Security</span> @else
                                        <span class="badge badge-secondary">Tamu</span>
                                    @endif
                                </td>
                                <td>
                                    @if($card->resident)
                                        {{ $card->resident->nama }} <br>
                                        <small class="text-muted">{{ $card->resident->alamat }}</small>
                                    @else
                                        <span class="text-muted font-italic">- Tidak terikat penghuni -</span>
                                    @endif
                                </td>
                                <td>
                                    @if($card->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Blokir</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cards.edit', $card->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('cards.destroy', $card->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus kartu ini?');">
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
                                <td colspan="5" class="text-center">Belum ada kartu terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $cards->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection