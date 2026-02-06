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
            'ditolak'   => Cuti::where('status', 'Ditolak')->count(),
        ];

        $pengajuan = Cuti::with('pegawai', 'delegasi')
            ->where('status', 'Disetujui Atasan')
            ->orderBy('created_at', 'desc')
            ->get();

        $riwayat = Cuti::with('pegawai', 'delegasi')
            ->whereIn('status', ['Disetujui', 'Ditolak'])
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

}