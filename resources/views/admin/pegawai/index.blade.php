@extends('layouts.admin')

@section('title', 'Data Pegawai & Pengajuan Cuti')

@section('content')
{{-- Root x-data container (no styling) --}}
<div x-data="{

// =========================
// Fungsi Buka Modal Edit (SINKRON)
// =========================
openEditModal(pegawai) {
    // 1. Reset state agar tidak ada sisa data sebelumnya
    this.id_atasan_langsung = '';
    this.id_pejabat_pemberi_cuti = '';

    // 2. Kloning data asli
    this.selectedPegawai = JSON.parse(JSON.stringify(pegawai));
    this.originalPegawai = JSON.parse(JSON.stringify(pegawai));

    // 3. Set data dasar (Gunakan trim() untuk mencegah ketidakcocokan karakter)
    const unitAsli = pegawai.unit_kerja ? pegawai.unit_kerja.trim() : '';
    const roleAsli = pegawai.role || pegawai.user?.role;

    // 4. Set Role dan Unit secara bersamaan
    this.role = roleAsli;
    this.unit_kerja = unitAsli;
    this.jabatan = pegawai.jabatan;

    // 5. Gunakan $nextTick untuk sinkronisasi dropdown yang bergantung pada filter unit/role
    this.$nextTick(() => {
        // Paksa kembali unit_kerja jika sempat ter-reset oleh logika lain
        this.unit_kerja = unitAsli;
        
        this.id_atasan_langsung = pegawai.id_atasan_langsung ? String(pegawai.id_atasan_langsung) : '';
        this.id_pejabat_pemberi_cuti = pegawai.id_pejabat_pemberi_cuti ? String(pegawai.id_pejabat_pemberi_cuti) : '';
        
        this.pejabat = (this.role === 'pejabat') ? 'Hj. Erna Lisa Halaby' : (pegawai.pejabat || 'Kanafi, S.IP, MM');
    });

    this.showEditModal = true;
},


    // 1. Status Modal
    showCreateModal: {{ $errors->any() ? 'true' : 'false' }},
    showEditModal: false,
    showDetailModal: false,
    showDelete: false,
    // State untuk edit pegawai
    id_atasan_langsung: '',
    id_pejabat_pemberi_cuti: '',


    // 1. DAFTAR SEMUA UNIT KERJA
    daftarUnit: [
        'Bidang Sekretariat',
        'Bidang Informatika',
        'Bidang Statistik & Persandian',
        'Bidang Komunikasi',
        'Dinas Komunikasi dan Informatika'
    ],

    // 2. GETTER UNTUK FILTER UNIT KERJA
    get filteredUnitKerja() {
        // Jika role adalah pegawai ATAU atasan, sembunyikan unit induk Dinas
        if (this.role === 'pegawai' || this.role === 'atasan') {
            return this.daftarUnit.filter(u => u !== 'Dinas Komunikasi dan Informatika');
        }
        
        // Untuk role Pejabat atau Admin, tampilkan semua (termasuk Dinas)
        return this.daftarUnit;
    },

    // 3. LOGIKA OTOMATIS: Jika Atasan pilih Dinas, otomatis pilihkan Sekretaris
    // Masukkan ini ke dalam tag select Unit Kerja menggunakan @change
    handleUnitChange() {
        if (this.role === 'atasan' && this.unit_kerja === 'Dinas Komunikasi dan Informatika') {
            this.jabatan = 'Sekretaris';
        } else if (this.role === 'atasan') {
            this.jabatan = ''; // Reset jika ganti ke bidang lain agar admin pilih manual
        }
    },


    // 1. DAFTAR JABATAN PER BIDANG (Khusus untuk Role Atasan)
    jabatanMap: {
        'Bidang Sekretariat': ['Kepala Sub Bagian Umum dan Kepegawaian', 'Kepala Sub Bagian Perencanaan dan Keuangan', 'Sekretaris Dinas'],
        'Bidang Informatika': ['Kepala Seksi Pengelolaan Jaringan Komunikasi Data','Kepala Seksi Pengembangan Sistem Informasi dan Website Pemerintah', 'Kepala Bidang Informatika'],
        'Bidang Statistik & Persandian': ['Kepala Seksi Statistik', 'Kepala Seksi Persandian', 'Kepala Bidang Statistik & Persandian'],
        'Bidang Komunikasi': ['Kepala Seksi Pelayanan Informasi', 'Kepala Seksi Komunikasi dan Kelembagaan Informasi Publik','Kepala Bidang Komunikasi'],
        'Dinas Komunikasi dan Informatika': ['Sekretaris Dinas']
    },

    // 2. Variabel Form Tambah (Hanya didefinisikan satu kali agar tidak bentrok)
    nama: '',
    nip: '',
    namaError: false, // <-- Tambahkan ini
    nipError: false,
    atasan: '',      // Terhubung ke select atasan
    jabatan: '',
    unit_kerja: '',  
    role: '',        // Terhubung ke select role
    status: '',
    password: '',
    pejabat: 'Kanafi, S.IP, MM',
    showPassword: false,

    handleJabatanChange() {

    //KADIS KE WALIKOTA
        if (this.jabatan === 'Kepala Dinas') {
            this.atasan = 'Hj. Erna Lisa Halaby';
        } 
        else if (this.jabatan.includes('Kepala Bidang') || this.jabatan === 'Sekretaris Dinas') {
            this.atasan = this.pejabat; // Melapor ke Kepala Dinas (Kanafi, S.IP, MM)
        }

        // Jika Kabid atau Sekretaris, otomatis set atasan ke Pejabat
        if (this.jabatan.includes('Kepala Bidang') || this.jabatan === 'Sekretaris Dinas') {
            this.atasan = this.pejabat; 
        } 
        // Jika posisi di Sekretariat (dan bukan Sekretarisnya), cari nama Sekretarisnya
        else if (this.unit_kerja === 'Bidang Sekretariat') {
            const sekre = this.dataAtasan.find(at => at.jabatan === 'Sekretaris Dinas');
            this.atasan = sekre ? sekre.nama : '';
        } 
        else {
            this.atasan = ''; // Reset untuk pilihan manual
        }
    },

    get hasUpper() {
    return /[A-Z]/.test(this.password)
},

