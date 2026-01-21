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
            'nama'          => 'required|string|max:255|unique:users,name', // Validasi unik untuk login name
            'nip'           => 'required|string|max:50|unique:pegawai,nip',
            'jabatan'       => 'required|string|max:100',
            'unit_kerja'    => 'required|string|max:100',
            'role'          => 'required|in:pegawai,admin,pemberi_cuti,atasan',
            'status'        => 'required|string',
            'email'         => 'required|email|unique:users,email',
            'atasan'        => 'nullable|string|max:255',
            'pemberi_cuti'  => 'nullable|string|max:255',
            'telepon'       => 'required|string|max:20',
            // Perbaikan 1: Validasi Password Kombinasi
            'password'      => [
                'required', 
                'string', 
                Password::min(8)
                    ->mixedCase()   // Wajib huruf besar & kecil
                    ->numbers()     // Wajib angka
                    ->symbols()     // Wajib simbol
            ],
        ]);

        $roleForDatabase = $validated['role'] === 'atasan' ? 'kepala_dinas' : $validated['role'];

        DB::beginTransaction();
        try {
            // 1. Simpan ke tabel Pegawai
            $pegawai = Pegawai::create([
                'nama'         => $validated['nama'],
                'email'        => $validated['email'],
                'nip'          => $validated['nip'],
                'jabatan'      => $validated['jabatan'],
                'unit_kerja'   => $validated['unit_kerja'],
                'atasan'       => $validated['atasan'],
                'pemberi_cuti' => $validated['pemberi_cuti'],
                'status'       => $validated['status'],
                'telepon'      => $validated['telepon'],
            ]);

            // 2. Simpan ke tabel User (Nama Lengkap otomatis jadi Nama Akun)
            User::create([
                'name'       => $validated['nama'], // Menggunakan Nama Lengkap sebagai identitas login
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => $roleForDatabase,
                'id_pegawai' => $pegawai->id, 
            ]);

            DB::commit();
            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:255|unique:users,name,' . $pegawai->user->id,
            'nip'          => 'nullable|string|max:255|unique:pegawai,nip,' . $pegawai->id,
            'email'        => 'required|email|max:255|unique:users,email,' . $pegawai->user->id,
            'role'         => 'required|in:pegawai,admin,pemberi_cuti,atasan',
            'status'       => 'required|string',
            'jabatan'      => 'nullable|string|max:255',
            'unit_kerja'   => 'nullable|string|max:255',
            'atasan'       => 'nullable|string|max:255',
            'pemberi_cuti' => 'nullable|string|max:255',
            'telepon'      => 'nullable|string|max:20',
            // Perbaikan 2: Validasi Password saat update (nullable)
            'password'     => [
                'nullable', 
                'string', 
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $roleForDatabase = $validated['role'] === 'atasan' ? 'kepala_dinas' : $validated['role'];

        DB::beginTransaction();
        try {
            // Update User
            $userData = [
                'name'  => $validated['nama'], // Update nama akun jika nama lengkap berubah
                'email' => $validated['email'],
                'role'  => $roleForDatabase,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $pegawai->user->update($userData);

            // Update Pegawai
            $pegawai->update([
                'nama'         => $validated['nama'],
                'email'        => $validated['email'],
                'nip'          => $validated['nip'] ?? null,
                'jabatan'      => $validated['jabatan'] ?? null,
                'unit_kerja'   => $validated['unit_kerja'] ?? null,
                'atasan'       => $validated['atasan'] ?? null,
                'pemberi_cuti' => $validated['pemberi_cuti'] ?? null,
                'status'       => $validated['status'],
                'telepon'      => $validated['telepon'] ?? null,
            ]);

            DB::commit();
            return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');

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