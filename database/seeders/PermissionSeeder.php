<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1. Daftar Permission
        // ============================================
        $permissions = [
            // Admin
            'kelola pegawai',
            'kelola cuti',
            'lihat laporan',

            // Pegawai
            'ajukan cuti',
            'lihat status cuti',

            // Kepala
            'validasi cuti',
            'lihat rekap cuti',
        ];

        // ============================================
        // 2. Buat Permission jika belum ada
        // ============================================
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // ============================================
        // 3. Buat Role (pastikan ada)
        // ============================================
        $roles = [
            'super-admin',
            'admin',
            'pegawai',
            'kepala'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }

        // Ambil ulang setelah dibuat
        $superAdmin = Role::findByName('super-admin');
        $admin      = Role::findByName('admin');
        $pegawai    = Role::findByName('pegawai');
        $kepala     = Role::findByName('kepala');

        // ============================================
        // 4. Assign Permission ke Role
        // ============================================

        // Super Admin → Semua permission otomatis
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin->syncPermissions([
            'kelola pegawai',
            'kelola cuti',
            'lihat laporan',
        ]);

        // Pegawai
        $pegawai->syncPermissions([
            'ajukan cuti',
            'lihat status cuti',
        ]);

        // Kepala
        $kepala->syncPermissions([
            'validasi cuti',
            'lihat rekap cuti',
        ]);
    }
}
