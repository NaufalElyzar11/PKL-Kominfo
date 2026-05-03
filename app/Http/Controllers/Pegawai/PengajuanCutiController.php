<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use App\Models\Notification;
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

    // 1. Ambil Rekan untuk Delegasi (INDEX METHOD)
    $rekanSebidang = collect(); 
    if ($pegawai && $pegawai->id_atasan_langsung) {
        $rekanSebidang = \App\Models\Pegawai::where('id_atasan_langsung', $pegawai->id_atasan_langsung)
            ->where('id', '!=', $pegawai->id) // Jangan diri sendiri
            ->where('status', 'aktif')       // WAJIB: Pastikan statusnya Aktif
            ->whereHas('user', function($query) {
                // IZINKAN role 'pegawai' DAN 'atasan' agar daftar tidak kosong
                $query->whereIn('role', ['pegawai', 'atasan']); 
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
        ->whereIn('status', ['Menunggu', 'Revisi Delegasi'])
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
            'hasPendingCuti' => Cuti::where('user_id', $user->id)
                        ->whereNotIn('status', ['Disetujui', 'Ditolak', 'disetujui', 'ditolak'])
                        ->exists(),
            'cutiIsPaginator' => $cuti instanceof \Illuminate\Pagination\LengthAwarePaginator,
            'riwayatIsPaginator' => $riwayat instanceof \Illuminate\Pagination\LengthAwarePaginator,
        ]);
    }

    /** ========================== 📝 STORE CUTI ============================= */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) return back()->with('error', 'Data pegawai tidak ditemukan.');

        $jenisCuti = $request->input('jenis_cuti');

        // Validasi dasar
        $validated = $request->validate([
            'jenis_cuti'      => 'required|in:Tahunan,Alasan Penting',
            'id_delegasi'     => 'nullable|exists:pegawai,id',
            'keterangan'      => 'required|string|max:500',
            'tanggal_mulai'   => ['required', 'date', function($a, $v, $fail) {
                if (Carbon::parse($v)->lt(Carbon::today())) $fail('Tanggal tidak boleh di masa lalu.');
            }],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // Cek double booking pribadi
        if (Cuti::where('user_id', $user->id)
            ->whereNotIn('status', ['Ditolak', 'ditolak'])
            ->where(fn($q) => $q->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])->where('tanggal_selesai', '>=', $validated['tanggal_mulai']))
            ->exists()) {
            return back()->with('error', 'Anda sudah memiliki jadwal cuti pada tanggal tersebut.');
        }

        $idDelegasiDarurat = null;

        if ($jenisCuti === 'Tahunan') {
            // ---- CUTI TAHUNAN: semua validasi berlaku ----
            if (!$request->id_delegasi) return back()->with('error', 'Cuti Tahunan wajib menyertakan delegasi.');

            $unitKerja = trim($pegawai->unit_kerja);
            $count = Cuti::whereHas('pegawai', fn($q) => $q->where('unit_kerja', $unitKerja))
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->whereMonth('tanggal_mulai', Carbon::parse($validated['tanggal_mulai'])->month)
                ->whereYear('tanggal_mulai', Carbon::parse($validated['tanggal_mulai'])->year)
                ->distinct('user_id')->count();
            if ($count >= 2) return back()->with('error', 'Kuota cuti di bidang Anda sudah penuh (maks. 2 orang).');

            if (Cuti::where('user_id', $user->id)->where('jenis_cuti', 'Tahunan')
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->whereMonth('tanggal_mulai', Carbon::parse($validated['tanggal_mulai'])->month)
                ->whereYear('tanggal_mulai', Carbon::parse($validated['tanggal_mulai'])->year)
                ->exists()) return back()->with('error', 'Cuti Tahunan hanya boleh 1x dalam sebulan.');

            $delegasi = \App\Models\Pegawai::with('user')->find($validated['id_delegasi']);
            if (!$delegasi || $delegasi->id_atasan_langsung !== $pegawai->id_atasan_langsung || $delegasi->id === $pegawai->id) {
                return back()->with('error', 'Delegasi harus satu atasan langsung dengan Anda.');
            }
            if (Cuti::where('id_pegawai', $delegasi->id)->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Menunggu'])
                ->where(fn($q) => $q->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])->where('tanggal_selesai', '>=', $validated['tanggal_mulai']))
                ->exists()) return back()->with('error', "Delegasi ({$delegasi->nama}) sedang cuti di tanggal tersebut.");

            // Pemohon sedang jadi delegasi → DITOLAK untuk Tahunan
            $cutiKonflikTahunan = Cuti::where('id_delegasi', $pegawai->id)
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->where(fn($q) => $q->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])->where('tanggal_selesai', '>=', $validated['tanggal_mulai']))
                ->first();
            if ($cutiKonflikTahunan) return back()->with('error', "Anda sedang menjadi delegasi untuk {$cutiKonflikTahunan->nama}. Untuk urusan mendesak, ajukan Cuti Alasan Penting.");

            // Cek sisa kuota
            $jumlah_hari_cek = $this->calculateWorkingDays($validated['tanggal_mulai'], $validated['tanggal_selesai']);
            if ($this->hitungSisaCuti($user->id) < $jumlah_hari_cek) {
                return back()->with('error', 'Sisa kuota cuti tidak mencukupi.');
            }

        } else {
            // ---- CUTI ALASAN PENTING: delegasi opsional, kuota tidak dipotong ----

            // Validasi delegasi jika diisi
            if (!empty($validated['id_delegasi'])) {
                $delegasi = \App\Models\Pegawai::with('user')->find($validated['id_delegasi']);
                if (!$delegasi || $delegasi->id_atasan_langsung !== $pegawai->id_atasan_langsung || $delegasi->id === $pegawai->id) {
                    return back()->with('error', 'Delegasi harus satu atasan langsung dengan Anda.');
                }
                if (Cuti::where('id_pegawai', $delegasi->id)->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Menunggu'])
                    ->where(fn($q) => $q->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])->where('tanggal_selesai', '>=', $validated['tanggal_mulai']))
                    ->exists()) return back()->with('error', "Delegasi ({$delegasi->nama}) sedang cuti di tanggal tersebut.");
            }

            // Pemohon sedang jadi delegasi → DIIZINKAN tapi wajib tunjuk delegasi darurat
            $cutiKonflikAP = Cuti::where('id_delegasi', $pegawai->id)
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->where(fn($q) => $q->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])->where('tanggal_selesai', '>=', $validated['tanggal_mulai']))
                ->first();

            if ($cutiKonflikAP) {
                $request->validate(['id_delegasi_darurat' => 'required|exists:pegawai,id'], [
                    'id_delegasi_darurat.required' => 'Anda sedang menjadi delegasi. Pilih pengganti delegasi darurat.'
                ]);
                $delegasiDarurat = \App\Models\Pegawai::find($request->id_delegasi_darurat);
                if (!$delegasiDarurat || $delegasiDarurat->id_atasan_langsung !== $pegawai->id_atasan_langsung || $delegasiDarurat->id === $pegawai->id) {
                    return back()->with('error', 'Delegasi darurat harus satu atasan langsung dengan Anda.');
                }
                $idDelegasiDarurat = $request->id_delegasi_darurat;
            }
        }

        $jumlah_hari = $this->calculateWorkingDays($validated['tanggal_mulai'], $validated['tanggal_selesai']);

        $cutiBaru = Cuti::create([
            'user_id'               => $user->id,
            'id_pegawai'            => $pegawai->id,
            'id_delegasi'           => $validated['id_delegasi'] ?? null,
            'id_delegasi_darurat'   => $idDelegasiDarurat,
            'nama'                  => $pegawai->nama,
            'nip'                   => $pegawai->nip ?? '-',
            'jabatan'               => $pegawai->jabatan,
            'jenis_cuti'            => $validated['jenis_cuti'],
            'tanggal_mulai'         => $validated['tanggal_mulai'],
            'tanggal_selesai'       => $validated['tanggal_selesai'],
            'jumlah_hari'           => $jumlah_hari,
            'tahun'                 => date('Y'),
            'keterangan'            => $validated['keterangan'],
            'status'                => 'Menunggu',
            'atasan_nama'           => $pegawai->atasanLangsung->nama_atasan ?? '-',
            'pejabat_nama'          => $pegawai->pejabatPemberiCuti->nama_pejabat ?? '-',
            'id_atasan_langsung'    => $pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
        ]);

        // 🔔 NOTIFIKASI
        try {
            $nipAtasan = $pegawai->atasanLangsung->nip ?? null;
            if ($nipAtasan) {
                $atasanUser = \App\Models\User::whereHas('pegawai', fn($q) => $q->where('nip', $nipAtasan))->first();
                if ($atasanUser) \App\Models\Notification::create([
                    'user_id' => $atasanUser->id,
                    'title'   => 'Pengajuan Cuti Baru',
                    'message' => "Pegawai {$pegawai->nama} mengajukan {$validated['jenis_cuti']}. Mohon ditinjau.",
                    'is_read' => false,
                ]);
            }
            if (!empty($validated['id_delegasi'])) {
                $delegasiNotif = \App\Models\Pegawai::with('user')->find($validated['id_delegasi']);
                if ($delegasiNotif && $delegasiNotif->user) {
                    $tglMulai = Carbon::parse($validated['tanggal_mulai'])->translatedFormat('d F Y');
                    $tglSelesai = Carbon::parse($validated['tanggal_selesai'])->translatedFormat('d F Y');
                    \App\Models\Notification::create([
                        'user_id' => $delegasiNotif->user->id,
                        'title'   => 'Permintaan Delegasi Tugas',
                        'message' => "Anda ditunjuk sebagai pengganti {$pegawai->nama} dari {$tglMulai} s/d {$tglSelesai}.",
                        'is_read' => false,
                    ]);
                }
            }
            if ($idDelegasiDarurat) {
                $darurat = \App\Models\Pegawai::with('user')->find($idDelegasiDarurat);
                if ($darurat && $darurat->user) \App\Models\Notification::create([
                    'user_id' => $darurat->user->id,
                    'title'   => 'Penunjukan Delegasi Darurat',
                    'message' => "{$pegawai->nama} menunjuk Anda sebagai pengganti delegasi sementara karena mengajukan Cuti Alasan Penting.",
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal kirim notifikasi: ' . $e->getMessage());
        }

        return redirect()->route('pegawai.cuti.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    /** ========================== ✏️ UPDATE CUTI ============================= */
    public function update(Request $request, $id)
    {
        // 1. PENGAMAN: Izinkan jika status 'Menunggu' ATAU 'Revisi Delegasi'
        $cuti = Cuti::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($cuti->status, ['Menunggu', 'Revisi Delegasi'])) {
            return redirect()->route('pegawai.cuti.index')
                ->with('error', 'Gagal! Pengajuan sudah masuk tahap approval akhir dan tidak dapat diubah.');
        }

        // 2. VALIDASI — delegasi wajib hanya untuk Cuti Tahunan
        $validated = $request->validate([
            'tanggal_mulai' => [
                'required', 'date',
                function ($attribute, $value, $fail) {
                    if (\Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::today())) {
                        $fail('Tanggal mulai cuti tidak boleh di masa lalu.');
                    }
                },
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500',
            'id_delegasi'     => ($cuti->jenis_cuti === 'Tahunan') ? 'required|exists:pegawai,id' : 'nullable|exists:pegawai,id',
        ]);

        $pegawai = Auth::user()->pegawai;

        // 3. VALIDASI DELEGASI BARU
        if ($request->filled('id_delegasi')) {
            $delegasi = \App\Models\Pegawai::with('user')->find($request->id_delegasi);

            if ($delegasi->id_atasan_langsung !== $pegawai->id_atasan_langsung || $delegasi->id === $pegawai->id) {
                return back()->with('error', 'Pegawai pengganti harus berada di bawah atasan langsung yang sama.');
            }

            // Cek tabrakan jadwal cuti delegasi baru
            $isDelegateOnLeave = Cuti::where('id_pegawai', $delegasi->id)
                ->whereIn('status', ['Disetujui', 'Disetujui Atasan'])
                ->where(function ($query) use ($validated) {
                    $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                        ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
                })
                ->exists();

            if ($isDelegateOnLeave) {
                return back()->with('error', 'Pegawai pengganti (' . $delegasi->nama . ') sudah memiliki jadwal cuti di periode tersebut.');
            }
        }

        // ==================================================================================
        // POINT 1: VALIDASI BENTROK JADWAL PRIBADI (Pencegahan Double Booking)
        // ==================================================================================
        $existingLeaveUpdate = Cuti::where('user_id', Auth::id())
            ->where('id', '!=', $id) // Abaikan data yang sedang kita edit ini
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
            ->where(function ($query) use ($request) {
                $query->where('tanggal_mulai', '<=', $request->tanggal_selesai)
                      ->where('tanggal_selesai', '>=', $request->tanggal_mulai);
            })
            ->exists();

        if ($existingLeaveUpdate) {
            return back()->with('error', '<b>Gagal Update!</b><br>Tanggal revisi yang Anda pilih bentrok dengan jadwal cuti Anda yang lain.');
        }

        // ==================================================================================
        // POINT 2: VALIDASI BATAS 1X CUTI TAHUNAN SEBULAN
        // ==================================================================================
        if ($cuti->jenis_cuti === 'Tahunan') { // <--- Bungkus dengan IF
            $bulanUpdate = \Carbon\Carbon::parse($request->tanggal_mulai)->month;
            $tahunUpdate = \Carbon\Carbon::parse($request->tanggal_mulai)->year;
            $unitKerja = trim($pegawai->unit_kerja);

            $cekKuotaUpdate = Cuti::whereHas('pegawai', function($q) use ($unitKerja) {
                    $q->where('unit_kerja', $unitKerja);
                })
                ->where('user_id', '!=', Auth::id())
                ->where('id', '!=', $id)
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->where(function($q) use ($bulanUpdate, $tahunUpdate) {
                    $q->whereMonth('tanggal_mulai', $bulanUpdate)
                      ->whereYear('tanggal_mulai', $tahunUpdate);
                })
                ->distinct('user_id')
                ->count('user_id');

            if ($cekKuotaUpdate >= 2) {
                return back()->with('error', 'Gagal Update! Kuota Cuti Tahunan sudah penuh.');
            }
        }

        // 4. HITUNG DURASI
        $jumlahHari = $this->calculateWorkingDays($request->tanggal_mulai, $request->tanggal_selesai);

        // 5. UPDATE DATA & RESET STATUS (PENTING)
        $updateData = [
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $jumlahHari,
            'keterangan'      => $request->keterangan,
            'id_delegasi'     => $request->id_delegasi ?: null,
            'status'          => 'Menunggu',
            'catatan_tolak_delegasi' => null,
        ];

        // Reset status_delegasi hanya untuk Tahunan (Alasan Penting tidak perlu approval delegasi)
        if ($cuti->jenis_cuti === 'Tahunan') {
            $updateData['status_delegasi'] = 'pending';
        }

        $cuti->update($updateData);

        return redirect()->route('pegawai.cuti.index')
            ->with('success', 'Pengajuan berhasil direvisi dan dikirim ulang ke atasan.');
    }

    /** ========================== 🔄 GET AVAILABLE DELEGATES ============================= */
    public function getAvailableDelegates(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai || !$pegawai->id_atasan_langsung) {
            return response()->json([]);
        }

        // 1. Ambil semua kandidat rekan sebidang yang aktif
        $candidates = \App\Models\Pegawai::where('id_atasan_langsung', $pegawai->id_atasan_langsung)
            ->where('id', '!=', $pegawai->id) // Jangan diri sendiri
            ->where('status', 'aktif')
            ->whereHas('user', function($query) {
                $query->whereIn('role', ['pegawai', 'atasan']);
            })
            ->get();

        // 2. Filter kandidat yang benar-benar tersedia
        $availableDelegates = $candidates->filter(function ($candidate) use ($request) {
            // CEK: Apakah kandidat sedang SIBUK di tanggal tersebut?
            // Sibuk = Dia sedang CUTI (id_pegawai) ATAU dia sudah jadi DELEGASI orang lain (id_delegasi)
            $isBusy = Cuti::where(function ($q) use ($candidate) {
                    $q->where('id_pegawai', $candidate->id) 
                    ->orWhere('id_delegasi', $candidate->id);
                })
                ->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Disetujui Kadis', 'Menunggu', 'Revisi Delegasi'])
                ->where(function ($query) use ($request) {
                    // Logika Overlap Tanggal
                    $query->where('tanggal_mulai', '<=', $request->tanggal_selesai)
                        ->where('tanggal_selesai', '>=', $request->tanggal_mulai);
                })
                ->exists();

            // Masukkan ke daftar hanya jika TIDAK SIBUK (isBusy == false)
            return !$isBusy;
        });

        // 3. Format response untuk dropdown
        return response()->json($availableDelegates->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'jabatan' => $p->jabatan
            ];
        })->values());
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
private function hitungSisaCuti($userId)
{
    $user = \App\Models\User::with('pegawai')->find($userId);
    $pegawai = $user->pegawai;
    if (!$pegawai) return 12;

    $jatahDasar = 12;
    $tahunIni = (int) date('Y');
    $tahunLalu = $tahunIni - 1;

    // 1. OTOMATIS: Hitung pemakaian tahun lalu di tabel Cuti (Untuk 2027 dst)
    $pakaiTahunLalu = Cuti::where('user_id', $userId)
        ->where('tahun', $tahunLalu)
        ->whereIn('status', ['Disetujui', 'disetujui'])
        ->sum('jumlah_hari');

    // 2. FALLBACK: Jika di tabel cuti 2025 kosong, ambil angka manual Anda (angka 5)
    if ($pakaiTahunLalu == 0) {
        $pakaiTahunLalu = (int) $pegawai->sisa_cuti; 
    }

    // 3. LOGIKA AKUMULASI: Sisa tahun lalu dibawa jika pakai <= 6 hari
    $jatahAkumulasi = ($pakaiTahunLalu > 0 && $pakaiTahunLalu <= 6) ? ($jatahDasar - $pakaiTahunLalu) : 0;
    $totalHakTahunIni = $jatahDasar + $jatahAkumulasi;


    // 4. HITUNG PEMAKAIAN TAHUN INI — HANYA CUTI TAHUNAN yang memotong kuota
    $terpakaiTahunIni = Cuti::where('user_id', $userId)
        ->where('tahun', $tahunIni)
        ->where('jenis_cuti', 'Tahunan')  // Alasan Penting tidak memotong kuota
        ->whereIn('status', ['Disetujui', 'disetujui', 'Menunggu', 'Revisi Delegasi', 'Disetujui Atasan'])
        ->sum('jumlah_hari');

    return max(0, $totalHakTahunIni - $terpakaiTahunIni);
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

    public function checkConflict(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $mulai = $request->tanggal_mulai;
        $selesai = $request->tanggal_selesai;

        if (!$pegawai || !$mulai || !$selesai) return response()->json([]);

        // Cari rekan kerja yang memiliki atasan yang sama
        $conflicts = Cuti::where('id_atasan_langsung', $pegawai->id_atasan_langsung)
            ->where('user_id', '!=', $user->id) // Jangan hitung diri sendiri
            ->whereIn('status', ['Menunggu', 'Disetujui Atasan', 'Disetujui']) // Status yang dianggap aktif
            ->where(function($q) use ($mulai, $selesai) {
                // Rumus Matematika Irisan Tanggal:
                // (TanggalMulai_A <= TanggalSelesai_B) DAN (TanggalSelesai_A >= TanggalMulai_B)
                $q->where('tanggal_mulai', '<=', $selesai)
                ->where('tanggal_selesai', '>=', $mulai);
            })
            ->with('pegawai')
            ->get()

            ->map(function ($cuti) {
            $cuti->tgl_mulai_format = \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y');
            $cuti->tgl_selesai_format = \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y');
            return $cuti;
        });

        return response()->json($conflicts);
    }

    /**
     * ========================== 🚨 CEK KONFLIK DELEGASI ============================
     * Endpoint AJAX: cek apakah pemohon sedang jadi delegasi di tanggal yang dipilih.
     * Digunakan frontend untuk menampilkan form delegasi darurat.
     */
    public function checkDelegasiKonflik(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai || !$request->tanggal_mulai || !$request->tanggal_selesai) {
            return response()->json(['konflik' => false]);
        }

        $cutiKonflik = Cuti::with('pegawai')
            ->where('id_delegasi', $pegawai->id)
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
            ->where(function($q) use ($request) {
                $q->where('tanggal_mulai', '<=', $request->tanggal_selesai)
                  ->where('tanggal_selesai', '>=', $request->tanggal_mulai);
            })
            ->first();

        if (!$cutiKonflik) {
            return response()->json(['konflik' => false]);
        }

        return response()->json([
            'konflik'         => true,
            'nama_pemohon'    => $cutiKonflik->nama,
            'tanggal_mulai'   => \Carbon\Carbon::parse($cutiKonflik->tanggal_mulai)->translatedFormat('d F Y'),
            'tanggal_selesai' => \Carbon\Carbon::parse($cutiKonflik->tanggal_selesai)->translatedFormat('d F Y'),
            'cuti_id'         => $cutiKonflik->id,
        ]);
    }
}