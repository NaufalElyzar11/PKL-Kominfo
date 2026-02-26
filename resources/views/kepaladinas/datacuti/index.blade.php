@extends('layouts.kepala-dinas')

@section('title', 'Pengajuan Cuti')

@section('content')
<div class="min-h-screen px-4 py-6 bg-[#E3F2FD]"
     x-data="{ 
        tab: 'pending',
        showDetail: false,
        showRejectModal: false, 
        showDelete: false,
        deleteId: null,
        rejectId: null, 
        detail: {},
        showNotif: {{ session('success') ? 'true' : 'false' }},
        notifMessage: '{{ session('success') }}'
     }"
     x-cloak>

    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md p-5 border border-gray-200 space-y-4">
        
        {{-- TITLE --}}
        <h1 class="text-lg font-bold text-sky-700 mb-2">Daftar Pengajuan Cuti</h1>

        {{-- TAB --}}
        <div class="flex gap-1 text-xs pt-2">
            <button @click="tab = 'pending'"
                :class="tab === 'pending' ? 'bg-[#039BE5] text-white' : 'bg-gray-300 text-gray-700'"
                class="px-3 py-1 rounded-md transition">
                Menunggu Persetujuan
            </button>

            <button @click="tab = 'history'"
                :class="tab === 'history' ? 'bg-[#039BE5] text-white' : 'bg-gray-300 text-gray-700'"
                class="px-3 py-1 rounded-md transition">
                Riwayat
            </button>
        </div>

