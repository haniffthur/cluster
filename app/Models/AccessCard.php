<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_number',
        'kategori',
        'resident_id',
        'is_active',
    ];

    // Relasi balik ke penghuni
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}