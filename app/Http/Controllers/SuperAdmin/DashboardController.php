<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard super admin
     */
    public function index()
    {
        // 🔹 Statistik User
        $total_super_admin = User::where('role', 'super_admin')->count();
        $total_admin       = User::where('role', 'admin')->count();
        $total_pegawai     = Pegawai::count();

        // 🔹 Statistik Cuti
        $total_pengajuan = Cuti::count();
        $cuti_disetujui  = Cuti::where('status', 'disetujui')->count();
        $cuti_ditolak    = Cuti::where('status', 'ditolak')->count();
        $cuti_pending    = Cuti::where('status', 'pending')->count();

        // 🔹 Data terbaru untuk aktivitas
        $pegawaiTerbaru = Pegawai::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $cutiTerbaru = Cuti::with('pegawai.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 🔹 Kirim semua data ke view
        return view('superadmin.dashboard.index', compact(
            'total_super_admin',
            'total_admin',
            'total_pegawai',
            'total_pengajuan',
            'cuti_disetujui',
            'cuti_ditolak',
            'cuti_pending',
            'pegawaiTerbaru',
            'cutiTerbaru'
        ));
    }
}