{{-- ===========================
      TABLE PENDING (FULL COMPACT)
============================ --}}
<div x-show="tab === 'pending'" 
     class="max-h-[420px] text-[9px] mt-2 border rounded-lg shadow-sm bg-white overflow-hidden">

    <table class="w-full border-collapse table-fixed">
        <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
           <tr>
                <th class="w-6 px-1 py-1.5 border">No</th>
                <th class="w-24 px-1 py-1.5 border text-center font-bold">Pegawai</th>
                <th class="w-16 px-1 py-1.5 border text-center font-medium">Jabatan</th>
                <th class="w-14 px-1 py-1.5 border">Jenis</th>
                <th class="w-24 px-1 py-1.5 border text-center font-medium">Periode</th>
                <th class="w-6 px-1 py-1.5 border text-center font-bold">Hr</th>
                <th class="w-18 px-1 py-1.5 border text-center font-medium">Alasan</th>
                <th class="w-18 px-1 py-1.5 border text-center font-medium">Alamat</th>
                <th class="w-20 px-1 py-1.5 border text-center text-[7.5px] leading-tight font-normal">Atasan/Kadis</th>
                <th class="w-18 px-1 py-1.5 border text-center font-bold">Status</th>
                <th class="w-12 px-1 py-1.5 border text-center font-bold">Aksi</th>
            </tr>
        </thead>

        <tbody class="text-gray-700">
            @forelse ($cutiPending as $i => $cuti)
                <tr class="border-b hover:bg-gray-50 transition-colors cursor-default">
                    <td class="px-1 py-1 border text-center text-gray-400">
                        {{ $cutiPending->firstItem() + $i }}
                    </td>
                    
                    <td class="px-1 py-1 border leading-tight overflow-hidden">
                        <div class="font-bold text-gray-800 truncate uppercase">{{ $cuti->pegawai->nama }}</div>
                        <div class="text-[8px] text-gray-400 font-mono tracking-tighter">{{ $cuti->pegawai->nip }}</div>
                    </td>

                    <td class="px-1 py-1 border text-center leading-tight truncate">
                        <div class="truncate w-full" title="{{ $cuti->pegawai->jabatan }}">{{ $cuti->pegawai->jabatan }}</div>
                    </td>

                    <td class="px-1 py-1 border text-center uppercase font-bold text-sky-700 text-[8px]">
                        {{ $cuti->jenis_cuti }}
                    </td>

                    <td class="px-1 py-1 border text-center whitespace-nowrap text-gray-500 font-medium tracking-tighter">
                        {{ $cuti->tanggal_mulai->format('d/m/y') }}-{{ $cuti->tanggal_selesai->format('d/m/y') }}
                    </td>

                    <td class="px-1 py-1 border text-center font-bold text-orange-600">
                        {{ $cuti->jumlah_hari }}
                    </td>

                    <td class="px-1 py-1 border leading-tight text-gray-500 italic">
                        <div class="truncate w-full" title="{{ $cuti->alasan_cuti }}">{{ $cuti->alasan_cuti ?? '-' }}</div>
                    </td>

                    <td class="px-1 py-1 border leading-tight text-gray-500">
                        <div class="truncate w-full" title="{{ $cuti->alamat }}">{{ $cuti->alamat ?? '-' }}</div>
                    </td>

                    <td class="px-1 py-1 border leading-[1.1] text-[7.5px]">
                        <div class="text-blue-600 truncate" title="Atasan: {{ $cuti->atasanLangsung->nama_atasan ?? '-' }}">
                            <span class="font-bold">A:</span> {{ $cuti->atasanLangsung->nama_atasan ?? '-' }}
                        </div>
                        <div class="text-emerald-600 truncate mt-0.5" title="Kadis: {{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}">
                            <span class="font-bold">P:</span> {{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}
                        </div>
                    </td>

                    {{-- ISI KOLOM STATUS --}}
                    <td class="px-1 py-1 border text-center font-bold uppercase text-[7px]">
                        <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-600 border border-amber-200">
                            {{ $cuti->status }}
                        </span>
                    </td>

                    <td class="px-1 py-1 border text-center">
                        <div class="flex justify-center gap-1.5">
                            <button 
                                @click="
                                    detail = {
                                        id: '{{ $cuti->id }}',
                                        nama: '{{ $cuti->pegawai->nama }}',
                                        nip: '{{ $cuti->pegawai->nip }}',
                                        jabatan: '{{ $cuti->pegawai->jabatan }}',
                                        jenis: '{{ $cuti->jenis_cuti }}',
                                        alasan: '{{ $cuti->alasan_cuti ?? '-' }}',
                                        alamat: '{{ $cuti->alamat ?? '-' }}',
                                        mulai: '{{ $cuti->tanggal_mulai->format('d/m/Y') }}',
                                        selesai: '{{ $cuti->tanggal_selesai->format('d/m/Y') }}',
                                        hari: '{{ $cuti->jumlah_hari }}',
                                        atasan: '{{ $cuti->atasanLangsung->nama_atasan ?? '-' }}',
                                        pejabat: '{{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}'
                                    };
                                    showDetail = true;
                                "
                                class="text-sky-600 hover:text-sky-800 transition">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                            <button 
                                @click="showDelete = true; deleteId = {{ $cuti->id }};"
                                class="text-rose-500 hover:text-rose-700 transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-6 text-gray-400 italic">Tidak ada data pending.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

  {{-- ======================================================
      GABUNGAN MODAL DETAIL (RAMPING) & PENOLAKAN
====================================================== --}}
<template x-teleport="body">
    <div>
        {{-- 1. MODAL DETAIL (VERSI RAMPING) --}}
        <div x-show="showDetail" 
             x-cloak
             class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998] p-4">

            <div @click.away="showDetail = false"
                 x-show="showDetail"
                 x-transition.scale
                 class="bg-white rounded-xl p-4 w-full max-w-[300px] shadow-2xl text-[10px]">

                {{-- Header Modal --}}
                <div class="border-b pb-2 mb-3">
                    <h3 class="text-xs font-bold text-sky-600 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sm"></i> Detail Pengajuan
                    </h3>
                </div>

                {{-- List Data (Data Lengkap Tetap Terjaga) --}}
                <div class="space-y-1.5 text-gray-700">
                    <div class="flex flex-col border-b border-gray-50 pb-1">
                        <span class="text-gray-400 text-[9px] uppercase font-bold">Nama Pegawai</span>
                        <span class="font-bold text-gray-800" x-text="detail.nama"></span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 border-b border-gray-50 pb-1">
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">NIP</span>
                            <span class="font-mono" x-text="detail.nip"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">Jabatan</span>
                            <span x-text="detail.jabatan"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-b border-gray-50 pb-1">
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">Jenis Cuti</span>
                            <span class="font-bold text-sky-700" x-text="detail.jenis"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">Durasi</span>
                            <span class="font-bold text-orange-600" x-text="detail.hari + ' Hari'"></span>
                        </div>
                    </div>

                    <div class="flex flex-col border-b border-gray-50 pb-1">
                        <span class="text-gray-400 text-[9px] uppercase font-bold">Periode</span>
                        <span x-text="detail.mulai + ' - ' + detail.selesai"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-b border-gray-50 pb-1">
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">Atasan</span>
                            <span x-text="detail.atasan"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[9px] uppercase font-bold block">Kadis</span>
                            <span x-text="detail.pejabat"></span>
                        </div>
                    </div>

                    <div class="mt-2 space-y-2">
                        <div>
                            <span class="text-gray-400 font-bold block mb-0.5">Alamat:</span>
                            <p class="text-gray-700 border rounded p-2 bg-gray-50/50 leading-tight" x-text="detail.alamat"></p>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block mb-0.5">Alasan:</span>
                            <p class="text-gray-600 italic border rounded p-2 bg-gray-50/50 leading-tight" x-text="detail.alasan"></p>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end gap-1.5 mt-4 pt-3 border-t">
                    <template x-if="tab === 'pending'">
                        <div class="flex gap-1.5">
                            <form :action="'{{ route('kepaladinas.datacuti.approve', 'ID') }}'.replace('ID', detail.id)" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold transition">
                                    Setujui
                                </button>
                            </form>
                            <button @click="showDetail = false; showRejectModal = true; rejectId = detail.id" 
                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded font-bold transition">
                                Tolak
                            </button>
                        </div>
                    </template>
                    <button @click="showDetail = false"
                            class="px-3 py-1.5 bg-white text-gray-500 rounded font-bold border border-gray-200 hover:bg-gray-50 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    {{-- 2. MODAL CATATAN PENOLAKAN --}}
