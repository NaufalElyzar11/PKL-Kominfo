<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Tambahan untuk Export Excel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CutiExport;

class PengajuanCutiController extends Controller
{
/** ========================== 🏠 INDEX ============================= */
public function index()
{
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
    }

    $pegawai = $user->pegawai;

    /** 1. JALANKAN QUERY DATA TERLEBIH DAHULU **/
    // Ambil data cuti dari database agar variabel tidak kosong
    $cutiQuery = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
        ->where('user_id', $user->id)
        ->where('status', 'Menunggu');

    $riwayatQuery = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
        ->where('user_id', $user->id)
        ->whereIn('status', ['Disetujui', 'Ditolak']);

    // Tentukan pagination (sesuaikan dengan kode asli Anda)
    $cuti = $cutiQuery->latest()->paginate(10, ['*'], 'menunggu_page');
    $riwayat = $riwayatQuery->latest()->paginate(10, ['*'], 'riwayat_page');

    /** 2. LOGIKA PENGECEKAN PROFIL **/
    // Alih-alih langsung me-return view kosong, kita hanya menentukan pesan peringatan
    $warningMessage = null;
    
    if (!$pegawai) {
        $warningMessage = '⚠️ Data pegawai belum ditemukan. Silakan hubungi admin.';
    } elseif (!$this->isPegawaiLengkap($pegawai)) {
        $warningMessage = '⚠️ Lengkapi profil Anda terlebih dahulu sebelum mengajukan cuti.';
    }

    /** 3. RETURN VIEW DENGAN DATA ASLI **/
    return view('pegawai.pengajuancuti.index', [
        'pegawai' => $pegawai,
        'cuti' => $cuti,       // Sekarang variabel ini berisi data dari DB, bukan collect()
        'riwayat' => $riwayat, // Sekarang variabel ini berisi data dari DB, bukan collect()
        'tahun' => request('tahun', 'semua'),
        'totalCuti' => $cuti->total() + $riwayat->total(),
        'cutiPending' => $cuti->total(),
        'cutiDisetujui' => $riwayatQuery->where('status', 'Disetujui')->count(),
        'cutiDitolak' => $riwayatQuery->where('status', 'Ditolak')->count(),
        'sisaCuti' => $this->hitungSisaCuti($user->id),
        'warningMessage' => $warningMessage,
        'hasPendingCuti' => $cuti->total() > 0,
    ]);

    /** ========================== FILTER TAHUN ============================= */
    $tahun = request('tahun', 'semua');

    /** ========================== QUERY CUTI MENUNGGU ===================== */
    $cutiQuery = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
        ->where('user_id', $user->id)
        ->where('status', 'Menunggu');

    /** ========================== QUERY RIWAYAT =========================== */
    $riwayatQuery = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
        ->where('user_id', $user->id)
        ->whereIn('status', ['Disetujui', 'Ditolak']);

    // Filter tahun jika dipilih
    if ($tahun !== 'semua') {
        $cutiQuery->whereYear('tanggal_mulai', $tahun);
        $riwayatQuery->whereYear('tanggal_mulai', $tahun);
    }

    /** ========================== PAGINATION ============================= */
    $cuti = $cutiQuery->latest()->paginate(10, ['*'], 'menunggu_page');
    $riwayat = $riwayatQuery->latest()->paginate(10, ['*'], 'riwayat_page');

    /** ========================== HITUNG NILAI =========================== */
    // Hitungan total cuti yang SAMA dengan yang ada di view,
    // yang memperhitungkan filter tahun, TIDAK IDEAL untuk statistik
    // Global, tetapi akan mengikuti logika query yang Anda berikan.
    $totalCuti = (clone $cutiQuery)->count() + (clone $riwayatQuery)->count();
    $cutiPending = (clone $cutiQuery)->count();
    $cutiDisetujui = (clone $riwayatQuery)->where('status', 'Disetujui')->count();
    $cutiDitolak = (clone $riwayatQuery)->where('status', 'Ditolak')->count();

    /** ========================== LOCKING CUTI BARU ===================== */
    // Logika untuk mencegah pengajuan baru jika ada yang Menunggu (Mengabaikan filter tahun)
    $hasPendingCuti = Cuti::where('user_id', $user->id)
                          ->where('status', 'Menunggu')
                          ->exists(); // Check keberadaan data secara global


    /** ========================== SISA CUTI ============================== */
    $sisaCuti = $this->hitungSisaCuti($user->id);

