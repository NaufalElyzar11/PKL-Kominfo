@extends('layouts.kepala-dinas')

@section('title', 'Daftar Pegawai')

@section('content')
<div class="min-h-screen px-4 py-6 bg-[#E3F2FD]"
     x-data="{
        showDetailModal: false,
        selectedPegawai: {},

        openDetail(el) {
            this.selectedPegawai = {
                nama: el.dataset.nama,
                nip: el.dataset.nip,
                jabatan: el.dataset.jabatan,
                unit_kerja: el.dataset.unit,
                role: el.dataset.role,
                telepon: el.dataset.telepon,
                email: el.dataset.email,
                jatah_cuti: el.dataset.jatah,
                kuota_cuti: el.dataset.kuota,
                status: el.dataset.status,
            };
            this.showDetailModal = true;
        },

        closeModal() {
            this.showDetailModal = false;
        }
     }" x-cloak>

    {{-- ALERT NOTIF --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-lg flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif


    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-md p-5 border border-gray-200 space-y-4">

        {{-- TITLE --}}
        <h1 class="text-lg font-bold text-sky-700 mb-2">Daftar Pegawai</h1>

        {{-- FILTER --}}
        <form method="GET" action="{{ route('kepaladinas.datapegawai.index') }}"
              class="flex flex-wrap items-center gap-2 text-xs">

            <input type="text" name="nama" placeholder="Cari Nama..."
                   value="{{ request('nama') }}"
                   class="px-2 py-1 border border-gray-300 rounded-md w-40 focus:ring-1 focus:ring-blue-400">

            <input type="text" name="nip" placeholder="Cari NIP..."
                   value="{{ request('nip') }}"
                   class="px-2 py-1 border border-gray-300 rounded-md w-40 focus:ring-1 focus:ring-blue-400">

            <button type="submit"
                    class="px-3 py-1 bg-[#039BE5] text-white rounded-md hover:bg-[#0288D1]">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>

            @if(request('nama') || request('nip'))
                <a href="{{ route('kepaladinas.datapegawai.index') }}"
                   class="px-3 py-1 bg-red-400 text-white rounded-md hover:bg-red-500">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                </a>
            @endif
        </form>


        {{-- TABLE --}}
        <div class="overflow-x-auto overflow-y-auto max-h-[420px] text-xs mt-2 border border-gray-200 rounded-lg shadow-sm">
            <table class="w-full border-collapse bg-white">
                <thead class="bg-gradient-to-r from-sky-500 to-sky-700 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-1 border">No</th>
                        <th class="px-2 py-1 border">Nama</th>
                        <th class="px-2 py-1 border">NIP</th>
                        <th class="px-2 py-1 border">Jabatan</th>
                        <th class="px-2 py-1 border">Unit Kerja</th>
                        <th class="px-2 py-1 border">Role</th>
                        <th class="px-2 py-1 border">Telepon</th>
                        <th class="px-2 py-1 border">Status</th>
                        <th class="px-2 py-1 border text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pegawai as $i => $p)

                        @php
                            $nip = $p->nip ?? '-';
                            $nipMasked = strlen($nip) > 6
                                ? substr($nip, 0, 3) . str_repeat('*', strlen($nip) - 6) . substr($nip, -3)
                                : $nip;

                            $teleponMasked = $p->telepon
                                ? substr($p->telepon, 0, 3) . str_repeat('*', max(0, strlen($p->telepon) - 6))
                                : '-';

                            $role = $p->user?->role ?? '-';
                        @endphp

                        <tr class="border hover:bg-gray-50 cursor-pointer"
                            data-nama="{{ $p->nama }}"
                            data-nip="{{ $p->nip }}"
                            data-jabatan="{{ $p->jabatan }}"
                            data-unit="{{ $p->unit_kerja }}"
                            data-role="{{ $role }}"
                            data-telepon="{{ $p->telepon }}"
                            data-email="{{ $p->user?->email }}"
                            data-jatah="{{ $p->jatah_cuti }}"
                            data-kuota="{{ $p->kuota_cuti }}"
                            data-status="{{ $p->status }}"
                        >

                            <td class="px-2 py-1 border text-center">{{ $i + 1 }}</td>
                            <td class="px-2 py-1 border font-medium">{{ $p->nama }}</td>
                            <td class="px-2 py-1 border font-mono">{{ $nipMasked }}</td>
                            <td class="px-2 py-1 border">{{ $p->jabatan }}</td>
                            <td class="px-2 py-1 border">{{ $p->unit_kerja }}</td>
                            <td class="px-2 py-1 border text-center">{{ $role }}</td>
                            <td class="px-2 py-1 border text-center">{{ $teleponMasked }}</td>

                            <td class="px-2 py-1 border text-center">
                                <span class="px-2 py-1 rounded-full text-[11px]
                                    {{ $p->status === 'aktif'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>

                            <td class="px-2 py-1 border text-center">
                                <button type="button"
                                        @click="openDetail($el.closest('tr'))"
                                        class="text-blue-600 hover:text-blue-800 text-xs"
                                        title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-gray-500">
                                Tidak ada data pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $pegawai->links() }}
        </div>
    </div>


{{-- ================= MODAL DETAIL ================= --}}
<div x-show="showDetailModal" x-cloak 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
     @click.self="closeModal()">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-4 max-h-[85vh] overflow-y-auto"
         @click.away="closeModal()">

        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h3 class="text-sm font-bold text-sky-700 flex items-center gap-2">
                <i class="fa-solid fa-user-gear"></i>
                Detail Pegawai
            </h3>
            <button @click="closeModal()" class="text-gray-500 hover:text-gray-700 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-700">

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Nama</p>
                <p x-text="selectedPegawai.nama" class="font-medium"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">NIP</p>
                <p x-text="selectedPegawai.nip || '-'" class="font-medium"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Email</p>
                <p x-text="selectedPegawai.email || '-'" class="font-medium break-all"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Role Akun</p>
                <p x-text="selectedPegawai.role || '-'" class="font-medium capitalize"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Jabatan</p>
                <p x-text="selectedPegawai.jabatan || '-'" class="font-medium"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Unit Kerja</p>
                <p x-text="selectedPegawai.unit_kerja || '-'" class="font-medium"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Telepon</p>
                <p x-text="selectedPegawai.telepon || '-'" class="font-medium"></p>
            </div>

            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Status</p>
                <span x-text="selectedPegawai.status || '-'"
                      class="font-medium capitalize px-2 py-0.5 rounded-full text-[10px]"
                      :class="selectedPegawai.status === 'aktif'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'">
                </span>
            </div>

        </div>

        <div class="mt-4 text-right border-t pt-3">
            <button @click="closeModal()" 
                    class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                Tutup
            </button>
        </div>

    </div>
</div>

</div>
@endsection
