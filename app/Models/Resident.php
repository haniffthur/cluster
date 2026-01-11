<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_pelanggan',
        'no_va',
        'nama',
        'alamat',
        'is_active',
    ];

    // Relasi: Satu penghuni bisa punya banyak kartu akses
    public function accessCards()
    {
        return $this->hasMany(AccessCard::class);
    }

    // Relasi: Satu penghuni punya banyak tagihan
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}