    /** ========================== RETURN FINAL =========================== */
    return view('pegawai.pengajuancuti.index', [
        'pegawai' => $pegawai,
        'cuti' => $cuti,
        'riwayat' => $riwayat,
        'tahun' => $tahun,

        // Perhitungan fix
        'totalCuti' => $totalCuti,
        'cutiPending' => $cutiPending,
        'cutiDisetujui' => $cutiDisetujui,
        'cutiDitolak' => $cutiDitolak,

        'sisaCuti' => $sisaCuti,
        'warningMessage' => null,

        // VARIABEL BARU UNTUK LOCKING MODAL
        'hasPendingCuti' => $hasPendingCuti,
    ]);
}

    /** ========================== 📝 STORE CUTI ============================= */
public function store(Request $request)
{
    $user = Auth::user();
    $pegawai = $user->pegawai;

    if (!$pegawai) {
        return back()->with('error', 'Data pegawai tidak ditemukan.');
    }

    // 1. HAPUS id_atasan_langsung dan id_pejabat_pemberi_cuti dari validasi request
    $validated = $request->validate([
        'jenis_cuti'      => 'required|string',
        'tanggal_mulai'   => 'required|date',
        'tanggal_selesai' => 'required|date',
        'jumlah_hari'     => 'required|integer',
        'keterangan'      => 'required|string',
        'alamat'          => 'required|string',
    ]);

    $jumlah_hari = Carbon::parse($validated['tanggal_mulai'])
        ->diffInDays(Carbon::parse($validated['tanggal_selesai'])) + 1;

    // 2. Gunakan ID atasan/pejabat dari tabel pegawai secara otomatis
    Cuti::create([
        'user_id'                 => $user->id,
        'nama'                    => $pegawai->nama,
        'nip'                     => $pegawai->nip,
        'jabatan'                 => $pegawai->jabatan,
        'alamat'                  => $validated['alamat'],
        'jenis_cuti'              => $validated['jenis_cuti'],
        'tanggal_mulai'           => $validated['tanggal_mulai'],
        'tanggal_selesai'         => $validated['tanggal_selesai'],
        'jumlah_hari'             => $jumlah_hari,
        'tahun'                   => date('Y'),
        'keterangan'              => $validated['keterangan'],
        'status'                  => 'Menunggu',
        
        // Ambil otomatis dari profil pegawai
        'id_atasan_langsung'      => $pegawai->id_atasan_langsung, 
        'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
    ]);

    return redirect()->route('pegawai.cuti.index')
        ->with('success', 'Pengajuan cuti berhasil dikirim.');
}


    /** ========================== ✏️ UPDATE CUTI ============================= */
public function update(Request $request, $id)
{
    // 1. Validasi input (Sertakan nama, nip, dan alamat)
    $request->validate([
        'nama' => 'required|string|max:255', // Agar nama bisa diupdate
        'nip' => 'required',
        'jenis_cuti' => 'required',
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date',
        'jumlah_hari' => 'required|numeric',
        'alamat' => 'required', // Penting agar alamat tidak kosong
        'keterangan' => 'required',
    ]);

    // 2. Cari data cuti berdasarkan ID
    $cuti = Cuti::findOrFail($id);

    // 3. UPDATE DATA PEGAWAI (Ganti Paisal jadi Zainuddin)
    // Asumsi: Model Cuti memiliki relasi 'pegawai' ke model Pegawai
    if ($cuti->pegawai) {
        $cuti->pegawai->update([
            'nama' => $request->nama,
            'nip'  => $request->nip
        ]);
    }

    // 4. UPDATE DATA CUTI
    $cuti->jenis_cuti = $request->jenis_cuti;
    $cuti->tanggal_mulai = $request->tanggal_mulai;
    $cuti->tanggal_selesai = $request->tanggal_selesai;
    $cuti->jumlah_hari = $request->jumlah_hari;
    $cuti->alamat = $request->alamat; // Menyimpan alamat baru
    $cuti->keterangan = $request->keterangan;
    
    // Update atasan/pejabat hanya jika ada di request
    if ($request->has('id_atasan_langsung')) {
        $cuti->id_atasan_langsung = $request->id_atasan_langsung;
    }
    if ($request->has('id_pejabat_pemberi_cuti')) {
        $cuti->id_pejabat_pemberi_cuti = $request->id_pejabat_pemberi_cuti;
    }

    $cuti->tahun = date('Y');
    $cuti->save();

    // 5. Kembali dengan pesan sukses
    return redirect()->back()->with('success', 'Data Pegawai dan Pengajuan cuti berhasil diperbarui.');
}

    /** ========================== 🔍 DETAIL CUTI ============================ */
    public function detail($id)
    {
        $cuti = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
            ->findOrFail($id);

        $badgeClass = [
            'Menunggu'  => 'bg-yellow-500 text-white',
            'Disetujui' => 'bg-green-600 text-white',
            'Ditolak'   => 'bg-red-600 text-white',
        ];

        $class = $badgeClass[$cuti->status] ?? 'bg-gray-500 text-white';
        $statusBadgeHtml = "<span class='px-2 py-1 rounded text-xs {$class}'>{$cuti->status}</span>";

        return response()->json([
            'nama'   => $cuti->pegawai->nama ?? '-',
            'nip'    => $cuti->pegawai->nip ?? '-',
            'jenis_cuti'      => $cuti->jenis_cuti ?? '-',
            'tanggal_mulai'   => $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d-m-Y') : '-',
            'tanggal_selesai' => $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d-m-Y') : '-',
            'jumlah_hari'     => $cuti->jumlah_hari ?? 0,
            'alasan_cuti'     => $cuti->keterangan ?? '-',
            'status_badge'    => $statusBadgeHtml,
            'atasan_nama'     => $cuti->atasanLangsung->nama ?? '-',
            'pejabat_nama'    => $cuti->pejabatPemberiCuti->nama ?? '-',
        ]);
    }


    /** ========================== 🗑 DELETE CUTI ============================ */
    public function destroy($id)
    {
        $user = Auth::user();

        $cuti = Cuti::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cuti) {
            return redirect()->back()->with('error', 'Data cuti tidak ditemukan.');
        }

        $cuti->delete();

        return redirect()->back()->with('success', 'Pengajuan cuti berhasil dihapus.');
    }


 /**
 * ========================== 🔢 HITUNG SISA CUTI ============================
 */
