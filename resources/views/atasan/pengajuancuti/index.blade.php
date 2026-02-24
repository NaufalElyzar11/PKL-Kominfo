@extends('layouts.pegawai')

@section('title', 'Pengajuan Cuti Atasan')

@section('content')
@php
    use Illuminate\Pagination\LengthAwarePaginator;
    
    $cutiIsPaginator = isset($cuti) && $cuti instanceof LengthAwarePaginator;
    $riwayatIsPaginator = isset($riwayat) && $riwayat instanceof LengthAwarePaginator;

    $cutiCurrent = $cutiIsPaginator ? $cuti->currentPage() : 1;
    $cutiPerPage = $cutiIsPaginator ? $cuti->perPage() : 10;

    $riwayatCurrent = $riwayatIsPaginator ? $riwayat->currentPage() : 1;
    $riwayatPerPage = $riwayatIsPaginator ? $riwayat->perPage() : 10;

    $pegawai = $pegawai ?? (object)['id' => 1, 'nama' => '-', 'nip' => '-', 'jabatan' => '-', 'kuota_cuti' => 12];
@endphp

<style>
    [x-cloak] { display: none !important; }
    .flatpickr-day.holiday { background: #fee2e2 !important; color: #ef4444 !important; border-color: #fecaca !important; }
</style>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

<div x-data="{
    tab: 'menunggu',
    showModal: false, 
    showEditModal: false,
    showDetailPending: false, 
    showDetailRiwayat: false, 

    selectedCuti: {},
    isChanged: false,

    jenisCutiTambah: '',
    alasanCutiTambah: '',
    
    detailPending: {}, 
    detailRiwayat: {},
    hasPendingCuti: @json($hasPendingCuti ?? false),
    sisaCutiTersedia: @json($sisaCuti ?? 12),

    holidays: [],
    holidaysLoaded: false,

    // FORM TAMBAH
    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,

    async loadHolidays() {
        if (this.holidaysLoaded) return;
        try {
            const year = new Date().getFullYear();
            const response = await fetch(`https://dayoffapi.vercel.app/api?year=${year}`);
            const data = await response.json();
            this.holidays = data.map(h => ({ date: h.tanggal, desc: h.keterangan }));
            this.holidaysLoaded = true;
        } catch (error) { this.holidays = []; this.holidaysLoaded = true; }
    },

    calculateWorkingDays(start, end) {
        if (!start || !end) return 0;
        let count = 0;
        let current = new Date(start);
        const endDate = new Date(end);
        while (current <= endDate) {
            const dateStr = current.toLocaleDateString('en-CA');
            const isHoliday = this.holidays.some(h => h.date === dateStr);
            if (current.getDay() !== 0 && current.getDay() !== 6 && !isHoliday) count++;
            current.setDate(current.getDate() + 1);
        }
        return count;
    },

    async hitungHariTambah() {
        if (!this.tanggalMulaiTambah || !this.tanggalSelesaiTambah) { this.jumlahHariTambah = 0; return; }
        if (!this.holidaysLoaded) await this.loadHolidays();
        this.jumlahHariTambah = this.calculateWorkingDays(this.tanggalMulaiTambah, this.tanggalSelesaiTambah);
    },

    openEditModal(data) {
        this.selectedCuti = { ...data };
        this.showEditModal = true;
        this.isChanged = false;
    },

    async hitungHariEdit() {
        if (!this.selectedCuti.tanggal_mulai || !this.selectedCuti.tanggal_selesai) return;
        if (!this.holidaysLoaded) await this.loadHolidays();
        this.selectedCuti.jumlah_hari = this.calculateWorkingDays(this.selectedCuti.tanggal_mulai, this.selectedCuti.tanggal_selesai);
        this.isChanged = true;
    },

    showPendingDetail(data) { 
        this.detailPending = { ...data };
        this.showDetailPending = true; 
    }
}" x-init="loadHolidays()" class="space-y-4 font-sans text-gray-800">

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Daftar Pengajuan Cuti Atasan</h2>
            <button @click="showModal = true" class="text-white bg-green-600 hover:bg-green-700 text-xs px-2 py-1 rounded-md flex items-center gap-1 shadow-sm transition">
                <i class="fa-solid fa-plus-circle text-[10px]"></i>
                <span>Ajukan Cuti</span>
            </button>
        </div>

        {{-- TABS --}}
        <div class="flex gap-4 mb-4 border-b border-gray-200">
            <button @click="tab='menunggu'" :class="tab==='menunggu' ? 'text-sky-600 border-b-2 border-sky-600 font-semibold' : 'text-gray-500 hover:text-sky-500'" class="py-2 px-1 text-sm transition">Menunggu Persetujuan</button>
            <button @click="tab='riwayat'" :class="tab==='riwayat' ? 'text-sky-600 border-b-2 border-sky-600 font-semibold' : 'text-gray-500 hover:text-sky-500'" class="py-2 px-1 text-sm transition">Riwayat Cuti</button>
        </div>

        {{-- TAB MENUNGGU --}}
        <div x-show="tab === 'menunggu'" class="space-y-2">
            <div class="overflow-x-auto rounded border border-gray-300">
                <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                    <thead class="bg-sky-600 text-white">
                        <tr>
                            <th class="px-1 py-1 text-center font-semibold">No</th>
                            <th class="px-1 py-1 font-semibold text-left">Nama</th>
                            <th class="px-1 py-1 font-semibold text-left">Jenis</th>
                            <th class="px-1 py-1 font-semibold text-left">Tanggal</th>
                            <th class="px-1 py-1 text-center font-semibold">Hari</th>
                            <th class="px-1 py-1 text-center font-semibold">Status</th>
                            <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($cuti as $index => $c)
                            @php $no = ($cutiCurrent - 1) * $cutiPerPage + $index + 1; @endphp
                            <tr class="hover:bg-gray-50 text-gray-700">
                                <td class="px-1 py-2 text-center">{{ $no }}</td>
                                <td class="px-1 py-2">{{ $c->pegawai->nama ?? '-' }}</td>
                                <td class="px-1 py-2">{{ $c->jenis_cuti }}</td>
                                <td class="px-1 py-2">{{ $c->tanggal_mulai->format('d/m/Y') }} - {{ $c->tanggal_selesai->format('d/m/Y') }}</td>
                                <td class="px-1 py-2 text-center font-bold">{{ $c->jumlah_hari }}</td>
                                <td class="px-1 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded-full {{ $c->status == 'Menunggu' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }} text-[10px] font-bold">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                {{-- POINT B: KOLOM AKSI DENGAN EDIT & HAPUS --}}
                                <td class="px-1 py-2 text-center flex justify-center gap-1">
                                    <button @click="showPendingDetail({
                                        nama: '{{ $c->pegawai->nama }}', 
                                        nip: '{{ $c->pegawai->nip }}', 
                                        jabatan: '{{ $c->pegawai->jabatan }}',
                                        jenis_cuti: '{{ $c->jenis_cuti }}', 
                                        tanggal_mulai: '{{ $c->tanggal_mulai->format('d/m/Y') }}',
                                        tanggal_selesai: '{{ $c->tanggal_selesai->format('d/m/Y') }}', 
                                        jumlah_hari: {{ $c->jumlah_hari }},
                                        alasan_cuti: '{{ $c->keterangan }}',
                                        status: '{{ $c->status }}'
                                    })" class="p-1 text-sky-600 hover:bg-sky-50 rounded">
                                        <i class="fa-solid fa-eye text-[12px]"></i>
                                    </button>

                                    @if($c->status == 'Menunggu')
                                    <button @click="openEditModal({
                                        id: '{{ $c->id }}',
                                        jenis_cuti: '{{ $c->jenis_cuti }}',
                                        tanggal_mulai: '{{ $c->tanggal_mulai->format('Y-m-d') }}',
                                        tanggal_selesai: '{{ $c->tanggal_selesai->format('Y-m-d') }}',
                                        alasan_cuti: '{{ $c->keterangan }}',
                                        jumlah_hari: '{{ $c->jumlah_hari }}'
                                    })" class="p-1 text-orange-600 hover:bg-orange-50 rounded">
                                        <i class="fa-solid fa-pen-to-square text-[12px]"></i>
                                    </button>
                                    @endif

                                    <form action="{{ route('atasan.cuti.destroy', $c->id) }}" method="POST" class="form-delete inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" data-nama="{{ $c->pegawai->nama }}" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <i class="fa-solid fa-trash text-[12px]"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-2 py-4 text-center text-gray-500 italic">Tidak ada pengajuan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =====================================
        MODAL AJUKAN CUTI
    ===================================== --}}
    <template x-if="showModal">
        <div
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
            @click.self="showModal = false"
            x-cloak
            x-transition
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-xl overflow-hidden border border-gray-100"
                @click.stop
                x-transition
            >
                {{-- HEADER --}}
                <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fa-solid fa-calendar-plus text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-base tracking-wide">Ajukan Cuti</h3>
                                <p class="text-sky-100 text-[10px]">Pengajuan langsung ke Pejabat</p>
                            </div>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                            <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                        </button>
                    </div>
                </div>

                {{-- FORM CONTENT --}}
                <div class="p-4 sm:p-6 max-h-[80vh] overflow-y-auto">
                    <form action="{{ route('atasan.cuti.store') }}" method="POST">
                        @csrf

                        {{-- WARNING PENDING --}}
                        <div x-show="hasPendingCuti" x-transition
                             class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-clock text-amber-600"></i>
                            </div>
                            <div class="text-[11px]">
                                <p class="font-bold text-amber-800">Pengajuan Pending</p>
                                <p class="text-amber-700">Ada pengajuan yang masih menunggu persetujuan.</p>
                            </div>
                        </div>

                        <fieldset :disabled="hasPendingCuti" class="space-y-4">
                            {{-- INFO PEGAWAI --}}
                            <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fa-solid fa-user-tie text-sky-600 text-sm"></i>
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div>
                                        <p class="text-gray-400">Nama</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">NIP</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->nip ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Jabatan</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->jabatan ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Pejabat Pemberi Cuti</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->pemberi_cuti ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- JENIS CUTI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-tag text-sky-500 text-[10px]"></i>
                                    Jenis Cuti <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                <select name="jenis_cuti" 
                                        x-model="jenisCutiTambah" 
                                        {{-- LOGIKA: Jika pilih Tahunan, set alasan otomatis ke 'Hak ASN' --}}
                                        @change="if(jenisCutiTambah === 'Tahunan') { alasanCutiTambah = 'Hak ASN' } else { alasanCutiTambah = '' }"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] font-medium text-gray-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition-all appearance-none"
                                        required>
                                    <option value="" disabled selected>— Pilih jenis cuti —</option>
                                    <option value="Tahunan">Cuti Tahunan</option>
                                    <option value="Alasan Penting">Cuti Alasan Penting</option>
                                </select>
                                    
                                    {{-- Ikon Panah Kecil --}}
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-[9px]"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- TANGGAL (MODIFIKASI TAMPILAN SESUAI GAMBAR) --}}
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-bold text-gray-700">
                                    <i class="fa-solid fa-calendar-days text-sky-500"></i>
                                    Periode Cuti <span class="text-red-500">*</span>
                                </label>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- TANGGAL MULAI --}}
                                    <div class="relative group">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10 pointer-events-none">
                                            <i class="fa-regular fa-calendar text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                        </div>
                                        <input type="text" 
                                            name="tanggal_mulai" 
                                            x-model="tanggalMulaiTambah" 
                                            x-ref="tglMulai"
                                            placeholder="Tanggal Mulai"
                                            class="w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                            required readonly>
                                    </div>

                                    {{-- TANGGAL SELESAI --}}
                                    <div class="relative group">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10 pointer-events-none">
                                            <i class="fa-regular fa-calendar-check text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                        </div>
                                        <input type="text" 
                                            name="tanggal_selesai" 
                                            x-model="tanggalSelesaiTambah" 
                                            x-ref="tglSelesai"
                                            placeholder="Tanggal Selesai"
                                            :disabled="!tanggalMulaiTambah"
                                            :class="!tanggalMulaiTambah ? 'bg-gray-50 cursor-not-allowed opacity-60' : 'bg-white cursor-pointer'"
                                            class="w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                            required readonly>
                                    </div>
                                </div>

                                {{-- KETERANGAN / HELPER TEXT --}}
                                <p class="text-[9px] sm:text-[10px] text-gray-400 flex items-center gap-1.5 mt-1">
                                    <i class="fa-solid fa-circle-info text-gray-300"></i>
                                    Tanggal merah & akhir pekan otomatis dilewati
                                </p>
                            </div>

                            {{-- Watcher Logic untuk Inisialisasi Kalender --}}
                            <div x-effect="
                                if(showModal && holidaysLoaded) {
                                    const config = {
                                        locale: 'id',
                                        dateFormat: 'Y-m-d',
                                        disable: [
                                            function(date) { 
                                                // Matikan Sabtu (6) dan Minggu (0)
                                                return (date.getDay() === 0 || date.getDay() === 6); 
                                            },
                                            ...holidays.map(h => h.date) // Matikan Libur Nasional dari API
                                        ],
                                        onDayCreate: (dObj, dStr, fp, dayElem) => {
                                            const dateStr = dayElem.dateObj.toLocaleDateString('en-CA');
                                            if (holidays.some(h => h.date === dateStr)) {
                                                dayElem.classList.add('holiday');
                                            }
                                        }
                                    };

                                    // Init Mulai
                                    flatpickr($refs.tglMulai, {
                                        ...config,
                                        minDate: 'today', // <--- BISA PILIH HARI INI
                                        onChange: (sel, dateStr) => {
                                            tanggalMulaiTambah = dateStr;
                                            tanggalSelesaiTambah = ''; // Reset selesai jika mulai berubah
                                            if($refs.tglSelesai._flatpickr) $refs.tglSelesai._flatpickr.set('minDate', dateStr);
                                            hitungHariTambah();
                                        }
                                    });

                                    // Init Selesai
                                    flatpickr($refs.tglSelesai, {
                                        ...config,
                                        onChange: (sel, dateStr) => {
                                            tanggalSelesaiTambah = dateStr;
                                            hitungHariTambah();
                                        }
                                    });
                                }
                            "></div>

                            {{-- ALASAN --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-pen text-sky-500 text-[10px]"></i>
                                    Alasan Cuti <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="keterangan" 
                                    rows="2" 
                                    required
                                    {{-- 1. Hubungkan ke variabel Alpine --}}
                                    x-model="alasanCutiTambah"
                                    {{-- 2. Kunci input jika jenis cuti adalah Tahunan --}}
                                    :readonly="jenisCutiTambah === 'Tahunan'"
                                    {{-- 3. Beri warna abu-abu jika terkunci --}}
                                    :class="jenisCutiTambah === 'Tahunan' ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                    {{-- 4. Filter hanya huruf dan spasi --}}
                                    @input="alasanCutiTambah = $event.target.value.replace(/[^A-Za-z\s]/g, '')"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none resize-none"
                                    placeholder="Jelaskan alasan pengajuan cuti..."></textarea>
                            </div>
                        </fieldset>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2.5 text-[11px] font-semibold text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-100 transition-all">
                                Batal
                            </button>
                            <button type="submit" :disabled="hasPendingCuti"
                                class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white text-[11px] font-bold rounded-xl shadow-lg shadow-sky-200 hover:shadow-xl hover:from-sky-600 hover:to-blue-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <i class="fa-solid fa-paper-plane"></i>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
{{-- MODAL EDIT CUTI --}}
<template x-if="showEditModal">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-orange-500 to-amber-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Pengajuan
                </h3>
                <button @click="showEditModal = false"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6">
                <form :action="'/atasan/cuti/' + selectedCuti.id" method="POST">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" x-model="selectedCuti.tanggal_mulai" @change="hitungHariEdit()" 
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-orange-100 outline-none" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" x-model="selectedCuti.tanggal_selesai" @change="hitungHariEdit()" 
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-orange-100 outline-none" required>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-orange-600 bg-orange-50 p-2 rounded-lg border border-orange-100">
                            <i class="fa-solid fa-calendar-check text-[10px]"></i>
                            <span class="text-[11px] font-bold">Durasi Baru: <span x-text="selectedCuti.jumlah_hari"></span> Hari Kerja</span>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Alasan Revisi</label>
                            <textarea name="keterangan" x-model="selectedCuti.alasan_cuti" @input="isChanged = true" 
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-orange-100 outline-none resize-none" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors">Batal</button>
                        <button type="submit" :disabled="!isChanged" 
                            class="px-6 py-2 bg-orange-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-orange-200 disabled:opacity-50 disabled:grayscale transition-all hover:bg-orange-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


{{-- MODAL DETAIL PENDING --}}
<template x-if="showDetailPending">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100" @click.outside="showDetailPending = false">
            <div class="bg-sky-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-circle-info"></i> Detail Pengajuan Cuti
                </h3>
                <button @click="showDetailPending = false" class="hover:rotate-90 transition-transform"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase font-bold tracking-widest text-[9px]">Nama Pegawai</p>
                        <p class="font-bold text-gray-800" x-text="detailPending.nama"></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase font-bold tracking-widest text-[9px]">NIP</p>
                        <p class="font-semibold text-gray-800" x-text="detailPending.nip"></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase font-bold tracking-widest text-[9px]">Jenis Cuti</p>
                        <p class="font-bold text-sky-600" x-text="detailPending.jenis_cuti"></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase font-bold tracking-widest text-[9px]">Jumlah Hari</p>
                        <p class="font-bold text-gray-800"><span x-text="detailPending.jumlah_hari"></span> Hari</p>
                    </div>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Periode Cuti</p>
                    <p class="text-xs font-bold text-gray-700">
                        <span x-text="detailPending.tanggal_mulai"></span> s/d <span x-text="detailPending.tanggal_selesai"></span>
                    </p>
                </div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Alasan Cuti</p>
                    <p class="text-xs text-gray-700 italic bg-sky-50 p-3 rounded-xl border border-sky-100" x-text="detailPending.alasan_cuti"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button @click="showDetailPending = false" class="px-5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-all">Tutup</button>
            </div>
        </div>
    </div>
</template>


</div>

<script>
document.addEventListener('submit', function (e) {
    if (e.target.classList.contains('form-delete')) {
        e.preventDefault();
        const form = e.target;
        const nama = form.querySelector('button').dataset.nama;
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: `Cuti Anda akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }
});
</script>
