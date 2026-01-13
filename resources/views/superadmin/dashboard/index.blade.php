@extends('layouts.super-admin')

@section('title', 'Dashboard Super Admin')

@section('breadcrumb')
    Dashboard
@endsection

@section('content')
<div class="space-y-6">

    {{-- 🌟 Welcome Card --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-6 sm:p-8 rounded-2xl shadow-xl flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Selamat Datang, Super Admin</h1>
            <p class="mt-1.5 text-base font-light text-sky-100">Kelola seluruh data sistem dengan cepat dan efisien.</p>
        </div>
        <i class="fa-solid fa-shield-halved text-5xl opacity-80 hidden sm:block"></i>
    </div>

    {{-- 📊 Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Total Admin --}}
        <div class="p-5 bg-gradient-to-br from-sky-50 to-sky-100 dark:from-sky-800 dark:to-sky-900 rounded-xl shadow-lg border-l-4 border-sky-500 flex justify-between items-center">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Admin</p>
                <p class="text-3xl font-extrabold text-sky-700 dark:text-sky-300 mt-1">
                    {{ $total_admin ?? 0 }}
                </p>
            </div>
            <i class="fa-solid fa-user-gear text-sky-500 text-4xl opacity-70"></i>
        </div>

        {{-- Total Pegawai --}}
        <div class="p-5 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-800 dark:to-green-900 rounded-xl shadow-lg border-l-4 border-green-500 flex justify-between items-center">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Pegawai</p>
                <p class="text-3xl font-extrabold text-green-700 dark:text-green-300 mt-1">
                    {{ $total_pegawai ?? 0 }}
                </p>
            </div>
            <i class="fa-solid fa-users text-green-600 text-4xl opacity-70"></i>
        </div>

        {{-- Total Pengajuan Cuti --}}
        <div class="p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-800 dark:to-yellow-900 rounded-xl shadow-lg border-l-4 border-yellow-500 flex justify-between items-center">
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Pengajuan Cuti</p>
                <p class="text-3xl font-extrabold text-yellow-700 dark:text-yellow-300 mt-1">
                    {{ $total_pengajuan ?? 0 }}
                </p>
            </div>
            <i class="fa-solid fa-clipboard-list text-yellow-500 text-4xl opacity-70"></i>
        </div>

    </div>

    {{-- 📌 Aktivitas Terbaru --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-sky-600"></i>
            Aktivitas Terbaru
        </h3>

        <ul class="space-y-3 text-sm text-gray-700">
            {{-- Pegawai Terbaru --}}
            @if(!empty($pegawaiTerbaru))
                @foreach($pegawaiTerbaru as $pegawai)
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle text-[10px] text-sky-600 mt-1"></i>
                        <span>Pegawai 
                            <strong>{{ $pegawai->user->name ?? $pegawai->user->email ?? 'Unknown' }}</strong> 
                            ditambahkan.
                        </span>
                    </li>
                @endforeach
            @else
                <li class="text-gray-400">Tidak ada pegawai terbaru.</li>
            @endif

            {{-- Cuti Terbaru --}}
            @if(!empty($cutiTerbaru))
                @foreach($cutiTerbaru as $cuti)
                    @php
                        $color = match($cuti->status ?? 'pending') {
                            'disetujui' => 'green',
                            'ditolak'   => 'red',
                            default     => 'yellow',
                        };
                    @endphp
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle text-[10px] text-{{ $color }}-600 mt-1"></i>
                        <span>Pengajuan cuti dari 
                            <strong>{{ $cuti->pegawai->user->name ?? $cuti->pegawai->user->email ?? 'Unknown' }}</strong> 
                            <span class="font-semibold">{{ $cuti->status ?? 'pending' }}</span>.
                        </span>
                    </li>
                @endforeach
            @else
                <li class="text-gray-400">Tidak ada pengajuan cuti terbaru.</li>
            @endif
        </ul>
    </div>

</div>
@endsection
