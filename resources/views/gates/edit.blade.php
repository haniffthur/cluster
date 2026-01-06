@extends('layouts.app')

@section('title', 'Edit Gate')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Edit Konfigurasi Gate</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit: {{ $device->termno }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('gates.update', $device->id) }}" method="POST">
            @csrf
            @method('PUT')

            <h6 class="font-weight-bold text-secondary mb-3">1. Identitas Mesin Tapping</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>TermNo (Terminal ID)</label>
                        <input type="text" name="termno" class="form-control @error('termno') is-invalid @enderror" value="{{ old('termno', $device->termno) }}" required>
                        @error('termno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Lokasi / Deskripsi</label>
                        <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $device->lokasi) }}" required>
                    </div>
                </div>
            </div>

            <!-- <hr>
            <h6 class="font-weight-bold text-secondary mb-3">2. Koneksi Kamera Dahua</h6>
            
            <div class="form-group">
                <label>IP Address Kamera</label>
                <input type="text" name="cam_ip" class="form-control @error('cam_ip') is-invalid @enderror" value="{{ old('cam_ip', $device->cam_ip) }}" required>
                @error('cam_ip') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username Kamera</label>
                        <input type="text" name="cam_username" class="form-control" value="{{ old('cam_username', $device->cam_username) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password Kamera</label>
                        <input type="text" name="cam_password" class="form-control" value="{{ old('cam_password', $device->cam_password) }}" required>
                    </div>
                </div>
            </div>

            <hr> -->
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Konfigurasi</button>
            <a href="{{ route('gates.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection