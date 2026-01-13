<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Hapus kolom 'bidang' hanya jika memang ada
            if (Schema::hasColumn('pegawai', 'bidang')) {
                $table->dropColumn('bidang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Tambahkan kembali jika rollback dan kolom belum ada
            if (!Schema::hasColumn('pegawai', 'bidang')) {
                $table->string('bidang')->nullable();
            }
        });
    }
};
