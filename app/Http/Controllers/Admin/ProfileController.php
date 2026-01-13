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

        // Ambil relasi pegawai (jika ada)
        $pegawai = $user->pegawai ?? null;

        // Kirim $user dan $pegawai ke Blade
        return view('admin.profile.profile', compact('user', 'pegawai'));
    }

    /**
     * 🔹 Update profil admin.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input profil
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
        ]);

        // Update data user di database
        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui!');
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
