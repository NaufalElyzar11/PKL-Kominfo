<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
    \Log::info('Store request received', $request->all());
    
    $validated = $request->validate([
        'nama'       => 'required|string|max:255',
        'nip'        => 'required|string|max:50|unique:pegawai,nip', // Ubah ke required jika di DB wajib
        'jabatan'    => 'required|string|max:100',                  // Ubah ke required
        'unit_kerja' => 'required|string|max:100',                  // WAJIB REQUIRED agar tidak null di DB
        'role'       => 'required|in:pegawai,admin,pemberi_cuti,atasan',
        'status'     => 'required|string',
        'email'      => 'required|email|unique:users,email',
        'telepon'    => 'required|string|max:20',                  // Ubah ke required
        'password'   => 'required|string|min:8',
    ]);

    // Konversi role untuk database
    $roleForDatabase = $validated['role'] === 'atasan' ? 'kepala_dinas' : $validated['role'];

    DB::beginTransaction();

    try {
        // 1. Simpan ke tabel Pegawai
        $pegawai = Pegawai::create([
            'nama'       => $validated['nama'],
            'email'      => $validated['email'],
            'nip'        => $validated['nip'],
            'jabatan'    => $validated['jabatan'],
            'unit_kerja' => $validated['unit_kerja'], // Data ini sekarang dijamin ada
            'status'     => $validated['status'],
            'telepon'    => $validated['telepon'],
        ]);

        // 2. Simpan ke tabel User
        User::create([
            'name'       => $validated['nama'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $roleForDatabase,
            'id_pegawai' => $pegawai->id, 
        ]);

        DB::commit();
        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Gagal Simpan Pegawai: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
    }
}


    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);

        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:255|unique:pegawai,nip,' . $pegawai->id,
            'email'      => 'required|email|max:255|unique:users,email,' . $pegawai->user->id,
            'role'       => 'required|in:pegawai,admin,pemberi_cuti,atasan',
            'jabatan'    => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'telepon'    => 'nullable|string|max:20',
            'password'   => 'nullable|min:8',
        ]);

        // 🔧 PENTING: Convert 'kadis' ke 'kepala_dinas' sesuai enum di database
        $roleForDatabase = $validated['role'] === 'atasan' ? 'pemberi_cuti' : $validated['role'];

        DB::beginTransaction(); // Pakai transaction juga di sini biar aman

        try {
            // Update User
            $userData = [
                'name'  => $validated['nama'],
                'email' => $validated['email'],
                'role'  => $roleForDatabase,  //
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $pegawai->user->update($userData);

            // Update Pegawai
            $pegawai->update([
                'nama'       => $validated['nama'],
                'email'      => $validated['email'], // <--- TAMBAHAN: Update email di tabel pegawai juga
                'nip'        => $validated['nip'] ?? null,
                'jabatan'    => $validated['jabatan'] ?? null,
                'unit_kerja' => $validated['unit_kerja'] ?? null,
                'telepon'    => $validated['telepon'] ?? null,
            ]);

            DB::commit();
            
            return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // ... method destroy biarkan tetap sama ...
    public function destroy($id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);
        $pegawai->user->delete(); // Karena cascade delete biasanya di database, tapi manual lebih aman
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }

}
