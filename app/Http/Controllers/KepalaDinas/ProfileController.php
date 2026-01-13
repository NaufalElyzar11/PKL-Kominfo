<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Tampilkan profil Kepala Dinas.
     */
    public function show()
    {
        // Pastikan relasi pegawai ikut diload
        $user = Auth::user()->load('pegawai');

        return view('kepaladinas.profile.profile', compact('user'));
    }

    /**
     * Update data profil Kepala Dinas.
     */
    public function update(Request $request)
    {
        $user = Auth::user()->load('pegawai');

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ],[
            'password.regex' => 'Password harus mengandung huruf besar dan angka.'
        ]);

        // Update data user
        $user->name  = $request->name;
        $user->email = $request->email;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Jika Kepala Dinas juga punya data pegawai → update juga (opsional)
        if ($user->pegawai) {
            $user->pegawai->update([
                'nama'       => $request->name, // otomatis ikut berubah
            ]);
        }

        return redirect()
            ->route('kepaladinas.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
