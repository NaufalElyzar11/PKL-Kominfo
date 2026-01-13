<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AtasanLangsung;

class AtasanLangsungController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_atasan'   => 'required|string|max:255',
            'nip_atasan'    => 'required|string|max:50|unique:atasan_langsung,nip_atasan',
            'jabatan_atasan'=> 'nullable|string|max:100',
        ]);

        AtasanLangsung::create($validated);

        return redirect()->back()->with('success', 'Atasan langsung berhasil ditambahkan.');
    }
}
