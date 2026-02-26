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
                        ->whereIn('status', ['Menunggu', 'Revisi Delegasi'])
                        ->exists();
        
        if ($hasPending) {
            return back()->with('error', 'Gagal! Anda masih memiliki pengajuan cuti yang sedang diproses.');
        }

        // 2. VALIDASI FORM
        $validated = $request->validate([
            'id_delegasi'     => 'required|exists:pegawai,id',
            'jenis_cuti'      => 'required|in:Tahunan,Alasan Penting',
            'keterangan'      => 'required|string|max:500',
            'tanggal_mulai'   => [
                'required', 'date',
                function ($attribute, $value, $fail) {
                    if (\Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::today())) {
                        $fail('Tanggal mulai cuti tidak boleh di masa lalu.');
                    }
                },
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // 3. VALIDASI HUBUNGAN DELEGASI
        $delegasi = \App\Models\Pegawai::with('user')->find($validated['id_delegasi']);
        if ($delegasi->id_atasan_langsung !== $pegawai->id_atasan_langsung || $delegasi->id === $pegawai->id) {
            return back()->with('error', 'Pegawai pengganti harus berada di bawah naungan Atasan Langsung yang sama.');
        }

        // --- Langkah 4: VALIDASI KUOTA BIDANG (Hanya untuk Cuti Tahunan) ---
        if ($validated['jenis_cuti'] === 'Tahunan') {
            $bulanCuti = \Carbon\Carbon::parse($validated['tanggal_mulai'])->month;
            $tahunCuti = \Carbon\Carbon::parse($validated['tanggal_mulai'])->year;
            $unitKerja = trim($pegawai->unit_kerja);

            $jumlahOrangCuti = Cuti::whereHas('pegawai', function($q) use ($unitKerja) {
                    $q->where('unit_kerja', $unitKerja);
                })
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->where(function($q) use ($bulanCuti, $tahunCuti) {
                    $q->whereMonth('tanggal_mulai', $bulanCuti)
                      ->whereYear('tanggal_mulai', $tahunCuti);
                })
                ->distinct('user_id')
                ->count('user_id');

            if ($jumlahOrangCuti >= 2) {
                return back()->with('error', "Gagal! Kuota Cuti Tahunan di $unitKerja sudah penuh (Maks. 2 orang).");
            }
        } // <--- KURUNG PENUTUP LANGKAH 4 HARUS DI SINI

        // 5. CEK APAKAH DELEGASI YANG DIPILIH SEDANG CUTI?
        $isDelegateOnLeave = Cuti::where('id_pegawai', $delegasi->id)
            ->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Menunggu'])
            ->where(function ($query) use ($validated) {
                $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                    ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
            })
            ->exists();

        if ($isDelegateOnLeave) {
            return back()->with('error', "Gagal! Pegawai pengganti ({$delegasi->nama}) sudah memiliki jadwal cuti.");
        }

        // 6. CEK APAKAH PEMOHON SEDANG JADI DELEGASI ORANG LAIN?
        $conflictTask = Cuti::where('id_delegasi', $pegawai->id)
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
            ->where(function ($query) use ($validated) {
                $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                    ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
            })
            ->first();

        if ($conflictTask) {
            return back()->with('error', "Gagal! Anda terdaftar sebagai Petugas Pengganti untuk {$conflictTask->nama} di tanggal tersebut.");
        }

        // 6B. VALIDASI: Cek Double Booking (Pribadi)
        $existingLeave = Cuti::where('user_id', $user->id)
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
            ->where(function ($query) use ($validated) {
                $query->where('tanggal_mulai', '<=', $validated['tanggal_selesai'])
                    ->where('tanggal_selesai', '>=', $validated['tanggal_mulai']);
            })
            ->exists();

        if ($existingLeave) {
            return back()->with('error', "Gagal! Anda sudah memiliki jadwal cuti lain di tanggal tersebut.");
        }

        // 6C. VALIDASI: Batas 1x Cuti Tahunan Sebulan
        if ($validated['jenis_cuti'] === 'Tahunan') {
            $bulanMulai = \Carbon\Carbon::parse($validated['tanggal_mulai'])->month;
            $tahunMulai = \Carbon\Carbon::parse($validated['tanggal_mulai'])->year;

            $alreadyHasTahunan = Cuti::where('user_id', $user->id)
                ->where('jenis_cuti', 'Tahunan')
                ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
                ->whereMonth('tanggal_mulai', $bulanMulai)
                ->whereYear('tanggal_mulai', $tahunMulai)
                ->exists();

            if ($alreadyHasTahunan) {
                return back()->with('error', "Gagal! Cuti Tahunan hanya boleh diajukan 1x dalam sebulan.");
            }
        }

        // 7. HITUNG DURASI & CEK SISA KUOTA PRIBADI
        $jumlah_hari = $this->calculateWorkingDays($validated['tanggal_mulai'], $validated['tanggal_selesai']);
        if ($this->hitungSisaCuti($user->id) < $jumlah_hari) {
            return back()->with('error', 'Gagal! Sisa kuota cuti Anda tidak mencukupi untuk durasi ini.');
        }

        // 8. SIMPAN DATA
        $cutiBaru = Cuti::create([
            'user_id'         => $user->id,
            'id_pegawai'      => $pegawai->id,
            'id_delegasi'     => $validated['id_delegasi'],
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
            'atasan_nama'     => $pegawai->atasanLangsung->nama_atasan ?? '-', 
            'pejabat_nama'    => $pegawai->pejabatPemberiCuti->nama_pejabat ?? '-',
            'id_atasan_langsung'      => $pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $pegawai->id_pejabat_pemberi_cuti,
        ]);

        // ==================================================================================
        // 🔔 NOTIFIKASI
        // ==================================================================================
        try {
            // A. NOTIFIKASI UNTUK ATASAN LANGSUNG
            $nipAtasan = $pegawai->atasanLangsung->nip ?? null;
            if ($nipAtasan) {
                $atasanUser = \App\Models\User::whereHas('pegawai', function($q) use ($nipAtasan) {
                    $q->where('nip', $nipAtasan);
                })->first();

                if ($atasanUser) {
                    \App\Models\Notification::create([
                        'user_id' => $atasanUser->id,
                        'title'   => 'Pengajuan Cuti Baru',
                        'message' => "Pegawai {$pegawai->nama} telah mengajukan cuti. Mohon segera ditinjau.",
                        'is_read' => false,
                    ]);
                } else {
                    \Log::warning("Notification Warning: Atasan with NIP {$nipAtasan} not found in Users table.");
                }
            }

            // B. NOTIFIKASI UNTUK DELEGASI (PEGAWAI PENGGANTI)
            // $delegasi sudah di-query di atas (validasi no. 3)
            if ($delegasi) {
                if ($delegasi->user) {
                    $tglMulaiIndo   = \Carbon\Carbon::parse($validated['tanggal_mulai'])->translatedFormat('d F Y');
                    $tglSelesaiIndo = \Carbon\Carbon::parse($validated['tanggal_selesai'])->translatedFormat('d F Y');

                    \App\Models\Notification::create([
                        'user_id' => $delegasi->user->id, 
                        'title'   => 'Permintaan Delegasi Tugas',
                        'message' => "Halo {$delegasi->nama}, Anda ditunjuk sebagai pengganti untuk cuti {$pegawai->nama} dari tanggal {$tglMulaiIndo} s/d {$tglSelesaiIndo}.",
                        'is_read' => false,
                    ]);
                    \Log::info("Notification Success: Delegasi {$delegasi->nama} notified.");
                } else {
                    \Log::error("Notification Error: Delegasi {$delegasi->nama} (ID: {$delegasi->id}) does not have a linked User account.");
                }
            } else {
                \Log::error("Notification Error: Variable \$delegasi is null.");
            }

        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
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

        // 2. VALIDASI (Sama seperti sebelumnya)
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
            'id_delegasi'     => 'required|exists:pegawai,id', 
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


        // --- Tambahan Validasi Kuota Bidang pada saat Update/Revisi ---
        $bulanUpdate = \Carbon\Carbon::parse($request->tanggal_mulai)->month;
        $tahunUpdate = \Carbon\Carbon::parse($request->tanggal_mulai)->year;
        $unitKerja = trim($pegawai->unit_kerja);

        $cekKuotaUpdate = Cuti::whereHas('pegawai', function($q) use ($unitKerja) {
                $q->where('unit_kerja', $unitKerja);
            })
            ->where('user_id', '!=', Auth::id()) // Abaikan diri sendiri
            ->where('id', '!=', $id)             // Abaikan record yang sedang diedit ini
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Disetujui Atasan', 'Revisi Delegasi'])
            ->where(function($q) use ($bulanUpdate, $tahunUpdate) {
                $q->whereMonth('tanggal_mulai', $bulanUpdate)
                ->whereYear('tanggal_mulai', $tahunUpdate);
            })
            ->distinct('user_id')
            ->count('user_id');

        if ($cekKuotaUpdate >= 2) {
            return back()->with('error', '<b>Gagal Update!</b><br>Kuota cuti di bidang Anda untuk bulan tersebut sudah penuh (Maks. 2 orang).');
        }

        // 5. UPDATE DATA & RESET STATUS (PENTING)
        $cuti->update([
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $jumlahHari,
            'keterangan'      => $request->keterangan,
            'id_delegasi'     => $request->id_delegasi,
            'status'          => 'Menunggu',
            'status_delegasi' => 'pending',         // Reset agar atasan bisa klik setuju/tolak lagi
            'catatan_tolak_delegasi' => null        // Bersihkan catatan lama
        ]);

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
                ->whereIn('status', ['Disetujui', 'Disetujui Atasan', 'Disetujui Kadis', 'Menunggu'])
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

    // 4. HITUNG PEMAKAIAN TAHUN INI (Termasuk yang sedang diproses)
    $terpakaiTahunIni = Cuti::where('user_id', $userId)
        ->where('tahun', $tahunIni)
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
}