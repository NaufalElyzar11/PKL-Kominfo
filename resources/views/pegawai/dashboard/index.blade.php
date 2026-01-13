@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="p-6 space-y-8">

    {{-- 🌟 Kartu Selamat Datang --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-6 sm:p-8 rounded-2xl shadow-xl flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">
                Selamat Datang, {{ Auth::user()->name }}
            </h1>
            <p class="mt-1.5 text-base font-light text-sky-100">
                Pantau data pegawai dan kelola pengajuan cuti dengan mudah.
            </p>
        </div>
        <div class="hidden sm:flex justify-center items-center bg-white/20 p-4 rounded-full">
            <i class="fa-solid fa-clipboard-user text-5xl opacity-90"></i>
        </div>
    </div>

    {{-- 📊 Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Pegawai --}}
        <div class="p-5 bg-gradient-to-br from-sky-50 to-sky-100 rounded-xl shadow-lg border-l-4 border-sky-500 flex items-center justify-between hover:scale-[1.02] transition-transform duration-200">
            <div>
                <p class="text-xs font-medium text-gray-600">Total Pegawai</p>
                <p class="text-3xl font-extrabold text-sky-700 mt-1">{{ $totalPegawai ?? 0 }}</p>
            </div>
            <div class="bg-sky-100 p-3 rounded-full">
                <i class="fa-solid fa-users text-sky-500 text-3xl"></i>
            </div>
        </div>

        {{-- Cuti Menunggu --}}
        <div class="p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg border-l-4 border-yellow-500 flex items-center justify-between hover:scale-[1.02] transition-transform duration-200">
            <div>
                <p class="text-xs font-medium text-gray-600">Cuti Menunggu</p>
                <p class="text-3xl font-extrabold text-yellow-700 mt-1">{{ $cutiPending ?? 0 }}</p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fa-solid fa-hourglass-half text-yellow-500 text-3xl"></i>
            </div>
        </div>

        {{-- Cuti Disetujui --}}
        <div class="p-5 bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg border-l-4 border-green-500 flex items-center justify-between hover:scale-[1.02] transition-transform duration-200">
            <div>
                <p class="text-xs font-medium text-gray-600">Cuti Disetujui</p>
                <p class="text-3xl font-extrabold text-green-700 mt-1">{{ $cutiDisetujui ?? 0 }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fa-solid fa-circle-check text-green-500 text-3xl"></i>
            </div>
        </div>

        {{-- Cuti Ditolak --}}
        <div class="p-5 bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg border-l-4 border-red-500 flex items-center justify-between hover:scale-[1.02] transition-transform duration-200">
            <div>
                <p class="text-xs font-medium text-gray-600">Cuti Ditolak</p>
                <p class="text-3xl font-extrabold text-red-700 mt-1">{{ $cutiDitolak ?? 0 }}</p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <i class="fa-solid fa-circle-xmark text-red-500 text-3xl"></i>
            </div>
        </div>
    </div>

    {{-- 📋 Tabel Riwayat & Statistik --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Riwayat Cuti --}}
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-lg border border-gray-200">
            <h2 class="text-lg font-bold text-sky-700 mb-3 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-clock text-sky-600"></i> Riwayat Cuti Pegawai
            </h2>

            <div class="overflow-x-auto overflow-y-auto max-h-[28rem] border rounded-lg text-[11px] sm:text-[12px] leading-tight">
                <table class="min-w-[1000px] border-collapse">
                    <thead class="bg-sky-600 text-white sticky top-0 z-10 text-[11px] uppercase">
                        <tr>
                            <th class="px-2 py-2 border text-center w-8">No</th>
                            <th class="px-2 py-2 border">Nama</th>
                            <th class="px-2 py-2 border">NIP</th>
                            <th class="px-2 py-2 border">Jabatan</th>
                            <th class="px-2 py-2 border">Jenis Cuti</th>
                            <th class="px-2 py-2 border">Mulai</th>
                            <th class="px-2 py-2 border">Selesai</th>
                            <th class="px-2 py-2 border">Hari</th>
                            <th class="px-2 py-2 border">Alasan</th>
                            <th class="px-2 py-2 border">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($latestCuti ?? [] as $index => $c)
                        <tr class="hover:bg-gray-100 transition text-gray-700">
                            <td class="px-2 py-1 border text-center">{{ $index + 1 }}</td>
                            <td class="px-2 py-1 border truncate">{{ $c->pegawai->nama ?? '-' }}</td>
                            <td class="px-2 py-1 border text-center">
                                {{ $c->pegawai->nip ? substr($c->pegawai->nip, 0, 4) . '•••' . substr($c->pegawai->nip, -2) : '-' }}
                            </td>
                            <td class="px-2 py-1 border truncate">{{ $c->pegawai->jabatan ?? '-' }}</td>
                            <td class="px-2 py-1 border truncate">{{ $c->jenis_cuti ?? '-' }}</td>
                            <td class="px-2 py-1 border text-center">{{ $c->tanggal_mulai?->format('d-m-Y') }}</td>
                            <td class="px-2 py-1 border text-center">{{ $c->tanggal_selesai?->format('d-m-Y') }}</td>
                            <td class="px-2 py-1 border text-center">{{ $c->jumlah_hari ?? '-' }}</td>
                            <td class="px-2 py-1 border truncate">{{ $c->keterangan ?? '-' }}</td>
                            <td class="px-2 py-1 border text-center">
                                @if ($c->status === 'disetujui')
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-semibold">Disetujui</span>
                                @elseif ($c->status === 'ditolak')
                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[10px] font-semibold">Ditolak</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-[10px] font-semibold">Menunggu</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="px-3 py-4 text-center text-gray-500">
                                Tidak ada riwayat cuti ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Statistik Persentase --}}
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 h-fit">
            <h2 class="text-lg font-bold text-sky-700 mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-sky-600"></i> Statistik Pengajuan Cuti
            </h2>

            <div class="space-y-4">
                @php
                    $stats = [
                        ['label' => 'Disetujui', 'value' => $cutiDisetujui ?? 0, 'color' => 'green'],
                        ['label' => 'Menunggu', 'value' => $cutiPending ?? 0, 'color' => 'yellow'],
                        ['label' => 'Ditolak', 'value' => $cutiDitolak ?? 0, 'color' => 'red'],
                    ];
                    $total = $totalCuti ?? 0;
                @endphp

                @foreach ($stats as $stat)
                    <div>
                        <p class="text-sm font-medium text-gray-700 flex justify-between">
                            <span>{{ $stat['label'] }}</span>
                            <span class="font-bold">{{ $stat['value'] }} dari {{ $total }}</span>
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            @php $width = $total > 0 ? ($stat['value'] / $total) * 100 : 0; @endphp
                            <div class="h-2 rounded-full bg-{{ $stat['color'] }}-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
