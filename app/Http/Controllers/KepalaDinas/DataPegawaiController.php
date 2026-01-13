<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class DataPegawaiController extends Controller
{
    public function index(Request $request)
    {
        // ============================
        // 📌 Query utama langsung dari tabel Pegawai
        // ============================
        $query = Pegawai::orderBy('nama', 'asc');

        // ============================
        // 🔍 Filter: Nama
        // ============================
        if ($request->filled('nama')) {
            $query->where('nama', 'LIKE', '%' . $request->nama . '%');
        }

        // ============================
        // 🔍 Filter: NIP
        // ============================
        if ($request->filled('nip')) {
            $query->where('nip', 'LIKE', '%' . $request->nip . '%');
        }

        // ============================
        // 🔍 Filter: Unit Kerja
        // ============================
        if ($request->filled('unit')) {
            $query->where('unit_kerja', $request->unit);
        }

        // ============================
        // 📄 Pagination
        // ============================
        $pegawai = $query->paginate(15)->withQueryString();

        // ============================
        // 📌 Dropdown Unit Kerja (Unique)
        // ============================
        $units = Pegawai::select('unit_kerja')
            ->whereNotNull('unit_kerja')
            ->distinct()
            ->pluck('unit_kerja');

        return view('kepaladinas.datapegawai.index', compact('pegawai', 'units'));
    }

    public function show($id)
    {
        // Detail 1 pegawai
        $pegawai = Pegawai::findOrFail($id);

        return view('kepaladinas.datapegawai.show', compact('pegawai'));
    }
}
