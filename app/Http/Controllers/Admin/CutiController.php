<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CutiController extends Controller
{
    /**
     * 🔹 Tampilkan semua data cuti
     */
    public function index(Request $request)
    {
        $query = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // PAGINATION < 1 2 >
        $cuti = $query->paginate(10)->onEachSide(1)->withQueryString();

        return view('admin.cuti.index', compact('cuti'));
    }

    /**
     * 🔹 Form tambah cuti
     */
    public function create()
    {
        $pegawai   = Pegawai::all();
        $atasan    = AtasanLangsung::all();
        $pejabat   = PejabatPemberiCuti::all();

        return view('admin.cuti.create', compact('pegawai', 'atasan', 'pejabat'));
    }

    /**
     * 🔹 Simpan data cuti
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pegawai'                 => 'required|exists:pegawai,id',
            'id_atasan_langsung'         => 'nullable|exists:atasan_langsung,id',
            'id_pejabat_pemberi_cuti'    => 'nullable|exists:pejabat_pemberi_cuti,id',
            'alamat'                     => 'nullable|string|max:255',
            'jenis_cuti'                 => 'required|string|max:100',
            'alasan_cuti'                => 'required|string|max:255',
            'tanggal_mulai'              => 'required|date',
            'tanggal_selesai'            => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'                => 'required|integer|min:1',
            'status'                     => 'nullable|in:pending,disetujui,ditolak',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        Cuti::create($validated);

        return redirect()->route('admin.cuti.index')
            ->with('success', 'Data cuti berhasil ditambahkan.');
    }

    /**
     * 🔹 Form edit cuti
     */
    public function edit($id)
    {
        $cuti     = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])->findOrFail($id);
        $pegawai  = Pegawai::all();
        $atasan   = AtasanLangsung::all();
        $pejabat  = PejabatPemberiCuti::all();

        $pegawaiCuti = $cuti->pegawai;
        $jatahCutiTahunan = $pegawaiCuti->jatah_cuti ?? 12;
        $tahun = Carbon::now()->year;

        $cutiTerpakai = Cuti::where('id_pegawai', $pegawaiCuti->id)
            ->where('status', 'disetujui')
            ->whereYear('tanggal_mulai', $tahun)
            ->where('id', '!=', $cuti->id)
            ->sum('jumlah_hari');

        $sisaCuti = max(0, $jatahCutiTahunan - $cutiTerpakai);

        return view('admin.cuti.edit', compact(
            'cuti',
            'pegawai',
            'atasan',
            'pejabat',
            'jatahCutiTahunan',
            'cutiTerpakai',
            'sisaCuti'
        ));
    }

    /**
     * 🔹 Update cuti
     */
   /**
 * 🔹 Update cuti
 */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'id_pegawai'                 => 'required|exists:pegawai,id',
        'id_atasan_langsung'         => 'nullable|exists:atasan_langsung,id',
        'id_pejabat_pemberi_cuti'    => 'nullable|exists:pejabat_pemberi_cuti,id',
        'alamat'                     => 'nullable|string|max:255',
        'jenis_cuti'                 => 'required|string|max:100',
        'alasan_cuti'                => 'required|string|max:255',
        'tanggal_mulai'              => 'required|date',
        'tanggal_selesai'            => 'required|date|after_or_equal:tanggal_mulai',
        'jumlah_hari'                => 'required|integer|min:1',
        'status'                     => 'required|in:pending,disetujui,ditolak',
    ]);

    $cuti = Cuti::findOrFail($id);
    $cuti->update($validated);

    return redirect()->route('admin.cuti.index')
        ->with('success', 'Data cuti berhasil diperbarui.');
}

    /**
     * 🔹 Hapus cuti
     */
    public function destroy($id)
    {
        $cuti = Cuti::findOrFail($id);
        $cuti->delete();

        return redirect()->route('admin.cuti.index')
            ->with('success', 'Data cuti berhasil dihapus.');
    }

    /**
     * 🔹 Detail cuti
     */
    public function show($id)
    {
        $cuti = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti'])->findOrFail($id);
        return view('admin.cuti.show', compact('cuti'));
    }
}
