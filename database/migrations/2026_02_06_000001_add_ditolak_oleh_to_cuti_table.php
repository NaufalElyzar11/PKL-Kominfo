<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Kolom untuk tracking siapa yang menolak (atasan/pejabat)
            $table->string('ditolak_oleh')->nullable()->after('catatan_penolakan');
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropColumn('ditolak_oleh');
        });
    }
};
