<?php

namespace App\Http\Controllers\Pejabat;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Notification;

class PejabatApprovalController extends Controller
{
    public function dashboard()
    {
        // Kueri dasar untuk mengambil pengajuan yang harus diproses Pejabat (Kepala Dinas)
        $queryPejabat = Cuti::where(function($query) {
            // 1. Ambil semua pengajuan (Pegawai/Kasi) yang SUDAH disetujui Kabid
            $query->where('status', 'Disetujui Atasan')
            
            // 2. ATAU ambil pengajuan yang memang tidak punya Atasan Langsung (Langsung ke Kadis)
            ->orWhere(function($q) {
                $q->where('status', 'Menunggu')
                ->whereNull('id_atasan_langsung'); // Pemohon yang boss-nya langsung Kepala Dinas
            });
        });

        // Statistik tetap menggunakan clone dari query yang sudah diperbaiki
        $stats = [
            'menunggu'  => (clone $queryPejabat)->count(),
            'disetujui' => Cuti::where('status', 'Disetujui')->count(),
            'ditolak'   => Cuti::where('status', 'Ditolak')->whereNotNull('catatan_tolak_pejabat')->count(),
        ];

        $pengajuan = (clone $queryPejabat)->with('pegawai', 'delegasi')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pengajuan_page');

        // Bagian Riwayat tetap sama
        $riwayat = Cuti::with('pegawai', 'delegasi')
            ->where(function($query) {
                $query->where('status', 'Disetujui')
                    ->orWhere(function($q) {
                        $q->where('status', 'Ditolak')
                            ->whereNotNull('catatan_tolak_pejabat');
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'riwayat_page');

        return view('pejabat.dashboard', compact('stats', 'pengajuan', 'riwayat'));
    }

    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);
        
        // PERBAIKAN: Cukup update status menjadi 'Disetujui'
        // Jatah cuti akan otomatis berkurang karena fungsi Accessor di Model Pegawai
        $cuti->update(['status' => 'Disetujui']);

        // Notify User
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Cuti Disetujui',
            'message' => 'Selamat! Pengajuan cuti Anda telah disetujui sepenuhnya oleh pejabat berwenang.',
            'is_read' => false,
        ]);

