<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_device_ios_table.php
public function up()
{
    Schema::create('device_ios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
        $table->tinyInteger('io_status'); // 1 = Masuk, 0 = Keluar
        $table->string('label')->nullable(); // Label: "Gerbang Depan Masuk"
        
        // Data Kamera dipindah ke sini (karena beda IO bisa beda kamera)
        $table->string('cam_ip');
        $table->string('cam_username')->default('admin');
        $table->string('cam_password');
        
        $table->timestamps();
    });

    // Opsional: Hapus kolom kamera di tabel devices lama agar tidak bingung
    Schema::table('devices', function (Blueprint $table) {
        $table->dropColumn(['cam_ip', 'cam_username', 'cam_password',]);
    });
}

public function down()
{
    Schema::dropIfExists('device_ios');
}
};