private function hitungSisaCuti($pegawaiId)
{
    $tahunSekarang = date('Y');
    $tahunLalu = $tahunSekarang - 1;

    // Jatah cuti standar per tahun
    $jatahTahunan = 12;

    // 1. Hitung penggunaan cuti tahun lalu (Hanya yang Disetujui)
    $cutiDisetujuiTahunLalu = Cuti::where('user_id', $pegawaiId)
        ->whereIn('status', ['Disetujui', 'disetujui']) // Mengantisipasi perbedaan penulisan
        ->where('tahun', $tahunLalu)
        ->sum('jumlah_hari');

    // 2. Hitung sisa tahun lalu (Jatah 12 - Terpakai Tahun Lalu)
    // Jika ingin dibatasi sesuai aturan umum (max 6 hari), gunakan: min(6, max(0, ...))
    $sisaTahunLalu = max(0, $jatahTahunan - $cutiDisetujuiTahunLalu);

    // 3. Total jatah yang dimiliki di tahun ini (12 + sisa tahun lalu)
    $totalJatahTahunIni = $jatahTahunan + $sisaTahunLalu;

    // 4. Hitung penggunaan cuti tahun berjalan (Tahun Sekarang)
    $cutiDisetujuiTahunIni = Cuti::where('user_id', $pegawaiId)
        ->whereIn('status', ['Disetujui', 'disetujui'])
        ->where('tahun', $tahunSekarang)
        ->sum('jumlah_hari');

    // 5. Hasil Akhir: Total Jatah - Terpakai Tahun Ini
    $sisaFinal = max(0, $totalJatahTahunIni - $cutiDisetujuiTahunIni);

    return $sisaFinal;
}

public function exportExcel(Request $request)
{
    $user = Auth::user();
    // Pastikan mengambil data pegawai yang terhubung dengan user
    $pegawai = $user->pegawai;

    if (!$pegawai) {
        return back()->with('error', 'Data pegawai tidak ditemukan.');
    }

    // Ambil tahun dari request. Jika dari Blade menggunakan :value="detailRiwayat.tahun",
    // maka $request->input('tahun') akan menangkap nilai tersebut.
    $tahun = $request->input('tahun', date('Y'));

    $namaFile = 'Laporan_Cuti_' . str_replace(' ', '_', $pegawai->nama) . '_' . $tahun . '.xlsx';

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\CutiExport($pegawai->id, $tahun),
        $namaFile
    );
}
/**
 * ========================== ✔ CEK DATA PEGAWAI LENGKAP ============================
 * Mengecek apakah data wajib pegawai sudah lengkap
 * ======================================================================
 */
private function isPegawaiLengkap($pegawai)
{
    if (!$pegawai) return false;

    $dataWajib = [
        'nama',
        'nip',
        'jabatan',
        'unit_kerja',
        'telepon',
        'id_atasan_langsung',      // Tambahkan ini agar sistem tidak error saat store
        'id_pejabat_pemberi_cuti', // Tambahkan ini agar sistem tidak error saat store
    ];

    foreach ($dataWajib as $field) {
        if (empty($pegawai->$field)) {
            return false;
        }
    }

    return true;
}}