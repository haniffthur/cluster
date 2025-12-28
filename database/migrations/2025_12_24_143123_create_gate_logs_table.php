<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_logs', function (Blueprint $table) {
            $table->id();
            
            // Data dari API Mesin Tapping
            $table->string('termno');      // Dari parameter termno
            $table->string('card_number'); // Dari parameter card
            $table->tinyInteger('io_status'); // 1 = Masuk, 0 = Keluar
            
            // Hasil Snapshot
            $table->string('snapshot_path')->nullable(); // Lokasi file foto tersimpan
            
            // Waktu tapping
            $table->timestamp('tapped_at')->useCurrent();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_logs');
    }
};