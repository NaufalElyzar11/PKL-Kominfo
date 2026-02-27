<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class ApprovalController extends Controller
{
    /**
     * 🔹 Dashboard Atasan (URL: /atasan/dashboard)
     */
    public function dashboard()
    {
        $user = Auth::user();
        $atasanName = $user->name;
        $pegawai = $user->pegawai;

        $stats = [
            'menunggu' => Cuti::whereHas('pegawai', function($q) use ($atasanName) {
                $q->where('atasan', $atasanName);
            })->where('status', 'Menunggu')->count(),

            'disetujui' => Cuti::whereHas('pegawai', function($q) use ($atasanName) {
                $q->where('atasan', $atasanName);
            })->whereIn('status', ['Disetujui Atasan', 'Disetujui'])->count(),

            'ditolak' => Cuti::whereHas('pegawai', function($q) use ($atasanName) {
                $q->where('atasan', $atasanName);
            })->where('status', 'Ditolak')->count(),
        ];

        // PERBAIKAN: Tambahkan 'delegasi' di dalam with()
        $pengajuan = Cuti::with(['pegawai', 'delegasi'])
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->where('status', 'Menunggu')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pengajuan_page');

        // PERBAIKAN: Tambahkan 'delegasi' juga di riwayat
        $riwayat = Cuti::with(['pegawai', 'delegasi'])
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->whereIn('status', ['Disetujui Atasan', 'Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'riwayat_page');

        // Ambil rekan sebidang untuk delegasi (pegawai di bawah atasan ini)
        $rekanSebidang = collect();
        if ($pegawai && $pegawai->unit_kerja) {
            $rekanSebidang = \App\Models\Pegawai::where('unit_kerja', 'LIKE', trim($pegawai->unit_kerja))
                ->where('id', '!=', $pegawai->id)
                ->whereHas('user', function($query) {
                    $query->where('role', 'pegawai');
                })
                ->get();
        }

        return view('atasan.dashboard', compact('stats', 'pengajuan', 'riwayat', 'rekanSebidang', 'pegawai'));
    }

    /**
     * 🔹 Halaman Pengajuan Cuti untuk Atasan
     */
    public function indexCuti()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $tahun = request('tahun', date('Y'));

        // Query cuti milik atasan sendiri
        $baseQuery = Cuti::with(['pegawai', 'delegasi'])
            ->where('user_id', $user->id);

        if ($tahun !== 'semua') {
            $baseQuery->where('tahun', $tahun);
        }

        // Pengajuan menunggu
        $cuti = (clone $baseQuery)
            ->whereIn('status', ['Menunggu', 'Disetujui Atasan'])
            ->latest()
            ->paginate(10, ['*'], 'menunggu_page');

        // Riwayat (sudah diproses)
        $riwayat = (clone $baseQuery)
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->latest()
            ->paginate(10, ['*'], 'riwayat_page');

        // Global stats
        $globalStats = Cuti::where('user_id', $user->id);

        return view('atasan.pengajuancuti.index', [
            'pegawai' => $pegawai,
            'cuti' => $cuti,
            'riwayat' => $riwayat,
            'tahun' => $tahun,
            'totalCuti' => (clone $globalStats)->count(),
            'cutiPending' => (clone $globalStats)->whereIn('status', ['Menunggu', 'Disetujui Atasan'])->count(),
            'cutiDisetujui' => (clone $globalStats)->where('status', 'Disetujui')->count(),
            'cutiDitolak' => (clone $globalStats)->where('status', 'Ditolak')->count(),
            'hasPendingCuti' => Cuti::where('user_id', $user->id)->whereIn('status', ['Menunggu', 'Disetujui Atasan'])->exists(),
        ]);
    }

    /**
     * 🔹 1. Setujui Delegasi (Aksi Langkah 1 di Modal)
     */
    public function approveDelegasi($id)
    {
        $cuti = Cuti::findOrFail($id);
        $cuti->update(['status_delegasi' => 'disetujui']);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Success']);
        }

        // Notify User
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Delegasi Disetujui',
            'message' => 'Permintaan delegasi tugas Anda kepada ' . ($cuti->delegasi->nama ?? 'Rekan') . ' telah disetujui.',
            'is_read' => false,
        ]);

        return back()->with('success', 'Delegasi berhasil disetujui.');
    }

    /**
     * 🔹 2. Setujui Pengajuan Cuti (Aksi Langkah 2 di Modal)
     */
    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);
        
        // Pastikan delegasi sudah disetujui jika ingin lanjut ke persetujuan cuti
        if ($cuti->status_delegasi !== 'disetujui') {
            return back()->with('error', 'Silakan setujui delegasi terlebih dahulu.');
        }

        $cuti->update([
            'status' => 'Disetujui Atasan',
            'status_atasan' => 'disetujui'
        ]);

        // Notify User (Pemohon)
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Cuti Disetujui Atasan',
            'message' => 'Pengajuan cuti Anda telah disetujui oleh atasan langsung dan diteruskan ke pejabat berwenang.',
            'is_read' => false,
        ]);

        // ==================================================================================
        // 🔔 NOTIFIKASI UNTUK SEMUA PEJABAT (Authorize Role: Pejabat)
        // ==================================================================================
        try {
            // Ambil semua user dengan role 'pejabat'
            $pejabatUsers = \App\Models\User::where('role', 'pejabat')->get();

            foreach ($pejabatUsers as $pejabat) {
                \App\Models\Notification::create([
                    'user_id' => $pejabat->id,
                    'title'   => 'Approval Cuti Diperlukan',
                    'message' => "Pengajuan cuti pegawai {$cuti->pegawai->nama} menunggu persetujuan akhir Anda.",
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi ke pejabat: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan cuti berhasil disetujui dan diteruskan.');
    }

    /**
     * 🔹 3. Tolak Delegasi (Langkah 1)
     */
    public function tolakDelegasi(Request $request, $id)
    {
        $request->validate([
        // regex:/^[a-zA-Z\s]+$/ artinya hanya menerima huruf dan spasi
        'catatan_tolak_delegasi' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        ], [
            'catatan_tolak_delegasi.regex' => 'Alasan penolakan hanya boleh berisi huruf dan spasi.'
        ]);

        $cuti = Cuti::findOrFail($id);
        $cuti->update([
            'status_delegasi' => 'ditolak',
            'status' => 'Revisi Delegasi',
            'catatan_tolak_delegasi' => $request->catatan_tolak_delegasi
        ]);

        // Notify User
        Notification::create([
                'user_id' => $cuti->user_id,
                'title'   => 'Revisi Delegasi Diperlukan',
                'message' => 'Petugas pengganti ditolak atasan. Alasan: ' . $request->catatan_tolak_delegasi . '. Silakan pilih delegasi lain.',
                'is_read' => false,
        ]);

        return back()->with('success', 'Permintaan revisi delegasi dikirim ke pegawai.');
    }

    /**
     * 🔹 4. Tolak Pengajuan Cuti secara Final (Langkah 2)
     */
    public function reject(Request $request, $id)
    {
       $request->validate([
        'catatan_tolak_atasan' => [
            'required',
            'string',
            'max:100',
            // Regex: Hanya huruf (A-Z, a-z) dan Spasi (\s)
            'regex:/^[a-zA-Z\s]+$/' 
        ],
        ], [
            'catatan_tolak_atasan.regex' => 'Alasan penolakan hanya boleh berisi huruf dan spasi.',
        ]);

        $cuti = Cuti::findOrFail($id);
        $cuti->update([
            'status' => 'Ditolak',
            'status_atasan' => 'ditolak',
            'catatan_tolak_atasan' => $request->catatan_tolak_atasan
        ]);

        // Notify User
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Cuti Ditolak Atasan',
            'message' => 'Pengajuan cuti Anda ditolak oleh atasan langsung. Alasan: ' . $request->catatan_tolak_atasan,
            'is_read' => false,
        ]);

        return back()->with('success', 'Pengajuan cuti telah ditolak.');
    }

    /**
     * 5. Atasan Mengajukan Cuti Sendiri
     */
    public function storeCuti(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->with('error', 'Data pegawai belum ditemukan. Hubungi admin.');
        }

        // 1. Validasi Form
        $validated = $request->validate([
            'jenis_cuti'      => 'required|in:Tahunan,Alasan Penting',
            'keterangan'      => [
                'required', 'string', 'max:500',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'tanggal_mulai'   => [
                'required', 'date',
                function ($attribute, $value, $fail) {
                    if (\Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::today())) {
                        $fail('Tanggal mulai cuti tidak boleh di masa lalu.');
                    }
                },
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'keterangan.regex' => 'Alasan cuti hanya boleh berisi huruf dan spasi.',
        ]);

        // =====================================================================
        // 2. CEK DOUBLE BOOKING (Mencegah cuti di tanggal yang sama)
        // =====================================================================
        $isOverlap = Cuti::where('user_id', $user->id)
            ->whereIn('status', ['Menunggu', 'Disetujui Atasan', 'Disetujui']) 
            ->where(function ($query) use ($validated) {
                // Rumus Irisan: (StartA <= EndB) AND (EndA >= StartB)
                $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                      ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
            })
            ->exists();

        if ($isOverlap) {
            return back()->with('error', 'Gagal! Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut.');
        }
        // =====================================================================

        // 3. HITUNG HARI KERJA (Gunakan fungsi dinamis agar Sabtu/Minggu tidak dihitung)
        $jumlah_hari = $this->calculateWorkingDays($validated['tanggal_mulai'], $validated['tanggal_selesai']);

        // 4. CEK SISA CUTI (Gunakan hitungan dinamis)
        $sisaCutiSaatIni = $this->hitungSisaCuti($user->id);
        if ($sisaCutiSaatIni < $jumlah_hari) {
            return back()->with('error', "Gagal! Sisa jatah Anda ($sisaCutiSaatIni hari) tidak cukup untuk pengajuan $jumlah_hari hari.");
        }

        // 5. SIMPAN DATA
        Cuti::create([
            'user_id'         => $user->id,
            'id_pegawai'      => $pegawai->id,
            'id_delegasi'     => null, // <-- PASTIKAN INI NULL KARENA ATASAN TIDAK PAKAI DELEGASI
            'nama'            => $pegawai->nama,
            'nip'             => $pegawai->nip ?? '-',
            'jabatan'         => $pegawai->jabatan,
            'jenis_cuti'      => $validated['jenis_cuti'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jumlah_hari'     => $jumlah_hari,
            'tahun'           => date('Y'),
            'keterangan'      => $validated['keterangan'],
            'status'          => 'Menunggu', 
            'status_delegasi' => 'disetujui', 
            'status_atasan'   => 'pending', // Kasi (Atasan) butuh disetujui Kabid
            'atasan_nama'     => $pegawai->atasanLangsung->nama_atasan ?? '-', 
            'pejabat_nama'    => $pegawai->pejabatPemberiCuti->nama_pejabat ?? '-',
            'id_atasan_langsung'      => $pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
        ]);

        return back()->with('success', 'Pengajuan berhasil dikirim ke Atasan (Kabid) untuk ditinjau.');
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $holidays = $this->getHolidays($start->year);
        
        $workingDays = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if (!in_array($current->dayOfWeek, [0, 6]) && !in_array($current->toDateString(), $holidays)) {
                $workingDays++;
            }
            $current->addDay();
        }
        return max(1, $workingDays);
    }

    private function getHolidays($year)
    {
        try {
            $url = "https://dayoffapi.vercel.app/api?year={$year}";
            $response = file_get_contents($url);
            return array_column(json_decode($response, true), 'tanggal');
        } catch (\Exception $e) { return []; }
    }

    private function hitungSisaCuti($userId)
    {
        $user = \App\Models\User::with('pegawai')->find($userId);
        $pegawai = $user->pegawai;
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;
        $jatahDasar = 12;

        $pakaiTahunLalu = Cuti::where('user_id', $userId)->where('tahun', $tahunLalu)->whereIn('status', ['Disetujui', 'disetujui'])->sum('jumlah_hari');
        if ($pakaiTahunLalu == 0) $pakaiTahunLalu = (int) ($pegawai->sisa_cuti ?? 0);

        $jatahAkumulasi = ($pakaiTahunLalu > 0 && $pakaiTahunLalu <= 6) ? ($jatahDasar - $pakaiTahunLalu) : 0;
        $totalHak = $jatahDasar + $jatahAkumulasi;

        $terpakai = Cuti::where('user_id', $userId)->where('tahun', $tahunIni)->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Atasan', 'Menunggu'])->sum('jumlah_hari');
        return max(0, $totalHak - $terpakai);
    }

    /**
     * 🔹 Update Pengajuan Cuti Sendiri
     */
   public function updateCuti(Request $request, $id)
    {
        $cuti = Cuti::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500|regex:/^[a-zA-Z\s]+$/',
        ]);

        // =====================================================================
        // CEK DOUBLE BOOKING SAAT EDIT (Abaikan ID yang sedang diedit)
        // =====================================================================
        $isOverlapUpdate = Cuti::where('user_id', Auth::id())
            ->where('id', '!=', $id) // Abaikan data ini sendiri
            ->whereIn('status', ['Menunggu', 'Disetujui Atasan', 'Disetujui'])
            ->where(function ($query) use ($request) {
                $query->where('tanggal_mulai', '<=', $request->tanggal_selesai)
                      ->where('tanggal_selesai', '>=', $request->tanggal_mulai);
            })
            ->exists();

        if ($isOverlapUpdate) {
            return back()->with('error', 'Gagal Update! Tanggal revisi yang Anda pilih bentrok dengan jadwal cuti Anda yang lain.');
        }
        // =====================================================================

        $jumlahHari = $this->calculateWorkingDays($request->tanggal_mulai, $request->tanggal_selesai);

        $cuti->update([
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $jumlahHari,
            'keterangan'      => $request->keterangan,
            'status'          => 'Menunggu', // Reset status jika diedit
        ]);

        return redirect()->back()->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    /**
     * 🔹 Hapus Pengajuan Cuti Sendiri
     */
    public function destroyCuti($id)
    {
        $cuti = Cuti::where('user_id', Auth::id())->findOrFail($id);
        $cuti->delete();

        return redirect()->back()->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

/**
     * 🔹 Export Excel Pengajuan Cuti Sendiri (Atasan)
     */
    public function exportExcel(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $user = Auth::user();
        
        // AMBIL DATA PEGAWAI DARI USER YANG LOGIN
        $pegawai = $user->pegawai;

        // JIKA TIDAK ADA DATA PEGAWAI, KEMBALIKAN ERROR
        if (!$pegawai) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Nama file dinamis sesuai tahun
        $namaFile = 'Riwayat_Cuti_Atasan_' . ($tahun == 'semua' ? 'Semua_Tahun' : $tahun) . '.xlsx';

        // PENTING: KIRIM ID PEGAWAI ($pegawai->id), BUKAN ID USER ($user->id)
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CutiExport($pegawai->id, $tahun), 
            $namaFile
        );
    }

}