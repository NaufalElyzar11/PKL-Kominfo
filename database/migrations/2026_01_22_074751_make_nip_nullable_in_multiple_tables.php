<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Mengubah kolom nip agar boleh kosong (null)
            $table->string('nip', 18)->nullable()->change(); 
        });

        Schema::table('users', function (Blueprint $table) {
            // Lakukan hal yang sama pada tabel users
            $table->string('nip', 18)->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiple_tables', function (Blueprint $table) {
            //
        });
    }
};
