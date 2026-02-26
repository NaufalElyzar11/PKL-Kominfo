<?php

namespace App\Http\Controllers\Pegawai;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;
use App\Models\Cuti;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;

class DashboardController extends Controller
{
    /**
     * 🏠 Tampilkan Dashboard Pegawai
     */

    public function index()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.profile.show')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        // 1. TAHUN DINAMIS & JATAH DASAR
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;
        $jatahDasar = 12;

        // 2. HITUNG PEMAKAIAN TAHUN LALU (Data Dummy 2025)
        // Cek di tabel cuti untuk pemakaian tahun lalu
        $pakaiTahunLalu = Cuti::where('user_id', $user->id)
            ->where('tahun', $tahunLalu)
            ->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Atasan'])
            ->sum('jumlah_hari');

        // FALLBACK: Jika di tabel cuti 2025 kosong, ambil angka pemakaian manual (angka 5) 
        // yang Anda input di kolom sisa_cuti tabel pegawai.
        if ($pakaiTahunLalu == 0) {
            $pakaiTahunLalu = (int) ($pegawai->sisa_cuti ?? 0); 
        }

        // 3. LOGIKA AKUMULASI: Sisa tahun lalu dibawa jika pakai <= 6 hari
        $jatahAkumulasi = 0;
        if ($pakaiTahunLalu > 0 && $pakaiTahunLalu <= 6) {
            $jatahAkumulasi = $jatahDasar - $pakaiTahunLalu; // Contoh: 12 - 5 = 7
        }
        
        // HAK CUTI TOTAL (Misal: 12 + 7 = 19 Hari)
        $hakCuti = $jatahDasar + $jatahAkumulasi;

        // 4. HITUNG PEMAKAIAN TAHUN INI (Termasuk yang sedang diproses)
        // Masukkan status 'Disetujui Atasan' agar sisa cuti Dicky langsung berkurang
        $statusTerhitung = [
            'Disetujui', 'disetujui', 
            'Disetujui Atasan', 'disetujui atasan', 
            'Menunggu', 'menunggu', 
            'Revisi Delegasi'
        ];
        
        $cutiQuery = Cuti::where('user_id', $user->id);
        $cutiTerpakai = (clone $cutiQuery)
            ->where('tahun', $tahunIni)
            ->whereIn('status', $statusTerhitung)
            ->sum('jumlah_hari');

        // 5. SISA CUTI AKHIR
        $sisaCuti = max(0, $hakCuti - $cutiTerpakai);

        // 6. STATISTIK DASHBOARD
        $latestCuti = (clone $cutiQuery)->with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])->latest()->take(5)->get();
        $cutiPending = (clone $cutiQuery)->whereIn('status', ['Menunggu', 'menunggu', 'Pending', 'pending', 'Disetujui Atasan', 'disetujui atasan'])->count();
        $cutiDisetujuiCount = (clone $cutiQuery)->whereIn('status', ['Disetujui', 'disetujui'])->count();
        $cutiDitolakCount = (clone $cutiQuery)->whereIn('status', ['Ditolak', 'ditolak'])->count();

        return view('pegawai.dashboard.index', [
            'user' => $user,
            'pegawai' => $pegawai,
            'totalPegawai' => Pegawai::count(),
            'totalCuti' => (clone $cutiQuery)->count(),
            'cutiPending' => $cutiPending,
            'cutiDisetujui' => $cutiDisetujuiCount,
            'cutiDitolak' => $cutiDitolakCount,
            'cutiTerpakai' => $cutiTerpakai,
            'latestCuti' => $latestCuti,
            'hakCuti' => $hakCuti,   // Kirim jatah yang sudah terakumulasi
            'sisaCuti' => $sisaCuti, // Kirim sisa yang sudah dipotong pemakaian
        ]);
    }
}