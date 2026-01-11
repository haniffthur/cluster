@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $pageTitle }}</h1>

        {{-- Tombol Generate Tagihan Massal (Hanya muncul di halaman Riwayat) --}}
        @if(!$isArrearsPage)
            <div>
                <form action="{{ route('bills.generate') }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Generate tagihan bulan ini untuk semua warga aktif?');">
                    @csrf
                    <!-- <button type="submit" class="btn btn-sm btn-info shadow-sm mr-2">
                        <i class="fas fa-sync-alt fa-sm text-white-50"></i> Generate Bulan Ini
                    </button> -->
                </form>

                <a href="{{ route('bills.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Buat Tagihan Manual
                </a>
            </div>
        @endif
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Tagihan</h6>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Filter & Search Bar --}}
            <form action="{{ $isArrearsPage ? route('tunggakan.index') : route('ipl_bills.index') }}" method="GET"
                class="mb-4">
                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama / No Pelanggan..."
                            value="{{ request('search') }}">
                    </div>

                    {{-- Filter Bulan/Tahun hanya relevan jika bukan halaman Tunggakan (karena tunggakan menampilkan semua
                    utang lama) --}}
                    @if(!$isArrearsPage)
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Bulan</label>
                            <select name="bulan" class="form-control">
                                <option value="">- Semua -</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}" {{ request('bulan') == sprintf('%02d', $i) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="">- Semua -</option>
                                <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar
                                </option>
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i>
                            Filter</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Periode</th>
                            <th>Pelanggan</th>
                            <th>Jumlah Tagihan</th>
                            <th>Status</th>
                            <th>Tgl Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr class="{{ $bill->status == 'belum_bayar' ? 'table-warning' : '' }}">
                                <td>
                                    {{-- Cek apakah bulan berupa angka (01-12) atau Teks (Total) --}}
                                    @if(is_numeric($bill->bulan))
                                        {{-- Tambahkan (int) untuk memaksa string "01" menjadi angka 1 --}}
                                        <span
                                            class="font-weight-bold">{{ date('F', mktime(0, 0, 0, (int) $bill->bulan, 10)) }}</span>
                                        <br>{{ $bill->tahun }}
                                    @else
                                        {{-- Jika isinya teks "Total" (Data Import CSV) --}}
                                        <span class="badge badge-warning">{{ $bill->bulan }}</span>
                                        <br><small>{{ $bill->tahun }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $bill->resident->nama }}</div>
                                    <small class="text-muted">{{ $bill->resident->no_pelanggan }}</small>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    Rp {{ number_format($bill->jumlah_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($bill->status == 'lunas')
                                        <span class="badge badge-success px-2 py-1">LUNAS</span>
                                    @else
                                        <span class="badge badge-danger px-2 py-1">BELUM BAYAR</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $bill->tanggal_bayar ? \Carbon\Carbon::parse($bill->tanggal_bayar)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @if($bill->status == 'belum_bayar')
                                        {{-- Tombol Bayar (Trigger Modal) --}}
                                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                            data-target="#payModal{{ $bill->id }}">
                                            <i class="fas fa-money-bill-wave"></i> Bayar
                                        </button>

                                        {{-- Modal Konfirmasi Bayar --}}
                                        <div class="modal fade" id="payModal{{ $bill->id }}" tabindex="-1" role="dialog"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('bills.pay', $bill->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <p>Anda akan melakukan konfirmasi pembayaran untuk:</p>
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <td>Nama</td>
                                                                    <td>: <b>{{ $bill->resident->nama }}</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Periode</td>
                                                                    <td>: {{ $bill->bulan }}/{{ $bill->tahun }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Nominal</td>
                                                                    <td>: <b>Rp
                                                                            {{ number_format($bill->jumlah_tagihan, 0, ',', '.') }}</b>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <div class="alert alert-info small">
                                                                Pastikan uang fisik/transfer sudah diterima sebelum menekan tombol
                                                                konfirmasi.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success">Konfirmasi Lunas</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled><i class="fas fa-check"></i>
                                            Selesai</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data tagihan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $bills->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection