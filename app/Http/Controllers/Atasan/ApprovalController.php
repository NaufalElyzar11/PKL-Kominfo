<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->get();

        // PERBAIKAN: Tambahkan 'delegasi' juga di riwayat
        $riwayat = Cuti::with(['pegawai', 'delegasi'])
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->whereIn('status', ['Disetujui Atasan', 'Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

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

        // Ambil rekan sebidang untuk delegasi
        $rekanSebidang = collect();
        if ($pegawai && $pegawai->unit_kerja) {
            $rekanSebidang = \App\Models\Pegawai::where('unit_kerja', 'LIKE', trim($pegawai->unit_kerja))
                ->where('id', '!=', $pegawai->id)
                ->whereHas('user', function($query) {
                    $query->where('role', 'pegawai');
                })
                ->get();
        }

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
            'rekanSebidang' => $rekanSebidang,
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

        return back()->with('success', 'Pengajuan cuti berhasil disetujui dan diteruskan.');
    }

    /**
     * 🔹 3. Tolak Delegasi (Langkah 1)
     */
    public function tolakDelegasi(Request $request, $id)
    {
        $request->validate(['catatan_tolak_delegasi' => 'required|string|max:255']);

        $cuti = Cuti::findOrFail($id);
        $cuti->update([
            'status_delegasi' => 'ditolak',
            'status' => 'Ditolak',
            'catatan_tolak_delegasi' => $request->catatan_tolak_delegasi
        ]);

        return back()->with('success', 'Delegasi ditolak.');
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

        return back()->with('success', 'Pengajuan cuti telah ditolak.');
    }

    /**
     * 🔹 5. Atasan Mengajukan Cuti Sendiri
     * Status langsung 'Disetujui Atasan' karena tidak perlu approval dari diri sendiri
     */
    public function storeCuti(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->with('error', 'Data pegawai belum ditemukan. Hubungi admin.');
        }

        // Cek pengajuan pending
        $hasPending = Cuti::where('user_id', $user->id)
                        ->where('status', 'Menunggu')
                        ->exists();
        
        if ($hasPending) {
            return back()->with('error', 'Anda masih memiliki pengajuan cuti yang menunggu persetujuan.');
        }

        // Validasi form
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

        // Hitung jumlah hari (sederhana, tanpa skip weekend)
        $start = \Carbon\Carbon::parse($validated['tanggal_mulai']);
        $end = \Carbon\Carbon::parse($validated['tanggal_selesai']);
        $jumlah_hari = $start->diffInDays($end) + 1;

        // Cek sisa cuti
        if ($pegawai->sisa_cuti < $jumlah_hari) {
            return back()->with('error', 'Sisa cuti Anda tidak mencukupi.');
        }

        // Simpan cuti dengan status langsung 'Disetujui Atasan'
        Cuti::create([
            'user_id'         => $user->id,
            'id_pegawai'      => $pegawai->id,
            'id_delegasi'     => $validated['id_delegasi'],
            'nama'            => $pegawai->nama,
            'nip'             => $pegawai->nip ?? '-',
            'jabatan'         => $pegawai->jabatan,
            'alamat'          => $validated['alamat'],
            'jenis_cuti'      => $validated['jenis_cuti'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jumlah_hari'     => $jumlah_hari,
            'tahun'           => date('Y'),
            'keterangan'      => $validated['keterangan'],
            // Status langsung 'Disetujui Atasan' karena atasan tidak perlu approval dari diri sendiri
            'status'          => 'Disetujui Atasan',
            'status_delegasi' => 'disetujui', // Auto approve delegasi karena atasan
            'status_atasan'   => 'disetujui', // Auto approve karena diri sendiri atasan
            
            'atasan_nama'     => $pegawai->nama, // Atasan = diri sendiri
            'pejabat_nama'    => $pegawai->pejabatPemberiCuti->nama_pejabat ?? '-',
            'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil dikirim ke Pejabat untuk approval.');
    }
}