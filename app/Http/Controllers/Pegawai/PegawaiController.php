<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data pegawai sesuai user yang login
        $pegawai = $user->pegawai;

        // Jika pegawai belum terdaftar, buat query kosong
        if (!$pegawai) {
            $cutiQuery = Cuti::query()->whereNull('id');
        } else {
            $cutiQuery = Cuti::where('user_id', $user->id);
        }

        // Statistik cuti
        $totalCuti     = (clone $cutiQuery)->count();
        $cutiPending   = (clone $cutiQuery)->where('status', 'pending')->count();
        $cutiDisetujui = (clone $cutiQuery)->where('status', 'disetujui')->count();
        $cutiDitolak   = (clone $cutiQuery)->where('status', 'ditolak')->count();

        // 5 data cuti terbaru beserta relasi pegawai
        $latestCuti = (clone $cutiQuery)
            ->with('pegawai') // relasi untuk nama, nip, jabatan
            ->latest()
            ->take(5)
            ->get();

        // Riwayat lengkap cuti
        $riwayatCuti = (clone $cutiQuery)
            ->with('pegawai')
            ->latest()
            ->get();

        return view('pegawai.dashboard.index', compact(
            'user',
            'pegawai',
            'totalCuti',
            'cutiPending',
            'cutiDisetujui',
            'cutiDitolak',
            'latestCuti',
            'riwayatCuti'
        ));
    }
}
