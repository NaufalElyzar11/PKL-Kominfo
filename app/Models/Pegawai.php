<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'unit_kerja',
        'atasan',
        'pemberi_cuti',
        'status',
        'jatah_cuti',
        'kuota_cuti',
        'atasan_id',
        'email',
        'telepon',
    ];

    protected $appends = ['sisa_cuti'];

    protected $casts = [
        'jatah_cuti' => 'integer',
        'kuota_cuti' => 'integer',
    ];

    /**
     * PERBAIKAN PENTING:
     * Pegawai -> User adalah hasOne (1 Pegawai punya 1 Akun User)
     * Kuncinya ada di tabel users (namanya 'id_pegawai')
     */
    public function user()
    {
        // Ubah dari belongsTo menjadi hasOne
        return $this->hasOne(User::class, 'id_pegawai', 'id');
    }

    /**
     * PERBAIKAN RELASI CUTI:
     * Karena tabel Cuti tidak punya 'pegawai_id' (tapi punyanya 'user_id'),
     * kita harus tembus lewat tabel User.
     */
    public function cuti()
    {
        // Cara bacanya: Pegawai -> User -> Cuti
        return $this->hasManyThrough(
            Cuti::class, 
            User::class, 
            'id_pegawai', // FK di tabel users
            'user_id',    // FK di tabel cuti
            'id',         // PK di tabel pegawai
            'id'          // PK di tabel users
        );
    }

    /**
     * Pegawai → Catatan Cuti (hasMany)
     */
    public function catatanCuti()
    {
        return $this->hasMany(CatatanCuti::class, 'pegawai_id');
    }

    /**
     * Hitung sisa cuti otomatis
     */
    public function getSisaCutiAttribute(): int
    {
        $tahun = date('Y');

        $catatan = $this->catatanCuti()->where('tahun', $tahun)->first();
        $terpakai = $catatan?->terpakai ?? 0;

        return max(0, ($this->kuota_cuti ?? 0) - $terpakai);
    }

    /**
     * Relasi: Atasan langsung (pegawai juga)
     */
    public function atasanLangsung()
    {
        return $this->belongsTo(Pegawai::class, 'atasan_id');
    }

    /**
     * Relasi: Daftar bawahan pegawai
     */
    public function bawahan()
    {
        return $this->hasMany(Pegawai::class, 'atasan_id');
    }
}