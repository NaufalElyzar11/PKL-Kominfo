@extends('layouts.pegawai')

@section('title', 'Profil Pegawai')

@section('content')
@php
    $namaPegawai = $pegawai->nama ?? $user->name ?? 'Pegawai';
    $jabatan = $pegawai->jabatan ?? 'Pegawai';
    // Gunakan unit kerja sebagai alamat domisili sementara jika data alamat tidak ada
    $alamat = $pegawai->unit_kerja ?? '-'; 
    $nip = $pegawai->nip ?? '-';
    $telepon = $pegawai->telepon ?? '-';
    $email = $pegawai->email ?? $user->email ?? '-';
    $unitKerja = $pegawai->unit_kerja ?? '-';
    
    // Avatar Initials
    $initials = strtoupper(substr($namaPegawai, 0, 1));
@endphp

<div class="space-y-8">
    {{-- Header Section --}}
    <div class="relative w-full rounded-hub overflow-hidden bg-gradient-to-br from-electric-blue to-primary-dark p-8 lg:p-12 text-white shadow-2xl">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-lime-green/20 rounded-full blur-3xl -ml-20 -mb-20 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center gap-8 lg:gap-12 text-center lg:text-left">
            {{-- Profile Picture --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-lime-green rounded-hub rotate-6 group-hover:rotate-12 transition-transform duration-500"></div>
                <!-- Avatar Container -->
                <div class="relative w-40 h-40 lg:w-48 lg:h-48 rounded-hub overflow-hidden border-4 border-white shadow-2xl bg-white flex items-center justify-center">
                    <span class="text-6xl font-extrabold text-electric-blue select-none">{{ $initials }}</span>
                </div>
                {{-- Edit Trigger (Optional - Linking to Edit Page) --}}
                <a href="{{ route('pegawai.profile.show') }}" class="absolute -bottom-2 -right-2 bg-soft-orange text-white p-3 rounded-2xl shadow-lg cursor-pointer hover:scale-110 transition-transform">
                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                </a>
            </div>

            {{-- Text Info --}}
            <div class="flex-1">
                <div class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-white text-xs font-bold uppercase tracking-widest mb-4 border border-white/30">
                    NIP: {{ $nip }}
                </div>

                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <i class="fa-solid fa-id-card text-sky-600 text-xl mb-2 block"></i>
                    <p class="text-xs text-gray-500 mt-1">NIP</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $pegawai->nip ?? '-' }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <i class="fa-solid fa-phone text-sky-600 text-xl mb-2 block"></i>
                    <p class="text-xs text-gray-500 mt-1">Telepon</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $pegawai->telepon ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 -mt-8 relative z-20 px-4">
        
        {{-- Resource Status Card --}}
        <div class="lg:col-span-12">
            <div class="bg-white rounded-hub p-8 lg:p-10 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-white flex flex-col md:flex-row items-center justify-between gap-8 overflow-hidden relative group">
                <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-lime-green/5 skew-x-12 translate-x-12 -z-10"></div>
                
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Status Cuti</h3>
                    <p class="text-slate-500">Ketersediaan cuti tahunan Anda diperbarui secara real-time.</p>
                    <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-3">
                        <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-bold flex items-center gap-2">
                            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Status Aktif
                        </span>
                        <span class="px-4 py-2 bg-electric-blue/10 text-electric-blue rounded-full text-sm font-bold">Periode {{ date('Y') }}</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- DATA AKUN --}}
            <div>
                <h3 class="text-base font-semibold text-sky-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-lock"></i>
                    Data Akun
                </h3>

                <div class="space-y-3">
                    {{-- Email --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Email</span>
                        <span class="text-sm text-gray-800">
                    {{ $pegawai->email ?? $user->email ?? '-' }}
                        </span>
                    </div>

        {{-- Data Kolom Kiri: Kontak --}}
        <div class="lg:col-span-6 flex flex-col gap-8">
            <div class="bg-white rounded-card p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-white group h-full">
                <div class="w-14 h-14 bg-soft-orange/10 text-soft-orange rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-at text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-6">Informasi Kontak</h3>
                <div class="space-y-6">
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Email</label>
                        <p class="font-bold text-slate-800 break-all">{{ $email }}</p>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Telepon</label>
                        <p class="font-bold text-slate-800">{{ $telepon }}</p>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Role</label>
                        <div class="flex">
                            <span class="px-3 py-1 bg-electric-blue/10 text-electric-blue rounded-lg text-sm font-bold border border-electric-blue/20">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    </div>
                    {{-- Edit Trigger --}}
                    <button class="w-full py-3 border-2 border-slate-100 rounded-xl font-bold text-slate-500 hover:border-soft-orange hover:text-soft-orange transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen text-sm"></i>
                        <span>Edit Kontak</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Data Kolom Kanan: Jabatan --}}
        <div class="lg:col-span-6 flex flex-col gap-8">
            <div class="bg-white rounded-card p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-white h-full">
                <div class="w-14 h-14 bg-electric-blue/10 text-electric-blue rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-id-badge text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-6">Informasi Jabatan</h3>
                <div class="space-y-6">
                    <div class="p-4 bg-slate-50 rounded-2xl">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Unit Kerja</label>
                        <p class="text-sm font-bold text-slate-700 leading-tight">{{ $unitKerja }}</p>
                    </div>

                    {{-- NIP --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">NIP</span>
                        <span class="text-sm text-gray-800 font-mono">{{ $pegawai->nip ?? '-' }}</span>
                    </div>

                    {{-- No Telepon --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 font-medium">Telepon</span>
                        <span class="text-sm text-gray-800">{{ $pegawai->telepon ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen (Mockup Visual) --}}
        <!-- <div class="lg:col-span-12">
            <div class="bg-slate-900 rounded-card p-8 shadow-lg text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                <div class="w-14 h-14 bg-white/10 text-lime-green rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-folder-open text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-6">Dokumen & Arsip</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- File Item Mock --}}
                    <a class="flex items-center gap-4 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors group" href="#">
                        <div class="w-10 h-10 bg-red-500/20 text-red-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-file-pdf text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">SK Terakhir.pdf</p>
                            <p class="text-[10px] text-white/40">1.2 MB • Updated May 2024</p>
                        </div>
                        <i class="fa-solid fa-download text-white/30 group-hover:text-white transition-colors"></i>
                    </a>
                    <a class="flex items-center gap-4 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors group" href="#">
                        <div class="w-10 h-10 bg-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-file-lines text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">Kartu Pegawai.pdf</p>
                            <p class="text-[10px] text-white/40">850 KB • Permanent Doc</p>
                        </div>
                        <i class="fa-solid fa-download text-white/30 group-hover:text-white transition-colors"></i>
                    </a>
                    {{-- Add Button --}}
                    <div class="flex items-center justify-center p-3">
                        <button class="w-full py-3 bg-lime-green text-slate-900 rounded-xl font-bold hover:scale-105 transition-transform flex items-center justify-center gap-2">
                             <i class="fa-solid fa-plus"></i>
                            Tambah Dokumen
                        </button>
                    </div> 
                </div>
            </div>
        </div> -->

    </div>
</div>
@endsection
