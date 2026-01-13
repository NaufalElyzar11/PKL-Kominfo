<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            if (!Schema::hasColumn('cuti', 'nama')) {
                $table->string('nama')->after('pegawai_id');
            }
            if (!Schema::hasColumn('cuti', 'nip')) {
                $table->string('nip')->after('nama');
            }
            if (!Schema::hasColumn('cuti', 'jabatan')) {
                $table->string('jabatan')->after('nip');
            }
            if (!Schema::hasColumn('cuti', 'alamat')) {
                $table->string('alamat')->after('jabatan');
            }
            if (!Schema::hasColumn('cuti', 'jenis_cuti')) {
                $table->string('jenis_cuti')->default('Tahunan')->after('alamat');
            }
            if (!Schema::hasColumn('cuti', 'jumlah_hari')) {
                $table->integer('jumlah_hari')->after('tanggal_selesai');
            }
            if (!Schema::hasColumn('cuti', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('jumlah_hari');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            if (Schema::hasColumn('cuti', 'nama')) {
                $table->dropColumn('nama');
            }
            if (Schema::hasColumn('cuti', 'nip')) {
                $table->dropColumn('nip');
            }
            if (Schema::hasColumn('cuti', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
            if (Schema::hasColumn('cuti', 'alamat')) {
                $table->dropColumn('alamat');
            }
            if (Schema::hasColumn('cuti', 'jenis_cuti')) {
                $table->dropColumn('jenis_cuti');
            }
            if (Schema::hasColumn('cuti', 'jumlah_hari')) {
                $table->dropColumn('jumlah_hari');
            }
            if (Schema::hasColumn('cuti', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
