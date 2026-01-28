@extends('layouts.pegawai')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endpush

@section('title', 'Edit Profil')

@section('content')
<div class="py-12 bg-[#E3F2FD] min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        {{-- TOMBOL KEMBALI DAN HEADER --}}
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('admin.profile.index') }}" 
               class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all shadow-sm">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-[#0d141b] text-2xl font-bold leading-tight">Edit Profil</h1>
                <p class="text-[#4c739a] text-sm font-normal">Silakan perbarui informasi profil dan keamanan akun Anda.</p>
            </div>
        </div>

        {{-- BAGIAN 1: FORM INFORMASI PROFIL --}}
        <div class="p-4 sm:p-8 bg-white shadow-lg rounded-2xl border border-gray-100">
            <div class="max-w-xl">
                {{-- PENTING: Pastikan action di file ini sudah mengarah ke admin.profile.update --}}
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- BAGIAN 2: FORM UPDATE PASSWORD --}}
        <div class="p-4 sm:p-8 bg-white shadow-lg rounded-2xl border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- BAGIAN 3: DIHAPUS UNTUK MENGHINDARI ERROR profile.destroy --}}
        {{-- Jangan masukkan include delete-user-form di sini agar tidak error --}}
        
    </div>
</div>
@endsection