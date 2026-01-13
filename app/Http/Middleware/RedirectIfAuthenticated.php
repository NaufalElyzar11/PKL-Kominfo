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

                // Redirect berdasarkan role dari database
                switch ($user->role) {
                    case 'super_admin':
                        return redirect()->route('super.dashboard');
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'kepala_dinas':
                        return redirect()->route('kepaladinas.dashboard');
                    case 'pegawai':
                        return redirect()->route('pegawai.dashboard');
                    default:
                        Auth::logout();
                        return redirect()->route('login');
                }
            }
        }

        return $next($request);
    }
}
