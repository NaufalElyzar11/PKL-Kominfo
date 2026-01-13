@extends('layouts.kepala-dinas')

@section('title', 'Setujui Pengajuan Cuti')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-4 text-green-600">Konfirmasi Persetujuan Cuti</h2>

    <p class="text-gray-600 dark:text-gray-300 mb-6">
        Apakah Anda yakin ingin <strong>menyetujui</strong> pengajuan cuti berikut?
    </p>

    <ul class="mb-6 space-y-2 text-sm text-gray-700 dark:text-gray-300">
        <li><strong>Nama Pegawai:</strong> {{ $cuti->user->name }}</li>
        <li><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d F Y') }}</li>
        <li><strong>Alasan:</strong> {{ $cuti->alasan }}</li>
    </ul>

    <form action="{{ route('kepaladinas.datacuti.approve', $cuti->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="flex space-x-3">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Ya, Setujui
            </button>
            <a href="{{ route('kepaladinas.datacuti.index') }}" 
               class="px-4 py-2 bg-gray-300 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
