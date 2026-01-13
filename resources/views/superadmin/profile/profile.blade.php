@extends('layouts.super-admin')

@section('title', 'Profil Saya')

@section('content')
@php
    $user = Auth::user();
    $pegawai = optional($user->pegawai); // jika user tidak punya relasi pegawai
@endphp

<div class="flex flex-col items-center space-y-8">

    {{-- 🌟 Header Profil --}}
    <div class="flex flex-col items-center space-y-3">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 flex items-center justify-center text-white text-2xl font-bold shadow">
            {{ strtoupper(substr($pegawai->nama ?? $user->name ?? 'U', 0, 1)) }}
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ $pegawai->nama ?? $user->name ?? '-' }}</h1>
        <p class="text-sm text-gray-500">{{ ucfirst($user->role ?? 'Super Admin') }}</p>
    </div>

    {{-- 💼 Data Akun --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 w-[70%]">
        <h2 class="text-base font-semibold text-sky-700 border-b pb-2 mb-3">Data Akun</h2>
        <table class="w-full text-sm text-gray-800 text-left border-collapse">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="px-3 py-2 font-medium w-1/3">Email</td>
                    <td class="px-3 py-2">
                        @php
                            $email = $user->email ?? null;
                            if ($email && str_contains($email, '@')) {
                                [$name, $domain] = explode('@', $email);
                                $hiddenName = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 0));
                                $email = $hiddenName . '@' . $domain;
                            }
                        @endphp
                        {{ $email ?? '-' }}
                    </td>
                </tr>
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">Role</td>
                    <td class="px-3 py-2">{{ ucfirst($user->role ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 font-medium">Tanggal Bergabung</td>
                    <td class="px-3 py-2">
                        {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">Terakhir Diperbarui</td>
                    <td class="px-3 py-2">
                        {{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 👤 Informasi Pribadi --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 w-[70%]">
        <h2 class="text-base font-semibold text-sky-700 border-b pb-2 mb-3">Informasi Pribadi</h2>
        <table class="w-full text-sm text-gray-800 text-left border-collapse">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="px-3 py-2 font-medium w-1/3">Nama Lengkap</td>
                    <td class="px-3 py-2">{{ $pegawai->nama ?? $user->name ?? '-' }}</td>
                </tr>
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">Jabatan</td>
                    <td class="px-3 py-2">{{ $pegawai->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 font-medium">Unit Kerja</td>
                    <td class="px-3 py-2">{{ $pegawai->unit_kerja ?? '-' }}</td>
                </tr>
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">NIP</td>
                    <td class="px-3 py-2">
                        {{ $pegawai->nip ? substr($pegawai->nip, 0, 4) . str_repeat('*', max(strlen($pegawai->nip) - 4, 0)) : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="px-3 py-2 font-medium">No. Telepon</td>
                    <td class="px-3 py-2">
                        {{ $pegawai->telepon ? substr($pegawai->telepon, 0, 4) . str_repeat('*', max(strlen($pegawai->telepon) - 4, 0)) : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection
