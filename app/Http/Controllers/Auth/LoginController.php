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
        // 1. Ubah validasi dari 'email' menjadi 'name'
        $request->validate([
            'name'     => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 2. Cari user berdasarkan kolom 'name' (Nama Lengkap)
        $user = User::where('name', $request->name)->first();

        // 3. Penyesuaian pesan error
        if (!$user) {
            return back()->withInput()->with('error', 'Nama Lengkap tidak terdaftar!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'Kata sandi salah!');
        }

        // Login user dan regenerate session
        Auth::login($user);
        $request->session()->regenerate();

        // Ambil role dan pastikan formatnya kecil (lowercase)
        $role = strtolower($user->role);

        // 4. Update daftar role agar sinkron dengan PegawaiController Anda
        // Menambahkan 'kepala_dinas' dan 'pemberi_cuti' ke dalam daftar izin
        $allowedRoles = ['super_admin', 'admin', 'kepala_dinas', 'pegawai', 'pemberi_cuti'];

        if (!in_array($role, $allowedRoles)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hak akses (role) tidak dikenali.');
        }

        // Sinkronisasi dengan Spatie Roles jika belum ada
        if (!$user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        return $this->redirectByRole($role);
    }

    // Redirect berdasarkan role
    protected function redirectByRole($role)
    {
        $routeMap = [
            'super_admin' => 'super.dashboard',
            'admin'       => 'admin.dashboard',
            'kadis'       => 'kepaladinas.dashboard',
            'pegawai'     => 'pegawai.dashboard',
        ];

        return redirect()->route($routeMap[$role]);
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
