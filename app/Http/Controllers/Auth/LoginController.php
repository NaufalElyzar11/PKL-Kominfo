<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        // 1. Ubah validasi: 'name' diubah menjadi 'login_identifier' agar lebih umum
        $request->validate([
            'login_identifier' => ['required', 'string'],
            'password'         => ['required'],
        ]);

        $identifier = $request->login_identifier;

        // 2. Cari user berdasarkan Nama Lengkap (tabel users) 
        // ATAU berdasarkan NIP (tabel pegawai) melalui relasi
        $user = User::where('name', $identifier)
            ->orWhereHas('pegawai', function ($query) use ($identifier) {
                $query->where('nip', $identifier);
            })
            ->first();

        // 3. Penyesuaian pesan error jika akun tidak ditemukan
        if (!$user) {
            return back()->withInput()->with('error', 'Nama Lengkap atau NIP tidak terdaftar!');
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'Kata sandi salah!');
        }

        // Login user dan regenerate session
        Auth::login($user);
        $request->session()->regenerate();

        $role = strtolower($user->role);

        // Pastikan role diizinkan
        $allowedRoles = ['super_admin', 'admin', 'atasan', 'pegawai', 'pejabat'];

        if (!in_array($role, $allowedRoles)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hak akses tidak dikenali.');
        }

        // Sinkronisasi Spatie Roles (tetap sama)
        if (!$user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        return $this->redirectByRole($role);
    }

    // Perbaikan Mapping Route (Pastikan kunci array sama dengan isi kolom role di DB)
    protected function redirectByRole($role)
    {
        $routeMap = [
            'super_admin'  => 'super.dashboard',
            'admin'        => 'admin.dashboard',
            'atasan'       => 'atasan.dashboard',
            'pegawai'      => 'pegawai.dashboard',
            'pejabat'     => 'pejabat.dashboard',
        ];

        return redirect()->route($routeMap[$role] ?? 'login');
    }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda berhasil logout.');
    }
}
