<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    // Mengubah kolom ENUM agar menerima 'security'
    DB::statement("ALTER TABLE access_cards MODIFY COLUMN kategori ENUM('penghuni', 'tamu', 'security') NOT NULL DEFAULT 'penghuni'");
}

    /**
     * Reverse the migrations.
     */
   public function down()
{
    DB::statement("ALTER TABLE access_cards MODIFY COLUMN kategori ENUM('penghuni', 'tamu') NOT NULL DEFAULT 'penghuni'");
}
};
