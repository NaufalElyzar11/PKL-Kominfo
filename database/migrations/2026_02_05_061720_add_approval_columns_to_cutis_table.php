<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan perubahan (Forward).
     */
    public function up(): void
{
    Schema::table('cuti', function (Blueprint $table) {
        // Cek dulu apakah kolom sudah ada sebelum menambah (mencegah error duplikasi)
        if (!Schema::hasColumn('cuti', 'status_delegasi')) {
            $table->enum('status_delegasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->enum('status_atasan', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->enum('status_pejabat', ['pending', 'disetujui', 'ditolak'])->default('pending');
        }

        if (!Schema::hasColumn('cuti', 'catatan_tolak_delegasi')) {
            $table->text('catatan_tolak_delegasi')->nullable();
            $table->text('catatan_tolak_atasan')->nullable();
            $table->text('catatan_tolak_pejabat')->nullable();
        }

        if (!Schema::hasColumn('cuti', 'id_delegasi')) {
            // Gunakan unsignedBigInteger jika foreignId bermasalah
            $table->unsignedBigInteger('id_delegasi')->nullable();
            
            // Definisikan foreign key secara manual agar lebih fleksibel
            $table->foreign('id_delegasi')->references('id')->on('pegawais')->onDelete('set null');
        }
    });
}

    /**
     * Batalkan perubahan (Rollback).
     */
    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['id_delegasi']);
            
            // Hapus semua kolom yang tadi ditambah
            $table->dropColumn([
                'status_delegasi', 
                'status_atasan', 
                'status_pejabat', 
                'catatan_tolak_delegasi', 
                'catatan_tolak_atasan', 
                'catatan_tolak_pejabat',
                'id_delegasi'
            ]);
        });
    }
};