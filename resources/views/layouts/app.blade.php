<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Manajemen Cluster">
    <meta name="author" content="Bina Taruna">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - DjavaCluster</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @stack('styles')

   

<style>
    /* --- CUSTOM SIDEBAR STYLE --- */
    
    /* 1. Background Putih & Efek Blur (Shadow) Kanan */
    .sidebar {
        background-color: #ffffff !important;
        background-image: none !important;
        /* Shadow halus di kanan untuk efek pemisah/blur */
        box-shadow: 6px 0 20px rgba(0, 0, 0, 0.06) !important; 
        z-index: 999; /* Pastikan di atas content */
        border-right: 1px solid rgba(0,0,0,0.02);
    }

    /* 2. Warna Text Menu (Biru) */
    .sidebar .nav-item .nav-link span,
    .sidebar .nav-item .nav-link i {
        color: #4e73df !important; /* Biru Utama SB Admin */
        font-weight: 600; /* Agak tebal biar jelas */
        transition: all 0.2s ease;
    }

    /* 3. Efek Hover (Background Biru Muda + Text Biru Gelap) */
    .sidebar .nav-item .nav-link:hover {
        background-color: #f1f5fd !important; /* Biru sangat muda */
        border-radius: 5px;
        margin: 0 10px; /* Ada jarak dikit kiri kanan */
        width: auto;
    }
    
    .sidebar .nav-item .nav-link:hover span,
    .sidebar .nav-item .nav-link:hover i {
        color: #224abe !important; /* Biru lebih gelap saat hover */
        transform: translateX(3px); /* Efek geser dikit biar dinamis */
    }

    /* 4. Menu Aktif (Sedang dibuka) */
    .sidebar .nav-item.active .nav-link {
        background-color: #e8edfb !important;
        margin: 0 10px;
        width: auto;
        border-radius: 5px;
    }
    .sidebar .nav-item.active .nav-link span,
    .sidebar .nav-item.active .nav-link i {
        color: #224abe !important;
        font-weight: 800;
    }

    /* 5. Brand/Logo Text */
    .sidebar-brand-text {
        color: #224abe !important;
        font-weight: 800;
        letter-spacing: 1px;
    }
    
    /* Icon Logo */
    .sidebar-brand-icon i {
        color: #4e73df !important;
    }

    /* 6. Judul Group (Master Data, dll) */
    .sidebar-heading {
        color: #b0b9ce !important; /* Abu kebiruan biar rapi */
        font-weight: 700;
    }

    /* 7. Garis Pemisah */
    .sidebar .sidebar-divider {
        border-top: 1px solid #f1f3f8 !important;
    }
    
    /* 8. Panah Dropdown (Collapse Arrow) */
    .sidebar .nav-item .nav-link::after {
        color: #4e73df !important; /* Panah ikut biru */
    }

    /* Fix tampilan mobile agar shadow tidak aneh */
    @media (max-width: 768px) {
        .sidebar { box-shadow: none !important; }
    }
</style>
</head>

<body id="page-top">

    <div id="wrapper">
        @include('layouts.sidebar')

        <div id="content-wrapper" class="d-flex flex-column bg-white">
            <div id="content">
                @include('layouts.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            
            @include('layouts.footer')
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

    <div id="tapping-toast-container"></div>

    <script>
        $(document).ready(function () {
            // Kita cek apakah route ada, jika tidak ada, script tidak jalan (biar tidak error di console)
            let apiUrl = "{{ Route::has('api.tap-logs.latest') ? route('api.tap-logs.latest') : '' }}";
            let lastLogId = 0;

            if(apiUrl) {
                setInterval(function() {
                    $.get(apiUrl, { since_id: lastLogId }, function(logs) {
                        if(Array.isArray(logs) && logs.length > 0) {
                            // Update ID terakhir biar gak muncul berulang
                            lastLogId = Math.max(...logs.map(l => l.id));
                            logs.forEach(log => showNotification(log));
                        }
                    });
                }, 3000); // Cek setiap 3 detik
            }

            function showNotification(data) {
                const isSuccess = data.io_status == 1; // 1 = Masuk
                const color = isSuccess ? '#1cc88a' : '#e74a3b';
                const icon = isSuccess ? 'fa-door-open' : 'fa-door-closed';
                const title = isSuccess ? 'GATE OPEN' : 'GATE ACCESS';
                
                const html = `
                <div class="tapping-toast-item">
                    <div class="toast-body-content">
                        <div class="d-flex align-items-center">
                            <i class="fas ${icon} fa-2x mr-3" style="color: ${color}"></i>
                            <div>
                                <h6 class="font-weight-bold mb-0 text-uppercase" style="color: ${color}">${title} - ${data.termno}</h6>
                                <small class="text-muted">${data.tapped_at}</small><br>
                                <span class="font-weight-bold text-dark">${data.card_number}</span>
                            </div>
                        </div>
                    </div>
                    <div class="toast-progress-bar" style="background-color: ${color}"></div>
                </div>`;
                
                let $toast = $(html).appendTo('#tapping-toast-container');
                setTimeout(() => { 
                    $toast.addClass('toast-hide'); 
                    setTimeout(() => $toast.remove(), 500);
                }, 4000);
            }
        });
    </script>
</body>
</html>