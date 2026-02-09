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
        // Ambil data pegawai sesuai user login
        $pegawai = $user->pegawai;
        if (!$pegawai) {
            return redirect()->route('pegawai.profile.show')
                ->with('error', 'Data pegawai tidak ditemukan. Lengkapi profil Anda terlebih dahulu.');
        }
        // Query data cuti pegawai
        $cutiQuery = Cuti::where('user_id', $user->id);

        // Normalisasi status (karena di beberapa bagian aplikasi ada yang pakai "Menunggu"/"Disetujui"/"Ditolak"
        // dan ada juga yang pakai lowercase seperti "pending"/"disetujui"/"ditolak")
        $statusMenunggu  = ['Menunggu', 'menunggu', 'Pending', 'pending', 'Disetujui Atasan', 'disetujui atasan'];
        // PERBAIKAN: Hanya hitung yang sudah final approved (bukan "Disetujui Atasan")
        $statusDisetujui = ['Disetujui', 'disetujui'];
        $statusDitolak   = ['Ditolak', 'ditolak'];

        $totalCuti     = (clone $cutiQuery)->count();
        $cutiPending   = (clone $cutiQuery)->whereIn('status', $statusMenunggu)->count();
        $cutiDisetujui = (clone $cutiQuery)->whereIn('status', $statusDisetujui)->count();
        $cutiDitolak   = (clone $cutiQuery)->whereIn('status', $statusDitolak)->count();
        
        // HITUNG CUTI TERPAKAI TAHUN INI (hanya yang final approved)
        $cutiTerpakai = (clone $cutiQuery)
            ->where('tahun', date('Y'))
            ->whereIn('status', $statusDisetujui)
            ->sum('jumlah_hari');

        // Ambil 5 cuti terbaru lengkap dengan relasi
        $latestCuti = $cutiQuery
            ->with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
            ->latest()
            ->take(5)
            ->get();

        // Ambil daftar atasan langsung & pejabat cuti
        $atasanLangsung     = AtasanLangsung::orderBy('nama_atasan', 'asc')->get();
        $pejabatPemberiCuti = PejabatPemberiCuti::orderBy('nama_pejabat', 'asc')->get();

        // Total pegawai untuk statistik
        $totalPegawai = Pegawai::count();

        // TAMBAHAN: Hitung hak cuti dan sisa cuti dari database
        $hakCuti = $pegawai->kuota_cuti ?? 12; // Ambil dari database, default 12
        $sisaCuti = max(0, $hakCuti - $cutiTerpakai);

        // Kirim semua data ke view
        return view('pegawai.dashboard.index', compact(
            'user',
            'pegawai',
            'totalPegawai',
            'totalCuti',
            'cutiPending',
            'cutiDisetujui',
            'cutiDitolak',
            'cutiTerpakai',
            'latestCuti',
            'atasanLangsung',
            'pejabatPemberiCuti',
            'hakCuti',      // BARU: Kuota cuti dari database
            'sisaCuti'      // BARU: Sisa cuti yang dihitung
        ));
    }
}