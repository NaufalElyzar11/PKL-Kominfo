<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('cuti', function (Blueprint $table) {
        if (Schema::hasColumn('cuti', 'id_pegawai')) {
            $table->renameColumn('id_pegawai', 'user_id');
        }
    });
}

public function down(): void
{
    Schema::table('cuti', function (Blueprint $table) {
        if (Schema::hasColumn('cuti', 'user_id')) {
            $table->renameColumn('user_id', 'id_pegawai');
        }
    });
}

};
