<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Pegawai;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total pegawai
        $totalPegawai = Pegawai::count();

        // 2. Total semua cuti
        $totalCuti = Cuti::count();

        // 3. Total cuti berdasarkan status
        $cutiDisetujui = Cuti::where('status', 'disetujui')->count();
        $cutiDitolak   = Cuti::where('status', 'ditolak')->count();
        $cutiPending   = Cuti::where('status', 'menunggu')->count();

        // 4. Statistik cuti berdasarkan unit kerja (INI YANG SAYA PERBAIKI)
        // Jalurnya: Cuti -> User -> Pegawai
        $cutiPerUnitKerja = Cuti::join('users', 'cuti.user_id', '=', 'users.id')
            ->join('pegawai', 'users.id_pegawai', '=', 'pegawai.id')
            ->selectRaw('pegawai.unit_kerja, COUNT(cuti.id) as total')
            ->groupBy('pegawai.unit_kerja')
            ->get();

        // 5. Data cuti terbaru
        $cutiTerbaru = Cuti::with(['pegawai'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // 6. Data pegawai terbaru
        $pegawaiTerbaru = Pegawai::with('user.roles') 
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalPegawai',
            'totalCuti',
            'cutiDisetujui',
            'cutiDitolak',
            'cutiPending',
            'cutiPerUnitKerja',
            'cutiTerbaru',
            'pegawaiTerbaru'
        ));
    }
}
