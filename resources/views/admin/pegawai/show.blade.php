@extends('layouts.admin')

@section('content')
<div class="p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm">

    <!-- Judul -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            Detail Pegawai
        </h1>
    </div>

    <!-- Card Detail -->
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-4 text-xs">
        @php
            // Privasi NIP
            $nip = $pegawai->nip ? substr($pegawai->nip, 0, 4) . str_repeat('*', max(strlen($pegawai->nip) - 4, 0)) : '-';

            // Privasi Email
            $email = optional($pegawai->user)->email ?? $pegawai->email;
            if ($email) {
                $emailParts = explode('@', $email);
                $email = substr($emailParts[0], 0, 3) . str_repeat('*', max(strlen($emailParts[0]) - 3, 0)) . '@' . ($emailParts[1] ?? '');
            } else {pilih atasan
                $email = '-';
            }

            // Privasi Telepon
            $telepon = $pegawai->telepon;
            if ($telepon) {
                $telepon = substr($telepon, 0, 3) . str_repeat('*', max(strlen($telepon) - 3, 0));
            } else {
                $telepon = '-';
            }
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Nama</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $pegawai->nama ?? optional($pegawai->user)->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">NIP</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $nip }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $email }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Role</p>
                <p class="font-semibold capitalize text-gray-800 dark:text-gray-100">
                    {{ optional($pegawai->user)->role ?? $pegawai->role ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Jabatan</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $pegawai->jabatan ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Unit Kerja</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $pegawai->unit_kerja ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Telepon</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $telepon }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ ucfirst($pegawai->status ?? '-') }}</p>
            </div>

            <div class="sm:col-span-2 mt-2">
                <p class="text-gray-500 dark:text-gray-400">Dibuat pada</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">
                    {{ $pegawai->created_at ? $pegawai->created_at->format('d M Y H:i') : '-' }}
                </p>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
            <a href="{{ route('admin.pegawai.index') }}"
                class="px-3 py-1 rounded bg-gray-400 text-white hover:bg-gray-500 dark:bg-gray-600 dark:hover:bg-gray-500 transition">
                Kembali
            </a>
            <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}"
                class="px-3 py-1 rounded bg-sky-600 text-white hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 transition">
                Edit
            </a>
        </div>
    </div>
</div>@extends('layouts.admin')

@section('content')
<div class="p-4 flex justify-center">
    <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">

        <!-- Judul -->
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Detail Data Pegawai
            </h1>
        </div>

        @php
            // Privasi NIP
            $nip = $pegawai->nip ? substr($pegawai->nip, 0, 4) . str_repeat('*', max(strlen($pegawai->nip) - 4, 0)) : '-';

            // Privasi Email
            $email = optional($pegawai->user)->email ?? $pegawai->email;
            if ($email) {
                $emailParts = explode('@', $email);
                $email = substr($emailParts[0], 0, 3) . str_repeat('*', max(strlen($emailParts[0]) - 3, 0)) . '@' . ($emailParts[1] ?? '');
            } else {
                $email = '-';
            }

            // Privasi Telepon
            $telepon = $pegawai->telepon;
            if ($telepon) {
                $telepon = substr($telepon, 0, 3) . str_repeat('*', max(strlen($telepon) - 3, 0));
            } else {
                $telepon = '-';
            }
        @endphp

        <!-- Data Pegawai -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">
                    {{ $pegawai->nama ?? optional($pegawai->user)->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">NIP</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $nip }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Alamat Email</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $email }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Peran (Role)</p>
                <p class="font-semibold capitalize text-gray-800 dark:text-gray-100">
                    {{ optional($pegawai->user)->role ?? $pegawai->role ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Jabatan</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $pegawai->jabatan ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Unit Kerja</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $pegawai->unit_kerja ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Nomor Telepon</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $telepon }}</p>
            </div>

            <div>
                <p class="text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ ucfirst($pegawai->status ?? '-') }}</p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-gray-500 dark:text-gray-400">Tanggal Data Dibuat</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">
                    {{ $pegawai->created_at ? $pegawai->created_at->translatedFormat('d F Y H:i') : '-' }}
                </p>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
            <a href="{{ route('admin.pegawai.index') }}"
                class="px-3 py-1 rounded bg-gray-400 text-white hover:bg-gray-500 dark:bg-gray-600 dark:hover:bg-gray-500 transition">
                Kembali
            </a>
            <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}"
                class="px-3 py-1 rounded bg-sky-600 text-white hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 transition">
                Edit
            </a>
        </div>
    </div>
</div>
@endsection

@endsection
