@extends('layouts.pegawai')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-600 italic">Panel Pejabat Pemberi Cuti - Sistem Informasi Cuti</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border-l-4 border-yellow-400 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full text-yellow-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Menunggu Approval</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['menunggu'] }} <span class="text-sm font-normal text-gray-400">Pegawai</span></p>
                </div>
            </div>
            <a href="{{ route('pejabat.approval.index') }}"class="mt-4 block text-sm text-yellow-600 hover:underline font-semibold">Lihat Daftar &rarr;</a>
        </div>

        <div class="bg-white border-l-4 border-green-500 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full text-green-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Telah Disetujui</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['disetujui'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border-l-4 border-red-500 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full text-red-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Ditolak</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['ditolak'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800 text-sm">
        <div class="flex">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"></path></svg>
            <p><strong>Tips:</strong> Segera periksa pengajuan cuti yang masuk. Pegawai Anda sangat bergantung pada keputusan cepat Anda untuk merencanakan waktu mereka.</p>
        </div>
    </div>
</div>
@endsection