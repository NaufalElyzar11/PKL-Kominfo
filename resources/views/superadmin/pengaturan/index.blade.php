@extends('layouts.super-admin')

@section('title', 'Pengaturan Sistem')
@section('breadcrumb', 'Pengaturan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- 🌟 Pengaturan Umum --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-700">

        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-gears text-sky-600"></i> Pengaturan Umum
        </h2>

        {{-- 🔔 Notifikasi --}}
        <div id="notif"
            class="hidden mb-4 p-2 rounded-md text-xs border border-green-300 text-green-700 bg-green-100 font-medium flex items-center gap-2">
            <i class="fa-solid fa-check-circle"></i> Perubahan berhasil disimpan
        </div>

        <form id="pengaturanForm" class="space-y-4" autocomplete="off">
            @csrf

            {{-- 🏷 Nama Aplikasi --}}
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                    Nama Aplikasi
                </label>
                <input type="text" name="nama_aplikasi" id="nama_aplikasi"
                       value="{{ $settings['nama_aplikasi'] ?? '' }}"
                       class="w-full h-9 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md
                              focus:ring-sky-500 focus:border-sky-500 text-xs px-2.5 shadow-sm">
            </div>

            {{-- 🎨 Tema --}}
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                    Tema Tampilan
                </label>
                <select name="tema" id="tema"
                        class="w-full h-9 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md
                               focus:ring-sky-500 focus:border-sky-500 text-xs px-2.5 shadow-sm">
                    <option value="default" {{ ($settings['tema'] ?? '') == 'default' ? 'selected' : '' }}>Default</option>
                    <option value="gelap" {{ ($settings['tema'] ?? '') == 'gelap' ? 'selected' : '' }}>Gelap</option>
                    <option value="terang" {{ ($settings['tema'] ?? '') == 'terang' ? 'selected' : '' }}>Terang</option>
                </select>
            </div>

        </form>

        {{-- 💾 Tombol Simpan --}}
        <div class="flex justify-end pt-2">
            <button id="btnSave"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-md flex items-center gap-1">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>
    </div>

    {{-- ℹ️ Informasi --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-sky-600"></i> Informasi Aplikasi
        </h2>

        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2.5">
            <li class="flex justify-between">
                <span class="font-medium text-gray-600 dark:text-gray-300">Nama Aplikasi:</span>
                <span id="infoNama" class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $settings['nama_aplikasi'] ?? 'Sistem Manajemen' }}
                </span>
            </li>
            <li class="flex justify-between">
                <span class="font-medium text-gray-600 dark:text-gray-300">Versi:</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $settings['versi'] ?? '1.0.0' }}
                </span>
            </li>
        </ul>
    </div>

</div>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notif = document.getElementById('notif');
    const infoNama = document.getElementById('infoNama');
    const btnSave = document.getElementById('btnSave');
    const fields = ['nama_aplikasi', 'tema'];

    function showNotif() {
        notif.classList.remove('hidden');
        notif.style.opacity = "1";

        setTimeout(() => {
            notif.style.opacity = "0";
            setTimeout(() => notif.classList.add('hidden'), 300);
        }, 1300);
    }

    function saveSetting(field, value) {
        fetch("{{ route('super.pengaturan.autosave') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ [field]: value }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (field === 'nama_aplikasi') infoNama.textContent = value;
                showNotif();
            }
        });
    }

    fields.forEach(field => {
        const el = document.getElementById(field);
        el.addEventListener('change', () => saveSetting(field, el.value));
    });

    btnSave.addEventListener('click', () => {
        fields.forEach(field => saveSetting(field, document.getElementById(field).value));
    });
});
</script>
@endsection
