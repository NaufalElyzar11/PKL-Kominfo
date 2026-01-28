<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.dashboard')
                ->with('error', 'Data profil pegawai tidak ditemukan.');
        }

        return view('pegawai.profile.edit', [
            'user' => $user,
            'pegawai' => $pegawai,
        ]);
    }

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
     * Menyimpan pembaruan data profil dan foto
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email'   => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telepon' => ['required', 'string', 'min:10', 'max:13', 'regex:/^[0-9]+$/'],
            'foto'    => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            $fotoPath = $pegawai->foto;

            // 1. Cek jika user meminta hapus foto
            if ($request->hapus_foto == '1') {
                if ($pegawai->foto) {
                    Storage::disk('public')->delete($pegawai->foto);
                }
                $fotoPath = null;
            }

            // 2. Cek jika ada upload foto baru
            if ($request->hasFile('foto')) {
                if ($pegawai->foto) {
                    Storage::disk('public')->delete($pegawai->foto);
                }
                $fotoPath = $request->file('foto')->store('profile_photos', 'public');
            }

            // 3. Update User
            $user->update([
                'name'  => $validated['nama'],
                'email' => $validated['email'],
            ]);

            // 4. Update Pegawai
            $pegawai->update([
                'nama'    => $validated['nama'],
                'email'   => $validated['email'],
                'telepon' => $validated['telepon'],
                'foto'    => $fotoPath,
            ]);

            DB::commit();
            return back()->with('success', 'Profil Anda berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        // Gunakan validateWithBag agar pesan error sinkron dengan variabel $errors->updatePassword di Blade
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        // Update password di database menggunakan Hash
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}