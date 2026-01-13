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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'nip')) {
            $table->string('nip', 30)->nullable();
        }

        if (!Schema::hasColumn('users', 'telepon')) {
            $table->string('telepon', 20)->nullable();
        }

        if (!Schema::hasColumn('users', 'jabatan')) {
            $table->string('jabatan', 100)->nullable();
        }

        if (!Schema::hasColumn('users', 'unit_kerja')) {
            $table->string('unit_kerja', 100)->nullable();
        }

        if (!Schema::hasColumn('users', 'id_pegawai')) {
            $table->unsignedBigInteger('id_pegawai')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['nip','telepon','jabatan','unit_kerja','id_pegawai']);
    });
}

};
