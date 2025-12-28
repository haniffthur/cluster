<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceIo extends Model
{
    protected $table = 'device_ios';
    protected $fillable = [
        'device_id', 'io_status', 'label', 
        'cam_ip', 'cam_username', 'cam_password'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}