<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;

class AdminController extends Controller
{
    /**
     * Menampilkan daftar Admin.
     */
    public function index()
    {
        // Ambil hanya 6 admin terbaru
        $admins = Admin::orderBy('created_at', 'desc')
                        ->take(6)
                        ->get();

        return view('superadmin.admin.index', compact('admins'));
    }
}
