@extends('layouts.admin')

@section('title', 'Edit Data Cuti')

@section('content')
<div class="max-w-4xl mx-auto p-6">

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-center text-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
            Edit Data Cuti Pegawai
        </h2>

        <form action="{{ route('admin.cuti.update', $cuti->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="id_pegawai" value="{{ $cuti->id_pegawai }}">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Nama</label>
                    <input type="text" value="{{ $cuti->pegawai->nama ?? '-' }}" readonly
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  bg-gray-50 dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">NIP</label>
                    <input type="text" value="{{ $cuti->pegawai->nip ?? '-' }}" readonly
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  bg-gray-50 dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Jabatan</label>
                    <input type="text" value="{{ $cuti->pegawai->jabatan ?? '-' }}" readonly
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  bg-gray-50 dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Jenis Cuti</label>
                    <select name="jenis_cuti" class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                                   dark:bg-gray-700 dark:text-gray-100 text-sm">
                        <option value="Cuti Tahunan" {{ $cuti->jenis_cuti == 'Cuti Tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Alasan</label>
                    <input type="text" name="alasan_cuti" value="{{ $cuti->alasan_cuti }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $cuti->tanggal_mulai->format('Y-m-d') }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $cuti->tanggal_selesai->format('Y-m-d') }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium">Jumlah Hari</label>
                    <input type="number" name="jumlah_hari" value="{{ $cuti->jumlah_hari }}" readonly
                           class="w-full mt-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                  bg-gray-50 dark:bg-gray-700 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.cuti.index') }}" 
                   class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 
                          dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 text-sm font-medium transition">
                    Kembali
                </a>
                <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 
                               text-white font-semibold text-sm shadow-md transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
