@extends('layouts.kepala-dinas')

@section('title', 'Dashboard Kepala Dinas')

@section('content')
<div class="space-y-6">

    {{-- 🌟 Kartu Selamat Datang --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-6 sm:p-8 rounded-2xl shadow-xl flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Selamat Datang, {{ Auth::user()->name }}</h1>
            <p class="mt-1.5 text-base font-light text-sky-100">Pantau data pegawai dan kelola pengajuan cuti dengan mudah.</p>
        </div>
        <i class="fa-solid fa-clipboard-user text-5xl opacity-80 hidden sm:block"></i>
    </div>

    {{-- 📊 Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Card 1 --}}
        <div class="p-5 bg-gradient-to-br from-sky-50 to-sky-100 dark:from-sky-800 dark:to-sky-900 
                    rounded-xl shadow-lg border-l-4 border-sky-500 flex items-center justify-between
                    transition duration-300 ease-out transform hover:scale-105">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Pegawai</p>
                <p class="text-3xl font-extrabold text-sky-700 dark:text-sky-300 mt-1">{{ $totalPegawai ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-users text-sky-400 text-3xl opacity-60"></i>
        </div>

        {{-- Card 2 --}}
        <div class="p-5 bg-gradient-to-br from-yellow-50 to-white-100 dark:from-yellow-800 dark:to-yellow-900 
                    rounded-xl shadow-lg border-l-4 border-yellow-500 flex items-center justify-between
                    transition duration-300 ease-out transform hover:scale-105">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Cuti Menunggu</p>
                <p class="text-3xl font-extrabold text-yellow-700 dark:text-yellow-300 mt-1">{{ $cutiPending ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-hourglass-half text-yellow-400 text-3xl opacity-60"></i>
        </div>

        {{-- Card 3 --}}
        <div class="p-5 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-800 dark:to-green-900 
                    rounded-xl shadow-lg border-l-4 border-green-500 flex items-center justify-between
                    transition duration-300 ease-out transform hover:scale-105">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Cuti Disetujui</p>
                <p class="text-3xl font-extrabold text-green-700 dark:text-green-300 mt-1">{{ $cutiDisetujui ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-check-circle text-green-400 text-3xl opacity-60"></i>
        </div>

        {{-- Card 4 --}}
        <div class="p-5 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-800 dark:to-red-900 
                    rounded-xl shadow-lg border-l-4 border-red-500 flex items-center justify-between
                    transition duration-300 ease-out transform hover:scale-105">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Cuti Ditolak</p>
                <p class="text-3xl font-extrabold text-red-700 dark:text-red-300 mt-1">{{ $cutiDitolak ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-times-circle text-red-400 text-3xl opacity-60"></i>
        </div>

    </div>

    {{-- 🧾 Konten Utama --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 📋 Data Pegawai --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-5 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-bold text-sky-700 dark:text-sky-400 mb-3 border-b pb-2">Data Pegawai Terbaru</h2>

            <div class="overflow-auto max-h-96 border rounded-lg text-xs">
                <table class="min-w-full border-collapse text-xs">
                    <thead class="bg-sky-600 text-white sticky top-0 z-10 text-[11px]">
                        <tr>
                            <th class="px-1 py-1 border text-center w-8">No</th>
                            <th class="px-1 py-1 border w-32">Nama</th>
                            <th class="px-1 py-1 border w-24">NIP</th>
                            <th class="px-1 py-1 border w-24">Jabatan</th>
                            <th class="px-1 py-1 border w-28">Unit Kerja</th>
                            <th class="px-1 py-1 border w-16">Role</th>
                            <th class="px-1 py-1 border w-20">Telepon</th>
                            <th class="px-1 py-1 border w-16 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 text-[11px]">

                        @forelse ($pegawaiTerbaru ?? [] as $index => $p)

                            @php
                                $nip = $p->nip 
                                    ? substr($p->nip, 0, 4) . '•••' . substr($p->nip, -2) 
                                    : '-';

                                $telepon = $p->telepon 
                                    ? substr($p->telepon, 0, 3) . '•••' . substr($p->telepon, -2) 
                                    : '-';

                                // 🔧 PERBAIKAN: ambil dari kolom users.role
                                $role = $p->user->role ?? '-';
                            @endphp

                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <td class="px-1 py-1 border text-center">{{ $index + 1 }}</td>
                                <td class="px-1 py-1 border truncate">{{ $p->nama ?? '-' }}</td>
                                <td class="px-1 py-1 border text-center">{{ $nip }}</td>
                                <td class="px-1 py-1 border truncate">{{ $p->jabatan ?? '-' }}</td>
                                <td class="px-1 py-1 border truncate">{{ $p->unit_kerja ?? '-' }}</td>
                                <td class="px-1 py-1 border text-center">{{ $role }}</td>
                                <td class="px-1 py-1 border text-center">{{ $telepon }}</td>

                                <td class="px-1 py-1 border text-center">
                                    @if ($p->status === 'aktif')
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[9px] font-semibold">Aktif</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[9px] font-semibold">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data pegawai ditemukan.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📈 Ringkasan Cuti --}}
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-lg border border-gray-200 dark:bg-gray-900 dark:border-gray-700 h-fit">
            <h2 class="text-lg font-bold text-sky-700 dark:text-sky-400 mb-4 border-b pb-2">Statistik Pengajuan Cuti</h2>

            @php
                $stats = [
                    ['label' => 'Disetujui', 'value' => $cutiDisetujui ?? 0, 'color' => 'green'],
                    ['label' => 'Menunggu',   'value' => $cutiPending   ?? 0, 'color' => 'yellow'],
                    ['label' => 'Ditolak',    'value' => $cutiDitolak   ?? 0, 'color' => 'red'],
                ];

                $total = $totalCuti ?? 0;
            @endphp

            <div class="space-y-4 text-sm">
                @foreach ($stats as $stat)
                    <div>
                        <p class="flex justify-between text-gray-700 dark:text-gray-300 text-xs">
                            <span>{{ $stat['label'] }}</span>
                            <span class="font-bold">{{ $stat['value'] }} / {{ $total }}</span>
                        </p>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                            @php
                                $width = $total > 0 ? ($stat['value'] / $total) * 100 : 0;
                            @endphp
                            <div class="h-2 rounded-full bg-{{ $stat['color'] }}-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>
</div>
@endsection
