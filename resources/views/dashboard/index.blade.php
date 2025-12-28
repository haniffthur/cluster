@extends('layouts.app')

@section('title', 'Dashboard Monitoring')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard & Monitoring</h1>
        {{-- Tombol Report (Opsional, hanya hiasan dulu) --}}
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Penghuni</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalResidents }} Unit</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Tunggakan (Unpaid)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Akses Gate (24 Jam)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todaysGateActivity }} x</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Kartu RFID Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCards }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Log Akses Gate Terakhir</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Opsi:</div>
                            <a class="dropdown-item" href="#">Lihat Semua Log</a>
                            <a class="dropdown-item" href="#">Refresh Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 20%">Waktu</th>
                                    <th style="width: 15%">Gate (TermNo)</th>
                                    <th>Nomor Kartu / Identitas</th>
                                    <th style="width: 15%">Arah</th>
                                    <th style="width: 15%">Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestLogs as $log)
                                    <tr>
                                        {{-- Format Tanggal agar lebih mudah dibaca --}}
                                        <td class="align-middle">
                                            {{ \Carbon\Carbon::parse($log->tapped_at)->format('d M Y, H:i:s') }}
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $log->termno }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold text-dark">{{ $log->card_number }}</span>
                                                {{-- Jika relasi kartu ke resident ada, tampilkan nama --}}
                                                @if($log->card && $log->card->resident)
                                                    <small class="text-muted">{{ $log->card->resident->nama }}</small>
                                                @else
                                                    <small class="text-muted font-italic">Tamu / Tidak Terdaftar</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($log->io_status == 1)
                                                <span class="badge badge-success px-3 py-2">
                                                    <i class="fas fa-arrow-right mr-1"></i> MASUK
                                                </span>
                                            @else
                                                <span class="badge badge-warning px-3 py-2">
                                                    <i class="fas fa-arrow-left mr-1"></i> KELUAR
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($log->snapshot_path)
                                                {{-- Tombol Modal untuk lihat foto --}}
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#photoModal{{ $log->id }}">
                                                    <i class="fas fa-camera"></i> Lihat
                                                </button>

                                                {{-- Modal Foto --}}
                                                <div class="modal fade" id="photoModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Snapshot Gate</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ asset('storage/' . $log->snapshot_path) }}" class="img-fluid rounded" alt="Snapshot">
                                                                <p class="mt-2 text-muted small">{{ $log->tapped_at }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">- No Image -</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500">
                                            <i class="fas fa-info-circle mb-2"></i><br>
                                            Belum ada aktivitas gate hari ini.
                                        </td>
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