        return back()->with('success', 'Pengajuan cuti telah disetujui secara final.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'catatan_tolak_pejabat' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z\s]+$/'
            ]
        ], [
            'catatan_tolak_pejabat.required' => 'Alasan penolakan wajib diisi.',
            'catatan_tolak_pejabat.max' => 'Alasan penolakan maksimal 100 karakter.',
            'catatan_tolak_pejabat.regex' => 'Alasan penolakan hanya boleh berisi huruf dan spasi saja.'
        ]);

        $cuti = Cuti::findOrFail($id);

        $cuti->update([
            'status' => 'Ditolak',
            'catatan_tolak_pejabat' => $request->catatan_tolak_pejabat
        ]);

        // Notify User
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Cuti Ditolak Pejabat',
            'message' => 'Pengajuan cuti Anda ditolak oleh pejabat berwenang. Alasan: ' . $request->catatan_tolak_pejabat,
            'is_read' => false,
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil ditolak.');
    }

    /**
     * Reset persetujuan cuti - mengembalikan status ke 'Disetujui Atasan'
     * Membutuhkan alasan reset sebelum melakukan aksi
     * Hanya dapat dilakukan dalam 8 jam setelah approve/reject
     */
    public function reset(Request $request, $id)
    {
        $request->validate([
            'alasan_reset' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z\\s]+$/'
            ]
        ], [
            'alasan_reset.required' => 'Alasan reset wajib diisi sebelum membatalkan persetujuan.',
            'alasan_reset.max' => 'Alasan reset maksimal 100 karakter.',
            'alasan_reset.regex' => 'Alasan reset hanya boleh berisi huruf dan spasi saja.'
        ]);

        $cuti = Cuti::findOrFail($id);

        // Validasi: Cek apakah sudah lewat 8 jam sejak update terakhir
        $hoursSinceUpdate = $cuti->updated_at->diffInHours(now());
        
        if ($hoursSinceUpdate >= 8) {
            return back()->withErrors(['error' => 'Reset tidak dapat dilakukan. Sudah lewat 8 jam sejak persetujuan/penolakan terakhir.']);
        }

        // Validasi: Hanya bisa reset jika status Disetujui atau Ditolak
        if (!in_array($cuti->status, ['Disetujui', 'Ditolak'])) {
            return back()->withErrors(['error' => 'Hanya pengajuan yang sudah disetujui atau ditolak yang dapat di-reset.']);
        }

        // Validasi tambahan jika status sebelumnya adalah Ditolak
        // Ada 2 lapisan validasi yang harus lolos sebelum reset diizinkan:
        if ($cuti->status === 'Ditolak') {
            $userId = $cuti->user_id;

            // ─── LAPISAN 1: CEK TUMPANG TINDIH TANGGAL ───────────────────────────
            // Pastikan tidak ada cuti lain milik pegawai yang AKTIF
            // pada rentang tanggal yang sama dengan pengajuan yang akan di-reset ini.
            // Ini mencegah exploit: ditolak → ajukan cuti baru di tanggal sama → reset ditolak
            // → pegawai punya 2 cuti aktif di tanggal yang sama sekaligus.
            $hasDateOverlap = \App\Models\Cuti::where('user_id', $userId)
                ->where('id', '!=', $cuti->id) // Abaikan record ini sendiri
                ->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Atasan', 'Menunggu', 'Revisi Delegasi'])
                ->where(function ($q) use ($cuti) {
                    // Rumus irisan tanggal: (StartA <= EndB) AND (EndA >= StartB)
                    $q->where('tanggal_mulai', '<=', $cuti->tanggal_selesai)
                      ->where('tanggal_selesai', '>=', $cuti->tanggal_mulai);
                })
                ->exists();

            if ($hasDateOverlap) {
                return back()->withErrors(['error' => 'Reset tidak dapat dilakukan! Pegawai sudah memiliki pengajuan cuti aktif lain yang tanggalnya bertabrakan dengan pengajuan ini (' . $cuti->tanggal_mulai->format('d/m/Y') . ' s/d ' . $cuti->tanggal_selesai->format('d/m/Y') . '). Cuti yang aktif harus diselesaikan atau dibatalkan terlebih dahulu.']);
            }

            // ─── LAPISAN 2: CEK SISA KUOTA CUTI ─────────────────────────────────
            // Jika tidak ada tumpang tindih tanggal, pastikan sisa jatah cuti pegawai
            // masih mencukupi untuk durasi pengajuan yang akan di-reaktivasi ini.
            $user = \App\Models\User::with('pegawai')->find($userId);
            $pegawai = $user ? $user->pegawai : null;

            if ($pegawai) {
                $tahunIni = (int) date('Y');
                $tahunLalu = $tahunIni - 1;
                $jatahDasar = 12;

                $pakaiTahunLalu = \App\Models\Cuti::where('user_id', $userId)
                    ->where('tahun', $tahunLalu)
                    ->whereIn('status', ['Disetujui', 'disetujui'])
                    ->sum('jumlah_hari');

                if ($pakaiTahunLalu == 0) {
                    $pakaiTahunLalu = (int) ($pegawai->sisa_cuti ?? 0);
                }

                $jatahAkumulasi = ($pakaiTahunLalu > 0 && $pakaiTahunLalu <= 6) ? ($jatahDasar - $pakaiTahunLalu) : 0;
                $totalHak = $jatahDasar + $jatahAkumulasi;

                $terpakai = \App\Models\Cuti::where('user_id', $userId)
                    ->where('tahun', $tahunIni)
                    ->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Atasan', 'Menunggu', 'Revisi Delegasi'])
                    ->sum('jumlah_hari');

                $sisaCuti = max(0, $totalHak - $terpakai);

                if ($sisaCuti < $cuti->jumlah_hari) {
                    return back()->withErrors(['error' => 'Reset tidak dapat dilakukan! Sisa jatah cuti pegawai tidak mencukupi (Sisa: ' . $sisaCuti . ' hari, Pengajuan ini: ' . $cuti->jumlah_hari . ' hari). Pegawai mungkin sudah mengambil cuti lain setelah pengajuan ini ditolak.']);
                }
            }
        }

        // Simpan alasan reset dan kembalikan status ke 'Disetujui Atasan'
        // Bersihkan catatan pejabat karena reset
        $cuti->update([
            'status' => 'Disetujui Atasan',
            'catatan_tolak_pejabat' => $request->alasan_reset,
            'status_pejabat' => 'pending'
        ]);

        // Notify User
        Notification::create([
            'user_id' => $cuti->user_id,
            'title'   => 'Status Cuti Direset',
            'message' => 'Status pengajuan cuti Anda dikembalikan ke "Disetujui Atasan". Alasan: ' . $request->alasan_reset,
            'is_read' => false,
        ]);

        return back()->with('success', 'Persetujuan berhasil dibatalkan. Status dikembalikan ke "Disetujui Atasan".');
    }

}