<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PejabatPemberiCuti extends Model
{
    use HasFactory;

    protected $table = 'pejabat_pemberi_cuti'; // Pastikan nama tabel sesuai migration

    protected $fillable = [
        'nama_pejabat',
        'nip',
        'jabatan',
    ];

    /**
     * Relasi ke Pegawai (Satu pejabat bisa menyetujui cuti banyak pegawai)
     */
    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_pejabat_pemberi_cuti');
    }
}