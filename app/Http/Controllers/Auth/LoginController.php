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
        // Validasi input
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput()->with('email_error', 'Email tidak terdaftar!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('password_error', 'Password salah!');
        }

        // Login user dan regenerate session untuk keamanan
        Auth::login($user);
        $request->session()->regenerate();

        // Ambil role dari kolom user->role
        $role = strtolower($user->role);

        // Cek role Spatie, jika belum ada, tambahkan
        if (!in_array($role, ['super_admin','admin','kadis','pegawai'])) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak dikenali.');
        }

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
