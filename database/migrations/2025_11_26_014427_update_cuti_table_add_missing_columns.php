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
    Schema::table('cuti', function (Blueprint $table) {
        // Tambah kolom tahun
        if (!Schema::hasColumn('cuti', 'tahun')) {
            $table->integer('tahun')->nullable()->after('jumlah_hari');
        }

        // Tambah updated_at jika belum ada
        if (!Schema::hasColumn('cuti', 'updated_at')) {
            $table->timestamp('updated_at')->nullable();
        }
    });
}

public function down()
{
    Schema::table('cuti', function (Blueprint $table) {
        if (Schema::hasColumn('cuti', 'tahun')) {
            $table->dropColumn('tahun');
        }
        if (Schema::hasColumn('cuti', 'updated_at')) {
            $table->dropColumn('updated_at');
        }
    });
}

};
