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
        $atasanName = Auth::user()->name;

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

        return view('atasan.dashboard', compact('stats', 'pengajuan', 'riwayat'));
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
}