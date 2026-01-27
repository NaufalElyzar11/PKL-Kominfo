<?php

namespace App\Http\Controllers\Pejabat;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;

class PejabatApprovalController extends Controller
{
    public function index()
    {
        // Pejabat memproses data yang sudah lolos tahap 1 (Atasan)
        $pengajuan = Cuti::with('pegawai')
            ->where('status', 'Disetujui Atasan') 
            ->get();

        return view('pejabat.approval.index', compact('pengajuan'));
    }

    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);
        // Status menjadi 'Disetujui' (Ini adalah tahap FINAL)
        $cuti->update(['status' => 'Disetujui']);

        return back()->with('success', 'Pengajuan cuti telah disetujui secara final.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['catatan' => 'required|string']);

        $cuti = Cuti::findOrFail($id);
        $cuti->update([
            'status' => 'Ditolak',
            'catatan_penolakan' => $request->catatan // Gunakan kolom khusus alasan penolakan kadis
        ]);

        return back()->with('success', 'Pengajuan cuti telah ditolak oleh Pejabat.');
    }

    // app/Http/Controllers/Pejabat/PejabatApprovalController.php

    public function dashboard()
    {
        // Pejabat memantau data secara global atau sesuai lingkup instansi
        $stats = [
            // 'Menunggu' bagi Pejabat adalah yang sudah 'Disetujui Atasan'
            'menunggu'  => \App\Models\Cuti::where('status', 'Disetujui Atasan')->count(),
            
            // 'Disetujui' adalah yang sudah di-approve final oleh Pejabat
            'disetujui' => \App\Models\Cuti::where('status', 'Disetujui')->count(),
            
            // Total yang ditolak di semua tahap (atau bisa difilter yang ditolak pejabat saja)
            'ditolak'   => \App\Models\Cuti::where('status', 'Ditolak')->count(),
        ];

        // Ambil daftar pengajuan cuti yang Menunggu (Disetujui Atasan) untuk ditampilkan di tabel dashboard
        $pengajuan = Cuti::with('pegawai')
            ->where('status', 'Disetujui Atasan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil riwayat pengajuan (Disetujui Final, Ditolak)
        $riwayat = Cuti::with('pegawai')
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('pejabat.dashboard', compact('stats', 'pengajuan', 'riwayat'));
    }
}