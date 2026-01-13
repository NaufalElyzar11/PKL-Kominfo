<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama');
            $table->string('nip')->unique();
            $table->string('jabatan');
            $table->string('unit_kerja'); // ✅ menggantikan 'bidang'
            $table->string('email')->unique();
            $table->string('telepon')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('role')->default('pegawai'); // admin / staff / pegawai
            $table->integer('jatah_cuti')->default(12); // jatah cuti tahunan
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