<div x-show="showRejectModal" 
     x-cloak
     class="fixed inset-0 bg-black/60 flex items-center justify-center z-[9999] p-4 backdrop-blur-sm">

    <div @click.outside="showRejectModal = false"
         x-show="showRejectModal"
         x-transition.scale
         class="bg-white w-full max-w-[300px] rounded-xl shadow-2xl overflow-hidden border-t-4 border-red-600 text-[10px]">

        {{-- REVISI: Menggunakan placeholder __ID__ agar Laravel tidak error saat render halaman --}}
        <form :action="'{{ route('kepaladinas.datacuti.reject', ['id' => '__ID__']) }}'.replace('__ID__', detail.id)" method="POST">
            @csrf 
            @method('PUT')

            <div class="p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-gray-800 uppercase">Tolak Pengajuan</h2>
                        {{-- Menampilkan nama pegawai dari objek detail --}}
                        <p class="text-[9px] text-gray-400 italic" x-text="detail.nama"></p>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-red-600 uppercase tracking-tighter">Alasan Penolakan *</label>
                    {{-- Atribut name="catatan_penolakan" harus ada agar terbaca di Controller --}}
                    <textarea name="catatan_penolakan" rows="3" required
                        class="w-full p-2 border border-red-100 rounded bg-red-50/30 text-[10px] focus:ring-1 focus:ring-red-500 outline-none transition resize-none"
                        placeholder="Berikan alasan singkat..."></textarea>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 flex justify-end gap-2 border-t">
                <button type="button" @click="showRejectModal = false; showDetail = true"
                    class="px-3 py-1.5 text-gray-500 font-bold hover:text-gray-700 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-3 py-1.5 bg-red-600 text-white rounded font-bold hover:bg-red-700 shadow-sm transition">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>
    </div>
</template>

