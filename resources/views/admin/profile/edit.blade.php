@extends('layouts.pegawai') {{-- Sesuaikan dengan layout dashboard pegawai Anda --}}

@section('title', 'Edit Profil')

@section('content')
<div class="py-12 bg-[#E3F2FD] min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        {{-- BAGIAN 1: FORM INFORMASI PROFIL --}}
        <div class="p-4 sm:p-8 bg-white shadow-lg rounded-2xl border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- BAGIAN 2: FORM UPDATE PASSWORD --}}
        <div class="p-4 sm:p-8 bg-white shadow-lg rounded-2xl border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- BAGIAN 3: FORM HAPUS AKUN (OPSIONAL) --}}
        <div class="p-4 sm:p-8 bg-white shadow-lg rounded-2xl border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
        
    </div>
</div>
@endsection