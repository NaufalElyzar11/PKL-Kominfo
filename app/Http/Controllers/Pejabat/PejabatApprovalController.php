<?php

namespace App\Http\Controllers\Pejabat;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PejabatApprovalController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'menunggu'  => Cuti::where('status', 'Disetujui Atasan')->count(),
            'disetujui' => Cuti::where('status', 'Disetujui')->count(),
            // Hitung penolakan yang ada catatan dari pejabat (berarti pejabat yang menolak)
            'ditolak'   => Cuti::where('status', 'Ditolak')->whereNotNull('catatan_tolak_pejabat')->count(),
        ];

        $pengajuan = Cuti::with('pegawai', 'delegasi')
            ->where('status', 'Disetujui Atasan')
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

        return back()->with('success', 'Pengajuan cuti berhasil ditolak.');
    }

    /**
     * Reset persetujuan cuti - mengembalikan status ke 'Disetujui Atasan'
     * Membutuhkan alasan reset sebelum melakukan aksi
     */
    public function reset(Request $request, $id)
    {
        $request->validate([
            'alasan_reset' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z\s]+$/'
            ]
        ], [
            'alasan_reset.required' => 'Alasan reset wajib diisi sebelum membatalkan persetujuan.',
            'alasan_reset.max' => 'Alasan reset maksimal 100 karakter.',
            'alasan_reset.regex' => 'Alasan reset hanya boleh berisi huruf dan spasi saja.'
        ]);

        $cuti = Cuti::findOrFail($id);

        // Simpan alasan reset dan kembalikan status ke 'Disetujui Atasan'
        // Bersihkan catatan pejabat karena reset
        $cuti->update([
            'status' => 'Disetujui Atasan',
            'catatan_tolak_pejabat' => $request->alasan_reset,
            'status_pejabat' => 'pending'
        ]);

        return back()->with('success', 'Persetujuan berhasil dibatalkan. Status dikembalikan ke "Disetujui Atasan".');
    }

}