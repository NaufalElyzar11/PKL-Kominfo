<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftar Role Baru sesuai permintaan Anda
        $roles = [
            'superadmin',
            'admin',
            'atasan',
            'pejabat',
            'pegawai',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }
    }
}