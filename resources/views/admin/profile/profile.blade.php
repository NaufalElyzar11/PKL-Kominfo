@extends('layouts.admin')

@section('title', 'Profil Admin')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&amp;family=Lato:wght@400;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "electric-blue": "#2E5BFF",
                    "lime-green": "#86EFAC",
                    "soft-orange": "#FFB347",
                    "hub-grey": "#F3F4F6",
                },
                fontFamily: {
                    "display": ["Poppins", "sans-serif"],
                    "body": ["Lato", "sans-serif"]
                },
                borderRadius: { 
                    "hub": "2.5rem",
                    "card": "1.75rem"
                },
            },
        },
    }
</script>
<style>
    .font-display { font-family: 'Poppins', sans-serif; }
    .font-body { font-family: 'Lato', sans-serif; }
    .vibrant-gradient {
        background: linear-gradient(135deg, #2E5BFF 0%, #137fec 100%);
    }
    .card-shadow {
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
    }
    /* Scoped styles */
    .profile-page-wrapper h1, 
    .profile-page-wrapper h2, 
    .profile-page-wrapper h3, 
    .profile-page-wrapper h4 { font-family: 'Poppins', sans-serif; }
</style>
@endpush

@section('content')
{{-- WRAPPER UTAMA: Relative agar z-index bekerja dengan benar di dalam layout --}}
<div class="profile-page-wrapper -m-6 relative isolate">

    @php
        // Ambil data nama (prioritas: Pegawai -> User Name -> 'Admin')
        $namaAdmin = $user->pegawai?->nama ?? $user->name ?? 'Admin';
    @endphp

    {{-- 1. HERO HEADER: (Z-Index 0) --}}
    <div class="relative w-full vibrant-gradient px-6 py-12 overflow-hidden rounded-[2.5rem] z-0 mb-8">
        {{-- Background Decorations --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-lime-green/20 rounded-full blur-3xl -ml-20 -mb-20 pointer-events-none"></div>
        
        <div class="container mx-auto max-w-7xl relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12 text-center lg:text-left">
                
                {{-- Profile Picture --}}
                <div class="relative group flex-shrink-0 cursor-pointer">
                    {{-- Latar belakang yang berputar --}}
                    <div class="absolute inset-0 bg-lime-green rounded-hub rotate-6 group-hover:rotate-12 transition-transform duration-500"></div>
                    
                    {{-- Container Foto Utama --}}
                    <div class="relative w-36 h-36 lg:w-44 lg:h-44 rounded-hub overflow-hidden border-4 border-white shadow-2xl bg-white flex items-center justify-center z-10">
                        @if($pegawai && $pegawai->foto)
                            <img src="{{ asset('storage/' . $pegawai->foto) }}" 
                                alt="Foto Profil" 
                                class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl lg:text-6xl font-extrabold text-electric-blue select-none">
                                {{ strtoupper(substr($namaAdmin, 0, 1)) }}
                            </span>
                        @endif

                        {{-- Overlay Hover (Hanya jika belum ada foto dan terhubung data pegawai, atau jika ingin selalu tampil) --}}
                        {{-- Untuk Admin, kita tampilkan saja icon edit jika ingin mengubah --}}
                        <div class="absolute inset-0 bg-electric-blue/20 hidden md:flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                            <div class="flex flex-col items-center gap-1">
                                <span class="material-symbols-outlined text-white text-2xl">edit</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Edit Foto (Link ke halaman edit) --}}
                    <a href="{{ route('admin.profile.edit') }}" class="absolute -bottom-1 -right-1 lg:bottom-0 lg:right-0 z-20 bg-white text-electric-blue p-2 lg:p-2.5 rounded-full border-2 border-electric-blue shadow-[0_4px_10px_rgba(46,91,255,0.2)] flex items-center justify-center hover:bg-electric-blue hover:text-white transition-colors duration-300" title="Ubah Foto Profil">
                        <span class="material-symbols-outlined text-[18px] lg:text-[20px]">edit</span>
                    </a>
                </div>

                {{-- User Info --}}
                <div class="flex-1 min-w-0">
                    <div class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-white text-xs font-bold uppercase tracking-widest mb-4 border border-white/30">
                        @if($pegawai)
                            NIP: {{ $pegawai->nip ?? '-' }}
                        @else
                            ADMINISTRATOR
                        @endif
                    </div>
                    <h1 class="text-3xl lg:text-5xl font-extrabold text-white leading-tight break-words">
                        {{ $namaAdmin }}
                    </h1>
                    <p class="text-white/80 text-lg mt-2 font-medium">
                        {{ $pegawai->jabatan ?? 'Administrator Sistem' }} • {{ $pegawai->unit_kerja ?? 'Kominfo' }}
                    </p>
                    
                    <div class="mt-8 flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="{{ route('admin.profile.edit') }}" class="px-6 py-3 bg-white text-electric-blue rounded-2xl font-bold shadow-xl hover:bg-lime-green hover:text-slate-900 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined">settings</span>
                            Pengaturan Akun
                        </a>
                        {{-- Tombol Ubah Password (jika ingin dipisah, sementara arahkan ke edit juga) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MAIN CONTENT (Cards): (Z-Index 10) --}}
    <main class="container mx-auto max-w-7xl px-4 relative z-10 mb-20 font-body">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            
            {{-- ROW 1: STATUS AKUN (ADMIN VERSION) --}}
            <div class="lg:col-span-12">
                <div class="bg-white rounded-hub p-8 lg:p-10 card-shadow border border-white flex flex-col md:flex-row items-center justify-between gap-8 overflow-hidden relative group">
                    {{-- Kiri: Info Umum --}}
                    <div class="flex-1 text-center md:text-left z-10">
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Status Akun</h3>
                        <p class="text-slate-500 text-sm">Informasi status akun administrator Anda.</p>
                        <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-3">
                            <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-bold flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                {{ ucfirst($pegawai->status ?? 'Aktif') }}
                            </span>
                            <span class="px-4 py-2 bg-electric-blue/10 text-electric-blue rounded-full text-sm font-bold">
                                {{ date('Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Kanan: Detail (Jika ada pegawai, tampilkan sisa cuti. Jika tidak, tampilkan Role) --}}
                    <div class="relative flex-shrink-0 z-10">
                        @if($pegawai)
                            <div class="w-40 h-40 lg:w-48 lg:h-48 rounded-full border-[12px] border-slate-100 flex items-center justify-center relative bg-white">
                                <div class="absolute inset-0 rounded-full border-[12px] border-electric-blue border-t-transparent -rotate-45"></div>
                                <div class="text-center">
                                    <span class="block text-4xl lg:text-5xl font-black text-electric-blue">{{ $pegawai->sisa_cuti ?? 0 }}</span>
                                    <span class="block text-[10px] lg:text-xs font-bold text-slate-400 uppercase tracking-tighter">Sisa Cuti</span>
                                </div>
                            </div>
                        @else
                            {{-- Tampilan Alternatif untuk Admin Murni --}}
                            <div class="w-40 h-40 lg:w-48 lg:h-48 rounded-full border-[12px] border-slate-100 flex items-center justify-center relative bg-white">
                                <div class="text-center">
                                    <span class="material-symbols-outlined text-6xl text-electric-blue mb-2">admin_panel_settings</span>
                                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-tighter">Super User</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Background decorative shape --}}
                    <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-lime-green/5 skew-x-12 translate-x-12 z-0 pointer-events-none"></div>
                </div>
            </div>

            {{-- ROW 2: DETAIL INFO --}}
            
            {{-- Kontak --}}
            <div class="lg:col-span-6 flex flex-col h-full">
                <div class="bg-white rounded-card p-8 card-shadow border border-white h-full flex flex-col hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-soft-orange/10 text-soft-orange rounded-2xl flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-2xl">alternate_email</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-6 font-display">Kontak</h3>
                    <div class="space-y-6 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Email</label>
                            <p class="font-bold text-slate-800 text-sm break-all">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Telepon</label>
                            <p class="font-bold text-slate-800 text-sm">{{ $pegawai->telepon ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-6 mt-auto">
                        <a href="{{ route('admin.profile.edit') }}" class="w-full py-3 border-2 border-slate-100 rounded-xl font-bold text-slate-500 hover:border-soft-orange hover:text-soft-orange transition-all flex items-center justify-center gap-2 text-sm group">
                            <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform">edit</span>
                            Edit Kontak
                        </a>
                    </div>
                </div>
            </div>

            {{-- Jabatan / Info Tambahan --}}
            <div class="lg:col-span-6 flex flex-col h-full">
                <div class="bg-white rounded-card p-8 card-shadow border border-white h-full hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-electric-blue/10 text-electric-blue rounded-2xl flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-2xl">badge</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-6 font-display">Jabatan</h3>
                    <div class="space-y-4">
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Unit Kerja</label>
                            <p class="text-sm font-bold text-slate-700 leading-tight">{{ $pegawai->unit_kerja ?? 'Administrator' }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Jabatan</label>
                            <p class="text-sm font-bold text-slate-700">{{ $pegawai->jabatan ?? 'System Admin' }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Role System</label>
                            <p class="text-sm font-bold text-slate-700 font-mono uppercase">{{ $user->role }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-2xl', // Menyesuaikan dengan tema rounded Anda
        }
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        confirmButtonColor: '#ef4444',
        customClass: {
            popup: 'rounded-2xl',
        }
    });
</script>
@endif
@endsection