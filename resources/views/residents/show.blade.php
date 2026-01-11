@extends('layouts.app')

@section('title', 'Detail Warga')

@section('content')

<div class="card shadow-sm mb-4 border-0 overflow-hidden" style="border-radius: 15px;">
    <div class="bg-gradient-primary px-4 py-4" style="background: linear-gradient(45deg, #4e73df, #224abe);">
        <div class="d-flex align-items-center text-white">
            <div class="mr-3">
                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                    {{ strtoupper(substr($resident->nama, 0, 1)) }}
                </div>
            </div>
            <div>
                <h4 class="font-weight-bold mb-0">{{ $resident->nama }}</h4>
                <p class="mb-0 opacity-80 small">{{ $resident->no_pelanggan }} | <i class="fas fa-map-marker-alt ml-1"></i> {{ $resident->alamat }}</p>
            </div>
            <div class="ml-auto">
                <a href="{{ route('residents.edit', $resident->id) }}" class="btn btn-light btn-sm font-weight-bold text-primary shadow-sm" style="border-radius: 20px;">
                    <i class="fas fa-edit mr-1"></i> Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        
        <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-secondary small font-weight-bold text-uppercase">Status Huni</span>
                    @if($resident->is_active)
                        <span class="badge badge-success px-3 py-1 rounded-pill">Aktif</span>
                    @else
                        <span class="badge badge-secondary px-3 py-1 rounded-pill">Non-Aktif</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-secondary small font-weight-bold text-uppercase">Total Tunggakan</span>
                </div>
                @php 
                    $totalTunggakan = $resident->bills->where('status', 'belum_bayar')->sum('jumlah_tagihan');
                @endphp
                <h3 class="font-weight-bold {{ $totalTunggakan > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-id-badge mr-2 text-primary"></i>Akses RFID</h6>
                <a href="{{ route('cards.create') }}" class="btn btn-primary btn-sm rounded-circle shadow-sm" title="Tambah Kartu" style="width: 30px; height: 30px; padding: 4px;">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($resident->accessCards->isEmpty())
                    <div class="text-center py-4 text-muted small">Belum ada kartu terdaftar.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($resident->accessCards as $card)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4">
                            <div>
                                <div class="font-weight-bold text-dark">{{ $card->card_number }}</div>
                                <small class="text-muted">
                                    {{ $card->is_active ? 'Aktif' : 'Blokir' }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('cards.edit', $card->id) }}" class="text-secondary mr-2"><i class="fas fa-cog"></i></a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-warning"></i>Riwayat Keuangan</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" width="100%">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 pl-4 py-3">Periode</th>
                                <th class="border-0 py-3">Nominal</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3 text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resident->bills as $bill)
                            <tr>
                                <td class="pl-4 align-middle">
                                    @if($bill->bulan == 'Total')
                                        <span class="font-weight-bold text-dark">Saldo Awal</span>
                                    @else
                                        <span class="font-weight-bold text-dark">{{ $bill->bulan }}/{{ $bill->tahun }}</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $bill->created_at->format('d M Y') }}</small>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">
                                    Rp {{ number_format($bill->jumlah_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="align-middle text-center">
                                    @if($bill->status == 'lunas')
                                        <span class="badge badge-soft-success text-success px-3 py-2 rounded-pill bg-light">Lunas</span>
                                    @else
                                        <span class="badge badge-soft-danger text-danger px-3 py-2 rounded-pill bg-light">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right pr-4">
                                    @if($bill->status == 'belum_bayar')
                                        <form action="{{ route('bills.pay', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi Lunas?');">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="fas fa-check mr-1"></i> Bayar
                                            </button>
                                        </form>
                                    @else
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Selesai</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Tidak ada riwayat tagihan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection