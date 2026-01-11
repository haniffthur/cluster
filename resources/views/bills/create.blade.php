@extends('layouts.app')

@section('title', 'Buat Tagihan Manual')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Buat Tagihan Manual</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Tagihan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('bills.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Pilih Penghuni <span class="text-danger">*</span></label>
                <select name="resident_id" id="resident_id" class="form-control select2" required>
                    <option value="">-- Cari Penghuni --</option>
                    @foreach($residents as $resident)
                        <option value="{{ $resident->id }}" data-iuran="{{ $resident->iuran_bulanan }}">
                            {{ $resident->nama }} - {{ $resident->no_pelanggan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Periode Bulan</label>
                        <select name="bulan" class="form-control">
                            @for($i=1; $i<=12; $i++)
                                <option value="{{ sprintf('%02d', $i) }}" {{ date('m') == sprintf('%02d', $i) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="jumlah_tagihan" id="jumlah_tagihan" class="form-control" required>
                <small class="text-muted">Nominal otomatis terisi sesuai settingan master data penghuni.</small>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Tagihan</button>
            <a href="{{ route('ipl_bills.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        // Auto isi nominal saat penghuni dipilih
       $('#resident_id').on('change', function() {
    // var iuran = $(this).find(':selected').data('iuran'); <-- Hapus
    // Logic auto fill dihapus, user harus ketik manual nominalnya
    $('#jumlah_tagihan').val(''); 
});
    });
</script>
@endpush