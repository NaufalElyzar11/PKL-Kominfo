@extends('layouts.admin')

@section('title', 'Data Pegawai & Pengajuan Cuti')

@section('content')
{{-- Root x-data container (no styling) --}}
<div x-data="{
    // 1. Status Modal
    showCreateModal: false,
    showEditModal: false,
    showDetailModal: false,
    showDelete: false,

    // 2. Variabel Form Tambah (Hanya didefinisikan satu kali agar tidak bentrok)
    nama: '',
    nip: '',
    atasan: '',      // Terhubung ke select atasan
    jabatan: '',
    unit_kerja: '',  
    role: '',        // Terhubung ke select role
    status: '',
    password: '',
    pemberi_cuti: 'Kanafi, S.IP, MM',
    showPassword: false,

    get hasUpper() {
    return /[A-Z]/.test(this.password)
},

get hasNumber() {
    return /[0-9]/.test(this.password)
},

get hasSymbol() {
    return /[!@#$%^&*(),.?':{}|<>]/.test(this.password)
},

get isLongEnough() {
    return this.password.length >= 8
},

    // 3. Data dari Laravel (Hanya untuk referensi dropdown)
    dataAtasan: {{ Js::from($listAtasan) }},
    dataPejabat: {{ Js::from($listPejabat) }},

// Getter untuk filter daftar nama di dropdown
    get filteredAtasan() {
        if (this.role === 'pegawai') return this.dataAtasan;
        if (this.role === 'atasan') return this.dataPejabat;
        if (this.role === 'pemberi_cuti') return [{id: 99, nama: 'Lisa Halaby'}]; // Default Pejabat
        return [];
    },

    // Fungsi tambahan untuk auto-select nama
    handleRoleChange() {
        this.atasan = '';

        if (this.role === 'pemberi_cuti') {
            this.atasan = 'Lisa Halaby';
            this.pemberi_cuti = 'Lisa Halaby';
        } else {
            this.pemberi_cuti = 'Kanafi, S.IP, MM';
        }
    },


    // 5. Logika Validasi Tombol Simpan
    isFormValid() {
        return this.nama.trim() !== '' &&
               this.atasan !== '' && 
               this.jabatan.trim() !== '' &&
               this.unit_kerja !== '' &&
               this.role !== '' &&
               this.status !== '' &&
               this.password.length >= 8 &&
               (/[A-Z]/.test(this.password)) && 
               (/[0-9]/.test(this.password)) && 
               (/[!@#$%^&*]/.test(this.password)) && 
               (this.nip.length === 0 || this.nip.length >= 13);
    }, // <-- PENTING: Koma di sini tadi hilang, sekarang sudah ada.

    // 6. Data Pegawai terpilih (Edit/Detail)
    selectedPegawai: null,
    originalPegawai: null,
    editRoute: '{{ route('admin.pegawai.update', ':pegawaiId') }}',

    // =========================
    // Fungsi Buka Modal Tambah
    // =========================
    openCreateModal() {
        // RESET semua input agar benar-benar bersih setiap kali dibuka
        this.nama = '';
        this.nip = '';
        this.atasan = '';    
        this.jabatan = '';
        this.unit_kerja = ''; 
        this.role = '';
        this.status = '';
        this.password = '';
        
        this.showCreateModal = true;
    },

    // =========================
    // Fungsi Buka Modal Edit
    // =========================
    openEditModal(pegawai) {
        this.selectedPegawai = JSON.parse(JSON.stringify(pegawai));
        this.originalPegawai = JSON.parse(JSON.stringify(pegawai));
        this.showEditModal = true;
    },

    isUnchanged() {
        if (!this.selectedPegawai || !this.originalPegawai) return true;
        return JSON.stringify(this.selectedPegawai) === JSON.stringify(this.originalPegawai);
    },

    openDetailModal(pegawai) {
        this.selectedPegawai = pegawai;
        this.showDetailModal = true;
    },

    closeModal() {
        this.showCreateModal = false;
        this.showEditModal = false;
        this.showDetailModal = false;
        this.selectedPegawai = null;
    }
}" @keydown.escape.window="closeModal()">

{{-- Inner container with background styling --}}
<div class="min-h-screen px-4 py-6 bg-[#E3F2FD]">
    {{-- =============================================== --}}
    {{-- BAGIAN 1: DATA PEGAWAI (Daftar & Filter) --}}
    {{-- =============================================== --}}
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md p-5 border border-gray-200">
        
        {{-- HEADER: JUDUL & TOMBOL TAMBAH --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa-solid fa-users text-sky-600 mr-2"></i>Daftar Data Pegawai
            </h1>
            
            <button @click="openCreateModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
            </button>
        </div>

        {{-- 🔍 FILTER SECTION --}}
        <div class="bg-sky-50 p-4 rounded-xl border border-sky-100 mb-4">
            <form method="GET" action="{{ route('admin.pegawai.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
                
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari Nama / NIP..."
                           class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 outline-none w-full sm:w-64">
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition font-medium">
                    Cari
                </button>

                @if(request('search') || request('unit_kerja'))
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

       {{-- 📑 Table Pegawai --}}
<div>

    <div class="overflow-x-auto overflow-y-auto max-h-[430px] text-xs mt-2 border border-gray-200 rounded-lg shadow-sm">
        <table class="w-full border-collapse bg-white">
            <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
                <tr>
                    <th class="px-2 py-1 border whitespace-nowrap">No</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Nama</th>
                    <th class="px-2 py-1 border whitespace-nowrap">NIP</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden md:table-cell">Email</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Telepon</th>                    
                    <th class="px-2 py-1 border whitespace-nowrap hidden md:table-cell">Role</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Jabatan</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Unit</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Atasan</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Pemberi Cuti</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Status</th>
                    <th class="px-2 py-1 border text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pegawai as $i => $p)
                    @php
                        $pegawaiData = [
                            'id' => $p->id,
                            'nama' => $p->nama,
                            'nip' => $p->nip,
                            'email' => optional($p->user)->email,
                            'role' => optional($p->user)->role,
                            'jabatan' => $p->jabatan,
                            'unit_kerja' => $p->unit_kerja,
                            'atasan' => $p->atasan, 
                            'pemberi_cuti' => $p->pemberi_cuti,
                            'telepon' => $p->telepon,
                            'status' => $p->status
                        ];

                        $nip = $p->nip ?? '-';

                        $email = optional($p->user)->email ?? ''; 

                       $telepon = $p->telepon ?? '-';
                    @endphp

                    <tr class="border hover:bg-gray-50 bg-white" data-pegawai='@json($pegawaiData)'>
                        <td class="px-2 py-1 border text-center">{{ $i + $pegawai->firstItem() }}</td>
                        <td class="px-2 py-1 border font-medium">{{ $p->nama }}</td>
                        <td class="px-2 py-1 border font-mono">{{ $nip }}</td>
                        <td class="px-2 py-1 border hidden md:table-cell">{{ $email }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->telepon }}</td>
                        <td class="px-2 py-1 border capitalize hidden md:table-cell">{{ optional($p->user)->role ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $p->jabatan ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->unit_kerja ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->atasan ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->pemberi_cuti ?? '-' }}</td>
                        <td class="px-2 py-1 border text-center">{{ $p->status ?? '-' }}</td>

                        <td class="px-2 py-1 border text-center whitespace-nowrap">
                            <div class="flex justify-center items-center gap-2">

                                {{-- Tombol Detail --}}
                                <button type="button"
                                    @click="
                                        const d = $el.closest('tr').dataset.pegawai;
                                        if (d) openDetailModal(JSON.parse(d));
                                    "
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Tombol Edit --}}
                                <button type="button"
                                    @click="
                                        const d = $el.closest('tr').dataset.pegawai;
                                        if (d) openEditModal(JSON.parse(d));
                                    "
                                    class="text-yellow-500 hover:text-yellow-700"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                    {{-- Tombol Hapus yang Baru --}}
                            <button type="button" 
                                    onclick="confirmDelete('{{ $p->id }}', '{{ addslashes($p->nama) }}')"
                                    class="text-red-600 hover:text-red-800" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                            {{-- Form Delete Tersembunyi (Sangat Penting: Harus Unik per ID) --}}
                            <form id="delete-form-{{ $p->id }}" 
                                action="{{ route('admin.pegawai.destroy', $p->id) }}" 
                                method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="10" class="text-center py-3 text-gray-500">Belum ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


    </div>
    </div>

    {{-- Container Pagination --}}
    <div class="mt-4 flex items-center justify-end space-x-2 text-[11px] select-none">
        
        {{-- Tombol Sebelumnya (<) --}}
        @if ($pegawai->onFirstPage())
            <span class="px-2 py-1 border rounded bg-gray-50 text-gray-400 cursor-not-allowed">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $pegawai->previousPageUrl() }}" 
            class="px-2 py-1 border rounded hover:bg-gray-100 text-gray-600 transition shadow-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Indikator Halaman (Angka 1) --}}
        <div class="flex items-center">
            <span class="px-3 py-1 bg-blue-600 text-white rounded font-bold shadow-sm">
                {{ $pegawai->currentPage() }}
            </span>
            {{-- Opsional: Menampilkan total halaman --}}
            <span class="ml-2 text-gray-500">dari {{ $pegawai->lastPage() }}</span>
        </div>

        {{-- Tombol Selanjutnya (>) --}}
        @if ($pegawai->hasMorePages())
            <a href="{{ $pegawai->nextPageUrl() }}" 
            class="px-2 py-1 border rounded hover:bg-gray-100 text-gray-600 transition shadow-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="px-2 py-1 border rounded bg-gray-50 text-gray-400 cursor-not-allowed">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </div>

    <hr>

