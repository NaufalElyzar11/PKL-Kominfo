<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Menambahkan kolom id_delegasi setelah kolom user_id
            // Kita gunakan nullable() agar data lama tidak error
            $table->unsignedBigInteger('id_delegasi')->nullable()->after('user_id');

            // Opsional: Tambahkan foreign key agar data konsisten dengan tabel pegawai
            $table->foreign('id_delegasi')->references('id')->on('pegawai')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Menghapus foreign key dan kolom jika migration di-rollback
            $table->dropForeign(['id_delegasi']);
            $table->dropColumn('id_delegasi');
        });
    }
};