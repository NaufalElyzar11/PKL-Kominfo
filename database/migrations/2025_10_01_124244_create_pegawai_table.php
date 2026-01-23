<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->nullable()->unique();
            $table->string('nama', 150);
            $table->string('jabatan', 100)->nullable();
            $table->string('unit_kerja', 100)->nullable(); // Pastikan namanya unit_kerja
            $table->string('atasan')->nullable();
            $table->string('pemberi_cuti')->nullable();
            $table->string('status')->default('aktif');
            
            // PENTING: Tambahkan ->nullable() di sini
            $table->string('email')->nullable(); 
            $table->string('telepon')->nullable(); 
            
            $table->unsignedInteger('kuota_cuti')->default(12);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
