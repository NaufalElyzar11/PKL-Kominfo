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
        // Nama route: {role}.dashboard
        $route = $role . '.dashboard';

        if (Route::has($route)) {
            return redirect()->route($route);
        }

        // Role tidak valid → logout
        Auth::logout();

        return redirect()->route('login')
            ->with('error', 'Role tidak memiliki akses.');
    }
}
