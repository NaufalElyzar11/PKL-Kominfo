<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtasanLangsung extends Model
{
    use HasFactory;

    protected $table = 'atasan_langsung'; // Pastikan nama tabel sesuai migration

    protected $fillable = [
        'nama_atasan',
        'nip',
        'jabatan',
    ];

    /**
     * Relasi ke Pegawai (Satu atasan bisa membawahi banyak pegawai)
     */
    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_atasan_langsung');
    }
}