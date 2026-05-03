<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Kolom untuk menyimpan delegasi darurat ketika pemohon cuti alasan penting
            // sedang bertugas sebagai delegasi orang lain di tanggal yang sama
            $table->unsignedBigInteger('id_delegasi_darurat')->nullable()->after('id_delegasi');
            $table->foreign('id_delegasi_darurat')->references('id')->on('pegawai')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['id_delegasi_darurat']);
            $table->dropColumn('id_delegasi_darurat');
        });
    }
};
