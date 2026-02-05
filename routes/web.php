<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordWaController;
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
use App\Http\Controllers\KepalaDinas\DataPegawaiController as KadisDataPegawai;
use App\Http\Controllers\KepalaDinas\DataCutiController as KadisDataCuti;

use App\Http\Controllers\Atasan\ApprovalController as AtasanApprovalController;

use App\Http\Controllers\Pejabat\PejabatApprovalController;

// ------------------------------------------------------------------
// 1. GUEST & AUTH CORE
// ------------------------------------------------------------------
Route::view('/', 'landingpage')->name('landingpage');

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// Forgot Password via WhatsApp (Fonnte)
Route::prefix('forgot-password')->name('password.')->group(function () {
    Route::get('/', [ForgotPasswordWaController::class, 'showRequestForm'])->name('request');
    Route::post('/send', [ForgotPasswordWaController::class, 'sendResetToken'])->name('send');
    Route::get('/verify', [ForgotPasswordWaController::class, 'showVerifyForm'])->name('verify');
    Route::post('/verify', [ForgotPasswordWaController::class, 'verifyToken'])->name('verify.post');
    Route::post('/resend', [ForgotPasswordWaController::class, 'resendOtp'])->name('resend');
    Route::get('/reset', [ForgotPasswordWaController::class, 'showResetForm'])->name('reset');
    Route::post('/reset', [ForgotPasswordWaController::class, 'resetPassword'])->name('update');
});

// Logika Redirect Dashboard (Satu blok saja agar tidak bentrok)
Route::get('/dashboard', function () {
    if (!Auth::check()) return redirect()->route('login');
    
    $user = Auth::user();
    return match($user->role) {
        'super_admin'   => redirect()->route('super.dashboard'),
        'admin'         => redirect()->route('admin.dashboard'),
        'atasan'        => redirect()->route('atasan.dashboard'),
        'pegawai'       => redirect()->route('pegawai.dashboard'),
        'pejabat'       => redirect()->route('pejabat.dashboard'),
        default         => redirect('/'),
    };
})->name('dashboard');

// ------------------------------------------------------------------
// 2. PROTECTED ROUTES (LOGGED IN)
// ------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // --- ATASAN LANGSUNG (Approval Tahap 1) ---
    Route::prefix('atasan')->as('atasan.')->middleware('role:atasan')->group(function () {
    Route::get('/dashboard', [AtasanApprovalController::class, 'dashboard'])->name('dashboard');

        Route::prefix('approval')->as('approval.')->group(function () {
            Route::get('/', [AtasanApprovalController::class, 'index'])->name('index');
            Route::post('/{id}/setuju', [AtasanApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/tolak', [AtasanApprovalController::class, 'reject'])->name('reject');
        });

        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [PegawaiProfileController::class, 'show'])->name('show');
            Route::get('/edit', [PegawaiProfileController::class, 'edit'])->name('edit');
            Route::patch('/update', [PegawaiProfileController::class, 'update'])->name('update');
        });
    });

    // --- ADMIN ---
    Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::resource('pegawai', AdminPegawaiController::class);
        
        // 🔹 PINDAHKAN RUTE PDF KE SINI (DI ATAS RESOURCE) 🔹
        Route::get('/cuti/export-pdf', [AdminCutiController::class, 'exportPdf'])->name('cuti.export-pdf');
        Route::get('/cuti/approval', [AdminCutiController::class, 'approval'])->name('cuti.approval');
        
        // Cuti Admin
        Route::resource('cuti', AdminCutiController::class);
        Route::post('/cuti/{id}/setuju', [AdminCutiController::class, 'approve'])->name('cuti.approve');
        Route::post('/cuti/{id}/tolak', [AdminCutiController::class, 'reject'])->name('cuti.reject');
        
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [AdminProfileController::class, 'index'])->name('index');
            Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
            Route::patch('/update', [AdminProfileController::class, 'update'])->name('update');
        });
    });

    // --- KEPALA DINAS (Pejabat Pemberi Cuti / Approval Tahap 2) ---
    Route::prefix('kepaladinas')->as('kepaladinas.')->middleware('role:atasan')->group(function () {
        Route::get('/dashboard', [KadisDashboard::class, 'index'])->name('dashboard');
        
        // Resource Routes untuk Menu Samping
        Route::resource('datapegawai', KadisDataPegawai::class);
        Route::resource('datacuti', KadisDataCuti::class);
        
        // Tambahkan Route Approval untuk Kadis
        Route::post('/approval/{id}/setuju', [KadisDashboard::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{id}/tolak', [KadisDashboard::class, 'reject'])->name('approval.reject');

        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [KadisProfileController::class, 'show'])->name('show');
            Route::get('/edit', [KadisProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [KadisProfileController::class, 'update'])->name('update');
        });
    });

    // --- PEGAWAI ---
    Route::prefix('pegawai')->as('pegawai.')->middleware('role:pegawai')->group(function () {
        Route::get('/dashboard', [PegawaiDashboard::class, 'index'])->name('dashboard');
        Route::get('/cuti/export-excel', [PegawaiCutiController::class, 'exportExcel'])->name('cuti.export-excel');
        Route::resource('cuti', PegawaiCutiController::class)->except(['show']);
        Route::post('/cuti/ajax-store', [PegawaiCutiController::class, 'ajaxStore'])->name('cuti.ajax-store');

        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [PegawaiProfileController::class, 'show'])->name('show');
            Route::get('/edit', [PegawaiProfileController::class, 'edit'])->name('edit');
            Route::patch('/update', [PegawaiProfileController::class, 'update'])->name('update');
            Route::put('/password', [PegawaiProfileController::class, 'updatePassword'])->name('password.update');
        });
    });

    // --- SUPER ADMIN ---
    Route::prefix('super')->as('super.')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [SuperProfileController::class, 'index'])->name('index');
            Route::get('/edit', [SuperProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [SuperProfileController::class, 'update'])->name('update');
        });
    });

    Route::post('/notif/{id}/read', function($id) {
    App\Models\Notification::where('id', $id)->where('user_id', Auth::id())->update(['is_read' => true]);
    return back();
})->name('pegawai.notif.read');

});

// GRUP UTAMA PEJABAT (Hanya satu pembuka di sini)
Route::middleware(['auth', 'role:pejabat'])->prefix('pejabat')->name('pejabat.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [PejabatApprovalController::class, 'dashboard'])->name('dashboard');

    // Approve cuti
    Route::post('/approval/{id}/setuju', 
        [PejabatApprovalController::class, 'approve']
    )->name('approval.approve');

    // Reject cuti (pakai modal)
    Route::post('/cuti/{id}/tolak', 
        [PejabatApprovalController::class, 'cancel']
    )->name('approval.reject');

    // Cancel persetujuan
    Route::post('/approval/{id}/cancel', 
        [PejabatApprovalController::class, 'cancel']
    )->name('approval.cancel');

    // Profile
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [PegawaiProfileController::class, 'show'])->name('show');
        Route::get('/edit', [PegawaiProfileController::class, 'edit'])->name('edit');
        Route::patch('/update', [PegawaiProfileController::class, 'update'])->name('update');
    });

    // routes/web.php
Route::get('/pegawai/cuti/cek-tersedia', [App\Http\Controllers\Pegawai\PengajuanCutiController::class, 'getAvailableDelegates'])
    ->name('pegawai.cuti.cek-tersedia');

}); // <--- Penutup Grup Utama Pejabat (PASTIKAN TIDAK ADA LAGI }); DI BAWAH INI)