{{-- ===========================
      TABLE HISTORY (FULL COMPACT)
============================ --}}
<div x-show="tab === 'history'" 
     class="max-h-[420px] text-[9px] mt-2 border rounded-lg shadow-sm bg-white overflow-hidden">

    <table class="w-full border-collapse table-fixed">
        <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
            <tr>
                <th class="w-6 px-1 py-1.5 border">No</th>
                <th class="w-24 px-1 py-1.5 border text-center font-bold">Pegawai</th>
                <th class="w-18 px-1 py-1.5 border text-center font-medium">Jabatan</th>
                <th class="w-14 px-1 py-1.5 border">Jenis</th>
                <th class="w-24 px-1 py-1.5 border text-center font-medium">Periode</th>
                <th class="w-6 px-1 py-1.5 border text-center font-bold">Hr</th>
                <th class="w-18 px-1 py-1.5 border text-center italic font-medium">Alasan</th>
                {{-- KOLOM ALAMAT --}}
                <th class="w-18 px-1 py-1.5 border text-center font-medium">Alamat</th>
                {{-- KOLOM ATASAN/PEJABAT --}}
                <th class="w-20 px-1 py-1.5 border text-center text-[7.5px] leading-tight font-normal">Atasan/Kadis</th>
                <th class="w-14 px-1 py-1.5 border text-center font-bold">Status</th>
                <th class="w-10 px-1 py-1.5 border text-center font-bold">Aksi</th>
            </tr>
        </thead>

        <tbody class="text-gray-700">
            @forelse ($cutiHistory as $i => $cuti)
                @php
                    $nip = $cuti->pegawai->nip ?? '';
                    $nipMasked = substr($nip, 0, 4) . '***' . substr($nip, -4);
                @endphp
                <tr class="border-b hover:bg-gray-50 transition-colors cursor-default">
                    <td class="px-1 py-1 border text-center text-gray-400">
                        {{ $cutiHistory->firstItem() + $i }}
                    </td>
                    
                    <td class="px-1 py-1 border leading-tight overflow-hidden">
                        <div class="font-bold text-gray-800 truncate uppercase">{{ $cuti->pegawai->nama }}</div>
                        <div class="text-[8px] text-gray-400 font-mono tracking-tighter">{{ $nipMasked }}</div>
                    </td>

                    <td class="px-1 py-1 border text-center leading-tight">
                        <div class="truncate w-full" title="{{ $cuti->pegawai->jabatan }}">{{ $cuti->pegawai->jabatan }}</div>
                    </td>

                    <td class="px-1 py-1 border text-center uppercase font-bold text-sky-700 text-[8px]">
                        {{ $cuti->jenis_cuti }}
                    </td>

                    <td class="px-1 py-1 border text-center whitespace-nowrap text-gray-500 font-medium tracking-tighter">
                        {{ $cuti->tanggal_mulai->format('d/m/y') }}-{{ $cuti->tanggal_selesai->format('d/m/y') }}
                    </td>

                    <td class="px-1 py-1 border text-center font-bold text-orange-600">
                        {{ $cuti->jumlah_hari }}
                    </td>

                    <td class="px-1 py-1 border leading-tight text-gray-500 italic">
                        <div class="truncate w-full" title="{{ $cuti->alasan_cuti }}">{{ $cuti->alasan_cuti ?? '-' }}</div>
                    </td>

                    {{-- ISI KOLOM ALAMAT --}}
                    <td class="px-1 py-1 border leading-tight text-gray-500">
                        <div class="truncate w-full" title="{{ $cuti->alamat }}">{{ $cuti->alamat ?? '-' }}</div>
                    </td>

                    {{-- ISI KOLOM ATASAN/PEJABAT --}}
                    <td class="px-1 py-1 border leading-[1.1] text-[7.5px]">
                        <div class="text-blue-600 truncate" title="Atasan: {{ $cuti->atasanLangsung->nama_atasan ?? '-' }}">
                            <span class="font-bold">A:</span> {{ $cuti->atasanLangsung->nama_atasan ?? '-' }}
                        </div>
                        <div class="text-emerald-600 truncate mt-0.5" title="Kadis: {{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}">
                            <span class="font-bold">P:</span> {{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}
                        </div>
                    </td>

                    <td class="px-1 py-1 border text-center font-bold uppercase text-[7px]">
                        <span class="px-1.5 py-0.5 rounded {{ $cuti->status === 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ $cuti->status }}
                        </span>
                    </td>

                    <td class="px-1 py-1 border">
                        <div class="flex justify-center gap-1">
                            <button @click="detail = { 
                                        id: '{{ $cuti->id }}',
                                        nama: '{{ $cuti->pegawai->nama }}',
                                        nip: '{{ $cuti->pegawai->nip }}',
                                        jabatan: '{{ $cuti->pegawai->jabatan }}',
                                        jenis: '{{ $cuti->jenis_cuti }}',
                                        alasan: '{{ $cuti->alasan_cuti ?? '-' }}',
                                        alamat: '{{ $cuti->alamat ?? '-' }}',
                                        mulai: '{{ $cuti->tanggal_mulai->format('d/m/Y') }}',
                                        selesai: '{{ $cuti->tanggal_selesai->format('d/m/Y') }}',
                                        hari: '{{ $cuti->jumlah_hari }}',
                                        status: '{{ $cuti->status }}',
                                        atasan: '{{ $cuti->atasanLangsung->nama_atasan ?? '-' }}',
                                        pejabat: '{{ $cuti->pejabatPemberiCuti->nama_pejabat ?? '-' }}'
                                    }; showDetail = true;" 
                                    class="text-sky-600 hover:text-sky-800 transition">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                            <button @click="showDelete = true; deleteId = {{ $cuti->id }};" 
                                    class="text-rose-500 hover:text-rose-700 transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    {{-- Updated colspan to 11 because we have 11 columns now --}}
                    <td colspan="11" class="text-center py-6 text-gray-400 italic font-medium">Tidak ada riwayat cuti.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===========================
      MODAL NOTIFIKASI SUKSES (AUTO-CLOSE)
