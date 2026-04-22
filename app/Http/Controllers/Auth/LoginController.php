<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter; // Wajib di-import
use Illuminate\Support\Str;                 // Wajib di-import

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_identifier' => ['required', 'string'],
            'password'         => ['required'],
        ]);

        // 1. Tentukan kunci unik berdasarkan identifier (nama/NIP) dan IP user
        $throttleKey = Str::lower($request->login_identifier) . '|' . $request->ip();

        // 2. CEK: Apakah user ini sudah diblokir
        if (RateLimiter::tooManyAttempts($throttleKey, 4)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            // Kita tambahkan ID 'countdown' agar bisa diakses JavaScript
            return back()->withInput()->with('error', 
                "Login Gagal. Terlalu banyak percobaan. Sistem mengunci akses Anda sementara. Silakan coba lagi dalam <span id='countdown' class='font-black underline'>$seconds</span> detik."
            );
        }

        $identifier = $request->login_identifier;
        
        // Cari user di database
        $user = User::where('name', $identifier)
            ->orWhereHas('pegawai', function ($query) use ($identifier) {
                $query->where('nip', $identifier);
            })
            ->first();

        // 3. VALIDASI: Cek user ada DAN password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            
            // GAGAL: Catat percobaan gagal ke dalam sistem (lockout 60 detik jika limit tercapai)
            RateLimiter::hit($throttleKey, 60); 

            $errorMessage = !$user ? 'Nama Lengkap atau NIP tidak terdaftar!' : 'Kata sandi salah!';
            return back()->withInput()->with('error', $errorMessage);
        }

        // 4. BERHASIL: Bersihkan catatan kegagalan (reset counter)
        RateLimiter::clear($throttleKey);

        // Eksekusi Login
        Auth::login($user);
        $request->session()->regenerate();

        $role = strtolower($user->role);
        $allowedRoles = ['super_admin', 'admin', 'atasan', 'pegawai', 'pejabat'];

        if (!in_array($role, $allowedRoles)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hak akses tidak dikenali.');
        }

        // Sinkronisasi Spatie Roles (jika pakai library Spatie)
        if (!$user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        return $this->redirectByRole($role);
    }

    protected function redirectByRole($role)
    {
        $routeMap = [
            'super_admin' => 'super.dashboard',
            'admin'       => 'admin.dashboard',
            'atasan'      => 'atasan.dashboard',
            'pegawai'     => 'pegawai.dashboard',
            'pejabat'     => 'pejabat.dashboard',
        ];

        return redirect()->route($routeMap[$role] ?? 'login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda berhasil logout.');
    }
}