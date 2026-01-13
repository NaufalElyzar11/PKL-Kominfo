<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('cuti', function (Blueprint $table) {
        // Menambahkan kolom catatan_penolakan setelah kolom status
        // nullable() penting agar data lama yang tidak ditolak tetap aman
        $table->text('catatan_penolakan')->nullable()->after('status');
        
        // Opsional: Tambahkan kolom tanggal diproses jika belum ada
        // $table->timestamp('approved_at')->nullable()->after('catatan_penolakan');
    });
}

public function down()
{
    Schema::table('cuti', function (Blueprint $table) {
        $table->dropColumn('catatan_penolakan');
    });
}
};
