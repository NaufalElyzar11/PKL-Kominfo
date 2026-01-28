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
            // Menambahkan kolom sisa_cuti dengan nilai default 12 setelah kolom foto
            $table->integer('sisa_cuti')->default(12)->after('foto');
        });
    }

    public function down()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('sisa_cuti');
        });
    }
};
