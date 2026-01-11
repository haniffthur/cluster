<ul class="navbar-nav bg-white sidebar sidebar-light accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            {{-- Icon Dungeon (Benteng/Gate) --}}
            <i class="fas fa-dungeon fa-2x"></i> 
        </div>
        <div class="sidebar-brand-text mx-3">DjavaCluster</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Master Data</div>

    <li class="nav-item {{ request()->routeIs('residents.*') || request()->routeIs('cards.*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWarga"
            aria-expanded="true" aria-controls="collapseWarga">
            <i class="fas fa-fw fa-users"></i>
            <span>Penghuni & Kartu</span>
        </a>
        <div id="collapseWarga" class="collapse {{ request()->routeIs('residents.*') || request()->routeIs('cards.*') ? 'show' : '' }}"
            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Data Warga:</h6>
                @if(Route::has('residents.index')) 
                    <a class="collapse-item {{ request()->routeIs('residents.*') ? 'active' : '' }}" href="{{ route('residents.index') }}">Data Penghuni</a> 
                @endif
                @if(Route::has('cards.index')) 
                    <a class="collapse-item {{ request()->routeIs('cards.*') ? 'active' : '' }}" href="{{ route('cards.index') }}">Data Kartu (RFID)</a> 
                @endif
            </div>
        </div>
    </li>

    {{-- Kita cek apakah route aktif adalah 'tunggakan' atau 'ipl_bills' --}}
    <li class="nav-item {{ request()->routeIs('tunggakan.*') || request()->routeIs('ipl_bills.*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeuangan"
            aria-expanded="true" aria-controls="collapseKeuangan">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Keuangan</span>
        </a>
        <div id="collapseKeuangan" class="collapse {{ request()->routeIs('tunggakan.*') || request()->routeIs('ipl_bills.*') ? 'show' : '' }}"
            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Tagihan & Pembayaran:</h6>
                
                {{-- Link ke Halaman Khusus Tunggakan (Belum Bayar) --}}
                <!-- @if(Route::has('tunggakan.index'))
                    <a class="collapse-item {{ request()->routeIs('tunggakan.*') ? 'active' : '' }}" href="{{ route('tunggakan.index') }}">Data Tunggakan</a>
                @endif -->
                    
                {{-- Link ke Halaman Semua Tagihan (History) --}}
                @if(Route::has('ipl_bills.index'))
                    <a class="collapse-item {{ request()->routeIs('ipl_bills.*') ? 'active' : '' }}" href="{{ route('ipl_bills.index') }}">Tagihan IPL (History)</a>
                @endif
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Gate System</div>

    <li class="nav-item {{ request()->routeIs('gates.*') ? 'active' : '' }}">
        @if(Route::has('gates.index'))
        <a class="nav-link" href="{{ route('gates.index') }}">
            <i class="fas fa-fw fa-video"></i>
            <span>Konfigurasi Gate & CCTV</span>
        </a>
        @else
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-video"></i>
            <span>Konfigurasi Gate & CCTV</span>
        </a>
        @endif
    </li>

    <li class="nav-item {{ request()->routeIs('access-logs.*') ? 'active' : '' }}">
        @if(Route::has('access-logs.index'))
        <a class="nav-link" href="{{ route('access-logs.index') }}">
            <i class="fas fa-fw fa-history"></i>
            <span>Riwayat Akses</span>
        </a>
        @else
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-history"></i>
            <span>Riwayat Akses</span>
        </a>
        @endif
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>