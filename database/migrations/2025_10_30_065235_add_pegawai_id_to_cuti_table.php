<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            if (Schema::hasColumn('cuti', 'pegawai_id')) {
                $table->renameColumn('pegawai_id', 'user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            if (Schema::hasColumn('cuti', 'user_id')) {
                $table->renameColumn('user_id', 'pegawai_id');
            }
        });
    }
};

