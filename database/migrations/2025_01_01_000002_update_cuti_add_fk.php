<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Tambah FK ke atasan_langsung
            if (!Schema::hasColumn('cuti', 'id_atasan_langsung')) {
                $table->foreignId('id_atasan_langsung')
                    ->nullable()
                    ->constrained('atasan_langsung')
                    ->nullOnDelete();
            }

            // Tambah FK ke pejabat_pemberi_cuti
            if (!Schema::hasColumn('cuti', 'id_pejabat_pemberi_cuti')) {
                $table->foreignId('id_pejabat_pemberi_cuti')
                    ->nullable()
                    ->constrained('pejabat_pemberi_cuti')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            if (Schema::hasColumn('cuti', 'id_atasan_langsung')) {
                $table->dropForeign(['id_atasan_langsung']);
                $table->dropColumn('id_atasan_langsung');
            }

            if (Schema::hasColumn('cuti', 'id_pejabat_pemberi_cuti')) {
                $table->dropForeign(['id_pejabat_pemberi_cuti']);
                $table->dropColumn('id_pejabat_pemberi_cuti');
            }
        });
    }
};
