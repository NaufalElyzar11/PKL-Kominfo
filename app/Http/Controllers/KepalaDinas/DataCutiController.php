<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\Pegawai;
use Carbon\Carbon;

class DataCutiController extends Controller
{
    public function index(Request $request)
    {
        $nipList = Pegawai::select('nip')
            ->whereNotNull('nip')
            ->distinct()
            ->pluck('nip');

        $query = Cuti::with('pegawai')->orderBy('created_at', 'desc');

        // FILTER NAMA
        if ($request->filled('nama')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->nama . '%');
            });
        }

        // FILTER NIP
        if ($request->filled('nip')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('nip', 'like', '%' . $request->nip . '%');
            });
        }

        // PAGINATION 10 DATA PER HALAMAN
        $cutiPending = (clone $query)
            ->where('status', 'menunggu')
            ->paginate(10, ['*'], 'pending_page');

        $cutiHistory = (clone $query)
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->paginate(10, ['*'], 'history_page');

        // Default untuk pencarian
        $cuti = $cutiPending;

        return view('kepaladinas.datacuti.index', compact('cutiPending', 'cutiHistory', 'nipList', 'cuti'));
    }

    public function menyetujui($id)
    {
        $cuti = Cuti::with(['pegawai'])->findOrFail($id);

        if ($cuti->status !== 'menunggu') {
            return redirect()
                ->route('kepaladinas.datacuti.index')
                ->with('warning', '❗ Pengajuan cuti ini sudah diproses sebelumnya.');
        }

        return view('kepaladinas.datacuti.menyetujui', compact('cuti'));
    }

    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);

        if ($cuti->status !== 'menunggu') {
            return redirect()
                ->route('kepaladinas.datacuti.index')
                ->with('warning', '❗ Pengajuan cuti ini sudah pernah diproses.');
        }

        $cuti->update([
            'status' => 'disetujui',
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('kepaladinas.datacuti.index')
            ->with('success', '✅ Pengajuan cuti telah disetujui.');
    }

    // ... method lainnya tetap sama ...

  public function reject(Request $request, $id) 
{
    // 1. Validasi input wajib ada agar database tidak menyimpan nilai kosong/null
    $request->validate([
        'catatan_penolakan' => 'required|string|min:5|max:255',
    ], [
        'catatan_penolakan.required' => 'Alasan penolakan wajib diisi.',
        'catatan_penolakan.min' => 'Alasan penolakan minimal 5 karakter.'
    ]);

    $cuti = Cuti::findOrFail($id);

    // 2. Proteksi status (Sudah benar)
    if ($cuti->status !== 'menunggu') {
        return redirect()
            ->route('kepaladinas.datacuti.index')
            ->with('error', '❗ Pengajuan cuti ini sudah pernah diproses.');
    }

    // 3. Eksekusi Update ke Database
    $cuti->update([
        'status' => 'ditolak',
        'catatan_penolakan' => $request->catatan_penolakan, 
        'updated_at' => now(), // Laravel otomatis mengupdate ini, tapi bagus untuk memastikan
    ]);

    return redirect()
        ->route('kepaladinas.datacuti.index')
        ->with('success', 'Pengajuan cuti telah berhasil ditolak.');
}
   public function destroy($id)
    {
        try {
            $cuti = Cuti::findOrFail($id);
            $cuti->delete();

            // Menggunakan 'success' agar variabel showNotif di AlpineJS menjadi true
            return redirect()
                ->route('kepaladinas.datacuti.index')
                ->with('success', 'Data riwayat cuti berhasil dihapus permanen.');
        } catch (\Exception $e) {
            return redirect()
                ->route('kepaladinas.datacuti.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}