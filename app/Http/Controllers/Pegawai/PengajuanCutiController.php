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
        $pegawai = $user->pegawai;
        $tahun = request('tahun', date('Y'));

        // 1. Ambil Rekan Sebidang untuk Delegasi
        // Kita pastikan data pegawai ada dan memiliki kolom 'unit_kerja'
        $rekanSebidang = collect(); 
        if ($pegawai && $pegawai->unit_kerja) {
            $rekanSebidang = \App\Models\Pegawai::where('unit_kerja', 'LIKE', trim($pegawai->unit_kerja))
            ->where('id', '!=', $pegawai->id)
            ->whereHas('user', function($query) {
                $query->where('role', 'pegawai');
            })
            ->get();
        }

        // 2. Logika Pengecekan Profil
        $warningMessage = null;
        if (!$pegawai) {
            $warningMessage = '⚠️ Data pegawai belum ditemukan. Silakan hubungi admin.';
        } elseif (!$this->isPegawaiLengkap($pegawai)) {
            $warningMessage = '⚠️ Lengkapi profil Anda terlebih dahulu sebelum mengajukan cuti.';
        }

        // 3. Query Dasar
        $baseQuery = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti', 'delegasi'])
                ->where('user_id', $user->id);

        if ($tahun !== 'semua') {
            $baseQuery->where('tahun', $tahun);
        }

        // 4. Pagination
        $cuti = (clone $baseQuery)
        ->whereIn('status', ['Menunggu', 'menunggu', 'MENUNGGU'])
        ->latest()
        ->paginate(10, ['*'], 'menunggu_page');

        $riwayat = (clone $baseQuery)
        ->whereIn('status', [
            'Disetujui', 'disetujui', 'DISETUJUI', 
            'Ditolak', 'ditolak', 'DITOLAK',
            'Disetujui Atasan', 'disetujui atasan'
        ])
        ->latest()
        ->paginate(10, ['*'], 'riwayat_page');

        $globalStats = Cuti::where('user_id', $user->id);

        return view('pegawai.pengajuancuti.index', [
            'pegawai' => $pegawai,
            'rekanSebidang' => $rekanSebidang,
            'cuti' => $cuti,
            'riwayat' => $riwayat,
            'tahun' => $tahun,
            'totalCuti' => (clone $globalStats)->count(),
            'cutiPending' => (clone $globalStats)->where('status', 'Menunggu')->count(),
            'cutiDisetujui' => (clone $globalStats)->where('status', 'Disetujui')->count(),
            'cutiDitolak' => (clone $globalStats)->where('status', 'Ditolak')->count(),
            'sisaCuti' => $this->hitungSisaCuti($user->id),
            'warningMessage' => $warningMessage,
            'hasPendingCuti' => Cuti::where('user_id', $user->id)->where('status', 'Menunggu')->exists(),
            'cutiIsPaginator' => $cuti instanceof \Illuminate\Pagination\LengthAwarePaginator,
            'riwayatIsPaginator' => $riwayat instanceof \Illuminate\Pagination\LengthAwarePaginator,
        ]);
    }

    /** ========================== 📝 STORE CUTI ============================= */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->with('error', 'Data pegawai belum ditemukan.');
        }

        // 1. PENGAMAN: Cek pengajuan pending milik sendiri
        $hasPending = Cuti::where('user_id', $user->id)
                        ->where('status', 'Menunggu')
                        ->exists();
        
        if ($hasPending) {
            return back()->with('error', 'Anda masih memiliki pengajuan cuti yang menunggu persetujuan.');
        }

        // 2. VALIDASI FORM
        $validated = $request->validate([
            'id_delegasi'     => 'required|exists:pegawai,id',
            'jenis_cuti'      => 'required|in:Tahunan',
            'alamat'          => 'required|string|max:255',
            'keterangan'      => 'required|string|max:500',
            'tanggal_mulai'   => [
                'required', 'date',
                function ($attribute, $value, $fail) {
                    if (\Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::today()->addDays(3))) {
                        $fail('Tanggal mulai cuti minimal 3 hari dari hari ini.');
                    }
                },
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // 3. VALIDASI DELEGASI (Unit Kerja & Role)
        // Gunakan eager load 'user' untuk mengecek role
        $delegasi = \App\Models\Pegawai::with('user')->find($validated['id_delegasi']);

        if ($delegasi->unit_kerja !== $pegawai->unit_kerja || $delegasi->id === $pegawai->id) {
            return back()->with('error', 'Pegawai pengganti harus berada di unit kerja yang sama dan bukan diri sendiri.');
        }

        // Filter agar hanya role 'pegawai' yang bisa dipilih
        if ($delegasi->user->role !== 'pegawai') {
            return back()->with('error', 'Pegawai pengganti harus memiliki jabatan staf (bukan Atasan/Pejabat).');
        }

        // 4. VALIDASI KETERSEDIAAN (Cek tabrakan jadwal cuti delegasi)
        $isDelegateOnLeave = Cuti::where('id_pegawai', $delegasi->id)
            ->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Disetujui Kadis'])
            ->where(function ($query) use ($validated) {
                // Logika Overlap: (StartA <= EndB) AND (EndA >= StartB)
                $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                    ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
            })
            ->exists();

        if ($isDelegateOnLeave) {
            return back()->with('error', 'Gagal! Pegawai pengganti (' . $delegasi->nama . ') sudah memiliki jadwal cuti yang disetujui pada periode tersebut.');
        }

        // 5. HITUNG DURASI & CEK KUOTA
        $jumlah_hari = $this->calculateWorkingDays($validated['tanggal_mulai'], $validated['tanggal_selesai']);

        if ($pegawai->sisa_cuti < $jumlah_hari) {
            return back()->with('error', 'Gagal! Sisa cuti Anda tidak mencukupi.');
        }

        // 6. SIMPAN DATA
        Cuti::create([
            'user_id'         => $user->id,
            'id_pegawai'      => $pegawai->id,
            'id_delegasi'     => $validated['id_delegasi'],
            'nama'            => $pegawai->nama,
            'nip'             => $pegawai->nip,
            'jabatan'         => $pegawai->jabatan,
            'alamat'          => $validated['alamat'],
            'jenis_cuti'      => $validated['jenis_cuti'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jumlah_hari'     => $jumlah_hari,
            'tahun'           => date('Y'),
            'keterangan'      => $validated['keterangan'],
            'status'          => 'Menunggu',
            
            'atasan_nama'     => $pegawai->atasanLangsung->nama_atasan ?? '-', 
            'pejabat_nama'    => $pegawai->pejabatPemberiCuti->nama_pejabat ?? '-',
            'id_atasan_langsung'      => $pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
        ]);

        return redirect()->route('pegawai.cuti.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    /** ========================== ✏️ UPDATE CUTI ============================= */
    public function update(Request $request, $id)
    {
        // 1. PENGAMAN: Pastikan data milik user yang login & cegah ID guessing
        $cuti = Cuti::where('user_id', Auth::id())->findOrFail($id);

        // 2. KUNCI DATA: Jika status sudah bukan 'Menunggu', blokir akses
        if ($cuti->status !== 'Menunggu') {
            return redirect()->route('pegawai.cuti.index')
                ->with('error', 'Gagal! Pengajuan sudah diproses oleh atasan dan tidak dapat diubah lagi.');
        }

        // 3. VALIDASI (Tetap menggunakan aturan 3 hari lead time)
        $request->validate([
            'tanggal_mulai' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $minDate = \Carbon\Carbon::today()->addDays(3);
                    if (\Carbon\Carbon::parse($value)->lt($minDate)) {
                        $fail('Tanggal mulai cuti minimal 3 hari dari hari ini.');
                    }
                },
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500',
            'alamat'          => 'nullable|string|max:255',
        ]);

        // 4. HITUNG DURASI (Hari Kerja)
        $jumlahHari = $this->calculateWorkingDays($request->tanggal_mulai, $request->tanggal_selesai);

        // 5. EKSEKUSI UPDATE
        $cuti->update([
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $jumlahHari,
            'keterangan'      => $request->keterangan,
            'alamat'          => $request->alamat,
        ]);

        return redirect()->route('pegawai.cuti.index')
            ->with('success', 'Data pengajuan cuti berhasil diperbarui.');
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
            'nama'            => $cuti->pegawai->nama ?? '-',
            'nip'             => $cuti->pegawai->nip ?? '-',
            'jenis_cuti'      => $cuti->jenis_cuti ?? '-',
            'tanggal_mulai'   => $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d-m-Y') : '-',
            'tanggal_selesai' => $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d-m-Y') : '-',
            'jumlah_hari'     => $cuti->jumlah_hari ?? 0,
            'alasan_cuti'     => $cuti->keterangan ?? '-',
            'status_badge'    => $statusBadgeHtml,

            // Perbaikan: Ambil dari kolom yang sudah kita simpan di tabel cuti
            'atasan'          => $cuti->atasan_nama ?? '-', 
            'pejabat'         => $cuti->pejabat_nama ?? '-',
        ]);
    }


    /** ========================== 🗑 DELETE CUTI ============================ */
public function destroy($id)
{
    $user = Auth::user();

    // 1. Cari data sekaligus pastikan milik user yang sedang login
    $cuti = Cuti::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

    // 2. Jika data tidak ditemukan (misal ID salah atau punya orang lain)
    if (!$cuti) {
        return redirect()->back()->with('error', 'Data cuti tidak ditemukan.');
    }

    // 3. GEMBOK LOGIKA: Cek status sebelum hapus
    // Jika status sudah 'Disetujui' atau 'Ditolak', jangan biarkan dihapus!
    //if ($cuti->status !== 'Menunggu') {
        //return redirect()->back()->with('error', 'Gagal! Pengajuan yang sudah diproses oleh atasan tidak dapat dihapus untuk alasan arsip.');
    //}

    // 4. Eksekusi jika masih berstatus 'Menunggu'
    $cuti->delete();

    return redirect()->back()->with('success', 'Riwayat pengajuan cuti berhasil dihapus.');
}


 /**
 * ========================== 🔢 HITUNG SISA CUTI ============================
 */
private function hitungSisaCuti($pegawaiId)
{
    $tahunSekarang = date('Y'); // Mengambil tahun 2026

    // 1. Jatah cuti standar hanya untuk tahun berjalan
    $jatahTahunan = 12;

    // 2. Hitung penggunaan cuti tahun berjalan
    // Sertakan status 'Menunggu' agar kuota langsung terpotong saat diajukan
    $terpakaiTahunIni = Cuti::where('user_id', $pegawaiId)
        ->where('tahun', $tahunSekarang)
        ->whereIn('status', ['Disetujui', 'disetujui', 'Menunggu', 'menunggu'])
        ->sum('jumlah_hari');

    // 3. Hasil Akhir: 12 - (Cuti Disetujui + Cuti Menunggu)
    // Perhitungan: 12 - 5 = 7
    $sisaFinal = max(0, $jatahTahunan - $terpakaiTahunIni);

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
        'id_atasan_langsung',
        'id_pejabat_pemberi_cuti',
    ];

    foreach ($dataWajib as $field) {
        if (empty($pegawai->$field)) {
            return false;
        }
    }

    return true;
}

