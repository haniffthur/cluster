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
        
        {{-- PANEL FILTER (Biarkan seperti semula) --}}
        <form action="{{ route('access-logs.index') }}" method="GET" class="mb-4">
            {{-- ... Kode Form Filter Anda (Copy dari kode lama) ... --}}
            {{-- TIPS: Jika sedang memfilter (search/date), sebaiknya matikan auto refresh via JS --}}
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

        {{-- Pagination (Hanya muncul jika auto refresh mati / mode filter) --}}
        <div class="d-flex justify-content-end mt-3" id="pagination-links">
            {{ $logs->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

{{-- SINGLE MODAL UNTUK PREVIEW FOTO --}}
<div class="modal fade" id="globalSnapModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Snapshot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-dark">
                <img src="" id="modalImage" class="img-fluid" alt="Loading...">
            </div>
            <div class="modal-footer bg-light">
                <span class="mr-auto text-muted small" id="modalTime"></span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let isFilterActive = "{{ request()->hasAny(['search', 'termno', 'start_date']) }}";
        
        // Jika sedang filter, matikan auto refresh defaultnya
        if(isFilterActive) {
            $('#autoRefreshSwitch').prop('checked', false);
        }

        // Logic Auto Refresh
        setInterval(function() {
            if ($('#autoRefreshSwitch').is(':checked')) {
                fetchLogs();
            }
        }, 3000); // Cek setiap 3 detik

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
                    console.log("Gagal mengambil data live:", err);
                }
            });
        }

        // Logic untuk Modal Foto (Event Delegation karena tombolnya dinamis dari AJAX)
        $(document).on('click', '.btn-snapshot', function() {
            let imgUrl = $(this).data('img');
            let time = $(this).data('time');
            let term = $(this).data('term');

            $('#modalImage').attr('src', imgUrl);
            $('#modalTitle').text('Snapshot: ' + term);
            $('#modalTime').text('Waktu: ' + time);
            
            $('#globalSnapModal').modal('show');
        });
    });
</script>
@endpush