get hasNumber() {
    return /[0-9]/.test(this.password)
},

get hasSymbol() {
    return /[!@#$%^&*(),.?':{}|<>]/.test(this.password)
},

get isLongEnough() {
    return this.password.length >= 8
},

    // 3. Data dari Laravel (Hanya untuk referensi dropdown)
    dataAtasan: {{ Js::from($listAtasan) }},
    pejabatList: {{ Js::from($listPejabat) }},

    // Getter untuk filter daftar nama di dropdown
get filteredAtasan() {
    if (!this.unit_kerja || !this.jabatan) return [];

    // --- KADIS MELAPOR KE WALIKOTA ---
    if (this.jabatan === 'Kepala Dinas') {
        return [{ id: 0, nama: 'Hj. Erna Lisa Halaby' }];
    }

    // --- KABID & SEKRETARIS MELAPOR KE KADIS ---
    if (this.jabatan.includes('Kepala Bidang') || this.jabatan === 'Sekretaris Dinas') {
        const kadis = this.pejabatList.find(p => p.nama.includes('Kanafi'));
        return kadis ? [{ id: kadis.id, nama: kadis.nama }] : [];
    }

    // --- PEGAWAI (STAF) ---
    if (this.role === 'pegawai') {
        return this.dataAtasan.filter(at => {
            // 1. Kondisi Dasar: Atasan (Kasi/Kasubbag) di bidang yang sama
            const isStandardAtasan = at.unit_kerja === this.unit_kerja && 
                                    (at.jabatan.includes('Kepala Seksi') || 
                                     at.jabatan.includes('Kepala Sub Bagian'));

            // 2. KONDISI TAMBAHAN: Munculkan Sekretaris Dinas khusus untuk Bidang Sekretariat
            // Kita tidak cek at.unit_kerja di sini karena Sekretaris Dinas 
            // biasanya terdaftar di unit induk (Dinas), bukan unit Bidang.
            const isSekreForSekretariat = this.unit_kerja === 'Bidang Sekretariat' && 
                                          at.jabatan === 'Sekretaris Dinas';

            return isStandardAtasan || isSekreForSekretariat;
        });
    }

    // --- KASI / KASUBBAG MELAPOR KE KABID ---
    if (this.role === 'atasan') {
        if (this.unit_kerja === 'Bidang Sekretariat') {
            return this.dataAtasan.filter(at => at.jabatan === 'Sekretaris Dinas');
        }
        return this.dataAtasan.filter(at => 
            at.unit_kerja === this.unit_kerja && at.jabatan.includes('Kepala Bidang')
        );
    }

    return [];
},

    // Fungsi tambahan untuk auto-select nama
    handleRoleChange() {
        this.jabatan = '';
        this.atasan = '';

        if (this.role === 'pejabat') {
            this.unit_kerja = 'Dinas Komunikasi dan Informatika'; // Set otomatis
            this.jabatan = 'Kepala Dinas';
            this.atasan = 'Hj. Erna Lisa Halaby';
            this.pejabat = 'Hj. Erna Lisa Halaby';
        } else {
            this.pejabat = 'Kanafi, S.IP, MM';
            
            // Reset unit kerja jika sebelumnya memilih 'Dinas' (unit khusus Pejabat)
            if (this.unit_kerja === 'Dinas Komunikasi dan Informatika') {
                this.unit_kerja = '';
            }
        }
    },

    async checkDuplicate(field, value) {
        if (!value || value.trim().length < 3) {
            field === 'nama' ? this.namaError = false : this.nipError = false;
            return;
        }

        try {
            let response = await fetch('{{ route("admin.pegawai.checkUnique") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    field: field, 
                    value: value,
                    id: this.selectedPegawai ? this.selectedPegawai.id : null 
                })
            });
            let data = await response.json();
            
            if (field === 'nama') this.namaError = data.exists;
            if (field === 'nip') this.nipError = data.exists;
        } catch (error) {
            console.error('Gagal validasi:', error);
        }
    },


    // 5. Logika Validasi Tombol Simpan
    isFormValid() {
        return this.nama.trim() !== '' &&
                !this.namaError && // <-- Tambahkan ini
               !this.nipError &&  // <-- Tambahkan ini
               this.atasan !== '' && 
               this.jabatan.trim() !== '' &&
               this.unit_kerja !== '' &&
               this.role !== '' &&
               this.status !== '' &&
               this.password.length >= 8 &&
               (/[A-Z]/.test(this.password)) && 
               (/[0-9]/.test(this.password)) && 
               (/[!@#$%^&*]/.test(this.password)) && 
               (this.nip.length === 0 || this.nip.length >= 13);
    }, // <-- PENTING: Koma di sini tadi hilang, sekarang sudah ada.

    // 6. Data Pegawai terpilih (Edit/Detail)
    selectedPegawai: null,
    originalPegawai: null,
    editRoute: '{{ route('admin.pegawai.update', ':pegawaiId') }}',

    // =========================
    // Fungsi Buka Modal Tambah
    // =========================
    openCreateModal() {
        // RESET semua input agar benar-benar bersih setiap kali dibuka
        this.nama = '';
        this.nip = '';
        this.atasan = '';    
        this.jabatan = '';
        this.unit_kerja = ''; 
        this.role = '';
        this.status = '';
        this.password = '';
        this.namaError = false; // <-- WAJIB TAMBAHKAN INI
        this.nipError = false;  // <-- WAJIB TAMBAHKAN INI
        
        this.showCreateModal = true;
    },

    isUnchanged() {
        // Jika data belum dimuat, anggap tidak ada perubahan
        if (!this.selectedPegawai || !this.originalPegawai) return true;

        // 1. Cek perubahan pada data dasar (Nama, NIP, Status)
        const basicInfoUnchanged = JSON.stringify(this.selectedPegawai) === JSON.stringify(this.originalPegawai);

        // 2. Cek perubahan pada variabel mandiri (Role, Unit, Jabatan, Atasan, Pejabat)
        // Kita gunakan != (loose equality) karena data ID seringkali berupa angka (int) 
        // sedangkan input select membacanya sebagai teks (string).
        const hierarchyUnchanged = 
            this.role == (this.originalPegawai.role || this.originalPegawai.user?.role) &&
            this.unit_kerja == this.originalPegawai.unit_kerja &&
            this.jabatan == this.originalPegawai.jabatan &&
            this.id_atasan_langsung == this.originalPegawai.id_atasan_langsung &&
            this.id_pejabat_pemberi_cuti == this.originalPegawai.id_pejabat_pemberi_cuti;

        // Tombol Update hanya akan MATI (true) jika SEMUA data masih sama dengan aslinya.
        // Jika salah satu saja berubah, maka isUnchanged menjadi false (Tombol Menyala).
        return basicInfoUnchanged && hierarchyUnchanged;
    },

    openDetailModal(pegawai) {
        this.selectedPegawai = pegawai;
        this.showDetailModal = true;
    },

    closeModal() {
        this.showCreateModal = false;
        this.showEditModal = false;
        this.showDetailModal = false;
        
        // Reset variabel mandiri agar tidak membekas di modal berikutnya
        this.selectedPegawai = null;
        this.role = '';
        this.unit_kerja = '';
        this.id_atasan_langsung = '';
        this.id_pejabat_pemberi_cuti = '';
    }
}" @keydown.escape.window="closeModal()">

{{-- Inner container with background styling --}}
<div class="min-h-screen px-4 py-6 bg-[#E3F2FD]">
    {{-- =============================================== --}}
    {{-- BAGIAN 1: DATA PEGAWAI (Daftar & Filter) --}}
    {{-- =============================================== --}}
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md p-5 border border-gray-200">
        
        {{-- HEADER: JUDUL & TOMBOL TAMBAH --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa-solid fa-users text-sky-600 mr-2"></i>Daftar Data Pegawai
            </h1>
            
            <button @click="openCreateModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
            </button>
        </div>

        {{-- 🔍 FILTER SECTION --}}
        <div class="bg-sky-50 p-4 rounded-xl border border-sky-100 mb-4">
            <form method="GET" action="{{ route('admin.pegawai.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
                
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari Nama / NIP..."
                           class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 outline-none w-full sm:w-64">
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition font-medium">
                    Cari
                </button>

                @if(request('search') || request('unit_kerja'))
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

       {{-- 📑 Table Pegawai --}}
<div>

    <div class="overflow-x-auto overflow-y-auto max-h-[430px] text-xs mt-2 border border-gray-200 rounded-lg shadow-sm">
        <table class="w-full border-collapse bg-white">
            <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
                <tr>
                    <th class="px-2 py-1 border whitespace-nowrap">No</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Nama</th>
                    <th class="px-2 py-1 border whitespace-nowrap">NIP</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden md:table-cell">Email</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Telepon</th>                    
                    <th class="px-2 py-1 border whitespace-nowrap hidden md:table-cell">Role</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Jabatan</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Unit</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Atasan</th>
                    <th class="px-2 py-1 border whitespace-nowrap hidden lg:table-cell">Pemberi Cuti</th>
                    <th class="px-2 py-1 border whitespace-nowrap">Status</th>
                    <th class="px-2 py-1 border text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pegawai as $i => $p)
                    @php
                        $pegawaiData = [
                            'id' => $p->id,
                            'nama' => $p->nama,
                            'nip' => $p->nip,
                            'email' => optional($p->user)->email,
                            'role' => optional($p->user)->role,
                            'jabatan' => $p->jabatan,
                            'unit_kerja' => $p->unit_kerja,
                            'id_atasan_langsung' => $p->id_atasan_langsung,
                            'id_pejabat_pemberi_cuti' => $p->id_pejabat_pemberi_cuti,
                            'atasan' => $p->atasan, 
                            'pejabat' => $p->pemberi_cuti,
                            'telepon' => $p->telepon,
                            'status' => $p->status
                        ];

                        $nip = $p->nip ?? '-';

                        $email = optional($p->user)->email ?? ''; 

                       $telepon = $p->telepon ?? '-';
                    @endphp

                    <tr class="border hover:bg-gray-50 bg-white" data-pegawai='@json($pegawaiData)'>
                        <td class="px-2 py-1 border text-center">{{ $i + $pegawai->firstItem() }}</td>
                        <td class="px-2 py-1 border font-medium">{{ $p->nama }}</td>
                        <td class="px-2 py-1 border font-mono">{{ $nip }}</td>
                        <td class="px-2 py-1 border hidden md:table-cell">{{ $email }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->telepon }}</td>
                        <td class="px-2 py-1 border capitalize hidden md:table-cell">{{ optional($p->user)->role ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $p->jabatan ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->unit_kerja ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->atasan ?? '-' }}</td>
                        <td class="px-2 py-1 border hidden lg:table-cell">{{ $p->pemberi_cuti ?? '-' }}</td>
                        <td class="px-2 py-1 border text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                {{ $p->status === 'aktif' 
                                    ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' 
                                    : 'bg-rose-100 text-rose-700 border border-rose-200' }}">
                                {{ ucfirst($p->status ?? 'nonaktif') }}
                            </span>
                        </td>

                        <td class="px-2 py-1 border text-center whitespace-nowrap">
                            <div class="flex justify-center items-center gap-2">

                                {{-- Tombol Detail --}}
                                <button type="button"
                                    @click="
                                        const d = $el.closest('tr').dataset.pegawai;
                                        if (d) openDetailModal(JSON.parse(d));
                                    "
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Tombol Edit --}}
                                <button type="button"
                                    @click="
                                        const d = $el.closest('tr').dataset.pegawai;
                                        if (d) openEditModal(JSON.parse(d));
                                    "
                                    class="text-yellow-500 hover:text-yellow-700"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                    {{-- Tombol Hapus yang Baru --}}
                            <button type="button" 
                                    onclick="confirmDelete('{{ $p->id }}', '{{ addslashes($p->nama) }}')"
                                    class="text-red-600 hover:text-red-800" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                            {{-- Form Delete Tersembunyi (Sangat Penting: Harus Unik per ID) --}}
                            <form id="delete-form-{{ $p->id }}" 
                                action="{{ route('admin.pegawai.destroy', $p->id) }}" 
                                method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="10" class="text-center py-3 text-gray-500">Belum ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


    </div>
    </div>

    {{-- Container Pagination --}}
    <div class="mt-4 flex items-center justify-end space-x-2 text-[11px] select-none">
        
        {{-- Tombol Sebelumnya (<) --}}
        @if ($pegawai->onFirstPage())
            <span class="px-2 py-1 border rounded bg-gray-50 text-gray-400 cursor-not-allowed">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $pegawai->previousPageUrl() }}" 
            class="px-2 py-1 border rounded hover:bg-gray-100 text-gray-600 transition shadow-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Indikator Halaman (Angka 1) --}}
        <div class="flex items-center">
            <span class="px-3 py-1 bg-blue-600 text-white rounded font-bold shadow-sm">
                {{ $pegawai->currentPage() }}
            </span>
            {{-- Opsional: Menampilkan total halaman --}}
            <span class="ml-2 text-gray-500">dari {{ $pegawai->lastPage() }}</span>
        </div>

        {{-- Tombol Selanjutnya (>) --}}
        @if ($pegawai->hasMorePages())
            <a href="{{ $pegawai->nextPageUrl() }}" 
            class="px-2 py-1 border rounded hover:bg-gray-100 text-gray-600 transition shadow-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="px-2 py-1 border rounded bg-gray-50 text-gray-400 cursor-not-allowed">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </div>

    <hr>

