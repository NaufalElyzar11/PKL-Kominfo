<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data admin lama agar tidak duplikat
        User::where('email', 'admin@gmail.com')->delete();

        // Buat User Administrator baru
        $admin = User::create([
            'name'       => 'Administrator',
            'email'      => 'admin@gmail.com',
            'password'   => Hash::make('Admin*1234'),
            'role'       => 'admin', // Kolom tambahan di tabel users
            'nip'        => '11223344',
            'telepon'    => '0812345678', 
            'jabatan'    => 'Super Admin',
            'unit_kerja' => 'IT Pusat',
            'id_pegawai' => null,
        ]);

        // Tempelkan role Spatie (Pastikan namanya ada di RoleSeeder)
        $admin->assignRole('admin');
    }
}