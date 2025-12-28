<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            
            // Identitas Tagihan
            $table->string('bulan'); // Format: 01, 02, ... 12
            $table->string('tahun'); // Format: 2024, 2025
            $table->decimal('jumlah_tagihan', 12, 2);
            
            // Status Pembayaran
            $table->enum('status', ['lunas', 'belum_bayar'])->default('belum_bayar');
            $table->date('tanggal_bayar')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};