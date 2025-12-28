@extends('layouts.app')

@section('title', 'Import Data Warga')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Import Data Warga (CSV)</h1>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upload File CSV</h6>
            </div>
            <div class="card-body">
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('residents.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label>Pilih File CSV</label>
                        <input type="file" name="file_csv" class="form-control-file p-1 border rounded" required accept=".csv">
                        <small class="text-muted mt-2 d-block">
                            Pastikan format kolom sesuai dengan template: 
                            <b>NO, NO VA, NO PELANGGAN, Nama, ALAMAT, ..., TOTAL CHARGE</b>
                        </small>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sistem akan otomatis:
                        <ul class="mb-0 pl-3">
                            <li>Membuat data penghuni baru jika belum ada.</li>
                            <li>Mengupdate data jika No Pelanggan sudah ada.</li>
                            <li>Membuat tagihan "Tunggakan Lama" jika kolom TOTAL CHARGE > 0.</li>
                        </ul>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload mr-1"></i> Upload & Proses
                    </button>
                    <a href="{{ route('residents.index') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection