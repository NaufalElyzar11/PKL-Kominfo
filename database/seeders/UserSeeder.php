<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name'       => 'Administrator',
            'email'      => 'admin@gmail.com',
            'password'   => Hash::make('Admin*1234'),
            'role'       => 'Admin',
            
            // --- Tambahan Wajib (Sesuai Tabel Database Kamu) ---
            'nip'        => '11223344',    // Wajib diisi sembarang angka
            'telepon'    => '0812345678', // Wajib diisi
            'jabatan'    => 'Super Admin',
            'unit_kerja' => 'IT Pusat',
            // ---------------------------------------------------

            'id_pegawai' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}