=========================== --}}
<template x-teleport="body">
    <div x-show="showNotif" 
         x-transition.opacity
         x-cloak
         {{-- Logika: Jika showNotif true, tunggu 3500ms lalu ubah jadi false --}}
         x-init="$watch('showNotif', value => { if(value) setTimeout(() => showNotif = false, 3500) })"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[10001] p-4">

        <div @click.away="showNotif = false"
             x-transition.scale
             class="bg-white w-full max-w-[260px] p-6 rounded-2xl text-center shadow-2xl border border-gray-100">
            
            <div class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4 ring-4 ring-green-100">
                <i class="fa-solid fa-check text-2xl"></i>
            </div>
            
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Berhasil</h3>
            <p class="text-[11px] font-semibold text-gray-700 leading-relaxed" x-text="notifMessage"></p>
            
            <button @click="showNotif = false"
                    class="mt-5 w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-bold transition-all uppercase tracking-wider">
                Selesai
            </button>
        </div>
    </div>
</template>
{{-- ===========================
      MODAL NOTIFIKASI ERROR (AUTO-CLOSE)
=========================== --}}
@if(session('error'))
<template x-teleport="body">
    <div x-data="{ showErr: true }" 
         x-init="setTimeout(() => showErr = false, 4000)" {{-- Otomatis tutup dalam 4 detik --}}
         x-show="showErr" 
         x-transition.opacity
         x-cloak
         class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[10001] p-4">

        <div @click.away="showErr = false"
             x-transition.scale
             class="bg-white w-full max-w-[260px] p-6 rounded-2xl text-center shadow-2xl border border-gray-100">
            
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 ring-4 ring-red-100">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </div>
            
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Gagal</h3>
            <p class="text-[11px] font-semibold text-gray-700 leading-relaxed">
                {{ session('error') }}
            </p>
            
            <button @click="showErr = false"
                    class="mt-5 w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-[10px] font-bold transition-all uppercase tracking-wider">
                Mengerti
            </button>
        </div>
    </div>
</template>
@endif
</div>

@endsection
