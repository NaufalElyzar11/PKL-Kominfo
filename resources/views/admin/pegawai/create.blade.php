@extends('layouts.admin')

@section('content')
<div x-data="{ show: false }"
     x-init="setTimeout(() => show = true, 100)"
     x-show="show"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-3"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-3"
     class="p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm">

    <!-- Judul -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            Tambah Pegawai
        </h1>
    </div>

    <!-- Form -->
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-4">
        <form action="{{ route('admin.pegawai.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf

            {{-- Nama & NIP --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                    @error('nama') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip') }}"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none">
                    @error('nip') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                           dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                @error('email') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Jabatan & Unit Kerja --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none">
                    @error('jabatan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Unit Kerja</label>
                    <input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none">
                    @error('unit_kerja') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Telepon & Role --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none">
                    @error('telepon') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="kadis" {{ old('role') == 'kadis' ? 'selected' : '' }}>Kadis (Kepala Dinas)</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    @error('role') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Password & Konfirmasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                    @error('password') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                               dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Status</label>
                <select name="status"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded 
                           dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-sky-500 focus:outline-none" required>
                    <option value="" disabled>-- Pilih Status --</option>
                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-200 dark:border-gray-700 mt-4">
                <a href="{{ route('admin.pegawai.index') }}"
                    class="px-3 py-1 rounded bg-gray-400 text-white hover:bg-gray-500 dark:bg-gray-600 dark:hover:bg-gray-500 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-3 py-1 rounded bg-sky-600 text-white hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
