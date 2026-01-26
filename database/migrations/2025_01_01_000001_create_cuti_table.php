<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Hubungan ke akun login
            $table->string('nama');
            $table->string('nip');
            $table->string('jabatan');
            $table->string('alamat');
            $table->string('jenis_cuti')->default('Tahunan');
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->integer('tahun'); // Untuk filter laporan tahunan
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Menunggu');
            $table->text('catatan_penolakan')->nullable(); // Untuk alasan jika ditolak

            // Kolom Snapshot Nama (Agar riwayat tetap akurat secara historis)
            $table->string('atasan_nama')->nullable();
            $table->string('pejabat_nama')->nullable();

            // Kolom Relasi ID (Untuk sistem persetujuan)
            $table->unsignedBigInteger('id_atasan_langsung')->nullable();
            $table->unsignedBigInteger('id_pejabat_pemberi_cuti')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
