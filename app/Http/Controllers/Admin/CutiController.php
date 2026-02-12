<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_sampai);
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

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'id_pegawai'                 => 'required|exists:pegawai,id',
        'id_atasan_langsung'         => 'nullable|exists:atasan_langsung,id',
        'id_pejabat_pemberi_cuti'    => 'nullable|exists:pejabat_pemberi_cuti,id',
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

    public function exportPdf(Request $request)
    {
        // PERBAIKAN: Tambahkan 'delegasi' ke dalam eager loading agar data pengganti ikut diambil
        $query = Cuti::with(['pegawai', 'atasanLangsung', 'pejabatPemberiCuti', 'delegasi']);

        // 1. Search (Gunakan kolom di tabel cuti agar sinkron dengan index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // 2. Filter Status (Gunakan whereIn agar lebih fleksibel terhadap variasi status)
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'disetujui') {
                $query->whereIn('status', ['Disetujui', 'disetujui', 'Disetujui Kadis']);
            } else {
                $query->where('status', $status);
            }
        }

        // 3. Filter Tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_sampai);
        }

        // Urutkan berdasarkan yang terbaru agar laporan rapi
        $cuti = $query->orderBy('created_at', 'desc')->get();

        // Load view dengan data yang sudah menyertakan delegasi
        $pdf = Pdf::loadView('admin.cuti.export_pdf', compact('cuti'))
                    ->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Cuti_Pegawai_' . now()->format('d-m-Y') . '.pdf');
    }

    /**
     * 🔹 Halaman Approval Cuti (seperti dashboard pejabat)
     */
    public function approval()
    {
        $stats = [
            'menunggu'  => Cuti::where('status', 'Disetujui Atasan')->count(),
            'disetujui' => Cuti::where('status', 'Disetujui')->count(),
            'ditolak'   => Cuti::where('status', 'Ditolak')->count(),
        ];

        $pengajuan = Cuti::with('pegawai')
            ->where('status', 'Disetujui Atasan')
            ->orderBy('created_at', 'desc')
            ->get();

        $riwayat = Cuti::with('pegawai')
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.cuti.approval', compact('stats', 'pengajuan', 'riwayat'));
    }

    /**
     * 🔹 Setujui pengajuan cuti
     */
    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);
        
        $cuti->update(['status' => 'Disetujui']);

        return back()->with('success', 'Pengajuan cuti telah disetujui.');
    }

    /**
     * 🔹 Tolak pengajuan cuti
     */
    public function reject(Request $request, $id)
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
            'catatan_penolakan' => $request->catatan_penolakan
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil ditolak.');
    }

}
