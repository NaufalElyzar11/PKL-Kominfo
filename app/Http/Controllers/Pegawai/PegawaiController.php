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
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('pegawai.profile.show')->with('error', 'Data pegawai tidak ditemukan.');
        }

        // 1. TAHUN DINAMIS & JATAH DASAR
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;
        $jatahDasar = 12;

        // 2. HITUNG PEMAKAIAN TAHUN LALU (Untuk Akumulasi 2025 ke 2026)
        $pakaiTahunLalu = Cuti::where('user_id', $user->id)
            ->where('tahun', $tahunLalu)
            ->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Atasan'])
            ->sum('jumlah_hari');

        // FALLBACK: Jika di tabel cuti 2025 kosong, ambil angka manual (angka 5) 
        // yang Anda input di profil pegawai (kolom sisa_cuti)
        if ($pakaiTahunLalu == 0) {
            $pakaiTahunLalu = (int) ($pegawai->sisa_cuti ?? 0); 
        }

        // 3. LOGIKA AKUMULASI: Sisa tahun lalu dibawa jika pakai <= 6 hari
        $jatahAkumulasi = 0;
        if ($pakaiTahunLalu > 0 && $pakaiTahunLalu <= 6) {
            $jatahAkumulasi = $jatahDasar - $pakaiTahunLalu; // Contoh: 12 - 5 = 7
        }
        
        // HAK CUTI TOTAL (Contoh: 12 + 7 = 19 Hari)
        $hakCuti = $jatahDasar + $jatahAkumulasi;

        // 4. HITUNG PEMAKAIAN TAHUN INI (Agar angka Dicky langsung berkurang)
        // Tambahkan 'Disetujui Atasan' agar jatah Dicky langsung terpotong 3 hari
        $statusTerhitung = [
            'Disetujui', 'disetujui', 
            'Disetujui Atasan', 'disetujui atasan', 
            'Menunggu', 'menunggu', 
            'Revisi Delegasi'
        ];
        
        $queryCuti = Cuti::where('user_id', $user->id)->where('tahun', $tahunIni);
        $cutiTerpakai = (clone $queryCuti)
            ->whereIn('status', $statusTerhitung)
            ->sum('jumlah_hari');

        // 5. SISA CUTI DINAMIS (Matematika Otomatis)
        $sisaCutiFinal = max(0, $hakCuti - $cutiTerpakai);

        // 6. DATA TAMBAHAN LAINNYA
        $notif = Notification::where('user_id', $user->id)->where('is_read', false)->latest()->get();
        $cutiPending = (clone $queryCuti)->whereIn('status', ['Menunggu', 'Disetujui Atasan'])->count();
        $cutiDisetujui = (clone $queryCuti)->where('status', 'Disetujui')->count();
        $cutiDitolak = (clone $queryCuti)->where('status', 'Ditolak')->count();
        $latestCuti = Cuti::where('user_id', $user->id)->latest()->take(5)->get();
        $totalPegawai = Pegawai::count();
        $pegawaiSedangCuti = Cuti::where('status', 'Disetujui')
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
            'totalCuti'         => $hakCuti,        // Kirim jatah akumulasi (Misal 19)
            'sisaCuti'          => $sisaCutiFinal,  // Kirim sisa yang sudah dipotong (Misal 16)
            'totalPegawai'      => $totalPegawai,
            'pegawaiSedangCuti' => $pegawaiSedangCuti,
            'pegawai'           => $pegawai,
        ]);
    }
}