/**
 * ========================== 📅 HITUNG HARI KERJA ============================
 * Menghitung jumlah hari kerja (exclude weekend dan libur nasional)
 * ============================================================================
 */
private function calculateWorkingDays($startDate, $endDate)
{
    $start = \Carbon\Carbon::parse($startDate);
    $end = \Carbon\Carbon::parse($endDate);
    
    // Ambil data libur nasional dari API
    $holidays = $this->getHolidays($start->year);
    
    $workingDays = 0;
    $current = $start->copy();
    
    while ($current <= $end) {
        // Skip weekend (Sabtu = 6, Minggu = 0)
        if (!in_array($current->dayOfWeek, [0, 6])) {
            // Skip libur nasional
            if (!in_array($current->toDateString(), $holidays)) {
                $workingDays++;
            }
        }
        $current->addDay();
    }
    
    return max(1, $workingDays); // Minimal 1 hari
}

/**
 * ========================== 🎉 AMBIL DATA LIBUR NASIONAL ====================
 * Mengambil data libur dari API dayoffapi.vercel.app
 * ============================================================================
 */
    private function getHolidays($year)
    {
        try {
            $url = "https://dayoffapi.vercel.app/api?year={$year}";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            // Return array of date strings
            return array_column($data, 'tanggal');
        } catch (\Exception $e) {
            // Jika API error, return empty array
            \Log::warning('Failed to fetch holidays: ' . $e->getMessage());
            return [];
        }
    }
}