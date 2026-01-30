<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'user_id',
        'id_pegawai',
        'id_delegasi',
        'nama',
        'nip',
        'jabatan',
        'alamat',
        'jenis_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'tahun',
        'keterangan',
        'status',
        'catatan_penolakan',
        'id_atasan_langsung',
        'id_pejabat_pemberi_cuti',
        'atasan_nama',
        'pejabat_nama',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_hari'     => 'integer',
        'tahun'           => 'integer',
    ];

    // ================================
    // RELASI
    // ================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pegawai()
    {
        return $this->hasOneThrough(
            Pegawai::class,
            User::class,
            'id',           
            'id',           
            'user_id',      
            'id_pegawai'    
        );
    }

    public function atasanLangsung()
    {
        return $this->belongsTo(AtasanLangsung::class, 'id_atasan_langsung');
    }

    public function pejabatPemberiCuti()
    {
        return $this->belongsTo(PejabatPemberiCuti::class, 'id_pejabat_pemberi_cuti');
    }

    // ================================
    // ACCESSOR (Untuk kemudahan di Blade)
    // ================================

    public function getAlasanCutiAttribute(): string
    {
        return $this->keterangan ?? '-';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match (strtolower($this->status)) {
            'disetujui' => '<span class="px-2 py-1 text-[10px] bg-green-100 text-green-700 rounded-full font-bold">Disetujui</span>',
            'ditolak'   => '<span class="px-2 py-1 text-[10px] bg-red-100 text-red-700 rounded-full font-bold">Ditolak</span>',
            default     => '<span class="px-2 py-1 text-[10px] bg-yellow-100 text-yellow-700 rounded-full font-bold">Menunggu</span>',
        };
    }

    public function delegasi()
    {
        return $this->belongsTo(Pegawai::class, 'id_delegasi');
    }
}