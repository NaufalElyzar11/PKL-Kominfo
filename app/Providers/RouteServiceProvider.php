<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard'; 

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    public static function redirectToByRole($user)
    {
        if ($user->role === 'admin') {
            return route('admin.dashboard');
        } elseif ($user->role === 'superadmin') {
            return route('superadmin.dashboard');
        } elseif ($user->role === 'pegawai') {
            return route('pegawai.dashboard');
        } elseif ($user->role === 'kepaladinas') {
            return route('kepaladinas.dashboard');
        }

        return self::HOME; // default
    }
}
