<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number')->unique(); // Nomor UID kartu dari mesin tapping
            
            // Kategori: Penghuni atau Tamu (bisa ditambah nanti)
            $table->enum('kategori', ['penghuni', 'tamu'])->default('penghuni');
            
            // Relasi ke penghuni (Bisa null jika kartunya untuk tamu umum)
            $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('set null');
            
            $table->boolean('is_active')->default(true); // Bisa blokir kartu yang hilang
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_cards');
    }
};