<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;
use App\Models\Cuti;

class DashboardController extends Controller
{
    /**
     * 🏛️ Tampilkan Dashboard Kepala Dinas
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        // ✅ Cegah akses jika user belum login atau bukan kepala dinas
        if (!$user || $user->role !== 'kadis') {   // ⬅ PERBAIKAN: pakai kolom role di tabel users
            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', 'Akses ditolak. Silakan login kembali.');
        }

        // ✅ Statistik utama
        $totalPegawai  = Pegawai::count();
        $totalCuti     = Cuti::count();
        $cutiDisetujui = Cuti::where('status', 'disetujui')->count();
        $cutiDitolak   = Cuti::where('status', 'ditolak')->count();

        // ⛔ FIX: status pending seharusnya "menunggu"
        $cutiPending   = Cuti::where('status', 'menunggu')->count();

        // ✅ Data pegawai terbaru
        $pegawaiTerbaru = Pegawai::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Tambahkan role langsung dari tabel users
        foreach ($pegawaiTerbaru as $p) {
            $p->role = $p->user->role ?? '-';
        }

        // ✅ Data cuti terbaru
        $cutiTerbaru = Cuti::with(['pegawai.user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Tambahkan role juga untuk tabel cuti (kalau dibutuhkan di view)
        foreach ($cutiTerbaru as $c) {
            $c->role = $c->pegawai?->user?->role ?? '-';
        }

        // ✅ Kirim semua data ke view
        return view('kepaladinas.dashboard.index', [
            'totalPegawai'   => $totalPegawai,
            'totalCuti'      => $totalCuti,
            'cutiDisetujui'  => $cutiDisetujui,
            'cutiDitolak'    => $cutiDitolak,
            'cutiPending'    => $cutiPending,
            'pegawaiTerbaru' => $pegawaiTerbaru,
            'cutiTerbaru'    => $cutiTerbaru,
        ]);
    }
}
