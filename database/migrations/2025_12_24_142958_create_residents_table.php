<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            // Data sesuai CSV Monaco
            $table->string('no_pelanggan')->unique(); // Contoh: 019/MNC/C1/08/270622
            $table->string('no_va')->nullable();      // Contoh: 8628722201900647
            $table->string('nama');
            $table->text('alamat');   
            // Kolom tambahan untuk sistem tagihan
            $table->boolean('is_active')->default(true); // Status penghuni aktif/pindah
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};