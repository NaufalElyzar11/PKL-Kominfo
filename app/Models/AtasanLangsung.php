<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtasanLangsung extends Model
{
    use HasFactory;

    protected $table = 'atasan_langsung';

    protected $fillable = [
        'nama_atasan',
        'nip_atasan',
        'jabatan_atasan',
    ];

    /**
     * Relasi ke cuti yang diajukan pegawai di bawah atasan ini
     */
    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'id_atasan_langsung');
    }
}
