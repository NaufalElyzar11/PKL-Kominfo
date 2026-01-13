<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanCuti extends Model
{
    use HasFactory;

    protected $table = 'catatan_cuti'; // pastikan nama tabel sesuai

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'terpakai',
    ];

    /**
     * Relasi: CatatanCuti milik satu Pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }
}
