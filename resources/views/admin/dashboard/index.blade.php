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

        {{-- Card --}}
        <div class="p-4 bg-gradient-to-br from-sky-50 to-sky-100 dark:from-sky-800 dark:to-sky-900 
            rounded-xl shadow-md border-l-4 border-sky-500 flex items-center justify-between 
            transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">Total Pegawai</p>
                <p class="text-2xl font-extrabold text-sky-700 dark:text-sky-300 mt-1">{{ $totalPegawai ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-users text-sky-400 text-2xl opacity-70"></i>
        </div>

        <div class="p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-800 dark:to-yellow-900 
            rounded-xl shadow-md border-l-4 border-yellow-500 flex items-center justify-between
            transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">Cuti Menunggu</p>
                <p class="text-2xl font-extrabold text-yellow-700 dark:text-yellow-300 mt-1">{{ $cutiPending ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-hourglass-half text-yellow-400 text-2xl opacity-70"></i>
        </div>

        <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-800 dark:to-green-900 
            rounded-xl shadow-md border-l-4 border-green-500 flex items-center justify-between
            transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold ttext-gray-600 dark:text-gray-300">Cuti Disetujui</p>
                <p class="text-2xl font-extrabold text-green-700 dark:text-green-300 mt-1">{{ $cutiDisetujui ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-check-circle text-green-400 text-2xl opacity-70"></i>
        </div>

        <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-800 dark:to-red-900 
            rounded-xl shadow-md border-l-4 border-red-500 flex items-center justify-between
            transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            <div>
                <p class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">Cuti Ditolak</p>
                <p class="text-2xl font-extrabold text-red-700 dark:text-red-300 mt-1">{{ $cutiDitolak ?? 0 }}</p>
            </div>
            <i class="fa-solid fa-times-circle text-red-400 text-2xl opacity-70"></i>
        </div>

    </div>

    {{-- 🧾 Tabel Utama --}}
    <div class="space-y-6">

        {{-- 📋 Data Pegawai --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h2 class="text-base font-bold text-sky-700 dark:text-sky-400 mb-3 border-b pb-2">
                Data Pegawai Terbaru
            </h2>

            <div class="overflow-auto max-h-80 border rounded-lg text-[11px]">
                <table class="min-w-full border-collapse">
                    <thead class="bg-sky-600 text-white sticky top-0 z-10 text-[10px]">
                        <tr>
                            <th class="px-2 py-1 border w-10 text-center">No</th>
                            <th class="px-2 py-1 border w-32">Nama</th>
                            <th class="px-2 py-1 border w-24">NIP</th>
                            <th class="px-2 py-1 border w-36">Email</th>
                            <th class="px-2 py-1 border w-16 text-center">Role</th>
                            <th class="px-2 py-1 border w-24">Jabatan</th>
                            <th class="px-2 py-1 border w-24">Unit</th>
                            <th class="px-2 py-1 border w-20 text-center">Telepon</th>
                            <th class="px-2 py-1 border w-20 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($pegawaiTerbaru as $index => $p)
                            @php
                                $nip = $p->nip ? substr($p->nip, 0, 4).'•••'.substr($p->nip, -2) : '-';
                                $email = $p->user->email ?? '-';
                                if ($email !== '-') {
                                    [$name, $domain] = explode('@', $email);
                                    $masked = substr($name, 0, 3) . str_repeat('*', max(0, strlen($name) - 3));
                                    $email = $masked . '@' . $domain;
                                }
                            @endphp

                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                <td class="px-2 py-1 border text-center">{{ $index + 1 }}</td>
                                <td class="px-2 py-1 border">{{ $p->nama }}</td>
                                <td class="px-2 py-1 border text-center">{{ $nip }}</td>
                                <td class="px-2 py-1 border">{{ $email }}</td>
                                <td class="px-2 py-1 border text-center">{{ $p->user->role ?? '-' }}</td>
                                <td class="px-2 py-1 border text-center">{{ $p->jabatan }}</td>
                                <td class="px-2 py-1 border text-center">{{ $p->unit_kerja }}</td>
                                <td class="px-2 py-1 border text-center">
                                    @php
                                        $telp = $p->telepon;
                                        if ($telp) {
                                            $maskedTelp = substr($telp, 0, 4) . str_repeat('*', max(strlen($telp) - 4, 0));
                                        } else {
                                            $maskedTelp = '-';
                                        }
                                    @endphp

                                    {{ $maskedTelp }}
                                </td>
                                <td class="px-2 py-1 border text-center">
                                    @if ($p->status === 'aktif')
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[9px]">Aktif</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[9px]">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-3 text-center text-gray-500">
                                    Tidak ada data pegawai ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📋 Data Pengajuan Cuti --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h2 class="text-base font-bold text-sky-700 dark:text-sky-400 mb-3 border-b pb-2">
                Data Pengajuan Cuti Terbaru
            </h2>

            <div class="overflow-auto max-h-80 border rounded-lg text-[11px]">
                <table class="min-w-full border-collapse">
                    <thead class="bg-sky-600 text-white text-[10px] sticky top-0">
                        <tr>
                            <th class="px-2 py-1 border w-10 text-center">No</th>
                            <th class="px-2 py-1 border w-32">Nama / NIP</th>
                            <th class="px-2 py-1 border w-24">Jabatan</th>
                            <th class="px-2 py-1 border w-20">Jenis</th>
                            <th class="px-2 py-1 border w-36">Alasan</th>
                            <th class="px-2 py-1 border w-28">Tanggal</th>
                            <th class="px-2 py-1 border w-14 text-center">Hari</th>
                            <th class="px-2 py-1 border w-20">Atasan</th>
                            <th class="px-2 py-1 border w-20">Pemberi</th>
                            <th class="px-2 py-1 border w-20 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($cutiTerbaru as $i => $c)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                <td class="px-2 py-1 border text-center">{{ $i + 1 }}</td>

                               <td class="px-2 py-1 border">
                                    {{ $c->pegawai->nama }} <br>

                                    @php
                                        $nip = $c->pegawai->nip;
                                        if ($nip) {
                                            // 5 digit awal + ***** + 4 digit akhir
                                            $nipPrivasi = substr($nip, 0, 5)
                                                        . str_repeat('*', max(strlen($nip) - 9, 0))
                                                        . substr($nip, -4);
                                        } else {
                                            $nipPrivasi = '-';
                                        }
                                    @endphp

                                    <span class="text-gray-500 text-[9px]">{{ $nipPrivasi }}</span>
                                </td>

                                <td class="px-2 py-1 border text-center">{{ $c->pegawai->jabatan }}</td>
                                <td class="px-2 py-1 border text-center">{{ $c->jenis_cuti }}</td>

                                <td class="px-2 py-1 border">
                                    {{ $c->alasan_cuti ? Str::limit($c->alasan_cuti, 35) : '-' }}
                                </td>

                               <td class="px-2 py-1 border text-center text-[11px] leading-tight">
                                    {{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d-m-Y') }}
                                    <span class="block">s/d {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d-m-Y') }}</span>
                                </td>

                                <td class="px-2 py-1 border text-center">{{ $c->jumlah_hari }}</td>

                                <td class="px-2 py-1 border">{{ $c->atasanLangsung->nama_atasan }}</td>

                                <td class="px-2 py-1 border">{{ $c->pejabatPemberiCuti->nama_pejabat }}</td>

                                <td class="px-2 py-1 border text-center">
                                    @if ($c->status === 'menunggu')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-[9px]">
                                            Menunggu
                                        </span>
                                    @elseif ($c->status === 'disetujui')
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[9px]">
                                            Disetujui
                                        </span>
                                    @elseif ($c->status === 'ditolak')
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[9px]">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full text-[9px]">
                                            -
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-3 text-center text-gray-500">
                                    Tidak ada data cuti ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>
@endsection
