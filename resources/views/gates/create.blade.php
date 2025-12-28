@extends('layouts.app')

@section('title', 'Tambah Gate Baru')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Registrasi Gate Baru</h1>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Identitas Mesin</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('gates.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>TermNo (Terminal ID) <span class="text-danger">*</span></label>
                        <input type="text" name="termno" class="form-control @error('termno') is-invalid @enderror" value="{{ old('termno') }}" placeholder="Contoh: GATE-UTAMA" required>
                        <small class="text-muted">ID ini harus sama dengan settingan di hardware controller.</small>
                        @error('termno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>Lokasi / Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi') }}" placeholder="Contoh: Pos Security Depan" required>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Setelah disimpan, Anda akan diarahkan ke halaman konfigurasi untuk menambahkan <b>IO (Masuk/Keluar)</b> dan <b>IP Kamera</b>.
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan & Lanjut Setting</button>
                    <a href="{{ route('gates.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 