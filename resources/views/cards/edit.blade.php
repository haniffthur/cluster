@extends('layouts.app')

@section('title', 'Edit Kartu')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Edit Kartu Akses</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit: {{ $card->card_number }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('cards.update', $card->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nomor Kartu (UID)</label>
                <input type="text" name="card_number" class="form-control @error('card_number') is-invalid @enderror" value="{{ old('card_number', $card->card_number) }}" required>
                @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategori Kartu</label>
                        <select name="kategori" id="kategori" class="form-control">
                            <option value="penghuni" {{ $card->kategori == 'penghuni' ? 'selected' : '' }}>Penghuni Cluster</option>
                            <option value="tamu" {{ $card->kategori == 'tamu' ? 'selected' : '' }}>Tamu / Umum / Staff</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ $card->is_active == 1 ? 'selected' : '' }}>Aktif (Bisa Akses)</option>
                            <option value="0" {{ $card->is_active == 0 ? 'selected' : '' }}>Blokir (Akses Ditolak)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group" id="resident-wrapper">
                <label>Pilih Penghuni</label>
                <select name="resident_id" class="form-control select2">
                    <option value="">-- Cari Nama Penghuni --</option>
                    @foreach($residents as $resident)
                        <option value="{{ $resident->id }}" {{ $card->resident_id == $resident->id ? 'selected' : '' }}>
                            {{ $resident->nama }} - {{ $resident->no_pelanggan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Kartu</button>
            <a href="{{ route('cards.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        function toggleResident() {
            if($('#kategori').val() === 'penghuni') {
                $('#resident-wrapper').show();
            } else {
                $('#resident-wrapper').hide();
            }
        }
        $('#kategori').change(toggleResident);
        toggleResident();
    });
</script>
@endpush