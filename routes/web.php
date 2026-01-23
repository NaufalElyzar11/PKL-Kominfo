<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\KepalaDinas\DashboardController as KadisDashboard;
use App\Http\Controllers\Pegawai\PegawaiController as PegawaiDashboard;

use App\Http\Controllers\Admin\PegawaiController as AdminPegawaiController;
use App\Http\Controllers\Admin\CutiController as AdminCutiController;
use App\Http\Controllers\Pegawai\PengajuanCutiController as PegawaiCutiController;

use App\Http\Controllers\SuperAdmin\ProfileController as SuperProfileController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Pegawai\ProfileController as PegawaiProfileController;
use App\Http\Controllers\KepalaDinas\ProfileController as KadisProfileController;

// Auth Routes
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

Route::view('/', 'landingpage')->name('landingpage');

// Dashboard Redirect Logic
Route::get('/dashboard', function () {
    if (!Auth::check()) return redirect()->route('login');
    
    $user = Auth::user();
    // Sesuaikan nama role dengan yang ada di database Anda
    if ($user->role === 'super_admin') return redirect()->route('super.dashboard');
    if ($user->role === 'admin') return redirect()->route('admin.dashboard');
    if ($user->role === 'kepala_dinas') return redirect()->route('kepaladinas.dashboard');
    if ($user->role === 'pegawai') return redirect()->route('pegawai.dashboard');

    return redirect('/');
})->name('dashboard');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // --- SUPER ADMIN ---
    Route::prefix('super')->as('super.')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [SuperProfileController::class, 'index'])->name('index');
            Route::get('/edit', [SuperProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [SuperProfileController::class, 'update'])->name('update');
        });
    });

    // --- ADMIN ---
    Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::resource('pegawai', AdminPegawaiController::class);
        Route::resource('cuti', AdminCutiController::class);
        
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [AdminProfileController::class, 'index'])->name('index');
            Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
        });
    });

    // --- KEPALA DINAS / ATASAN ---
    // Pastikan middleware 'role:kepala_dinas' sesuai dengan isi kolom role di DB
    Route::prefix('kepaladinas')->as('kepaladinas.')->middleware('role:kepala_dinas')->group(function () {
        Route::get('/dashboard', [KadisDashboard::class, 'index'])->name('dashboard');
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [KadisProfileController::class, 'show'])->name('show');
            Route::get('/edit', [KadisProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [KadisProfileController::class, 'update'])->name('update');
        });
    });

    // --- PEGAWAI ---
    Route::prefix('pegawai')->as('pegawai.')->middleware('role:pegawai')->group(function () {
        Route::get('/dashboard', [PegawaiDashboard::class, 'index'])->name('dashboard');
        
        // Cuti
        Route::get('/cuti/export-excel', [PegawaiCutiController::class, 'exportExcel'])->name('cuti.export-excel');
        Route::resource('cuti', PegawaiCutiController::class)->except(['show']);
        Route::post('/cuti/ajax-store', [PegawaiCutiController::class, 'ajaxStore'])->name('cuti.ajax-store');

        // Profile (Fix untuk error Undefined Method edit)
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [PegawaiProfileController::class, 'show'])->name('show');
            Route::get('/edit', [PegawaiProfileController::class, 'edit'])->name('edit');
            Route::patch('/update', [PegawaiProfileController::class, 'update'])->name('update');
            Route::put('/password', [PegawaiProfileController::class, 'updatePassword'])->name('password.update');
        });
    });
});