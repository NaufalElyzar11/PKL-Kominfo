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
    'atasan_id',
    'pemberi_cuti_id',
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
];


    protected $casts = [
        'tanggal_mulai'   => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
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

    // Accessor untuk mendapatkan pegawai dari user
    // Ini bukan relationship, tapi property accessor yang memanfaatkan user relationship
    public function getPegawaiAttribute()
    {
        return $this->user?->pegawai;
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
    // ACCESSOR
    // ================================

    public function getAlasanCutiAttribute(): string
    {
        return $this->keterangan ?? '-';
    }

    public function getPeriodeCutiAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }

        return $this->tanggal_mulai->format('d M Y') . ' - ' . $this->tanggal_selesai->format('d M Y');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Disetujui</span>',
            'ditolak'   => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Ditolak</span>',
            default     => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Menunggu</span>',
        };
    }
}
