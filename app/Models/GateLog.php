<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'termno',
        'card_number',
        'io_status',
        'snapshot_path',
        'tapped_at',
    ];

    // Helper untuk menampilkan status teks
    public function getStatusLabelAttribute()
    {
        return $this->io_status == 1 ? 'MASUK' : 'KELUAR';
    }
    
    // Relasi opsional ke Access Card untuk tahu siapa yang tapping (jika kartu terdaftar)
    public function card()
    {
        return $this->belongsTo(AccessCard::class, 'card_number', 'card_number');
    }
}