<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;

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

        // ===========================
        // EMAIL PRIVASI (ditambahkan)
        // contoh: f****@gmail.com
        // ===========================
        $email = $pegawai->email ?? $user->email;
        $privateEmail = preg_replace('/(.).+(@.+)/', '$1****$2', $email);

        return view('pegawai.profile.profile', [
            'user' => $user,
            'pegawai' => $pegawai,
            'privateEmail' => $privateEmail
        ]);
    }
}
