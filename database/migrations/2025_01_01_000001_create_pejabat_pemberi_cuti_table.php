<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pejabat_pemberi_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pejabat', 100);
            $table->string('nip_pejabat', 30)->nullable();
            $table->string('jabatan_pejabat', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pejabat_pemberi_cuti');
    }
};
