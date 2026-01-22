@extends('layouts.pegawai')

@section('title', 'Profil Pegawai')

@section('content')
<div x-data="{ showProfileModal: false }">

<div class="space-y-8">

    @php
        // Nama pegawai (fallback ke user->name)
        $namaPegawai = $pegawai->nama ?? $user->name ?? 'Pegawai';
        $jabatan = $pegawai->jabatan ?? 'Pegawai';
    @endphp

    {{-- 🌟 PROFILE CARD --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
        <!-- Cover Image -->
        <div class="h-40 bg-gradient-to-r from-sky-400 to-sky-600"></div>

        <!-- Profile Content -->
        <div class="flex flex-col items-center px-6 pb-6 -mt-16 relative z-10">
            <!-- Avatar -->
            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 
                        flex items-center justify-center text-white text-5xl font-bold shadow-lg border-4 border-white">
                {{ strtoupper(substr($namaPegawai, 0, 1)) }}
            </div>

            <!-- Name & Title -->
            <h1 class="text-2xl font-bold text-gray-800 mt-4">{{ $namaPegawai }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $jabatan }}</p>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mt-6 w-full max-w-xs">
                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <i class="fa-solid fa-building text-sky-600 text-xl mb-2 block"></i>
                    <p class="text-xs text-gray-500 mt-1">Unit Kerja</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ substr($pegawai->unit_kerja ?? '-', 0, 15) }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <i class="fa-solid fa-id-card text-sky-600 text-xl mb-2 block"></i>
                    <p class="text-xs text-gray-500 mt-1">NIP</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $pegawai->nip ? substr($pegawai->nip, 0, 4) . '****' : '-' }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <i class="fa-solid fa-phone text-sky-600 text-xl mb-2 block"></i>
                    <p class="text-xs text-gray-500 mt-1">Telepon</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $pegawai->telepon ? substr($pegawai->telepon, 0, 4) . '****' : '-' }}</p>
                </div>
            </div>

            <!-- Edit Button -->
            <button @click="showProfileModal = true" class="mt-6 px-8 py-2 bg-sky-600 text-white rounded-full hover:bg-sky-700 transition font-semibold text-sm">
                <i class="fa-solid fa-eye mr-2"></i>Lihat Detail
            </button>
        </div>
    </div>

    {{-- 💼 DATA PROFIL (AKUN + INFORMASI PEGAWAI) - MODAL --}}
    <div x-show="showProfileModal" @keydown.escape="showProfileModal = false" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div x-show="showProfileModal" @click="showProfileModal = false" class="fixed inset-0 bg-black bg-opacity-50" style="display: none;"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="showProfileModal" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-w-4xl w-full" style="display: none;">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-sky-500 to-sky-600 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-user-circle"></i>
                        Profil Lengkap Pegawai
                    </h2>
                    
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
                            @php
                                $email = $pegawai->email ?? $user->email ?? '-';
                                $emailMasked = $email;

                                if ($email !== '-' && str_contains($email, '@')) {
                                    [$namePart, $domain] = explode('@', $email);

                                    if (strlen($namePart) <= 2) {
                                        $maskedName = substr($namePart, 0, 1) . '*';
                                    } else {
                                        $maskedName = substr($namePart, 0, 2) . str_repeat('*', strlen($namePart) - 2);
                                    }

                                    $emailMasked = $maskedName . '@' . $domain;
                                }
                            @endphp
                            {{ $emailMasked }}
                        </span>
                    </div>

                    {{-- Role --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Role</span>
                        <span class="text-sm text-gray-800 px-3 py-1 bg-sky-100 text-sky-700 rounded-full">{{ ucfirst($user->role) }}</span>
                    </div>

                    {{-- Tanggal Bergabung --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Bergabung</span>
                        <span class="text-sm text-gray-800">{{ $user->created_at->translatedFormat('d F Y') }}</span>
                    </div>

                    {{-- Terakhir Diperbarui --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 font-medium">Diperbarui</span>
                        <span class="text-sm text-gray-800">{{ $user->updated_at->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- INFORMASI PEGAWAI --}}
            <div>
                <h3 class="text-base font-semibold text-sky-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-id-badge"></i>
                    Informasi Pegawai
                </h3>

                <div class="space-y-3">
                    {{-- Nama Lengkap --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Nama</span>
                        <span class="text-sm text-gray-800">{{ $pegawai->nama ?? '-' }}</span>
                    </div>

                    {{-- Jabatan --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Jabatan</span>
                        <span class="text-sm text-gray-800 font-semibold">{{ $pegawai->jabatan ?? '-' }}</span>
                    </div>

                    {{-- Unit Kerja --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">Unit Kerja</span>
                        <span class="text-sm text-gray-800">{{ $pegawai->unit_kerja ?? '-' }}</span>
                    </div>

                    {{-- NIP --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-600 font-medium">NIP</span>
                        <span class="text-sm text-gray-800 font-mono">{{ $pegawai->nip ? substr($pegawai->nip, 0, 5) . '*****' : '-' }}</span>
                    </div>

                    {{-- No Telepon --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 font-medium">Telepon</span>
                        <span class="text-sm text-gray-800">{{ $pegawai->telepon ? substr($pegawai->telepon, 0, 5) . '*****' : '-' }}</span>
                    </div>
                </div>
            </div>
                </div>

                <!-- Footer dengan Button Edit -->
                <div class="bg-gray-50 px-8 py-4 flex items-center justify-center gap-3 border-t border-gray-200">
                    <button @click="showProfileModal = false" class="px-6 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-semibold text-sm">
                        <i class="fa-solid fa-times mr-2"></i>Tutup
                    </button>
                <a href="/pegawai/profile/edit"
                class="px-6 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition font-semibold text-sm flex items-center justify-center">
                    <i class="fa-solid fa-pen-to-square mr-2"></i>Edit Profile
                </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
