@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')

<style>
    /* Styling Tambahan untuk Chart */
    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 35px rgba(0,0,0,0.1);
    }
    .icon-bg {
        position: absolute;
        right: -10px; bottom: -10px;
        font-size: 5rem; opacity: 0.15;
        transform: rotate(-15deg);
    }
    .avatar-circle {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 16px; color: white;
    }
    /* Chart Container agar responsif */
    .chart-area { position: relative; height: 320px; width: 100%; }
    .chart-pie { position: relative; height: 280px; width: 100%; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Dashboard & Analytics</h1>
        <p class="mb-0 text-muted small">Ringkasan performa cluster dan aktivitas keamanan.</p>
    </div>
    <div class="d-none d-sm-block">
        <span class="bg-white px-3 py-2 rounded-pill shadow-sm text-primary font-weight-bold small">
            <i class="far fa-calendar-alt mr-2"></i> {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
</div>

<div class="row mb-4">
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8;">Total Unit</div>
                <div class="h2 mb-0 font-weight-bold">{{ $totalResidents }}</div>
                <i class="fas fa-home icon-bg"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8;">Infrastruktur</div>
                <div class="h2 mb-0 font-weight-bold">{{ $totalDevices }} <span style="font-size: 1rem">Unit</span></div>
                <div class="mt-2 small" style="opacity: 0.8;">Gate & CCTV Aktif</div>
                
                <i class="fas fa-network-wired icon-bg"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100 bg-gradient-warning text-white" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8;">Traffic Gate (24H)</div>
                <div class="h2 mb-0 font-weight-bold">{{ $todaysGateActivity }}</div>
                <i class="fas fa-car icon-bg"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-modern h-100 bg-gradient-info text-white" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
            <div class="card-body position-relative">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="opacity: 0.8;">Kartu Akses</div>
                <div class="h2 mb-0 font-weight-bold">{{ $activeCards }}</div>
                <i class="fas fa-id-card icon-bg"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-8 col-lg-7">
        
        <div class="card card-modern mb-4 bg-white">
            <div class="card-header py-3 bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-gray-800">Tren Akses Gate (7 Hari Terakhir)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card card-modern mb-4 bg-white">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                <h6 class="m-0 font-weight-bold text-gray-800">
                    Aktivitas Terkini <span class="badge badge-light text-danger ml-2 px-2 border border-danger">LIVE</span>
                </h6>
                <a href="{{ route('access-logs.index') }}" class="small font-weight-bold text-primary">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">Waktu</th>
                                <th class="border-0">User</th>
                                <th class="border-0 text-center">Arah</th>
                                <th class="border-0 text-right">Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestLogs as $log)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($log->tapped_at)->format('H:i') }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $name = $log->card && $log->card->resident ? $log->card->resident->nama : 'Tamu';
                                            $color = $log->card && $log->card->resident ? '#4e73df' : '#858796';
                                            $initial = strtoupper(substr($name, 0, 1));
                                        @endphp
                                        <div class="avatar-circle mr-2 shadow-sm" style="background-color: {{ $color }}; width:35px; height:35px; font-size:14px;">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark small">{{ $name }}</div>
                                            <div class="small text-muted">{{ $log->termno }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($log->io_status == 1)
                                        <span class="badge badge-success px-2">IN</span>
                                    @else
                                        <span class="badge badge-warning px-2">OUT</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($log->snapshot_path)
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" data-toggle="modal" data-target="#photoModal{{ $log->id }}">
                                            <i class="fas fa-camera text-primary"></i>
                                        </button>
                                        <div class="modal fade" id="photoModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <img src="{{ asset('storage/' . $log->snapshot_path) }}" class="img-fluid rounded">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center small py-3">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        
        <div class="card card-modern mb-4 bg-white">
            <div class="card-header py-3 bg-white border-0">
                <h6 class="m-0 font-weight-bold text-gray-800">Rasio Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-2 pb-2">
                    <canvas id="paymentPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Lunas ({{ $countLunas }})
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Nunggak ({{ $countNunggak }})
                    </span>
                </div>
            </div>
        </div>

        <div class="card card-modern bg-white">
            <div class="card-header py-3 bg-white border-0">
                <h6 class="m-0 font-weight-bold text-gray-800">Jalan Pintas</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('residents.create') }}" class="btn btn-primary btn-block text-left mb-3 shadow-sm" style="border-radius: 10px; height: 50px; display:flex; align-items:center;">
                    <i class="fas fa-user-plus mr-3 ml-2" style="font-size: 1.2rem"></i> <span class="font-weight-bold">Tambah Penghuni</span>
                </a>
                <a href="{{ route('cards.create') }}" class="btn btn-info btn-block text-left mb-3 shadow-sm" style="border-radius: 10px; height: 50px; display:flex; align-items:center;">
                    <i class="fas fa-id-card mr-3 ml-2" style="font-size: 1.2rem"></i> <span class="font-weight-bold">Registrasi Kartu</span>
                </a>
                <a href="{{ route('bills.create') }}" class="btn btn-warning btn-block text-left shadow-sm" style="border-radius: 10px; height: 50px; display:flex; align-items:center;">
                    <i class="fas fa-file-invoice-dollar mr-3 ml-2" style="font-size: 1.2rem"></i> <span class="font-weight-bold">Catat Tagihan</span>
                </a>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- 1. CONFIG AREA CHART (Traffic) ---
    var ctxTraffic = document.getElementById("trafficChart").getContext('2d');
    
    // Bikin Gradient Warna Ungu-Biru
    var gradientTraffic = ctxTraffic.createLinearGradient(0, 0, 0, 400);
    gradientTraffic.addColorStop(0, 'rgba(78, 115, 223, 0.5)'); // Warna Atas
    gradientTraffic.addColorStop(1, 'rgba(78, 115, 223, 0.0)'); // Warna Bawah (Transparan)

    var myTrafficChart = new Chart(ctxTraffic, {
        type: 'line',
        data: {
            // Ambil data dari Controller (Blade Directive)
            labels: @json($chartLabels), 
            datasets: [{
                label: "Kendaraan",
                lineTension: 0.3, // Garis melengkung halus
                backgroundColor: gradientTraffic,
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: @json($chartValues), // Data dari Controller
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
                x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } },
                y: { 
                    ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return value; } },
                    grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: { size: 14 },
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        }
    });

    // --- 2. CONFIG DOUGHNUT CHART (Keuangan) ---
    var ctxPie = document.getElementById("paymentPieChart");
    var myPieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ["Lunas", "Belum Bayar"],
            datasets: [{
                data: [{{ $countLunas }}, {{ $countNunggak }}],
                backgroundColor: ['#1cc88a', '#e74a3b'], // Hijau, Merah
                hoverBackgroundColor: ['#17a673', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%', // Lubang tengah lebih besar (Modern Look)
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
            },
        },
    });
</script>
@endpush