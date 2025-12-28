@extends('layouts.app')

@section('title', 'Setting Gate: ' . $device->termno)

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Konfigurasi: {{ $device->termno }}</h1>
    <a href="{{ route('gates.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Info Mesin</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>TermNo</th><td>{{ $device->termno }}</td></tr>
                    <tr><th>Lokasi</th><td>{{ $device->lokasi }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-plus-circle"></i> Tambah IO</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('gates.ios.store', $device->id) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih IO (Trigger)</label>
                        <select name="io_status" class="form-control">
                            <option value="1">IO = 1 (MASUK)</option>
                            <option value="0">IO = 0 (KELUAR)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="label" class="form-control" placeholder="Cth: Gate Depan In" required>
                    </div>

                    <hr>
                    <h6 class="small font-weight-bold text-secondary">Koneksi Kamera (Saat IO ini aktif)</h6>
                    
                    <div class="form-group">
                        <label>IP Camera</label>
                        <input type="text" name="cam_ip" class="form-control" placeholder="192.168.1.xxx" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>User</label>
                                <input type="text" name="cam_username" class="form-control" value="admin" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Pass</label>
                                <input type="text" name="cam_password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Simpan Setting IO</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar IO Terdaftar</h6>
            </div>
            <div class="card-body">
                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>IO Status</th>
                                <th>Label</th>
                                <th>Kamera Terhubung</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($device->ios as $io)
                            <tr>
                                <td class="text-center">
                                    @if($io->io_status == 1)
                                        <span class="badge badge-success px-3 py-2" style="font-size:1rem">IO = 1 (IN)</span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size:1rem">IO = 0 (OUT)</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold">{{ $io->label }}</td>
                                <td>
                                    <i class="fas fa-video mr-1"></i> {{ $io->cam_ip }} <br>
                                    <small class="text-muted">User: {{ $io->cam_username }}</small>
                                </td>
                                <td>
                                    <form action="{{ route('gates.ios.destroy', $io->id) }}" method="POST" onsubmit="return confirm('Hapus setting IO ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada IO yang disetting. Mesin ini belum bisa dipakai.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection