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
            // Tambahkan id_pegawai agar sinkron dengan kode di Controller
            $table->unsignedBigInteger('id_pegawai')->nullable()->after('user_id');
            
            // Tambahkan foreign key (opsional tapi disarankan)
            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['id_pegawai']);
            $table->dropColumn('id_pegawai');
        });
    }
};
