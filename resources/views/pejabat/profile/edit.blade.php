@extends('layouts.pegawai')

@section('title', 'Edit Profil')

{{-- resources\views\pejabat\profile\edit.blade.php --}}

@section('content')
<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- HEADER & BACK BUTTON --}}
        <div class="flex items-center justify-between mb-2">
            <div>
                <a href="{{ route('pejabat.profile.show') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 transition-colors font-medium mb-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Profil
                </a>
                {{-- Judul Dinamis --}}
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ request()->query('tab') === 'password' ? 'Ubah Kata Sandi' : 'Pengaturan Akun' }}
                </h1>
            </div>
        </div>

        {{-- 1. LOGIKA NOTIFIKASI INTERAKTIF (Alpine.js) --}}
        @if (session('success') || session('status') === 'password-updated')
            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-init="setTimeout(() => show = false, 5000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="flex items-center p-4 mb-6 bg-white border-l-4 border-green-500 rounded-r-xl shadow-md"
            >
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-bold text-gray-800">Berhasil!</p>
                    <p class="text-xs text-gray-600">
                        {{ session('success') ?? 'Kata sandi Anda telah berhasil diperbarui.' }}
                    </p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(request()->query('tab') === 'password')
            {{-- TAMPILKAN HANYA FORM PASSWORD --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transform transition hover:shadow-md">
                <div class="p-6 sm:p-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        @else
            {{-- TAMPILKAN HANYA FORM PROFIL (Default) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transform transition hover:shadow-md">
                <div class="p-6 sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        @endif

    </div>
</div>

@if (session('success'))
    <script>
        window.addEventListener('load', () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Profil Diperbarui',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-2xl' }
                });
            }
        });
    </script>
@endif

@endsection
