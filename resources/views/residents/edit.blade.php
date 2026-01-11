@extends('layouts.app')

@section('title', 'Edit Profil Warga')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Perbarui Data Warga</h1>
    <a href="{{ route('residents.show', $resident->id) }}" class="btn btn-light btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail
    </a>
</div>

<form action="{{ route('residents.update', $resident->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-edit mr-2"></i>Informasi Utama</h6>
                </div>
                
                <div class="card-body">
                    <div class="p-3 mb-4 rounded" style="background-color: #f8f9fc;">
                        <h6 class="text-xs font-weight-bold text-uppercase text-secondary mb-3">Identitas Unit & Pembayaran</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark">No Pelanggan / Blok <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-0 bg-white"><i class="fas fa-home text-gray-400"></i></span>
                                        </div>
                                        <input type="text" name="no_pelanggan" class="form-control border-0 bg-white shadow-sm" value="{{ old('no_pelanggan', $resident->no_pelanggan) }}" required style="height: 45px;">
                                    </div>
                                    @error('no_pelanggan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark">No Virtual Account</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-0 bg-white"><i class="fas fa-credit-card text-gray-400"></i></span>
                                        </div>
                                        <input type="number" name="no_va" class="form-control border-0 bg-white shadow-sm" value="{{ old('no_va', $resident->no_va) }}" placeholder="Opsional" style="height: 45px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-lg bg-light border-0" value="{{ old('nama', $resident->nama) }}" required style="border-radius: 10px;">
                        @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-dark">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control bg-light border-0" rows="3" style="border-radius: 10px; resize: none;" required>{{ old('alamat', $resident->alamat) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-info text-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-id-card mr-2"></i>Akses RFID</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="small font-weight-bold text-dark">Nomor Kartu (UID)</label>
                        
                        @php
                            $currentCardNumber = $resident->accessCards->first() ? $resident->accessCards->first()->card_number : '';
                        @endphp

                        <div class="input-group">
                            <input type="text" name="card_number" class="form-control border-0 bg-light" 
                                   value="{{ old('card_number', $currentCardNumber) }}" 
                                   placeholder="Tempel kartu baru..." style="height: 45px;">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-light"><i class="fas fa-wifi text-info"></i></span>
                            </div>
                        </div>
                        @error('card_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        
                        <div class="mt-2 text-xs text-muted">
                            <i class="fas fa-info-circle mr-1"></i> Kosongkan inputan jika ingin <b>menghapus</b> akses kartu warga ini.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-body">
                    <h6 class="font-weight-bold text-dark mb-3">Status Penghuni</h6>
                    
                    <div class="form-group">
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="activeRadio" name="is_active" class="custom-control-input" value="1" {{ $resident->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-success" for="activeRadio">
                                <i class="fas fa-check-circle mr-1"></i> Aktif (Menetap)
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="inactiveRadio" name="is_active" class="custom-control-input" value="0" {{ !$resident->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-secondary" for="inactiveRadio">
                                <i class="fas fa-times-circle mr-1"></i> Non-Aktif (Pindah)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-block btn-lg shadow-sm font-weight-bold" style="border-radius: 10px;">
                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN
            </button>
            <a href="{{ route('residents.index') }}" class="btn btn-light btn-block text-secondary font-weight-bold mt-2" style="border-radius: 10px;">
                Batal
            </a>

        </div>
    </div>
</form>

@endsection