<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Ambil data pegawai yang berelasi dengan user
        $pegawai = Pegawai::where('id', $user->id_pegawai)->first();

        // 1. Ambil Notifikasi yang belum dibaca
        $notif = Notification::where('user_id', $user->id)
                            ->where('is_read', false)
                            ->latest()
                            ->get();

        // 2. Statistik Cuti (Tahun Berjalan)
        $tahunSekarang = date('Y');
        $queryCuti = Cuti::where('user_id', $user->id)->where('tahun', $tahunSekarang);

        $cutiPending   = (clone $queryCuti)->where('status', 'menunggu')->count();
        $cutiDisetujui = (clone $queryCuti)->where('status', 'Disetujui Atasan')->count();
        $cutiDitolak   = (clone $queryCuti)->where('status', 'Ditolak')->count();
        
        // Menghitung cuti yang sudah terpakai (untuk progress bar)
        $cutiTerpakai = (clone $queryCuti)->where('status', 'Disetujui Atasan')->sum('jumlah_hari');
        
        // 3. Ambil 5 riwayat cuti terakhir untuk tabel di dashboard
        $latestCuti = Cuti::where('user_id', $user->id)
                          ->latest()
                          ->take(5)
                          ->get();

        // 4. Data Tambahan (Jika diperlukan di dashboard)
        $totalPegawai = Pegawai::count(); // Opsional, jika ingin menampilkan total pegawai kantor
        $pegawaiSedangCuti = Cuti::where('status', 'Disetujui Atasan')
                                 ->whereDate('tanggal_mulai', '<=', now())
                                 ->whereDate('tanggal_selesai', '>=', now())
                                 ->count();

        return view('pegawai.dashboard.index', [
            'notif'             => $notif,
            'cutiPending'       => $cutiPending,
            'cutiDisetujui'     => $cutiDisetujui,
            'cutiDitolak'       => $cutiDitolak,
            'cutiTerpakai'      => $cutiTerpakai,
            'latestCuti'        => $latestCuti,
            'totalCuti'         => 12,
            'sisaCuti'          => $pegawai ? $pegawai->sisa_cuti : 12,
            'totalPegawai'      => $totalPegawai,
            'pegawaiSedangCuti' => $pegawaiSedangCuti,
        ]);
    }
}
