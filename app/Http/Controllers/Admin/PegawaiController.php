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
        \Log::info('Store request received', $request->all());
        
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'nip'        => 'nullable|string|max:50|unique:pegawai,nip',
            'jabatan'    => 'nullable|string|max:100',
            'unit_kerja' => 'nullable|string|max:100',
            'role'       => 'required|in:pegawai,admin,super_admin,kadis',
            'status'     => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'telepon'    => 'nullable|string|max:20',
            'password'   => 'required|string|min:8',
        ]);

        \Log::info('Validation passed', $validated);
        
        // 🔧 PENTING: Convert 'kadis' ke 'kepala_dinas' sesuai enum di database
        $roleForDatabase = $validated['role'] === 'kadis' ? 'kepala_dinas' : $validated['role'];

        // Mulai Transaksi Database
        DB::beginTransaction();

        try {
            \Log::info('Creating pegawai...');
            
            // 1. Buat Pegawai terlebih dahulu
            $pegawai = Pegawai::create([
                'nama'       => $validated['nama'],
                'email'      => $validated['email'],
                'nip'        => $validated['nip'] ?? null,
                'jabatan'    => $validated['jabatan'] ?? null,
                'unit_kerja' => $validated['unit_kerja'] ?? null,
                'status'     => $validated['status'],
                'telepon'    => $validated['telepon'] ?? null,
            ]);

            \Log::info('Pegawai created', ['id' => $pegawai->id, 'nama' => $pegawai->nama]);

            // 2. Buat User dengan referensi ke pegawai yang baru dibuat
            $user = User::create([
                'name'       => $validated['nama'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => $roleForDatabase,  // ✅ Gunakan role yang sudah di-convert
                'id_pegawai' => $pegawai->id, // Hubungkan ke pegawai
            ]);

            \Log::info('User created', ['id' => $user->id, 'role' => $user->role]);

            // Jika semua sukses, simpan permanen
            DB::commit();

            \Log::info('Transaction committed successfully');

            return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan.');

        } catch (\Exception $e) {
            // Jika ada error di tengah jalan, batalkan SEMUANYA (Hapus user yang sempat dibuat)
            DB::rollBack();
            
            \Log::error('Error creating pegawai: ' . $e->getMessage(), [
                'exception' => $e,
                'validated' => $validated,
            ]);
            
            // Kembalikan error ke halaman
            return redirect()->back()->with('error', 'Gagal menambah data: ' . $e->getMessage());
        }
    }

    /**
     * Update data pegawai (PERLU UPDATE EMAIL JUGA DI TABEL PEGAWAI)
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

        // 🔧 PENTING: Convert 'kadis' ke 'kepala_dinas' sesuai enum di database
        $roleForDatabase = $validated['role'] === 'kadis' ? 'kepala_dinas' : $validated['role'];

        DB::beginTransaction(); // Pakai transaction juga di sini biar aman

        try {
            // Update User
            $userData = [
                'name'  => $validated['nama'],
                'email' => $validated['email'],
                'role'  => $roleForDatabase,  // ✅ Gunakan role yang sudah di-convert
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
