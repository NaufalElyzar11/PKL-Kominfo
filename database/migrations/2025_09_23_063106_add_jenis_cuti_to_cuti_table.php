<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {


    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropColumn('jenis_cuti');
        });
    }
};
