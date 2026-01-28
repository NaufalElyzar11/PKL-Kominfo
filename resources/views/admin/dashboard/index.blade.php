@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">

    {{-- 🌟 Kartu Selamat Datang --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-6 rounded-2xl shadow-xl flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight">Selamat Datang, {{ Auth::user()->name }}</h1>
            <p class="mt-1 text-sm text-sky-100">Kelola data pegawai dan pengajuan cuti dengan mudah.</p>
        </div>
        <i class="fa-solid fa-user-shield text-4xl opacity-80 hidden sm:block"></i>
    </div>

    {{-- 📊 Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Pegawai --}}
        <div class="p-4 bg-gradient-to-br from-sky-50 to-sky-100 rounded-xl shadow-md border-l-4 border-sky-500 flex items-center justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600">Total Pegawai</p>
                <p class="text-2xl font-extrabold text-sky-700 mt-1">{{ $totalPegawai ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-users text-sky-400 text-2xl opacity-70"></i>
        </div>

        {{-- Cuti Menunggu --}}
        <div class="p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-md border-l-4 border-yellow-500 flex items-center justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600">Cuti Menunggu</p>
                <p class="text-2xl font-extrabold text-yellow-700 mt-1">{{ $cutiPending ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-hourglass-half text-yellow-400 text-2xl opacity-70"></i>
        </div>

        {{-- Cuti Disetujui --}}
        <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md border-l-4 border-green-500 flex items-center justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600">Cuti Disetujui</p>
                <p class="text-2xl font-extrabold text-green-700 mt-1">{{ $cutiDisetujui ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-check-circle text-green-400 text-2xl opacity-70"></i>
        </div>

        {{-- Cuti Ditolak --}}
        <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-md border-l-4 border-red-500 flex items-center justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600">Cuti Ditolak</p>
                <p class="text-2xl font-extrabold text-red-700 mt-1">{{ $cutiDitolak ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-times-circle text-red-400 text-2xl opacity-70"></i>
        </div>
    </div>

    {{-- 📊 Statistik Unit Kerja (New Feature) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Statistik Cuti Per Unit Kerja --}}
        <div class="md:col-span-1 bg-white p-4 rounded-xl shadow-lg border border-gray-200 h-full">
            <h2 class="text-base font-bold text-sky-700 mb-3 border-b pb-2 flex justify-between items-center">
                <span>Statistik per Unit</span>
                <i class="fa-solid fa-chart-pie text-gray-400"></i>
            </h2>
            
            <div class="space-y-3 overflow-y-auto max-h-80 pr-1">
                @forelse($cutiPerUnitKerja as $stat)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg border border-gray-100 transition">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <div class="w-8 h-8 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 text-xs font-bold flex-shrink-0">
                                {{ substr($stat->unit_kerja, 0, 1) }}
                            </div>
                            <span class="text-xs font-semibold text-gray-700 truncate" title="{{ $stat->unit_kerja }}">
                                {{ Str::limit($stat->unit_kerja, 20) }}
                            </span>
                        </div>
                        <span class="px-2 py-1 bg-sky-600 text-white text-xs font-bold rounded-md shadow-sm">
                            {{ $stat->total }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-xs italic">
                        Belum ada data cuti.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Tabel Utama (Takes up 2 cols) --}}
        <div class="md:col-span-2 space-y-6">

            {{-- 📋 Data Pegawai Terbaru --}}
            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-200">
                <div class="flex justify-between items-center mb-3 border-b pb-2">
                    <h2 class="text-base font-bold text-sky-700">Data Pegawai Terbaru</h2>
                    <a href="{{ route('admin.pegawai.index') }}" class="text-[10px] sm:text-xs font-medium text-sky-600 hover:text-sky-800 flex items-center gap-1 transition">
                        Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-auto max-h-80 border rounded-lg text-xs">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10 text-xs shadow-sm">
                            <tr>
                                <th class="px-3 py-2 border w-10 text-center">No</th>
                                <th class="px-3 py-2 border">Nama</th>
                                <th class="px-3 py-2 border">NIP</th>
                                <th class="px-3 py-2 border">Email</th>
                                <th class="px-3 py-2 border text-center">Role</th>
                                <th class="px-3 py-2 border text-center">Jabatan</th>
                                <th class="px-3 py-2 border text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($pegawaiTerbaru as $index => $p)
                            @php
                                $nip = $p->nip ?? '-';
                                $email = $p->user?->email ?? '-';
                            @endphp
                                <tr class="hover:bg-blue-50 transition duration-150">
                                    <td class="px-3 py-2 border text-center">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 border font-medium text-gray-800">{{ $p->nama }}</td>
                                    <td class="px-3 py-2 border text-center font-mono text-gray-600">{{ $nip }}</td>
                                    <td class="px-3 py-2 border text-gray-600">{{ $email }}</td>
                                    <td class="px-3 py-2 border text-center capitalize">{{ $p->user?->role ?? '-' }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $p->jabatan ?? '-' }}</td>
                                    <td class="px-3 py-2 border text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500 italic">Belum ada data pegawai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 📋 Data Pengajuan Cuti Terbaru --}}
            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-200">
                <div class="flex justify-between items-center mb-3 border-b pb-2">
                    <h2 class="text-base font-bold text-sky-700">Data Pengajuan Cuti Terbaru</h2>
                    <a href="{{ route('admin.cuti.index') }}" class="text-[10px] sm:text-xs font-medium text-sky-600 hover:text-sky-800 flex items-center gap-1 transition">
                        Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-auto max-h-80 border rounded-lg text-xs">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-2 border w-10 text-center">No</th>
                                <th class="px-3 py-2 border">Nama / NIP</th>
                                <th class="px-3 py-2 border text-center">Jabatan</th>
                                <th class="px-3 py-2 border text-center">Jenis</th>
                                <th class="px-3 py-2 border">Alasan</th>
                                <th class="px-3 py-2 border text-center">Tanggal</th>
                                <th class="px-3 py-2 border text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($cutiTerbaru as $i => $c)
                            @php
                                $pegawaiNama = $c->pegawai?->nama ?? 'Pegawai Terhapus';
                                $pegawaiNip = $c->pegawai?->nip;
                                $nipPrivasi = $pegawaiNip ?? '-';
                            @endphp
                                <tr class="hover:bg-blue-50 transition duration-150">
                                    <td class="px-3 py-2 border text-center">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 border leading-tight">
                                        <span class="font-bold text-gray-800">{{ $pegawaiNama }}</span> <br>
                                        <span class="text-gray-500 text-[10px] font-mono">{{ $nipPrivasi }}</span>
                                    </td>
                                    <td class="px-3 py-2 border text-center text-gray-600">{{ $c->pegawai?->jabatan ?? '-' }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $c->jenis_cuti }}</td>
                                    <td class="px-3 py-2 border max-w-[150px] truncate" title="{{ $c->alasan_cuti }}">
                                        {{ $c->alasan_cuti ? Str::limit($c->alasan_cuti, 30) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 border text-center text-[11px] leading-tight font-mono">
                                        {{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d/m/y') }} <br>
                                        <span class="text-gray-400">s/d</span> {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d/m/y') }}
                                    </td>
                                    <td class="px-3 py-2 border text-center">
                                        @php
                                            $color = match($c->status) {
                                                'disetujui' => 'bg-green-100 text-green-700',
                                                'ditolak'   => 'bg-red-100 text-red-700',
                                                default     => 'bg-yellow-100 text-yellow-700'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $color }}">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500 italic">Tidak ada pengajuan terbaru.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 📜 SCRIPT AREA --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- PISAHKAN SCRIPT DI DALAM BLOK @IF AGAR VS CODE TIDAK MERAH --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000
    });
</script>
@endif

@endsection