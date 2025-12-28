@extends('layouts.app')

@section('title', 'Tambah Penghuni')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Tambah Penghuni Baru</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Input Data</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('residents.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" name="no_pelanggan" class="form-control @error('no_pelanggan') is-invalid @enderror" value="{{ old('no_pelanggan') }}" placeholder="Contoh: 019/MNC/C1/..." required>
                        @error('no_pelanggan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No Virtual Account (VA)</label>
                        <input type="number" name="no_va" class="form-control @error('no_va') is-invalid @enderror" value="{{ old('no_va') }}" placeholder="Opsional">
                        @error('no_va') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat') }}</textarea>
                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Iuran Bulanan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="iuran_bulanan" class="form-control @error('iuran_bulanan') is-invalid @enderror" value="{{ old('iuran_bulanan', 0) }}" required>
                        <small class="text-muted">Nominal ini akan menjadi tagihan otomatis setiap tanggal 1.</small>
                        @error('iuran_bulanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status Penghuni</label>
                        <select name="is_active" class="form-control">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Non-Aktif / Pindah</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
            <a href="{{ route('residents.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection