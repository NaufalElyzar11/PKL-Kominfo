<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User; // <--- Tambahkan baris ini biar kenal Model User

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role-nya dulu (Ini kodingan aslimu)
        $roles = [
            'super-admin',
            'admin',
            'kadis',
            'pegawai',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }

        // 2. BAGIAN BARU: Sambungkan ke User Admin
        // Kita cari user yang tadi dibuat di UserSeeder
        $user = User::where('email', 'admin@gmail.com')->first();
        
        // Kalau usernya ketemu, langsung tempel role 'admin'
        if ($user) {
            $user->assignRole('admin');
        }
    }
}