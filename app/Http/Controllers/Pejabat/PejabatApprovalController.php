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
        // Kueri dasar untuk mengambil pengajuan yang harus diproses Pejabat
        $queryPejabat = Cuti::where(function($query) {
            // 1. Ambil pengajuan Pegawai yang sudah lolos tahap Atasan
            $query->where('status', 'Disetujui Atasan')
            // 2. ATAU ambil pengajuan Atasan yang berstatus 'Menunggu'
                ->orWhere(function($q) {
                    $q->where('status', 'Menunggu')
                        ->whereHas('pegawai.user', function($u) {
                            $u->where('role', 'atasan');
                        });
                });
        });

        $stats = [
            'menunggu'  => (clone $queryPejabat)->count(),
            'disetujui' => Cuti::where('status', 'Disetujui')->count(),
            'ditolak'   => Cuti::where('status', 'Ditolak')->whereNotNull('catatan_tolak_pejabat')->count(),
        ];

        $pengajuan = (clone $queryPejabat)->with('pegawai', 'delegasi')
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat: tampilkan Disetujui ATAU Ditolak yang ada catatan dari pejabat
        // Penolakan dari atasan/delegasi saja TIDAK ditampilkan
        $riwayat = Cuti::with('pegawai', 'delegasi')
            ->where(function($query) {
                $query->where('status', 'Disetujui')
                      ->orWhere(function($q) {
                          $q->where('status', 'Ditolak')
                            ->whereNotNull('catatan_tolak_pejabat');
                      });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

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