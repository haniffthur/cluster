@forelse($logs as $log)
<tr>
    <td class="align-middle">
        <span class="font-weight-bold text-dark">
            {{ \Carbon\Carbon::parse($log->tapped_at)->format('d/m/Y') }}
        </span><br>
        <span class="text-primary">
            {{ \Carbon\Carbon::parse($log->tapped_at)->format('H:i:s') }} WIB
        </span>
    </td>
    <td class="align-middle">
        <span class="badge badge-light border">{{ $log->termno }}</span>
    </td>
    <td class="align-middle">
        <div class="d-flex flex-column">
            <span class="font-weight-bold text-dark">{{ $log->card_number }}</span>
            
            @if($log->card)
                @if($log->card->kategori == 'penghuni' && $log->card->resident)
                    <span class="text-success small">
                        <i class="fas fa-user-check mr-1"></i> {{ $log->card->resident->nama }}
                    </span>
                    <span class="text-muted small" style="font-size: 0.75rem">
                        {{ $log->card->resident->alamat }}
                    </span>
                @elseif($log->card->kategori == 'security')
                    <span class="text-dark small font-weight-bold">
                        <i class="fas fa-user-shield mr-1"></i> SECURITY / PATROLI
                    </span>
                @elseif($log->card->kategori == 'tamu')
                    <span class="text-info small">
                        <i class="fas fa-user-tag mr-1"></i> Tamu / Staff
                    </span>
                @else
                    <span class="text-warning small">Data Tidak Lengkap</span>
                @endif
            @else
                <span class="text-danger small">
                    <i class="fas fa-question-circle mr-1"></i> Kartu Tidak Terdaftar
                </span>
            @endif
        </div>
    </td>
    <td class="align-middle text-center">
        @if($log->io_status == 1)
            <span class="badge badge-success px-3 py-2">MASUK <i class="fas fa-sign-in-alt ml-1"></i></span>
        @else
            <span class="badge badge-warning px-3 py-2">KELUAR <i class="fas fa-sign-out-alt ml-1"></i></span>
        @endif
    </td>
    <td class="align-middle text-center">
        @if($log->snapshot_path)
            {{-- HANYA TOMBOL SAJA (Modal sudah dipindah ke index.blade.php) --}}
            <button type="button" class="btn btn-sm btn-info shadow-sm btn-snapshot" 
                data-img="{{ asset('storage/' . $log->snapshot_path) }}"
                data-time="{{ $log->tapped_at }}"
                data-term="{{ $log->termno }}">
                <i class="fas fa-camera"></i> Lihat Foto
            </button>
        @else
            <span class="text-muted small">- Tidak ada foto -</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5 text-gray-500">
        <i class="fas fa-search fa-3x mb-3 text-gray-300"></i><br>
        Belum ada data log.
    </td>
</tr>
@endforelse