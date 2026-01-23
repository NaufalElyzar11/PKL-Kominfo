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

        $query = Pegawai::with('user')
            ->whereHas('user', function ($q) {
                $q->whereIn('role', ['pegawai', 'admin', 'atasan', 'pemberi_cuti']);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($searchUnitKerja) {
            $query->where('unit_kerja', 'like', "%{$searchUnitKerja}%");
        }

        $pegawai = $query->orderBy('nama')->paginate(6)->withQueryString();

        return view('admin.pegawai.index', [
            'pegawai'         => $pegawai,
            'search'          => $search,
            'searchUnitKerja' => $searchUnitKerja,
            'totalPegawai'    => Pegawai::count(),
            'totalCuti'       => Cuti::count(),
            'cutiDisetujui'   => Cuti::where('status', 'disetujui')->count(),
            'cutiDitolak'     => Cuti::where('status', 'ditolak')->count(),
            'unitKerjaList'   => Pegawai::whereNotNull('unit_kerja')->distinct()->pluck('unit_kerja'),
        ]);
    }

    /**
     * Simpan pegawai baru
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'nama'         => 'required|string|max:255',
        'nip'          => ['nullable', 'string', 'min:13', 'max:18', 'unique:pegawai,nip'],
        'jabatan'      => 'required|string|max:100',
        'unit_kerja'   => 'required|string|max:100',
        'role'         => 'required|in:pegawai,admin,pemberi_cuti,atasan',
        'status'       => 'required|string',
        'atasan'       => 'nullable|string|max:255',       // Pastikan divalidasi
        'pemberi_cuti' => 'nullable|string|max:255',       // Pastikan divalidasi
        'password'     => ['required', 'string', Password::min(8)],
    ]);

    $roleForDatabase = $validated['role'] === 'atasan' ? 'kepala_dinas' : $validated['role'];

    DB::beginTransaction();
    try {
        // 1. Simpan Pegawai (Sertakan Atasan dan Pemberi Cuti)
        $pegawai = Pegawai::create([
            'nama'         => $validated['nama'],
            'nip'          => $validated['nip'],
            'jabatan'      => $validated['jabatan'],
            'unit_kerja'   => $validated['unit_kerja'],
            'status'       => $validated['status'],
            'atasan'       => $validated['atasan'],       // TAMBAHKAN INI
            'pemberi_cuti' => $validated['pemberi_cuti'], // TAMBAHKAN INI
            'email'        => null, 
            'telepon'      => null,
        ]);

        // 2. Simpan User
        User::create([
            'name'       => $validated['nama'],
            'password'   => Hash::make($validated['password']),
            'email'      => null,
            'role'       => $roleForDatabase,
            'nip'        => $request->nip,
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

    // Validasi HANYA data yang boleh diubah Admin
    $validated = $request->validate([
        'nama'       => 'required|string|max:255',
        'nip'        => 'nullable|string|min:13|max:18|unique:pegawai,nip,' . $pegawai->id,
        'role'       => 'required|in:pegawai,admin,pemberi_cuti,atasan',
        'status'     => 'required|string',
        'jabatan'    => 'nullable|string|max:255',
        'unit_kerja' => 'nullable|string|max:255',
        'atasan'     => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        // Update Akun (Hanya Nama dan Role)
        $pegawai->user->update([
            'name' => $validated['nama'],
            'role' => $validated['role'] === 'atasan' ? 'kepala_dinas' : $validated['role'],
        ]);

        // Update Data Pegawai
        $pegawai->update([
            'nama'       => $validated['nama'],
            'nip'        => $validated['nip'],
            'jabatan'    => $validated['jabatan'],
            'unit_kerja' => $validated['unit_kerja'],
            'status'     => $validated['status'],
            'atasan'     => $validated['atasan'],
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
}