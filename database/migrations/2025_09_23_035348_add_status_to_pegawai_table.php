<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pegawai')) {
            Schema::table('pegawai', function (Blueprint $table) {
                if (!Schema::hasColumn('pegawai', 'status')) {
                    $table->enum('status', ['aktif', 'nonaktif'])
                          ->default('aktif')
                          ->after('email');
                }

                if (!Schema::hasColumn('pegawai', 'role')) {
                    $table->enum('role', ['super-admin', 'admin', 'pegawai', 'kepala'])
                          ->default('pegawai')
                          ->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawai')) {
            Schema::table('pegawai', function (Blueprint $table) {
                if (Schema::hasColumn('pegawai', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('pegawai', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }
};
