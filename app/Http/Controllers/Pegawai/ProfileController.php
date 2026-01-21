<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $email = $pegawai->email ?? $user->email;
        $privateEmail = preg_replace('/(.).+(@.+)/', '$1****$2', $email);

        return view('pegawai.profile.profile', [
            'user' => $user,
            'pegawai' => $pegawai,
            'privateEmail' => $privateEmail
        ]);
    }

    public function edit(Request $request)
    {
        return view('admin.profile.edit', [ 
            'user' => $request->user(),
            'pegawai' => $request->user()->pegawai
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user();
            
            // 1. Update Tabel Users (untuk login)
            $user->fill($validated);
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();

            // 2. Sinkronisasi ke Tabel Pegawai (PENTING)
            if ($user->pegawai) {
                $user->pegawai->update([
                    'nama' => $validated['name'],
                    'email' => $validated['email']
                ]);
            }

            DB::commit();
            return Redirect::route('pegawai.profile.show')->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}