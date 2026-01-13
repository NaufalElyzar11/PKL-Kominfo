<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PejabatPemberiCuti;

class PemberiCutiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pejabat'    => 'required|string|max:255',
            'nip_pejabat'     => 'required|string|max:50|unique:pejabat_pemberi_cuti,nip_pejabat',
            'jabatan_pejabat' => 'nullable|string|max:100',
        ]);

        PejabatPemberiCuti::create($validated);

        return redirect()->back()->with('success', 'Pemberi cuti berhasil ditambahkan.');
    }
}
