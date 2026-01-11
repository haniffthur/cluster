@extends('layouts.app')

@section('title', 'Tambah Penghuni')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Tambah Warga Baru</h1>
    <a href="{{ route('residents.index') }}" class="btn btn-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>

<form action="{{ route('residents.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header bg-white py-3" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle mr-2"></i>Informasi Pribadi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-secondary">No Pelanggan / Unit <span class="text-danger">*</span></label>
                                <input type="text" name="no_pelanggan" class="form-control bg-light border-0" style="border-radius: 10px; height: 45px;" value="{{ old('no_pelanggan') }}" placeholder="Blok A1/..." required>
                                @error('no_pelanggan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold text-secondary">No Virtual Account</label>
                                <input type="number" name="no_va" class="form-control bg-light border-0" style="border-radius: 10px; height: 45px;" value="{{ old('no_va') }}" placeholder="Opsional">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control bg-light border-0" style="border-radius: 10px; height: 45px;" value="{{ old('nama') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control bg-light border-0" style="border-radius: 10px;" rows="3" required>{{ old('alamat') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Status Huni</label>
                        <select name="is_active" class="form-control bg-light border-0" style="border-radius: 10px; height: 45px;">
                            <option value="1" selected>Aktif (Menetap)</option>
                            <option value="0">Non-Aktif (Pindah/Kosong)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white py-3" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-id-card mr-2"></i>Kartu Akses Awal</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small" style="border-radius: 10px;">
                        <i class="fas fa-info-circle mr-1"></i> Anda bisa langsung mendaftarkan 1 kartu utama di sini.
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Nomor Kartu (UID)</label>
                        <div class="input-group">
                            <input type="text" name="card_number" class="form-control border-0 bg-light" style="height: 45px;" placeholder="Tempel Kartu..." value="{{ old('card_number') }}">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-light"><i class="fas fa-wifi text-primary"></i></span>
                            </div>
                        </div>
                        @error('card_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <small class="text-muted">Kosongkan jika belum ada kartu.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-save mr-2"></i> SIMPAN DATA
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection