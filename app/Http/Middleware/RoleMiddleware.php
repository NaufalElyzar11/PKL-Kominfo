<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Jika belum login
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil role dari kolom users.role
        $userRole = $user->role;

        // Jika role user tidak diizinkan untuk halaman ini
        if (!in_array($userRole, $roles)) {

            return $this->redirectByRole($userRole);
        }

        return $next($request);
    }

    private function redirectByRole($role)
    {
        $map = [
            'admin'  => 'admin.dashboard',
            'atasan' => 'atasan.dashboard',
            'pegawai' => 'pegawai.dashboard.index',
        ];

        $route = $map[$role] ?? 'login';

        if (Route::has($route)) {
            return redirect()->route($route);
        }

        Auth::logout();
        return redirect()->route('login')->with('error', 'Role tidak memiliki akses.');
    }
}
