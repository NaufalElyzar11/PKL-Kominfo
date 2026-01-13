@extends('layouts.admin')

@section('title', 'Edit Data Pegawai')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-gray-900 p-6 rounded-xl shadow-md 
            border border-gray-200 dark:border-gray-700 mt-6 transition-all duration-300">

    <h1 class="text-xl font-semibold mb-6 text-center text-sky-700 dark:text-sky-300">
        Edit Data Pegawai
    </h1>

    <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST" 
          class="space-y-4 bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
        @csrf
        @method('PUT')

        {{-- Nama Lengkap --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Nama Lengkap
            </label>
            <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                          bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                          focus:ring-2 focus:ring-sky-500 outline-none transition text-sm" required>
            @error('nama')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- NIP --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                NIP
            </label>
            <input type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                          bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                          focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">
            @error('nip')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Role --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Role / Hak Akses
            </label>
            <select name="role"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                           focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">
                <option value="" disabled>Pilih Role</option>
                <option value="super_admin" {{ old('role', $pegawai->user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ old('role', $pegawai->user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="kadis" {{ old('role', $pegawai->user->role) == 'kadis' ? 'selected' : '' }}>Kadis (Kepala Dinas)</option>
                <option value="pegawai" {{ old('role', $pegawai->user->role) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            </select>
            @error('role')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Jabatan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Jabatan
            </label>
            <input type="text" name="jabatan" value="{{ old('jabatan', $pegawai->jabatan) }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                          bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                          focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">
            @error('jabatan')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Unit Kerja --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Unit Kerja
            </label>
            <input type="text" name="unit_kerja" value="{{ old('unit_kerja', $pegawai->unit_kerja) }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                          bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                          focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">
            @error('unit_kerja')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Alamat
            </label>
            <textarea name="alamat" rows="2"
                      class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                             bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                             focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">{{ old('alamat', $pegawai->alamat) }}</textarea>
            @error('alamat')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Telepon --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Nomor Telepon
            </label>
            <input type="text" name="telepon" value="{{ old('telepon', $pegawai->telepon) }}"
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 
                          bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                          focus:ring-2 focus:ring-sky-500 outline-none transition text-sm">
            @error('telepon')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.pegawai.index') }}"
               class="px-4 py-1.5 bg-gray-400 dark:bg-gray-600 text-white rounded-md 
                      hover:bg-gray-500 dark:hover:bg-gray-500 transition text-xs font-medium">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-1.5 bg-sky-600 text-white rounded-md 
                           hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 
                           transition text-xs font-medium">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
