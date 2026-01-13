<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        // Ambil data dari cache (atau nanti bisa dari database)
        $settings = [
            'nama_aplikasi' => Cache::get('nama_aplikasi', 'Sistem Cuti Pegawai'),
            'versi' => '1.0.0',
            'tema' => Cache::get('tema', 'light'),
            'tahun' => date('Y'),
        ];

        return view('superadmin.pengaturan.index', compact('settings'));
    }

    /**
     * Menyimpan otomatis (autosave) setiap kali field diubah.
     */
    public function autosave(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Validasi agar hanya field tertentu yang bisa diubah
        $allowed = ['nama_aplikasi', 'tema'];
        if (!in_array($field, $allowed)) {
            return Response::json(['success' => false, 'message' => 'Field tidak valid.'], 400);
        }

        // Simpan sementara ke cache (bisa diubah ke DB nanti)
        Cache::put($field, $value);

        return Response::json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $field)) . ' diperbarui.',
        ]);
    }
}
