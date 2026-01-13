<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pegawai')) {
            Schema::create('pegawai', function (Blueprint $table) {
                $table->id();
                $table->string('nip', 50)->nullable()->unique();     // Nomor Induk Pegawai
                $table->string('nama', 150);
                $table->string('jabatan', 100)->nullable();
                $table->string('bidang', 100)->nullable();
                $table->unsignedInteger('kuota_cuti')->default(12);  // Kuota cuti per tahun
                $table->string('email', 150)->nullable()->unique();
                $table->string('no_hp', 20)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
