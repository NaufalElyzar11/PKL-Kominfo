<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HolidayController extends Controller
{
    /**
     * Mendapatkan data hari libur dari API eksternal
     */
    public function getHolidays($year)
    {
        // Contoh: Mengambil data dari API publik (misal: API Hari Libur Indonesia)
        // Anda bisa menyesuaikan URL API yang Anda gunakan di sini
        $response = Http::get("https://api-harilibur.vercel.app/api?year=" . $year);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Gagal mengambil data hari libur',
            'data' => []
        ], 500);
    }
}