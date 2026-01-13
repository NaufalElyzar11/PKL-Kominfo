@extends('layouts.kepala-dinas')

@section('title', 'Tolak Pengajuan Cuti')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-4 text-red-600">Tolak Pengajuan Cuti</h2>

    <p class="text-gray-600 dark:text-gray-300 mb-4">
        Anda akan menolak pengajuan cuti berikut:
    </p>

    <ul class="mb-6 space-y-2 text-sm text-gray-700 dark:text-gray-300">
        <li><strong>Nama Pegawai:</strong> {{ $cuti->user->name }}</li>
        <li><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d F Y') }}</li>
        <li><strong>Alasan:</strong> {{ $cuti->alasan }}</li>
    </ul>

    <form action="{{ route('kepaladinas.datacuti.reject', $cuti->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 mb-1">Catatan Penolakan (Opsional):</label>
            <textarea name="catatan" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Tolak Cuti
            </button>
            <a href="{{ route('kepaladinas.datacuti.index') }}" 
               class="px-4 py-2 bg-gray-300 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
