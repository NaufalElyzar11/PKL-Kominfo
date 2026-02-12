<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use App\Models\Pegawai;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip',
        'telepon',
        'jabatan',
        'unit_kerja',
        'id_pegawai',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * PERBAIKAN 1: Relasi User → Pegawai
     * Gunakan belongsTo karena kolom 'id_pegawai' ada di tabel users ini.
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    /**
     * PERBAIKAN 2: Relasi Cutinya User
     * Cuti terhubung lewat user_id, bukan pegawai_id
     */
    public function cuti()
    {
        return $this->hasMany(Cuti::class, 'user_id', 'id');
    }

    /**
     * Relasi Custom Notification (Bukan bawaan Laravel)
     */
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class, 'user_id');
    }

    /**
     * Relasi: User sebagai atasan langsung
     */
    public function cutiSebagaiAtasanLangsung()
    {
        return $this->hasMany(Cuti::class, 'id_atasan_langsung', 'id');
    }

    /**
     * Relasi: User sebagai pemberi cuti
     */
    public function cutiSebagaiPemberiCuti()
    {
        return $this->hasMany(Cuti::class, 'id_pemberi_cuti', 'id');
    }

    /**
     * Mutator otomatis hash password
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
    }

    // Accessor untuk jaga-jaga kalau ada kodingan lama yg panggil 'nama'
    public function getNamaAttribute()
    {
        return $this->attributes['name'] ?? null;
    }
    
    public function isRole($role)
    {
        return $this->role === $role;
    }
}