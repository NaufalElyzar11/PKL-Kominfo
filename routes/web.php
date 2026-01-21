<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| AUTH CONTROLLER
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| DASHBOARD CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminController;
use App\Http\Controllers\KepalaDinas\DashboardController as KepalaDinasDashboardController;
use App\Http\Controllers\Pegawai\PegawaiController as PegawaiDashboardController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\PegawaiController as AdminPegawaiController;
use App\Http\Controllers\Admin\CutiController as AdminCutiController;

/*
|--------------------------------------------------------------------------
| PEGAWAI CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Pegawai\PengajuanCutiController as PegawaiCutiController;


/*
|--------------------------------------------------------------------------
| PROFILE CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\SuperAdmin\ProfileController as SuperProfileController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Pegawai\ProfileController as PegawaiProfileController;
use App\Http\Controllers\KepalaDinas\ProfileController as KepalaDinasProfileController;

/*
|--------------------------------------------------------------------------
| KEPALA DINAS CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\KepalaDinas\DataPegawaiController;
use App\Http\Controllers\KepalaDinas\DataCutiController;

/*
|--------------------------------------------------------------------------
| SUPER ADMIN CONTROLLERS TAMBAHAN
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminDataController;
use App\Http\Controllers\SuperAdmin\LaporanController as SuperLaporanController;
use App\Http\Controllers\SuperAdmin\PengaturanController as SuperPengaturanController;
use App\Http\Controllers\SuperAdmin\LogController as SuperLogController;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES (HARUS PALING ATAS)
|--------------------------------------------------------------------------
*/
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});


/*
|--------------------------------------------------------------------------
| LANDING PAGE
|--------------------------------------------------------------------------
*/
Route::view('/', 'landingpage')->name('landingpage');


/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT OTOMATIS BERDASARKAN ROLE
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->hasRole('super-admin')) {
        return redirect()->route('super.dashboard');
    }
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('kadis')) {
        return redirect()->route('kepaladinas.dashboard');
    }
    if ($user->hasRole('pegawai')) {
        return redirect()->route('pegawai.dashboard');
    }

    return redirect('/');
})->name('dashboard');



/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {


  /*
|--------------------------------------------------------------------------
| SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('super')->as('super.')->middleware(['auth','role:super_admin'])->group(function () {

    // Dashboard Super Admin
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');

    // Manajemen Admin
    Route::get('/admin', [SuperAdminDataController::class, 'index'])->name('admin.index');

    // Pengaturan
    Route::get('/pengaturan', [SuperPengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan/autosave', [SuperPengaturanController::class, 'autoSave'])->name('pengaturan.autosave');

    // Profil Super Admin
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [SuperProfileController::class, 'index'])->name('index');   // sebelumnya show -> index
        Route::get('/edit', [SuperProfileController::class, 'edit'])->name('edit');
        Route::put('/', [SuperProfileController::class, 'update'])->name('update'); // ubah /update jadi / agar sesuai RESTful
    });
});


    /*
   /*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    Route::resource('pegawai', AdminPegawaiController::class);
    Route::resource('cuti', AdminCutiController::class);

    // Profile
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index'])->name('index');
        Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
    });

    // Atasan Langsung (untuk modal)
    Route::prefix('atasan')->as('atasan.')->group(function () {
        Route::post('/store', [\App\Http\Controllers\Admin\AtasanLangsungController::class, 'store'])->name('store');
    });

    // Pemberi Cuti (untuk modal)
    Route::prefix('pemberi-cuti')->as('pemberi_cuti.')->group(function () {
        Route::post('/store', [\App\Http\Controllers\Admin\PemberiCutiController::class, 'store'])->name('store');
    });
});


    /*
   /*
/*
|--------------------------------------------------------------------------
| KEPALA DINAS ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('kepaladinas')
    ->as('kepaladinas.')
    ->middleware('role:kadis')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [KepalaDinasDashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */
        Route::prefix('profile')->as('profile.')->group(function () {

            Route::get('/', [KepalaDinasProfileController::class, 'show'])
                ->name('show');

            Route::get('/edit', [KepalaDinasProfileController::class, 'edit'])
                ->name('edit');

            Route::put('/update', [KepalaDinasProfileController::class, 'update'])
                ->name('update');
        });


        /*
        |--------------------------------------------------------------------------
        | DATA PEGAWAI
        |--------------------------------------------------------------------------
        */
        Route::prefix('datapegawai')->as('datapegawai.')->group(function () {

            Route::get('/', [DataPegawaiController::class, 'index'])
                ->name('index');

            Route::get('/{id}', [DataPegawaiController::class, 'show'])
                ->name('show');

            Route::get('/{id}/edit', [DataPegawaiController::class, 'edit'])
                ->name('edit');

            Route::delete('/{id}', [DataPegawaiController::class, 'destroy'])
                ->name('destroy');
        });


        /*
        |--------------------------------------------------------------------------
        | DATA CUTI
        |--------------------------------------------------------------------------
        */
        Route::prefix('datacuti')->as('datacuti.')->group(function () {

            Route::get('/', [DataCutiController::class, 'index'])
                ->name('index');

            Route::post('/', [DataCutiController::class, 'store'])
                ->name('store');

            // Halaman detail / meninjau cuti
            Route::get('/{id}/menyetujui', [DataCutiController::class, 'menyetujui'])
                ->name('menyetujui');

            // Aksi approve / reject
            Route::put('/{id}/approve', [DataCutiController::class, 'approve'])
                ->name('approve');

            Route::put('/{id}/reject', [DataCutiController::class, 'reject'])
                ->name('reject');

            Route::delete('/{id}', [DataCutiController::class, 'destroy'])
                ->name('destroy');
        });

    });


   /*
|/*
|--------------------------------------------------------------------------
| PEGAWAI ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('pegawai')->as('pegawai.')->middleware('role:pegawai')->group(function () {

    // Dashboard Pegawai
    Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');

    // Route Export Excel (Tambahkan di sini)
    Route::get('/cuti/export-excel', [PegawaiCutiController::class, 'exportExcel'])->name('cuti.export-excel');

    // Resource Cuti (kecuali show)
    Route::resource('cuti', PegawaiCutiController::class)->except(['show']);

    // Route AJAX untuk submit cuti agar langsung tampil di tabel
    Route::post('/cuti/ajax-store', [PegawaiCutiController::class, 'ajaxStore'])->name('cuti.ajax-store');

    // Profile Pegawai
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [PegawaiProfileController::class, 'show'])->name('show');
        Route::get('/edit', [PegawaiProfileController::class, 'edit'])->name('edit');     // Wajib Ada
        Route::patch('/update', [PegawaiProfileController::class, 'update'])->name('update'); // Wajib Ada
    });
});
});
