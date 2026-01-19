@extends('layouts.app')

@section('title', 'Riwayat Akses Gate')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Riwayat Akses (Live) <span class="badge badge-danger blink_me">LIVE</span></h1>

<style>
    .blink_me { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    /* Transisi halus saat update */
    tbody { transition: all 0.3s ease; }
</style>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Keluar Masuk</h6>
        
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="autoRefreshSwitch" checked>
            <label class="custom-control-label small font-weight-bold" for="autoRefreshSwitch">Auto Refresh</label>
        </div>
    </div>
    <div class="card-body">
        
        {{-- PANEL FILTER --}}
        <form action="{{ route('access-logs.index') }}" method="GET" class="mb-4">
            <div class="form-row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold">Cari Nama / Kartu</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketikan sesuatu..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Filter Gate</label>
                    <select name="termno" class="form-control">
                        <option value="">- Semua Gate -</option>
                        @foreach($gates as $gate)
                            <option value="{{ $gate->termno }}" {{ request('termno') == $gate->termno ? 'selected' : '' }}>
                                {{ $gate->termno }} ({{ $gate->lokasi }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold">Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        <hr>

        {{-- TABEL DATA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 18%">Waktu Tapping</th>
                        <th style="width: 15%">Gate (TermNo)</th>
                        <th>Identitas Pengakses</th>
                        <th style="width: 10%">Arah</th>
                        <th style="width: 15%">Snapshot</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body">
                    {{-- Panggil Partial View untuk data awal --}}
                    @include('logs.partials.log-rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end mt-3" id="pagination-links">
            {{ $logs->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

{{-- GLOBAL MODAL (Di luar Tabel agar tidak kedap-kedip) --}}
<div class="modal fade" id="globalSnapModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Snapshot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-dark p-0">
                <img src="" id="modalImage" class="img-fluid" alt="Loading..." style="max-height: 80vh;">
            </div>
            <div class="modal-footer bg-light py-2">
                <span class="mr-auto text-muted small font-weight-bold" id="modalTime"></span>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. LOGIC PINTAR AUTO REFRESH
        // Cek apakah ada parameter filter atau page di URL
        const urlParams = new URLSearchParams(window.location.search);
        const hasPage = urlParams.has('page') && urlParams.get('page') != '1'; // Sedang di page 2, 3, dst
        const hasSearch = urlParams.has('search') || urlParams.has('termno') || urlParams.has('start_date');

        // Jika sedang buka halaman 2++ ATAU sedang cari data -> MATIKAN AUTO REFRESH
        if (hasPage || hasSearch) {
            $('#autoRefreshSwitch').prop('checked', false);
            console.log("Auto refresh dimatikan karena sedang filter/paging.");
        }

        // 2. INTERVAL REFRESH
        setInterval(function() {
            if ($('#autoRefreshSwitch').is(':checked')) {
                fetchLogs();
            }
        }, 2000); // Refresh tiap 2 detik (Lebih santai daripada 500ms)

        function fetchLogs() {
            $.ajax({
                url: "{{ route('api.logs.ajax') }}",
                method: "GET",
                success: function(response) {
                    if(response.html) {
                        $('#logs-table-body').html(response.html);
                    }
                },
                error: function(err) {
                    console.log("Gagal update log:", err);
                }
            });
        }

        // 3. LOGIC MODAL (Event Delegation)
        // Pakai $(document).on agar tombol yang baru muncul dari AJAX tetap bisa diklik
        $(document).on('click', '.btn-snapshot', function() {
            let imgUrl = $(this).data('img');
            let time = $(this).data('time');
            let term = $(this).data('term');

            $('#modalImage').attr('src', imgUrl);
            $('#modalTitle').text('Snapshot Gate: ' + term);
            $('#modalTime').text('Waktu Akses: ' + time);
            
            $('#globalSnapModal').modal('show');
        });
    });
</script>
@endpush