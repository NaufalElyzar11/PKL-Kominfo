<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    /**
     * Jika tabel bernama `admins`, maka sudah benar.
     * Jika berbeda, tinggal sesuaikan.
     */
    protected $table = 'admins';

    /**
     * Kolom yang bisa diisi (mass assignment)
     */
    protected $fillable = [
        'user_id',
        'nama',
        // tambah kolom lain bila ada
    ];

    /**
     * Relasi ke tabel users
     * Admin dimiliki oleh 1 user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
