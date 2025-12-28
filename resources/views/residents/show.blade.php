@extends('layouts.app')

@section('title', 'Detail Penghuni')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Penghuni</h1>
    <a href="{{ route('residents.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="row">

    <div class="col-lg-4">
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profil Warga</h6>
            </div>
            <div class="card-body text-center">
                <img class="img-profile rounded-circle mb-3" src="{{ asset('img/undraw_profile.svg') }}" style="width: 100px; height: 100px; object-fit: cover;">
                
                <h4 class="font-weight-bold text-dark mb-1">{{ $resident->nama }}</h4>
                <p class="text-muted mb-3">{{ $resident->no_pelanggan }}</p>
                
                @if($resident->is_active)
                    <span class="badge badge-success px-3 py-2 mb-3">AKTIF</span>
                @else
                    <span class="badge badge-secondary px-3 py-2 mb-3">NON-AKTIF</span>
                @endif

                <div class="text-left mt-4">
                    <h6 class="small font-weight-bold text-secondary text-uppercase">Alamat</h6>
                    <p class="text-dark bg-light p-2 rounded">{{ $resident->alamat }}</p>

                    <h6 class="small font-weight-bold text-secondary text-uppercase mt-3">No. Virtual Account</h6>
                    <p class="text-dark font-weight-bold">{{ $resident->no_va ?? '-' }}</p>

                    <h6 class="small font-weight-bold text-secondary text-uppercase mt-3">Iuran Bulanan</h6>
                    <p class="text-success font-weight-bold">Rp {{ number_format($resident->iuran_bulanan, 0, ',', '.') }}</p>
                </div>
                
                <hr>
                <a href="{{ route('residents.edit', $resident->id) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Profil
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-info">Kartu Akses Terdaftar</h6>
                <a href="{{ route('cards.create') }}" class="btn btn-sm btn-info"><i class="fas fa-plus"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-borderless mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No Kartu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resident->accessCards as $card)
                            <tr>
                                <td class="font-weight-bold">{{ $card->card_number }}</td>
                                <td>
                                    @if($card->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Blokir</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">Belum ada kartu.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Riwayat Tagihan & Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Periode</th>
                                <th>Jumlah Tagihan</th>
                                <th>Status</th>
                                <th>Tgl Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resident->bills as $bill)
                            <tr>
                                <td>
                                    @if($bill->bulan == 'Total')
                                        <span class="badge badge-warning">Tunggakan Lama</span>
                                    @else
                                        {{ $bill->bulan }}/{{ $bill->tahun }}
                                    @endif
                                </td>
                                <td class="text-right">Rp {{ number_format($bill->jumlah_tagihan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($bill->status == 'lunas')
                                        <span class="badge badge-success">Lunas</span>
                                    @else
                                        <span class="badge badge-danger">Belum Bayar</span>
                                    @endif
                                </td>
                                <td>{{ $bill->tanggal_bayar ?? '-' }}</td>
                                <td class="text-center">
                                    @if($bill->status == 'belum_bayar')
                                        <form action="{{ route('bills.pay', $bill->id) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran ini?');">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-success btn-sm btn-circle" title="Bayar Manual">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @else
                                        <i class="fas fa-check-circle text-success"></i>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Tidak ada data tagihan.</td>
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