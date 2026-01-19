@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')

<style>
    /* Styling Dashboard */
    .card-modern {
        border: none; border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.05);
        transition: transform 0.3s ease; overflow: hidden;
    }
    .card-modern:hover { transform: translateY(-5px); }
    .icon-bg {
        position: absolute; right: -10px; bottom: -10px;
        font-size: 5rem; opacity: 0.15; transform: rotate(-15deg);
    }
    .avatar-circle {
        width: 35px; height: 35px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 14px; color: white;
    }
    .chart-area { position: relative; height: 320px; width: 100%; }
    .chart-pie { position: relative; height: 280px; width: 100%; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard Overview</h1>
    <span class="bg-white px-3 py-2 rounded-pill shadow-sm text-primary font-weight-bold small">
        <i class="far fa-calendar-alt mr-2"></i> {{ now()->translatedFormat('l, d F Y') }}
    </span>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity:0.8">Total Unit</div>
                <div class="h2 mb-0 font-weight-bold">{{ $totalResidents }}</div>
                <i class="fas fa-home icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white;">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity:0.8">Infrastruktur</div>
                <div class="h2 mb-0 font-weight-bold">{{ $totalDevices }} <span style="font-size:1rem">Unit</span></div>
                <i class="fas fa-network-wired icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color:white;">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity:0.8">Traffic (24H)</div>
                <div class="h2 mb-0 font-weight-bold">{{ $todaysGateActivity }}</div>
                <i class="fas fa-car icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); color:white;">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity:0.8">Kartu Akses</div>
                <div class="h2 mb-0 font-weight-bold">{{ $activeCards }}</div>
                <i class="fas fa-id-card icon-bg"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card card-modern mb-4 bg-white">
            <div class="card-body">
                <h6 class="font-weight-bold text-gray-800 mb-3">Tren Akses 7 Hari</h6>
                <div class="chart-area"><canvas id="trafficChart"></canvas></div>
            </div>
        </div>

        <div class="card card-modern mb-4 bg-white">
            <div class="card-header py-3 bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-gray-800">Aktivitas Terkini</h6>
                <a href="{{ route('access-logs.index') }}" class="small font-weight-bold text-primary">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush mb-0">
                        <thead class="thead-light">
                            <tr><th>Waktu</th><th>User</th><th class="text-center">Arah</th><th class="text-right">Foto</th></tr>
                        </thead>
                        <tbody>
                            @forelse($latestLogs as $log)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($log->tapped_at)->format('H:i') }}</span>
                                    <small class="text-muted ml-1">{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $name = $log->card && $log->card->resident ? $log->card->resident->nama : ($log->card && $log->card->kategori == 'security' ? 'Security' : 'Tamu');
                                            $initial = strtoupper(substr($name, 0, 1));
                                            $bg = $log->card && $log->card->kategori == 'penghuni' ? '#4e73df' : ($log->card && $log->card->kategori == 'security' ? '#5a5c69' : '#858796');
                                        @endphp
                                        <div class="avatar-circle mr-2 shadow-sm" style="background-color: {{ $bg }};">{{ $initial }}</div>
                                        <div>
                                            <div class="font-weight-bold text-dark small">{{Str::limit($name, 15)}}</div>
                                            <div class="small text-muted">{{ $log->termno }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $log->io_status == 1 ? 'success' : 'warning' }} px-2">
                                        {{ $log->io_status == 1 ? 'IN' : 'OUT' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if($log->snapshot_path)
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm btn-snapshot-dash" 
                                            data-img="{{ asset('storage/' . $log->snapshot_path) }}"
                                            data-title="{{ $name }}">
                                            <i class="fas fa-camera text-primary"></i>
                                        </button>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 small">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card card-modern mb-4 bg-white">
            <div class="card-body">
                <h6 class="font-weight-bold text-gray-800 mb-3">Status Pembayaran</h6>
                <div class="chart-pie"><canvas id="paymentPieChart"></canvas></div>
                <div class="mt-3 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> Lunas</span>
                    <span class="mr-2"><i class="fas fa-circle text-danger"></i> Nunggak</span>
                </div>
            </div>
        </div>
        
        <div class="card card-modern bg-white">
            <div class="card-body">
                <h6 class="font-weight-bold text-gray-800 mb-3">Aksi Cepat</h6>
                <a href="{{ route('residents.create') }}" class="btn btn-primary btn-block text-left mb-2 shadow-sm" style="border-radius:10px;">
                    <i class="fas fa-user-plus mr-2"></i> Tambah Penghuni
                </a>
                <a href="{{ route('cards.create') }}" class="btn btn-info btn-block text-left mb-2 shadow-sm" style="border-radius:10px;">
                    <i class="fas fa-id-card mr-2"></i> Tambah Kartu
                </a>
                <a href="{{ route('bills.create') }}" class="btn btn-warning btn-block text-left shadow-sm" style="border-radius:10px;">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Catat Tagihan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dashboardSnapModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="position-relative">
                <img src="" id="dashModalImage" class="img-fluid w-100">
                <button type="button" class="close position-absolute text-white" data-dismiss="modal" 
                        style="top: 15px; right: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="position-absolute w-100 p-3" style="bottom: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                    <p class="text-white mb-0 font-weight-bold" id="dashModalTitle"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Config Chart Traffic & Pie (Sama seperti sebelumnya, copy paste saja bagian chart)
    var ctxTraffic = document.getElementById("trafficChart").getContext('2d');
    var gradientTraffic = ctxTraffic.createLinearGradient(0, 0, 0, 400);
    gradientTraffic.addColorStop(0, 'rgba(78, 115, 223, 0.5)');
    gradientTraffic.addColorStop(1, 'rgba(78, 115, 223, 0.0)');

    new Chart(ctxTraffic, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: "Kendaraan", lineTension: 0.3, backgroundColor: gradientTraffic,
                borderColor: "rgba(78, 115, 223, 1)", pointRadius: 3, borderWidth: 2,
                data: @json($chartValues)
            }]
        },
        options: { maintainAspectRatio: false, scales: { x: {grid:{display:false}}, y: {grid:{borderDash:[2]}} }, plugins:{legend:{display:false}} }
    });

    var ctxPie = document.getElementById("paymentPieChart");
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ["Lunas", "Nunggak"],
            datasets: [{
                data: [{{ $countLunas }}, {{ $countNunggak }}],
                backgroundColor: ['#1cc88a', '#e74a3b'], hoverBorderColor: "rgba(234, 236, 244, 1)"
            }]
        },
        options: { maintainAspectRatio: false, cutout: '75%', plugins:{legend:{display:false}} }
    });

    // LOGIC MODAL DASHBOARD
    $(document).on('click', '.btn-snapshot-dash', function() {
        let img = $(this).data('img');
        let title = $(this).data('title');
        $('#dashModalImage').attr('src', img);
        $('#dashModalTitle').text(title);
        $('#dashboardSnapModal').modal('show');
    });
</script>
@endpush