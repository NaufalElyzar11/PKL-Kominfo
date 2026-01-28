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
        'status', 
        'atasan', 
        'pemberi_cuti',
        'telepon',
        'foto',
        'email',
        'kuota_cuti',
        'sisa_cuti'
    ];

    protected $appends = ['sisa_cuti'];

    protected $casts = [
        'kuota_cuti' => 'integer',
    ];

    /**
     * Relasi ke Akun User (Login)
     * 1 Pegawai memiliki 1 User
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id_pegawai', 'id');
    }

    /**
     * Relasi ke Tabel Cuti (Melalui User)
     * Karena tabel Cuti menggunakan 'user_id' bukan 'pegawai_id'
     */
    public function cuti()
    {
        return $this->hasManyThrough(
            Cuti::class, 
            User::class, 
            'id_pegawai', // Foreign Key di tabel users
            'user_id',    // Foreign Key di tabel cuti
            'id',         // Local Key di tabel pegawai
            'id'          // Local Key di tabel users
        );
    }

    /**
     * Relasi ke Master Atasan Langsung
     */
    public function atasanLangsung()
    {
        return $this->belongsTo(AtasanLangsung::class, 'id_atasan_langsung');
    }

    /**
     * Relasi ke Master Pejabat Pemberi Cuti
     */
    public function pejabatPemberiCuti()
    {
        return $this->belongsTo(PejabatPemberiCuti::class, 'id_pejabat_pemberi_cuti');
    }

    /**
     * Accessor untuk hitung sisa cuti secara Real-time
     * Menghitung total cuti yang 'Disetujui' dan 'Menunggu' tahun ini
     */
    public function getSisaCutiAttribute(): int
    {
        $tahun = date('Y');
        
        // PERBAIKAN: Hanya hitung cuti yang statusnya SUDAH 'Disetujui' (Final)
        // Abaikan status 'Menunggu' dan 'Disetujui Atasan' agar jatah tidak berkurang duluan
        $terpakai = $this->cuti()
            ->where('tahun', $tahun)
            ->where('status', 'Disetujui') 
            ->sum('jumlah_hari');

        // Menggunakan kuota_cuti dari database, default 12 jika kosong
        return max(0, ($this->kuota_cuti ?? 12) - $terpakai);
    }
}