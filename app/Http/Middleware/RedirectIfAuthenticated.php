<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // PERBAIKAN: Sesuaikan Case dengan role yang ada di web.php
                switch ($user->role) {
                    case 'super_admin':
                        return redirect()->route('super.dashboard');
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'atasan': // Tambahkan ini
                        return redirect()->route('atasan.dashboard');
                    case 'pejabat': // Tambahkan ini
                        return redirect()->route('pejabat.dashboard');
                    case 'pegawai':
                        return redirect()->route('pegawai.dashboard');
                    default:
                        // Jika role tidak dikenal, amankan dengan logout
                        Auth::logout();
                        return redirect()->route('login')->with('error', 'Role tidak dikenali.');
                }
            }
        }

        return $next($request);
    }
}