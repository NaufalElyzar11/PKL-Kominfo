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
        $user = Auth::user(); // Lebih aman menggunakan Auth::user()

        // 1. Validasi Input
        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telepon' => [
                'required', 
                'string', 
                'min:10', // Biasanya minimal 10 digit
                'max:13', // Memberi ruang untuk format +62
                'regex:/^[0-9]+$/'
            ],
        ]);

        DB::beginTransaction();
        try {
            // 2. Update Tabel Users (Data untuk Login)
            $user->update([
                'name'  => $validated['nama'],
                'email' => $validated['email'],
            ]);

            // 3. Update Tabel Pegawai (Data untuk Administrasi/Admin)
            // Kita gunakan relasi 'pegawai' yang ada di model User
            if ($user->pegawai) {
                $user->pegawai->update([
                    'nama'    => $validated['nama'],
                    'email'   => $validated['email'], // Simpan juga di tabel pegawai agar tidak NULL
                    'telepon' => $validated['telepon'],
                ]);
            }

            DB::commit();
            return back()->with('success', 'Profil Anda berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Membantu debugging jika ada error database
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
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