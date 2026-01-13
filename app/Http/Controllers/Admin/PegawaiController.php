<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
                $q->whereIn('role', ['pegawai', 'admin', 'super_admin', 'kadis']);
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

        $pegawai = $query->orderBy('nama')->paginate(10)->withQueryString();

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
            'nama'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:50|unique:pegawai,nip',
            'jabatan'    => 'nullable|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'role'       => 'required|string',
            'status'     => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'telepon'    => 'nullable|string|max:20',
            'password'   => 'required|string|min:8',
        ]);

        // Buat user
        $user = User::create([
            'name'     => $validated['nama'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        // Buat data pegawai
        Pegawai::create([
            'user_id'    => $user->id,
            'nama'       => $validated['nama'],
            'nip'        => $validated['nip'] ?? null,
            'jabatan'    => $validated['jabatan'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'status'     => $validated['status'],
            'telepon'    => $validated['telepon'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);

        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:255|unique:pegawai,nip,' . $pegawai->id,
            'email'      => 'required|email|max:255|unique:users,email,' . $pegawai->user->id,
            'role'       => 'required|in:pegawai,admin,super_admin,kadis',
            'jabatan'    => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'telepon'    => 'nullable|string|max:20',
            'password'   => 'nullable|min:8',
        ]);

        // Update user
        $pegawai->user->update([
            'name'  => $validated['nama'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $pegawai->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Update data pegawai
        $pegawai->update([
            'nama'       => $validated['nama'],
            'nip'        => $validated['nip'] ?? null,
            'jabatan'    => $validated['jabatan'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'telepon'    => $validated['telepon'] ?? null,
        ]);

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Hapus pegawai
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);

        // Hapus user dan pegawai
        $pegawai->user->delete();
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }
}
