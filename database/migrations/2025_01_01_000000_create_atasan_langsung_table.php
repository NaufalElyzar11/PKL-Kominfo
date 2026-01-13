<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atasan_langsung', function (Blueprint $table) {
            $table->id();
            $table->string('nama_atasan', 100);
            $table->string('nip_atasan', 30)->nullable();
            $table->string('jabatan_atasan', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atasan_langsung');
    }
};
