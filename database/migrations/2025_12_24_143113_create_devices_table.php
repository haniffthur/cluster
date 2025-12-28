<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('termno')->unique(); // Kunci utama: 001, 002, dst
            $table->string('lokasi')->nullable(); // Misal: Gate Depan Kiri
            
            // Konfigurasi Kamera Dahua untuk TermNo ini
            $table->string('cam_ip');       // IP Address kamera
            $table->string('cam_username'); // Username login kamera
            $table->string('cam_password'); // Password login kamera
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};