<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form edit profil.
     * INI ADALAH FUNGSI YANG HILANG (Mengatasi error Undefined Method edit)
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        // Mengambil relasi pegawai dari user yang sedang login
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.dashboard')
                ->with('error', 'Data profil pegawai tidak ditemukan.');
        }

        // Ubah 'profile' menjadi 'edit' agar mengarah ke halaman formulir
        return view('pegawai.profile.edit', [
            'user' => $user,
            'pegawai' => $pegawai,
        ]);
    }

    /**
     * Opsional: Jika Anda masih butuh halaman preview (tampilan saja)
     */
    public function show()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.dashboard')->with('error', 'Data tidak ditemukan.');
        }

        return view('pegawai.profile.profile', [
            'user' => $user,
            'pegawai' => $pegawai,
        ]);
    }

    /**
     * Menyimpan pembaruan data dari pegawai ke database
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validasi Input
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telepon' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'],
        ]);

        DB::beginTransaction();
        try {
            // 2. Update Tabel Users (agar login & email sistem berubah)
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'],
            ]);

            // 3. Update Tabel Pegawai (agar muncul di data Admin)
            if ($user->pegawai) {
                $user->pegawai->update([
                    'nama' => $validated['nama'],
                    'email' => $validated['email'],
                    'telepon' => $validated['telepon'],
                ]);
            }

            DB::commit();
            // Kembali ke halaman edit dengan pesan sukses
            return Redirect::route('pegawai.profile.edit')->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
{
    $validated = $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', Password::defaults(), 'confirmed'],
    ]);

    $request->user()->update([
        'password' => Hash::make($validated['password']),
    ]);

    return back()->with('status', 'password-updated');
}
}