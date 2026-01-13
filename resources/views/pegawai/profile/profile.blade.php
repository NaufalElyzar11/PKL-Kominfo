@extends('layouts.pegawai')

@section('title', 'Profil Pegawai')

@section('content')
<div class="flex flex-col items-center space-y-8">

    @php
        // Nama pegawai (fallback ke user->name)
        $namaPegawai = $pegawai->nama ?? $user->name ?? 'Pegawai';
    @endphp

    {{-- 🌟 Header Profil --}}
    <div class="flex flex-col items-center space-y-3">
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 
                    flex items-center justify-center text-white text-2xl font-bold shadow">
            {{ strtoupper(substr($namaPegawai, 0, 1)) }}
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ $namaPegawai }}</h1>
        <p class="text-sm text-gray-500">Pegawai</p>

    </div>

    {{-- 💼 DATA AKUN --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 w-[70%]">
        <h2 class="text-base font-semibold text-sky-700 border-b pb-2 mb-3">Data Akun</h2>

        <table class="w-full text-sm text-gray-800 text-left border-collapse">
            <tbody class="divide-y divide-gray-100">

                {{-- Email (Privasi) --}}
<tr>
    <td class="px-3 py-2 font-medium w-1/3">Email</td>
    <td class="px-3 py-2">

        @php
            $email = $pegawai->email ?? $user->email ?? '-';
            $emailMasked = $email;

            if ($email !== '-' && str_contains($email, '@')) {
                [$namePart, $domain] = explode('@', $email);

                // Jika username <= 2 karakter
                if (strlen($namePart) <= 2) {
                    $maskedName = substr($namePart, 0, 1) . '*';
                } else {
                    $maskedName = substr($namePart, 0, 2) . str_repeat('*', strlen($namePart) - 2);
                }

                $emailMasked = $maskedName . '@' . $domain;
            }
        @endphp

        {{ $emailMasked }}

    </td>
</tr>


                {{-- Role --}}
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">Role</td>
                    <td class="px-3 py-2">{{ ucfirst($user->role) }}</td>
                </tr>

                {{-- Tanggal Bergabung --}}
                <tr>
                    <td class="px-3 py-2 font-medium">Tanggal Bergabung</td>
                    <td class="px-3 py-2">
                        {{ $user->created_at->translatedFormat('d F Y') }}
                    </td>
                </tr>

                {{-- Terakhir Diperbarui --}}
                <tr class="bg-sky-50">
                    <td class="px-3 py-2 font-medium">Terakhir Diperbarui</td>
                    <td class="px-3 py-2">
                        {{ $user->updated_at->translatedFormat('d F Y, H:i') }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- 👤 INFORMASI PEGAWAI --}}
<div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 w-[70%]">
    <h2 class="text-base font-semibold text-sky-700 border-b pb-2 mb-3">Informasi Pegawai</h2>

    <table class="w-full text-sm text-gray-800 text-left border-collapse">
        <tbody class="divide-y divide-gray-100">

            {{-- Nama Lengkap --}}
            <tr>
                <td class="px-3 py-2 font-medium w-1/3">Nama Lengkap</td>
                <td class="px-3 py-2">{{ $pegawai->nama ?? '-' }}</td>
            </tr>

            {{-- Jabatan --}}
            <tr class="bg-sky-50">
                <td class="px-3 py-2 font-medium">Jabatan</td>
                <td class="px-3 py-2">{{ $pegawai->jabatan ?? '-' }}</td>
            </tr>

            {{-- Unit Kerja --}}
            <tr>
                <td class="px-3 py-2 font-medium">Unit Kerja</td>
                <td class="px-3 py-2">{{ $pegawai->unit_kerja ?? '-' }}</td>
            </tr>

            {{-- NIP (Privasi) --}}
            <tr class="bg-sky-50">
                <td class="px-3 py-2 font-medium">NIP</td>
                <td class="px-3 py-2">
                    {{ $pegawai->nip ? substr($pegawai->nip, 0, 5) . '*****' : '-' }}
                </td>
            </tr>

            {{-- No Telepon (Privasi) --}}
            <tr>
                <td class="px-3 py-2 font-medium">No Telepon</td>
                <td class="px-3 py-2">
                    {{ $pegawai->telepon ? substr($pegawai->telepon, 0, 5) . '*****' : '-' }}
                </td>
            </tr>

        </tbody>
    </table>
</div>

</div>
@endsection
