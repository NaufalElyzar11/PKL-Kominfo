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
            // Hitung hanya penolakan dari pejabat
            'ditolak'   => Cuti::where('status', 'Ditolak')->where('ditolak_oleh', 'pejabat')->count(),
        ];

        $pengajuan = Cuti::with('pegawai')
            ->where('status', 'Disetujui Atasan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat: hanya tampilkan Disetujui atau Ditolak oleh pejabat
        // Penolakan dari atasan TIDAK ditampilkan di halaman pejabat
        $riwayat = Cuti::with('pegawai')
            ->where(function($query) {
                $query->where('status', 'Disetujui')
                      ->orWhere(function($q) {
                          $q->where('status', 'Ditolak')
                            ->where('ditolak_oleh', 'pejabat');
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
            'catatan_penolakan' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z\s]+$/'
            ]
        ], [
            'catatan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'catatan_penolakan.max' => 'Alasan penolakan maksimal 100 karakter.',
            'catatan_penolakan.regex' => 'Alasan penolakan hanya boleh berisi huruf dan spasi saja.'
        ]);

        $cuti = Cuti::findOrFail($id);

        $cuti->update([
            'status' => 'Ditolak',
            'catatan_penolakan' => $request->catatan_penolakan,
            'ditolak_oleh' => 'pejabat'
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
        $cuti->update([
            'status' => 'Disetujui Atasan',
            'catatan_penolakan' => $request->alasan_reset,
            'ditolak_oleh' => null // Reset karena bukan penolakan
        ]);

        return back()->with('success', 'Persetujuan berhasil dibatalkan. Status dikembalikan ke "Disetujui Atasan".');
    }

}