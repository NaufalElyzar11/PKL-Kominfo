<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * 🔹 Tampilkan halaman profil admin.
     */
    public function index()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai ?? null;

        return view('admin.profile.profile', compact('user', 'pegawai'));
    }

    /**
     * 🔹 TAMBAHKAN FUNGSI INI: Tampilkan halaman form edit.
     * Ini yang dicari oleh rute 'admin.profile.edit'
     */
    public function edit()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai ?? null;

        // Pastikan Anda sudah memiliki file: resources/views/admin/profile/edit.blade.php
        return view('admin.profile.edit', compact('user', 'pegawai'));
    }

    /**
     * 🔹 Update profil admin.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Pastikan nama field di sini SAMA dengan atribut 'name' di Blade
        $validated = $request->validate([
            'nama'    => 'required|string|max:100', // Sesuai name="nama"
            'email'   => 'required|email|max:100|unique:users,email,' . $user->id,
            'telepon' => 'nullable|string|max:13', // Sesuai name="telepon"
        ]);

        // 1. Update tabel Users
        $user->update([
            'name'  => $validated['nama'], // Simpan ke kolom 'name' di tabel users
            'email' => $validated['email'],
        ]);

        // 2. Update tabel Pegawai melalui relasi
        if ($user->pegawai) {
            $user->pegawai->update([
                'nama'    => $validated['nama'],
                'telepon' => $validated['telepon'], // Simpan ke kolom 'telepon' di tabel pegawai
            ]);
        }

        return redirect()->route('admin.profile.index')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * 🔹 Update password admin.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validasi password baru
        $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak cocok!']);
        }

        // Pastikan password baru tidak sama dengan password lama
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        // Update password
        $user->update(['password' => Hash::make($request->password)]);

        // Logout setelah update
        Auth::logout();

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil diperbarui! Silakan login kembali.');
    }
}
