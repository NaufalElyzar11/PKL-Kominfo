<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password; // Wajib diimpor untuk validasi password

class PegawaiController extends Controller
{
    /**
     * Tampilkan daftar pegawai
     */
    public function index(Request $request)
    {
        $search          = $request->search;
        $searchUnitKerja = $request->unit_kerja;

        $query = Pegawai::with('user'); 

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($searchUnitKerja) {
            $query->where('unit_kerja', 'like', "%{$searchUnitKerja}%");
        }

        // --- PERBAIKAN LOGIKA PENGURUTAN DI SINI ---
        $pegawai = $query
        // Pegawai tanpa NIP di urutan paling bawah
        ->orderByRaw("CASE WHEN nip IS NULL OR nip = '' THEN 1 ELSE 0 END ASC")
        // Kemudian urutkan berdasarkan hierarki jabatan
        ->orderByRaw("
            CASE 
                WHEN jabatan = 'Kepala Dinas' THEN 1
                WHEN jabatan = 'Sekretaris Dinas' THEN 2
                WHEN jabatan LIKE 'Kepala Bidang%' THEN 3
                WHEN jabatan LIKE 'Kepala Sub Bagian%' THEN 4
                WHEN jabatan LIKE 'Kepala Seksi%' THEN 5
                ELSE 6 
            END ASC
        ")
        ->orderBy('nama', 'asc') // Menangani urutan A-Z untuk pegawai (skor 6)
        ->paginate(10)
        ->withQueryString();
        // -------------------------------------------

        // Ambil daftar pendukung lainnya tetap seperti sebelumnya...
        $listAtasan = Pegawai::whereHas('user', function($q) {
            $q->where('role', 'atasan');
        })->get(['id', 'nama', 'unit_kerja', 'jabatan']);

        $listPejabat = Pegawai::whereHas('user', function($q) {
            $q->where('role', 'pejabat');
        })->get(['id', 'nama']);

        return view('admin.pegawai.index', [
            'pegawai'         => $pegawai,
            'search'          => $search,
            'searchUnitKerja' => $searchUnitKerja,
            'totalPegawai'    => Pegawai::count(),
            'totalCuti'       => Cuti::count(),
            'cutiDisetujui'   => Cuti::where('status', 'disetujui')->count(),
            'cutiDitolak'     => Cuti::where('status', 'ditolak')->count(),
            'unitKerjaList'   => Pegawai::whereNotNull('unit_kerja')->distinct()->pluck('unit_kerja'),
            'listAtasan'      => $listAtasan,
            'listPejabat'     => $listPejabat,
        ]);
    }

    /**
     * Simpan pegawai baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'nip'          => 'nullable|numeric|digits_between:13,18|unique:pegawai,nip',
            'jabatan'      => 'required|string|max:100',
            'unit_kerja'   => 'required|string|max:100',
            'role'         => 'required|in:pegawai,admin,pejabat,atasan',
            'status'       => 'required|string',
            // Tambahkan validasi ID agar data integritas terjaga
            'id_atasan_langsung'      => 'nullable|exists:pegawai,id', 
            'id_pejabat_pemberi_cuti' => 'nullable|exists:pegawai,id',
            'atasan'       => ['required', 'string', 'max:255'],
            'password'     => ['required', 'string', Password::min(8)],
        ]);

        // Validasi jabatan unik untuk Kepala Bidang, Kepala Seksi, Kepala Sub Bagian, Sekretaris, dan Kepala Dinas
        $jabatan = $validated['jabatan'];
        $unitKerja = $validated['unit_kerja'];
        
        // Daftar jabatan yang harus unik (hanya 1 per unit kerja atau 1 secara keseluruhan)
        $jabatanUnik = [
            'Kepala Dinas',
            'Sekretaris Dinas',
        ];
        
        // Cek jabatan yang bersifat global unik (hanya 1 di seluruh organisasi)
        if (in_array($jabatan, $jabatanUnik)) {
            $exists = Pegawai::where('jabatan', $jabatan)->exists();
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jabatan \"{$jabatan}\" sudah terisi. Hanya boleh ada 1 orang untuk jabatan ini.");
            }
        }
        
        // Cek jabatan Kepala Bidang, Kepala Seksi, dan Kepala Sub Bagian (unik per jabatan)
        if (str_starts_with($jabatan, 'Kepala Bidang') || 
            str_starts_with($jabatan, 'Kepala Seksi') || 
            str_starts_with($jabatan, 'Kepala Sub Bagian')) {
            
            $exists = Pegawai::where('jabatan', $jabatan)->exists();
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jabatan \"{$jabatan}\" sudah terisi. Hanya boleh ada 1 orang untuk jabatan ini.");
            }
        }

        $roleForDatabase = $validated['role'];

        DB::beginTransaction();
            try {
                // 1. Simpan Pegawai
                $pegawai = Pegawai::create([
                    'nama'                    => $validated['nama'],
                    'nip'                     => $validated['nip'],
                    'jabatan'                 => $validated['jabatan'],
                    'unit_kerja'              => $validated['unit_kerja'],
                    'status'                  => $validated['status'],
                    'atasan'                  => $request->atasan, // Nama (string)
                    'pemberi_cuti'            => $request->pejabat, // Nama (string)
                    'id_atasan_langsung'      => $request->id_atasan_langsung, // ID (Integer) - PENTING
                    'id_pejabat_pemberi_cuti' => $request->id_pejabat_pemberi_cuti, // ID (Integer) - PENTING
                    'kuota_cuti'              => 12,
                ]);

            // 2. Simpan User (Gunakan NIP sebagai email buatan agar tidak bentrok)
            User::create([
                'name'       => $validated['nama'],
                'password'   => Hash::make($validated['password']),
                'email'      => null,
                'role'       => $roleForDatabase,
                'nip'        => $validated['nip'],
                'id_pegawai' => $pegawai->id,
            ]);

        DB::commit();
                return redirect()->back()->with('success', 'Pegawai berhasil ditambah.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
            }
    }

    /**
     * Update data pegawai
     */
public function update(Request $request, $id)
{
    $pegawai = Pegawai::with('user')->findOrFail($id);

    // 1. Validasi Data
    $validated = $request->validate([
        'nama'       => 'required|string|max:255',
        'nip'        => 'nullable|string|min:13|max:18|unique:pegawai,nip,' . $pegawai->id,
        'role'       => 'required|in:pegawai,admin,pejabat,atasan',
        'status'     => 'required|string',
        'jabatan'    => 'nullable|string|max:255',
        'unit_kerja' => 'nullable|string|max:255',
        'atasan'     => 'required|string|max:255', 

        // Mengizinkan ID 0 untuk Walikota agar tidak "Invalid"
        'id_atasan_langsung' => [
            'nullable',
            function ($attribute, $value, $fail) {
                if ($value != 0 && $value != '' && !\App\Models\Pegawai::where('id', $value)->exists()) {
                    $fail('Atasan yang dipilih tidak valid.');
                }
            },
        ],
        'id_pejabat_pemberi_cuti' => 'nullable|exists:pegawai,id',
    ]);

    // 2. Ambil nilai Jabatan dari Request (bukan dari $validated) untuk cek keunikan
    // Gunakan fallback ke data lama jika input kosong (akibat disabled di UI)
    $inputJabatan = $request->jabatan ?? $pegawai->jabatan;

    if ($inputJabatan && $inputJabatan !== $pegawai->jabatan) {
        $jabatanUnik = ['Kepala Dinas', 'Sekretaris Dinas'];
        if (in_array($inputJabatan, $jabatanUnik) || 
            str_starts_with($inputJabatan, 'Kepala Bidang') || 
            str_starts_with($inputJabatan, 'Kepala Seksi') || 
            str_starts_with($inputJabatan, 'Kepala Sub Bagian')) {
            
            $exists = Pegawai::where('jabatan', $inputJabatan)->where('id', '!=', $id)->exists();
            if ($exists) {
                return redirect()->back()->with('error', "Jabatan \"{$inputJabatan}\" sudah terisi.");
            }
        }
    }

    DB::beginTransaction();
    try {
        // Update Akun (Hanya yang ada di $validated)
        $pegawai->user->update([
            'name' => $validated['nama'],
            'role' => $validated['role'],
        ]);

        // 3. Update Data Pegawai
        // Gunakan operator ?? (Null Coalescing) untuk menghindari "Undefined Array Key"
        $pegawai->update([
            'nama'                    => $validated['nama'],
            'nip'                     => $validated['nip'],
            'status'                  => $validated['status'],
            'atasan'                  => $validated['atasan'], 
            // Ambil langsung dari request atau gunakan data lama jika input kosong/disabled
            'jabatan'                 => $request->jabatan ?? $pegawai->jabatan, 
            'unit_kerja'              => $request->unit_kerja ?? $pegawai->unit_kerja,
            'pemberi_cuti'            => $request->pejabat ?? $pegawai->pemberi_cuti, 
            'id_atasan_langsung'      => $request->id_atasan_langsung, 
            'id_pejabat_pemberi_cuti' => $request->id_pejabat_pemberi_cuti,
        ]);

        DB::commit();
        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
    }
}
    /**
     * Hapus data pegawai
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);
        if ($pegawai->user) {
            $pegawai->user->delete();
        }
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function checkUnique(Request $request) {
    // Abaikan pengecekan jika input kosong
    if (!$request->value) return response()->json(['exists' => false]);

    $query = \App\Models\Pegawai::where($request->field, $request->value);
    
    // Jika sedang Edit, abaikan data milik sendiri agar tidak terbaca duplikat
    if ($request->id) {
        $query->where('id', '!=', $request->id);
    }

    return response()->json(['exists' => $query->exists()]);
    }
    
}