</div>{{-- End of bg-[#E3F2FD] container --}}

{{-- ================= MODAL TAMBAH PEGAWAI (PREMIUM DESIGN) ================= --}}
<template x-if="showCreateModal">
    <div x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="closeModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- ========== HEADER DENGAN GRADIENT ========== --}}
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-user-plus text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Tambah Pegawai</h3>
                            <p class="text-sky-100 text-[10px] sm:text-xs">Isi formulir untuk menambahkan pegawai baru</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                        <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>

            {{-- ========== FORM CONTENT ========== --}}
            <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
                <form action="{{ route('admin.pegawai.store') }}" method="POST" autocomplete="off">
                    @csrf

                    {{-- PESAN ERROR --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 text-[11px] rounded shadow-sm">
                            <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Gagal Menyimpan:</p>
                            <ul class="list-disc ml-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ========== 2-COLUMN LAYOUT ========== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        
                        {{-- ===== KOLOM KIRI ===== --}}
                        <div class="space-y-4">
                            {{-- DATA PEGAWAI SECTION --}}
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-tie text-sky-600 text-sm"></i>
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                    </div>
                                </div>
                                <div class="p-4 space-y-3">
                                    {{-- 1. NAMA LENGKAP --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-user text-sky-500 text-[10px]"></i> Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama" required x-model="nama"
                                        {{-- TAMBAHKAN DUA BARIS DI BAWAH INI --}}
                                        @input.debounce.500ms="checkDuplicate('nama', nama)"
                                        :class="namaError ? 'border-red-500 ring-red-100' : 'border-gray-200 focus:border-sky-400'"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border bg-white text-[11px] sm:text-xs outline-none transition-all duration-200"
                                        placeholder="Nama Pegawai"
                                        oninput="this.value = this.value.replace(/[^a-zA-Z\s.,]/g, '')">
                                    
                                    {{-- TAMBAHKAN TEMPLATE PESAN ERROR INI --}}
                                    <template x-if="namaError">
                                        <p class="text-[10px] text-red-600 font-bold flex items-center gap-1 animate-pulse">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Nama ini sudah terdaftar di sistem!
                                        </p>
                                    </template>

                                    <p x-show="!namaError" class="text-[9px] text-gray-400 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-info"></i> Nama lengkap sesuai data resmi
                                    </p>
                                </div>

                                    {{-- 2. NIP --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-hashtag text-sky-500 text-[10px]"></i> NIP 
                                    </label>
                                    <input type="text" name="nip" x-model="nip" maxlength="18" inputmode="numeric"
                                        {{-- TAMBAHKAN DUA BARIS DI BAWAH INI --}}
                                        @input.debounce.500ms="checkDuplicate('nip', nip)"
                                        :class="nipError ? 'border-red-500 ring-red-100' : 'border-gray-200 focus:border-sky-400'"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border bg-white text-[11px] sm:text-xs outline-none transition-all duration-200"
                                        placeholder="Masukkan 18 digit NIP"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    
                                    {{-- TAMBAHKAN TEMPLATE PESAN ERROR INI --}}
                                    <template x-if="nipError">
                                        <p class="text-[10px] text-red-600 font-bold flex items-center gap-1 animate-pulse">
                                            <i class="fa-solid fa-triangle-exclamation"></i> NIP sudah digunakan oleh pegawai lain!
                                        </p>
                                    </template>

                                    <p x-show="!nipError" class="text-[9px] text-gray-400 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-info"></i> Kosongkan jika belum memiliki NIP
                                    </p>
                                </div>

                                    {{-- 3. ROLE --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-shield-halved text-sky-500 text-[10px] sm:text-xs"></i>
                                            Role <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="role"
                                            x-model="role"
                                            @change="handleRoleChange()"
                                            required
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                           focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                                <option value="" disabled selected>Pilih Role</option>
                                                <option value="atasan">Atasan</option>
                                                <option value="pejabat">Pejabat</option>
                                                <option value="pegawai">Pegawai</option>
                                            </select>
                                        </div>
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Menentukan hak akses pengguna di sistem
                                        </p>
                                    </div>

                                    {{-- 4. STATUS --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-regular fa-circle-check text-sky-500 text-[10px] sm:text-xs"></i>
                                            Status <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="status" required x-model="status"
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                           focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                                <option value="" disabled selected>Pilih Status</option>
                                                <option value="aktif">Aktif</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </div>
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Pegawai nonaktif tidak dapat login ke sistem
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== KOLOM KANAN ===== --}}
                        <div class="space-y-4">
                            {{-- DATA JABATAN SECTION --}}
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building text-sky-600 text-sm"></i>
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Jabatan & Akses</span>
                                    </div>
                                </div>
                                <div class="p-4 space-y-3">
                                    {{-- 5. UNIT KERJA --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-building text-sky-500 text-[10px]"></i>
                                            Unit Kerja <span class="text-red-500">*</span>
                                        </label>
                                        
                                        <div class="relative">
                                            <select :name="role !== 'pejabat' ? 'unit_kerja' : ''" 
                                                    x-model="unit_kerja" 
                                                    :disabled="role === 'pejabat' || !role"
                                                    @change="handleUnitChange()"
                                                    required
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs disabled:bg-gray-100 disabled:cursor-not-allowed focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                                
                                                <option value="" disabled selected x-text="!role ? 'Pilih Role terlebih dahulu' : 'Pilih Unit Kerja'"></option>
                                                
                                                <template x-for="unit in filteredUnitKerja" :key="unit">
                                                    <option :value="unit" x-text="unit"></option>
                                                </template>
                                            </select>
                                        </div>

                                        {{-- Hidden input untuk role Pejabat --}}
                                        <template x-if="role === 'pejabat'">
                                            <input type="hidden" name="unit_kerja" value="Dinas Komunikasi dan Informatika">
                                        </template>
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Isi Role terlebih dahulu untuk memilih unit
                                        </p>
                                    </div>

                                    {{-- 6. JABATAN --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-briefcase text-sky-500 text-[10px]"></i>
                                            Jabatan <span class="text-red-500">*</span>
                                        </label>

                                        {{-- KONDISI 1: Role Pegawai atau Role Belum Dipilih --}}
                                        <template x-if="role === 'pegawai' || role === ''">
                                            <div>
                                                <input type="text" name="jabatan" x-model="jabatan" @change="handleJabatanChange()" required
                                                    :disabled="!role || !unit_kerja"
                                                    :placeholder="!role ? 'Pilih Role dahulu' : (!unit_kerja ? 'Pilih Unit Kerja dahulu' : 'Masukkan Nama Jabatan')"
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 disabled:bg-gray-50 disabled:cursor-not-allowed">
                                                <p class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    Isi Unit Kerja terlebih dahulu
                                                </p>
                                            </div>
                                        </template>

                                        {{-- KONDISI 2: Role Atasan atau Pejabat --}}
                                        <template x-if="role === 'atasan' || role === 'pejabat'">
                                            <div>
                                                <div class="relative">
                                                    <select :name="role !== 'pejabat' ? 'jabatan' : ''" 
                                                            x-model="jabatan" 
                                                            :disabled="role === 'pejabat' || !unit_kerja"
                                                            required
                                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs disabled:bg-gray-100 disabled:cursor-not-allowed focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                                        
                                                        <option value="" disabled selected x-text="role === 'pejabat' ? 'Kepala Dinas' : (!unit_kerja ? 'Pilih Unit Kerja dahulu' : 'Pilih Jabatan Bidang')"></option>
                                                        
                                                        <template x-if="role === 'atasan' && unit_kerja !== ''">
                                                            <template x-for="j in jabatanMap[unit_kerja]" :key="j">
                                                                <option :value="j" x-text="j"></option>
                                                            </template>
                                                        </template>

                                                        <template x-if="role === 'pejabat'">
                                                            <option value="Kepala Dinas" selected>Kepala Dinas</option>
                                                        </template>
                                                    </select>
                                                </div>

                                                {{-- Hidden input agar data tetap terkirim ke database meski select-nya disabled --}}
                                                <template x-if="role === 'pejabat'">
                                                    <input type="hidden" name="jabatan" value="Kepala Dinas">
                                                </template>
                                                <p class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span x-text="role === 'pejabat' ? 'Jabatan otomatis untuk Pejabat' : 'Isi Unit Kerja terlebih dahulu'"></span>
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    {{-- 7. ATASAN LANGSUNG --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-user-tie text-sky-500 text-[10px]"></i>
                                            Atasan Langsung <span class="text-red-500">*</span>
                                        </label>
                                        
                                        <div class="relative">
                                            <select name="atasan" x-model="atasan" 
                                                    :disabled="!unit_kerja || !role || !jabatan"
                                                    required
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs disabled:bg-gray-100 disabled:cursor-not-allowed focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                                
                                                <template x-if="!jabatan">
                                                    <option value="">Pilih Jabatan dahulu</option>
                                                </template>
                                                
                                                <template x-if="jabatan && filteredAtasan.length === 0">
                                                    <option value="">Tidak ada jabatan Atasan yang sesuai di bidang ini</option>
                                                </template>

                                                <template x-if="jabatan && filteredAtasan.length > 0">
                                                    <option value="" disabled selected>Pilih Nama Atasan</option>
                                                </template>

                                                <template x-for="item in filteredAtasan" :key="item.id">
                                                    <option :value="item.nama" x-text="item.nama"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Pejabat yang menyetujui cuti tahap pertama
                                        </p>
                                    </div>

                                    {{-- 8. PEMBERI CUTI --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-stamp text-sky-500 text-[10px]"></i>
                                            Pemberi Cuti
                                        </label>
                                        <input type="text"
                                               name="pejabat"
                                               x-model="pejabat"
                                               readonly
                                               :class="role === 'pejabat' ? 'text-sky-600 font-semibold' : 'text-gray-500'"
                                               class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-gray-100/50 text-[11px] sm:text-xs cursor-not-allowed">
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Pejabat yang memberikan persetujuan akhir cuti
                                        </p>
                                    </div>

                                    {{-- 9. PASSWORD --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-key text-sky-500 text-[10px] sm:text-xs"></i>
                                            Password <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative">
                                            <input :type="showPassword ? 'text' : 'password'"
                                                name="password"
                                                x-model="password"
                                                required
                                                class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200"
                                                placeholder="Kombinasi Minimal 8 Karakter">

                                            <span @click="showPassword = !showPassword"
                                                class="absolute inset-y-0 right-3 flex items-center cursor-pointer text-gray-400">
                                                <i class="fa-solid text-xs"
                                                :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </span>
                                        </div>

                                        {{-- Password validation indicators --}}
                                        <div class="grid grid-cols-2 gap-2 mt-2">
                                            <div class="flex items-center gap-1.5 text-[10px]"
                                                :class="hasUpper ? 'text-emerald-600' : 'text-gray-400'">
                                                <i class="fa-solid"
                                                :class="hasUpper ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                                Huruf Kapital (A-Z)
                                            </div>

                                            <div class="flex items-center gap-1.5 text-[10px]"
                                                :class="hasNumber ? 'text-emerald-600' : 'text-gray-400'">
                                                <i class="fa-solid"
                                                :class="hasNumber ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                                Angka (0-9)
                                            </div>

                                            <div class="flex items-center gap-1.5 text-[10px]"
                                                :class="hasSymbol ? 'text-emerald-600' : 'text-gray-400'">
                                                <i class="fa-solid"
                                                :class="hasSymbol ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                                Simbol (!@#$%^&*)
                                            </div>

                                            <div class="flex items-center gap-1.5 text-[10px]"
                                                :class="isLongEnough ? 'text-emerald-600' : 'text-gray-400'">
                                                <i class="fa-solid"
                                                :class="isLongEnough ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                                                Minimal 8 Karakter
                                            </div>
                                        </div>
                                        <p class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Password kuat untuk keamanan akun
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i>
                            Batal
                        </button>
                        
                        <button type="submit"
                                :disabled="!isFormValid()"
                                class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                                :class="!isFormValid() 
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' 
                                        : 'bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-200'">
                            <i class="fa-solid fa-paper-plane"></i>
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

{{-- ================= MODAL DETAIL (COMPACT VERSION) ================= --}}
<div x-show="showDetailModal" x-cloak @click.self="closeModal()" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

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

            {{-- Nama --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Nama</p>
                <p x-text="selectedPegawai?.nama" class="font-medium"></p>
            </div>

            {{-- NIP --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">NIP</p>
                <p x-text="selectedPegawai?.nip || '-'" class="font-medium"></p>
            </div>

            {{-- Email --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Email</p>
                <p x-text="selectedPegawai?.email || '-'" class="font-medium break-all"></p>
            </div>

            {{-- Role --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Role Akun</p>
                <p x-text="selectedPegawai?.role || '-'" class="font-medium capitalize"></p>
            </div>

            {{-- Jabatan --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Jabatan</p>
                <p x-text="selectedPegawai?.jabatan || '-'" class="font-medium"></p>
            </div>

            {{-- Unit Kerja --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Unit Kerja</p>
                <p x-text="selectedPegawai?.unit_kerja || '-'" class="font-medium"></p>
            </div>

            {{-- Atasan Langsung --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Atasan Langsung</p>
                <p x-text="selectedPegawai?.atasan || '-'" class="font-medium"></p>
            </div>

            {{-- Pejabat Pemberi Cuti --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Pejabat Pemberi Cuti</p>
                <p x-text="selectedPegawai?.pejabat || '-'" class="font-medium"></p>
            </div>

            {{-- Telepon --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Telepon</p>
                <p x-text="selectedPegawai?.telepon || '-'" class="font-medium"></p>
            </div>

            {{-- Status --}}
            <div class="p-1.5 border border-gray-200 rounded-md">
                <p class="font-semibold text-[10px] text-gray-500">Status</p>
                <span x-text="selectedPegawai?.status || '-'"
                      class="font-medium capitalize px-2 py-0.5 rounded-full text-[10px]"
                      :class="selectedPegawai?.status === 'aktif'
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

{{-- ================= MODAL EDIT (PREMIUM DESIGN - FIXED) ================= --}}
<template x-if="showEditModal">
    <div x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="closeModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- ========== HEADER DENGAN GRADIENT (AMBER) ========== --}}
            <div class="bg-gradient-to-r from-amber-500 to-yellow-600 px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-user-pen text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Edit Pegawai</h3>
                            <p class="text-amber-100 text-[10px] sm:text-xs">Perbarui informasi data pegawai</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                        <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>

            {{-- ========== FORM CONTENT ========== --}}
            <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
                <form x-bind:action="selectedPegawai ? editRoute.replace(':pegawaiId', selectedPegawai.id) : '#'" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        
                        {{-- ===== KOLOM KIRI: DATA PEGAWAI ===== --}}
                        <div class="space-y-4">
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-tie text-amber-600 text-sm"></i>
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                    </div>
                                </div>
                                <div class="p-4 space-y-3">
                                    {{-- Nama & NIP tetap Amber --}}
                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-user text-amber-500 text-[10px]"></i> Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nama" x-model="selectedPegawai.nama" class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none text-[11px] sm:text-xs transition-all">
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                            <i class="fa-solid fa-hashtag text-amber-500 text-[10px]"></i> NIP
                                        </label>
                                        <input type="text" name="nip" x-model="selectedPegawai.nip" maxlength="18" class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none text-[11px] sm:text-xs transition-all">
                                    </div>

                                    {{-- Jabatan & Unit Kerja --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-briefcase text-amber-500 text-[10px]"></i> Jabatan <span class="text-red-500">*</span>
                                            </label>
                                            {{-- Input Jabatan logic --}}
                                            <template x-if="role === 'pegawai' || role === 'admin' || role === ''">
                                                <input type="text" name="jabatan" x-model="jabatan" required class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none">
                                            </template>
                                            <template x-if="role === 'atasan' || role === 'pejabat'">
                                                <select name="jabatan" x-model="jabatan" :disabled="role === 'pejabat'" class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none disabled:bg-gray-100">
                                                    <template x-if="role === 'pejabat'"><option value="Kepala Dinas">Kepala Dinas</option></template>
                                                    <template x-if="role === 'atasan'"><template x-for="j in jabatanMap[unit_kerja]" :key="j"><option :value="j" x-text="j"></option></template></template>
                                                </select>
                                            </template>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                                <i class="fa-solid fa-building text-amber-500 text-[10px]"></i> Unit Kerja <span class="text-red-500">*</span>
                                            </label>
                                            {{-- Hapus @change pada select di modal edit agar tidak memicu reset jabatan secara otomatis saat modal baru dibuka --}}
                                            <select name="unit_kerja" 
                                                    x-model="unit_kerja" 
                                                    :disabled="role === 'pejabat'" 
                                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs focus:border-amber-400 outline-none disabled:bg-gray-100">
                                                <template x-for="unit in filteredUnitKerja" :key="unit">
                                                    <option :value="unit" x-text="unit" :selected="unit === unit_kerja"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- FIX: ATASAN & PEMBERI CUTI (THEME AMBER) --}}
                                    <div class="grid grid-cols-2 gap-3 mt-4">
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-semibold text-gray-600">Atasan Langsung *</label>
                                            <select name="id_atasan_langsung" x-model="id_atasan_langsung" required 
                                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-[11px]">
                                                <option value="">Pilih Atasan</option>
                                                <template x-for="item in filteredAtasan" :key="item.id">
                                                    <option :value="item.id" x-text="item.nama"></option>
                                                </template>
                                            </select>
                                            {{-- INPUT HIDDEN AGAR CONTROLLER MENERIMA NAMA --}}
                                            <input type="hidden" name="atasan" 
                                                :value="
                                                    id_atasan_langsung == 0 ? 'Hj. Erna Lisa Halaby' : 
                                                    (dataAtasan.find(a => a.id == id_atasan_langsung)?.nama || 
                                                    pejabatList.find(p => p.id == id_atasan_langsung)?.nama || '')
                                                ">
                                        </div>

                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-semibold text-gray-600">Pemberi Cuti</label>
                                            <select name="id_pejabat_pemberi_cuti" x-model="id_pejabat_pemberi_cuti" 
                                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-[11px]">
                                                <template x-for="p in pejabatList" :key="p.id">
                                                    <option :value="p.id" x-text="p.nama"></option>
                                                </template>
                                            </select>
                                            {{-- INPUT HIDDEN AGAR CONTROLLER MENERIMA NAMA PEJABAT --}}
                                            <input type="hidden" name="pejabat" :value="pejabatList.find(p => p.id == id_pejabat_pemberi_cuti)?.nama || 'Kanafi, S.IP, MM'">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== KOLOM KANAN: ROLE & STATUS (FIXED TO AMBER) ===== --}}
                        <div class="space-y-4">
                            {{-- Role Section --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-shield-halved text-amber-500 text-[10px] sm:text-xs"></i> Role <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="role" x-model="role" @change="handleRoleChange()" required
                                            class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                                focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all duration-200">
                                        <option value="atasan">Atasan</option>
                                        <option value="pejabat">Pejabat</option>
                                        <option value="pegawai">Pegawai</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Section --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-toggle-on text-amber-500 text-[10px] sm:text-xs"></i> Status
                                </label>
                                <select name="status" x-model="selectedPegawai.status"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                               focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition-all">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>

                            {{-- Info Box Amber --}}
                            <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                </div>
                                <div class="text-[11px] sm:text-xs">
                                    <p class="font-bold text-amber-800">Perhatian</p>
                                    <p class="text-amber-700">Perubahan data akan langsung tersimpan ke database.</p>
                                </div>
                            </div>

                            {{-- Data Saat Ini Section Amber --}}
                            <div class="hidden lg:block bg-slate-50 rounded-xl border border-gray-100 p-4 space-y-3">
                                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                    <i class="fa-solid fa-database text-amber-600 text-sm"></i>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Saat Ini</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-amber-100 shadow-sm">
                                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-id-badge text-amber-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-gray-400 uppercase tracking-wide">NIP Pegawai</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-gray-600" x-text="selectedPegawai?.nip || '-'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS (AMBER THEME) --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit"
                                :disabled="isUnchanged()"
                                class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all flex items-center justify-center gap-2 shadow-lg"
                                :class="isUnchanged() ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white hover:from-amber-600 hover:to-yellow-700 hover:shadow-amber-200'">
                            <i class="fa-solid fa-floppy-disk"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


{{-- =============================================== --}}
{{-- MODAL GLOBAL NOTIFIKASI --}}
{{-- =============================================== --}}
<div x-show="showNotif" x-transition.opacity x-cloak 
     class="fixed inset-0 flex items-center justify-center bg-black/50 z-[999]">

    <div @click.outside="showNotif=false"
         x-transition.scale.duration.200ms
         class="bg-white rounded-xl shadow-xl p-5 w-full max-w-sm text-center">

        <!-- Ikon Dinamis -->
        <div class="text-4xl mb-2"
             :class="{
                'text-green-600': notifType === 'success',
                'text-red-600': notifType === 'error',
                'text-yellow-500': notifType === 'warning',
                'text-sky-600': notifType === 'info'
             }">

            <template x-if="notifType === 'success'">
                <i class="fa-solid fa-circle-check"></i>
            </template>

            <template x-if="notifType === 'error'">
                <i class="fa-solid fa-circle-xmark"></i>
            </template>

            <template x-if="notifType === 'warning'">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </template>

            <template x-if="notifType === 'info'">
                <i class="fa-solid fa-circle-info"></i>
            </template>
        </div>

        <h3 class="text-lg font-bold mb-1" 
            :class="{
                'text-green-600': notifType === 'success',
                'text-red-600': notifType === 'error',
                'text-yellow-600': notifType === 'warning',
                'text-sky-600': notifType === 'info'
            }"
            x-text="notifTitle">
        </h3>

        <p class="text-sm text-gray-600 mt-1" x-text="notifMessage"></p>

        <div class="flex flex-col items-center mt-5">
            <button @click="showNotif=false"
                class="w-full px-4 py-2 rounded-lg text-white text-sm"
                :class="{
                    'bg-green-600 hover:bg-green-700': notifType === 'success',
                    'bg-red-600 hover:bg-red-700': notifType === 'error',
                    'bg-yellow-500 hover:bg-yellow-600': notifType === 'warning',
                    'bg-sky-600 hover:bg-sky-700': notifType === 'info'
                }">
                OK
            </button>
        </div>

    </div>
</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Notifikasi Otomatis (Muncul setelah Tambah / Edit / Hapus)
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            borderRadius: '15px'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: "{{ session('error') }}",
            borderRadius: '15px'
        });
    @endif

    // 2. Fungsi Konfirmasi Hapus Data
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Pegawai?',
            text: "Data " + nama + " akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mencari form delete tersembunyi dan melakukan submit
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

@endsection
