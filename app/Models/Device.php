<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'termno',
        'lokasi',
        'cam_ip',
        'cam_username',
        'cam_password',
    ];
    public function ios()
    {
        return $this->hasMany(DeviceIo::class);
    }
}