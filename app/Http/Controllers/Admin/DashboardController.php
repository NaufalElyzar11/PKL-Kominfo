<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Http\Request; // WAJIB tambahkan ini

class DashboardController extends Controller
{
    public function index(Request $request) // Tambahkan parameter Request
    {
        // 1. Ambil input filter dari user (default ke bulan & tahun sekarang jika kosong)
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // 2. Total pegawai
        $totalPegawai = Pegawai::count();

        // 3. Total semua cuti
        $totalCuti = Cuti::count();

        // 4. Total cuti berdasarkan status
        $cutiDisetujui = Cuti::whereIn('status', ['Disetujui Atasan', 'disetujui'])->count();
        $cutiDitolak   = Cuti::where('status', 'ditolak')->count();
        $cutiPending   = Cuti::where('status', 'menunggu')->count();

        // 5. Statistik cuti berdasarkan unit kerja (DENGAN FILTER BULANAN)
        $cutiPerUnitKerja = Cuti::join('users', 'cuti.user_id', '=', 'users.id')
            ->join('pegawai', 'users.id_pegawai', '=', 'pegawai.id')
            // --- TAMBAHKAN FILTER DI SINI ---
            ->whereMonth('cuti.tanggal_mulai', $bulan)
            ->whereYear('cuti.tanggal_mulai', $tahun)
            // --------------------------------
            ->selectRaw('pegawai.unit_kerja, COUNT(cuti.id) as total')
            ->groupBy('pegawai.unit_kerja')
            ->get();

        // 6. Data cuti terbaru
        $cutiTerbaru = Cuti::with(['pegawai'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'cuti_page');

        // 7. Data pegawai terbaru
        $pegawaiTerbaru = Pegawai::with('user.roles') 
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'pegawai_page');

        // Sertakan $bulan dan $tahun agar dropdown di Blade tetap terpilih (selected)
        return view('admin.dashboard.index', compact(
            'totalPegawai',
            'totalCuti',
            'cutiDisetujui',
            'cutiDitolak',
            'cutiPending',
            'cutiPerUnitKerja',
            'cutiTerbaru',
            'pegawaiTerbaru',
            'bulan', 
            'tahun'
        ));
    }
}