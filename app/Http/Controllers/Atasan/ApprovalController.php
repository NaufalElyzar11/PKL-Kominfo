<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $atasanName = Auth::user()->name;

        // Mencari cuti yang pegawainya memiliki atasan = nama user login
        $pengajuan = Cuti::with('pegawai')
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->where('status', 'Menunggu') // Hanya tampilkan yang butuh approval
            ->get();

        return view('atasan.approval.index', compact('pengajuan'));
    }

    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);
        // Status naik ke tahap berikutnya
        $cuti->update(['status' => 'Disetujui Atasan']);

        return back()->with('success', 'Pengajuan cuti berhasil disetujui.');
    }

    // app/Http/Controllers/Atasan/ApprovalController.php

// app/Http/Controllers/Atasan/ApprovalController.php

    public function dashboard()
    {
        $atasanName = Auth::user()->name; // Mengambil nama atasan yang sedang login

        // Menghitung statistik berdasarkan relasi ke tabel pegawai
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

        // Ambil daftar pengajuan cuti yang Menunggu untuk ditampilkan di tabel dashboard
        // Ambil daftar pengajuan cuti yang Menunggu untuk ditampilkan di tabel dashboard
        $pengajuan = Cuti::with('pegawai')
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->where('status', 'Menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil riwayat pengajuan (Disetujui Atasan, Disetujui, Ditolak)
        $riwayat = Cuti::with('pegawai')
            ->whereHas('pegawai', function($query) use ($atasanName) {
                $query->where('atasan', $atasanName);
            })
            ->whereIn('status', ['Disetujui Atasan', 'Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('atasan.dashboard', compact('stats', 'pengajuan', 'riwayat'));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_penolakan' => 'required|regex:/^[a-zA-Z\s]+$/|max:100',
        ], [
            'catatan_penolakan.regex' => 'Alasan penolakan hanya boleh berisi huruf dan spasi.',
        ]);

        $cuti = Cuti::findOrFail($id);

        $cuti->update([
            'status' => 'Ditolak',
            'catatan_penolakan' => $request->catatan_penolakan,
            'ditolak_oleh' => 'atasan'
        ]);

        return back()->with('success', 'Pengajuan cuti telah ditolak.');
    }

}