</div>{{-- End of bg-[#E3F2FD] container --}}

{{-- ================= MODAL TAMBAH PEGAWAI (PREMIUM DESIGN) ================= --}}
<template x-if="showCreateModal">
    <div x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="closeModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- ========== HEADER DENGAN GRADIENT ========== --}}
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-user-plus text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Tambah Pegawai</h3>
                            <p class="text-sky-100 text-[10px] sm:text-xs">Isi formulir untuk menambahkan pegawai baru</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                        <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>

            {{-- ========== FORM CONTENT ========== --}}
            <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
                <form action="{{ route('admin.pegawai.store') }}" method="POST" autocomplete="off">
                    @csrf

                    {{-- ========== 2-COLUMN LAYOUT ========== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        
                        {{-- ===== KOLOM KIRI: DATA PEGAWAI ===== --}}
                        <div class="space-y-4">
                            {{-- DATA PEGAWAI SECTION --}}
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-tie text-sky-600 text-sm"></i>
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                    </div>
                                </div>
                                <div class="p-4 space-y-3">
                                    {{-- Nama Lengkap --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-user text-sky-500 text-[10px]"></i>
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nama" required
                                        x-model="nama"
                                               class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                      focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                               placeholder="Nama Pegawai"
                                               oninput="this.value = this.value.replace(/[^a-zA-Z\s.,]/g, '')">
                                    </div>

                                    {{-- NIP --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-hashtag text-sky-500 text-[10px]"></i>
                                            {{-- 1. Hapus <span class="text-red-500">*</span> agar tidak terlihat wajib secara visual --}}
                                            NIP 
                                        </label>
                                        {{-- 2. Hapus atribut 'required' dan 'minlength="13"' agar browser mengizinkan input kosong --}}
                                        <input type="text" name="nip" x-model="nip"
                                            maxlength="18" inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                    focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                            {{-- 3. Ubah placeholder agar lebih informatif --}}
                                            placeholder="Masukkan 18 digit NIP">
                                    </div>

                                    {{-- Jabatan & Unit Kerja --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-briefcase text-sky-500 text-[10px]"></i>
                                                Jabatan <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="jabatan" required
                                            x-model="jabatan"
                                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                                   placeholder="Staf IT">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-building text-sky-500 text-[10px]"></i>
                                                Unit Kerja <span class="text-red-500">*</span>
                                            </label>
                                            
                                            {{-- Mengganti input dengan select --}}
                                            <select name="unit_kerja" 
                                                    x-model="unit_kerja" 
                                                    required
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs ...">
                                                
                                                {{-- Value wajib kosong agar sinkron dengan unit_kerja = '' --}}
                                                <option value="" disabled selected>Pilih Unit Kerja</option>
                                                
                                                <option value="Bidang Sekretariat">Bidang Sekretariat</option>
                                                <option value="Bidang Informatika">Bidang Informatika</option>
                                                <option value="Bidang Statistika & Persandian">Bidang Statistika & Persandian</option>
                                                <option value="Bidang Komunikasi">Bidang Komunikasi</option>
                                                <option value="Dinas Komunikasi dan Informatika">Dinas Komunikasi dan Informatika</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Atasan & Pemberi Cuti --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-user-tie text-sky-500 text-[10px]"></i>
                                                Atasan <span class="text-red-500">*</span>
                                            </label>
                                            
                                            {{-- Perbaikan: Input diganti Dropdown Dinamis --}}
                                            <select name="atasan" 
                                                    x-model="atasan" 
                                                    :disabled="!role" {{-- Akan aktif otomatis jika role sudah diisi --}}
                                                    required
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                        focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 
                                                        disabled:bg-gray-100 disabled:cursor-not-allowed appearance-none">
                                                
                                                {{-- Tambahkan atribut 'disabled' agar tidak bisa diklik, dan 'selected' sebagai pilihan awal --}}
                                                <option value="" 
                                                        disabled 
                                                        selected 
                                                        x-text="!role ? 'Pilih Role dahulu' : 'Pilih Nama Atasan'">
                                                </option>
                                                
                                                {{-- Loop daftar atasan yang sudah difilter oleh getter di x-data --}}
                                                <template x-for="item in filteredAtasan" :key="item.id">
                                                    <option :value="item.nama" x-text="item.nama" :selected="atasan === item.nama"></option>
                                                </template>
                                            </select>
                                        </div>
                                        
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-stamp text-sky-500 text-[10px]"></i>
                                                Pemberi Cuti
                                            </label>
                                            <input type="text"
                                            name="pemberi_cuti"
                                            x-model="pemberi_cuti"
                                            readonly
                                            :class="role === 'pemberi_cuti' ? 'text-sky-600 font-semibold' : 'text-gray-500'"
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-gray-100/50 text-[11px] sm:text-xs text-gray-500 cursor-not-allowed">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- RINGKASAN INFO (Desktop only) --}}
                            <div class="hidden lg:block bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                    <i class="fa-solid fa-circle-info text-sky-600 text-sm"></i>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Informasi</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-shield-halved text-sky-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Role Akses</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-600">Pilih role untuk pegawai</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-check-circle text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-emerald-500 uppercase tracking-wide">Status Default</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-emerald-700">Pegawai Aktif</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== KOLOM KANAN: KEAMANAN AKUN ===== --}}
                        <div class="space-y-4">
                            {{-- ROLE & STATUS --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-shield-halved text-sky-500 text-[10px] sm:text-xs"></i>
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="role"
                                    x-model="role"
                                    @change="handleRoleChange()" {{-- Tambahkan trigger ini --}}
                                    required
                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                   focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                        <option value="" disabled selected>Pilih Role</option>
                                        <option value="atasan">Atasan</option>
                                        <option value="pemberi_cuti">Pejabat</option>
                                        <option value="pegawai">Pegawai</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-regular fa-circle-check text-sky-500 text-[10px] sm:text-xs"></i>
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="status" required x-model="status"
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                   focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                        <option value="" disabled selected>Pilih Status</option>
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            {{-- PASSWORD DENGAN VALIDASI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-key text-sky-500 text-[10px] sm:text-xs"></i>
                                    Password <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'"
                                        name="password"
                                        x-model="password"
                                        required
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                        focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                        placeholder="Kombinasi Minimal 8 Karakter">

                                    <span @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-3 flex items-center cursor-pointer text-gray-400">
                                        <i class="fa-solid text-xs"
                                        :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </span>
                                </div>

                                {{-- Password validation indicators --}}
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div class="flex items-center gap-1.5 text-[10px]"
                                        :class="hasUpper ? 'text-emerald-600' : 'text-gray-400'">
                                        <i class="fa-solid"
                                        :class="hasUpper ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                        Huruf Kapital (A-Z)
                                    </div>

                                    <div class="flex items-center gap-1.5 text-[10px]"
                                        :class="hasNumber ? 'text-emerald-600' : 'text-gray-400'">
                                        <i class="fa-solid"
                                        :class="hasNumber ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                        Angka (0-9)
                                    </div>

                                    <div class="flex items-center gap-1.5 text-[10px]"
                                        :class="hasSymbol ? 'text-emerald-600' : 'text-gray-400'">
                                        <i class="fa-solid"
                                        :class="hasSymbol ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                        Simbol (!@#$%^&*)
                                    </div>

                                    <div class="flex items-center gap-1.5 text-[10px]"
                                        :class="isLongEnough ? 'text-emerald-600' : 'text-gray-400'">
                                        <i class="fa-solid"
                                        :class="isLongEnough ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                        Minimal 8 Karakter
                                    </div>
                                </div>

                            </div>

                            {{-- Info Box Warning --}}
                            <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                </div>
                                <div class="text-[11px] sm:text-xs">
                                    <p class="font-bold text-amber-800">Perhatian</p>
                                    <p class="text-amber-700">Pastikan semua data telah diisi dengan benar sebelum menyimpan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i>
                            Batal
                        </button>
                        
                        <button type="submit"
                                :disabled="!isFormValid()"
                                class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                                :class="!isFormValid() 
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' 
                                        : 'bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-200'">
                            <i class="fa-solid fa-paper-plane"></i>
                            Simpan Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

{{-- ================= MODAL DETAIL (COMPACT VERSION) ================= --}}
<div x-show="showDetailModal" x-cloak @click.self="closeModal()" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-4 max-h-[85vh] overflow-y-auto"
         @click.away="closeModal()">

        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h3 class="text-sm font-bold text-sky-700 flex items-center gap-2">
                <i class="fa-solid fa-user-gear"></i>
                Detail Pegawai
            </h3>
            <button @click="closeModal()" class="text-gray-500 hover:text-gray-700 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-700">

            {{-- Nama --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Nama</p>
                <p x-text="selectedPegawai?.nama" class="font-medium"></p>
            </div>

            {{-- NIP --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">NIP</p>
                <p x-text="selectedPegawai?.nip || '-'" class="font-medium"></p>
            </div>

            {{-- Email --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Email</p>
                <p x-text="selectedPegawai?.email || '-'" class="font-medium break-all"></p>
            </div>

            {{-- Role --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Role Akun</p>
                <p x-text="selectedPegawai?.role || '-'" class="font-medium capitalize"></p>
            </div>

            {{-- Jabatan --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Jabatan</p>
                <p x-text="selectedPegawai?.jabatan || '-'" class="font-medium"></p>
            </div>

            {{-- Unit Kerja --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Unit Kerja</p>
                <p x-text="selectedPegawai?.unit_kerja || '-'" class="font-medium"></p>
            </div>

            {{-- Atasan Langsung --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Atasan Langsung</p>
                <p x-text="selectedPegawai?.atasan || '-'" class="font-medium"></p>
            </div>

            {{-- Pejabat Pemberi Cuti --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Pejabat Pemberi Cuti</p>
                <p x-text="selectedPegawai?.pemberi_cuti || '-'" class="font-medium"></p>
            </div>

            {{-- Telepon --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Telepon</p>
                <p x-text="selectedPegawai?.telepon || '-'" class="font-medium"></p>
            </div>

            {{-- Status --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Status</p>
                <span x-text="selectedPegawai?.status || '-'"
                      class="font-medium capitalize px-2 py-0.5 rounded-full text-[10px]"
                      :class="selectedPegawai?.status === 'aktif'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'">
                </span>
            </div>
        </div>

        <div class="mt-4 text-right border-t pt-3">
            <button @click="closeModal()" 
                    class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                Tutup
            </button>
        </div>

    </div>
</div>
{{-- ================= MODAL EDIT (PREMIUM DESIGN) ================= --}}
<template x-if="showEditModal">
    <div x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="closeModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- ========== HEADER DENGAN GRADIENT ========== --}}
            <div class="bg-gradient-to-r from-amber-500 to-yellow-600 px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-user-pen text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Edit Pegawai</h3>
                            <p class="text-amber-100 text-[10px] sm:text-xs">Perbarui informasi data pegawai</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                        <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>

            {{-- ========== FORM CONTENT ========== --}}
            <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
                <form x-bind:action="selectedPegawai ? editRoute.replace(':pegawaiId', selectedPegawai.id) : '#'" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- ========== 2-COLUMN LAYOUT ========== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        
                        {{-- ===== KOLOM KIRI: DATA PEGAWAI ===== --}}
                        <div class="space-y-4">
                            {{-- DATA PEGAWAI SECTION --}}
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-tie text-amber-600 text-sm"></i>
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                    </div>
                                </div>
                                <div class="p-4 space-y-3">
                                    {{-- Nama Lengkap --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-user text-amber-500 text-[10px]"></i>
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nama"
                                               x-model="selectedPegawai.nama"
                                               @input="selectedPegawai.nama = selectedPegawai.nama.replace(/[^a-zA-Z\s.,]/g, '')"
                                               class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                      focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                               placeholder="Nama Pegawai">
                                    </div>

                                    {{-- NIP --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-hashtag text-amber-500 text-[10px]"></i>
                                            NIP
                                        </label>
                                        <input type="text" name="nip"
                                               x-model="selectedPegawai.nip"
                                               maxlength="18"
                                               @input="selectedPegawai.nip = selectedPegawai.nip.replace(/[^0-9]/g, '').slice(0,18)"
                                               class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                      focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                               placeholder="Masukkan 18 digit NIP">
                                    </div>

                                    {{-- Jabatan & Unit Kerja --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-briefcase text-amber-500 text-[10px]"></i>
                                                Jabatan
                                            </label>
                                            <input type="text" name="jabatan"
                                                   x-model="selectedPegawai.jabatan"
                                                   @input="selectedPegawai.jabatan = selectedPegawai.jabatan.replace(/[^a-zA-Z\s]/g, '')"
                                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                          focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                                   placeholder="Jabatan">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-building text-amber-500 text-[10px]"></i>
                                                Unit Kerja
                                            </label>
                                            <input type="text" name="unit_kerja"
                                                   x-model="selectedPegawai.unit_kerja"
                                                   @input="selectedPegawai.unit_kerja = selectedPegawai.unit_kerja.replace(/[^a-zA-Z\s]/g, '')"
                                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                          focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                                   placeholder="Unit Kerja">
                                        </div>
                                    </div>

                                {{-- Atasan & Pemberi Cuti --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-user-tie text-sky-500 text-[10px]"></i>
                                            Atasan Langsung <span class="text-red-500">*</span>
                                        </label>
                                        
                                        {{-- Perbaikan: Input diganti Dropdown Dinamis --}}
                                        <select name="atasan" 
                                                x-model="atasan" 
                                                :disabled="!role || (role !== 'pegawai' && role !== 'atasan')"
                                                required
                                                class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                    focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 
                                                    disabled:bg-gray-100 disabled:cursor-not-allowed appearance-none">
                                            
                                                {{-- Tambahkan atribut 'disabled' agar tidak bisa diklik, dan 'selected' sebagai pilihan awal --}}
                                                <option value="" 
                                                        disabled 
                                                        selected 
                                                        x-text="!role ? 'Pilih Role dahulu' : 'Pilih Nama Atasan'">
                                                </option>
                                            
                                            {{-- Loop daftar atasan yang sudah difilter oleh getter di x-data --}}
                                            <template x-for="item in filteredAtasan" :key="item.id">
                                                <option :value="item.nama" x-text="item.nama"></option>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-stamp text-sky-500 text-[10px]"></i>
                                            Pemberi Cuti
                                        </label>
                                        <input type="text"
                                        name="pemberi_cuti"
                                        x-model="pemberi_cuti"
                                        readonly
                                        :class="role === 'pemberi_cuti' ? 'text-sky-600 font-semibold' : 'text-gray-500'"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-gray-100/50 text-[11px] sm:text-xs text-gray-500 cursor-not-allowed">
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== KOLOM KANAN: ROLE & STATUS ===== --}}
                        <div class="space-y-4">
                            {{-- Role --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-shield-halved text-sky-500 text-[10px] sm:text-xs"></i>
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    {{-- Tambahkan x-model="role" di sini --}}
                                    <select name="role" x-model="role" required
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                        <option value="" disabled selected>Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="atasan">Atasan Langsung</option>
                                        <option value="pemberi_cuti">Pejabat Pemberi Cuti</option>
                                        <option value="pegawai">Pegawai</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-toggle-on text-amber-500 text-[10px] sm:text-xs"></i>
                                    Status
                                </label>
                                <div class="relative">
                                    <select name="status" x-model="selectedPegawai.status"
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                   focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Info Box --}}
                            <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                </div>
                                <div class="text-[11px] sm:text-xs">
                                    <p class="font-bold text-amber-800">Perhatian</p>
                                    <p class="text-amber-700">Perubahan data akan langsung tersimpan ke database.</p>
                                </div>
                            </div>

                            {{-- Ringkasan Info (Desktop only) --}}
                            <div class="hidden lg:block bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                    <i class="fa-solid fa-circle-info text-amber-600 text-sm"></i>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Saat Ini</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-amber-100 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-id-badge text-amber-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">NIP</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-600" x-text="selectedPegawai?.nip || '-'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i>
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="(selectedPegawai && selectedPegawai.nip && selectedPegawai.nip.length > 0 && selectedPegawai.nip.length < 13) || isUnchanged()"
                                class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                                :class="((selectedPegawai && selectedPegawai.nip && selectedPegawai.nip.length > 0 && selectedPegawai.nip.length < 13) || isUnchanged()) 
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' 
                                    : 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white hover:from-amber-600 hover:to-yellow-700 hover:shadow-amber-200'">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


{{-- =============================================== --}}
{{-- MODAL GLOBAL NOTIFIKASI --}}
{{-- =============================================== --}}
<div x-show="showNotif" x-transition.opacity x-cloak 
     class="fixed inset-0 flex items-center justify-center bg-black/50 z-[999]">

    <div @click.outside="showNotif=false"
         x-transition.scale.duration.200ms
         class="bg-white rounded-xl shadow-xl p-5 w-full max-w-sm text-center">

        <!-- Ikon Dinamis -->
        <div class="text-4xl mb-2"
             :class="{
                'text-green-600': notifType === 'success',
                'text-red-600': notifType === 'error',
                'text-yellow-500': notifType === 'warning',
                'text-sky-600': notifType === 'info'
             }">

            <template x-if="notifType === 'success'">
                <i class="fa-solid fa-circle-check"></i>
            </template>

            <template x-if="notifType === 'error'">
                <i class="fa-solid fa-circle-xmark"></i>
            </template>

            <template x-if="notifType === 'warning'">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </template>

            <template x-if="notifType === 'info'">
                <i class="fa-solid fa-circle-info"></i>
            </template>
        </div>

        <h3 class="text-lg font-bold mb-1" 
            :class="{
                'text-green-600': notifType === 'success',
                'text-red-600': notifType === 'error',
                'text-yellow-600': notifType === 'warning',
                'text-sky-600': notifType === 'info'
            }"
            x-text="notifTitle">
        </h3>

        <p class="text-sm text-gray-600 mt-1" x-text="notifMessage"></p>

        <div class="flex flex-col items-center mt-5">
            <button @click="showNotif=false"
                class="w-full px-4 py-2 rounded-lg text-white text-sm"
                :class="{
                    'bg-green-600 hover:bg-green-700': notifType === 'success',
                    'bg-red-600 hover:bg-red-700': notifType === 'error',
                    'bg-yellow-500 hover:bg-yellow-600': notifType === 'warning',
                    'bg-sky-600 hover:bg-sky-700': notifType === 'info'
                }">
                OK
            </button>
        </div>

    </div>
</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Notifikasi Otomatis (Muncul setelah Tambah / Edit / Hapus)
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            borderRadius: '15px'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: "{{ session('error') }}",
            borderRadius: '15px'
        });
    @endif

    // 2. Fungsi Konfirmasi Hapus Data
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Pegawai?',
            text: "Data " + nama + " akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mencari form delete tersembunyi dan melakukan submit
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

@endsection