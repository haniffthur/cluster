@extends('layouts.app')

@section('title', 'Registrasi Kartu')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Registrasi Kartu Baru</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Kartu</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('cards.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nomor Kartu (UID) <span class="text-danger">*</span></label>
                <input type="text" name="card_number" class="form-control @error('card_number') is-invalid @enderror" value="{{ old('card_number') }}" placeholder="Tempel kartu pada reader atau ketik manual..." required autofocus>
                @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Gunakan alat reader USB untuk input otomatis, atau copy dari log gate.</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategori Kartu</label>
                        <select name="kategori" id="kategori" class="form-control">
                            <option value="penghuni" selected>Penghuni Cluster</option>
                            <option value="tamu">Tamu / Umum / Staff</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Aktif (Bisa Akses)</option>
                            <option value="0">Blokir (Akses Ditolak)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Dropdown ini akan hilang jika kategori = tamu --}}
            <div class="form-group" id="resident-wrapper">
                <label>Pilih Penghuni <span class="text-danger">*</span></label>
                {{-- Menggunakan Select2 agar mudah dicari --}}
                <select name="resident_id" class="form-control select2">
                    <option value="">-- Cari Nama Penghuni --</option>
                    @foreach($residents as $resident)
                        <option value="{{ $resident->id }}">{{ $resident->nama }} - {{ $resident->no_pelanggan }}</option>
                    @endforeach
                </select>
                @error('resident_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Kartu</button>
            <a href="{{ route('cards.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Logika Show/Hide Penghuni
        function toggleResident() {
            if($('#kategori').val() === 'penghuni') {
                $('#resident-wrapper').show();
            } else {
                $('#resident-wrapper').hide();
                // Reset pilihan select2 jika pindah ke tamu
                $('.select2').val(null).trigger('change');
            }
        }

        $('#kategori').change(toggleResident);
        toggleResident(); // Run on load
    });
</script>
@endpush