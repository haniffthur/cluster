<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'bulan',
        'tahun',
        'jumlah_tagihan',
        'status',
        'tanggal_bayar',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}