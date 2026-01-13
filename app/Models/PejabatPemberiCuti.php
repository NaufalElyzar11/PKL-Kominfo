<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PejabatPemberiCuti extends Model
{
    use HasFactory;

    // Nama tabel sesuai database
    protected $table = 'pejabat_pemberi_cuti';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'nama_pejabat',
        'nip_pejabat',
        'jabatan_pejabat',
    ];

    /**
     * Relasi ke cuti yang disetujui oleh pejabat ini
     */
    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'id_pejabat_pemberi_cuti');
    }
}
