<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale('id');

        // ✅ Set locale sistem ke Bahasa Indonesia
        setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia', 'id_ID');

        // Sekarang format tanggal seperti ->translatedFormat('d F Y')
        // akan otomatis menampilkan contoh: "13 Oktober 2025"
    }
}
