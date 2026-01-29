@extends('layouts.admin')

@section('title', 'Data Pegawai & Pengajuan Cuti')

@section('content')
<div class="min-h-screen px-4 py-6 bg-[#E3F2FD]"
   x-data="{
    // Modal utama
    showCreateModal: false,
    showEditModal: false,
    showDetailModal: false,
    showDelete: false,

    isUnchanged() {
    if (!this.selectedPegawai || !this.originalPegawai) return true;
    return JSON.stringify(this.selectedPegawai) === JSON.stringify(this.originalPegawai);
},

    nip: '',

    // Modal jenis lain (Atasan / Pemberi Cuti)
    activeModal: null,

    // Data pegawai yang dipilih untuk Edit / Detail
    selectedPegawai: null,

    // Delete
    deleteUrl: '',
    deleteName: '',

    // Route base untuk update pegawai
    editRoute: '{{ route('admin.pegawai.update', ':pegawaiId') }}',

    // =========================
    // Fungsi buka modal Edit
    // =========================
    openEditModal(pegawai) {
        this.selectedPegawai = JSON.parse(JSON.stringify(pegawai));
        this.originalPegawai = JSON.parse(JSON.stringify(pegawai));
        this.showEditModal = true;
    },

    // =========================
    // Fungsi buka modal Detail
    // =========================
    openDetailModal(pegawai) {
        this.selectedPegawai = pegawai;
        this.showDetailModal = true;
    },

    // =========================
    // Fungsi buka modal Create (Tambah Pegawai)
    // =========================
    openCreateModal() {
        this.showCreateModal = true;
    },

    // =========================
    // Fungsi tutup semua modal
    // =========================
    closeModal() {
        this.showCreateModal = false;
        this.showEditModal = false;
        this.showDetailModal = false;
        this.activeModal = null;
        this.selectedPegawai = null;
    },

    // =========================
    // Tutup modal delete
    // =========================
    closeDelete() {
        this.showDelete = false;
        this.deleteUrl = '';
        this.deleteName = '';
    }
}"
     @keydown.escape.window="closeModal()">

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
                            'atasan' => $p->atasan, 
                            'pemberi_cuti' => $p->pemberi_cuti,
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
                        <td class="px-2 py-1 border text-center">{{ $p->status ?? '-' }}</td>

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

{{-- ================= MODAL TAMBAH PEGAWAI (LEBIH KECIL) ================= --}}
<div x-show="showCreateModal" x-cloak @click.self="closeModal()"
     class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-start justify-center z-50 pt-16">

    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-4 text-sm max-h-[85vh] overflow-y-auto">
        
       <h3 class="text-base font-bold text-sky-600 border-b pb-2 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-sky-600"></i>
            Formulir Tambah Pegawai Baru
        </h3>

        <form action="{{ route('admin.pegawai.store') }}" method="POST" class="space-y-3" autocomplete="off">
            @csrf

        {{-- FORM UTAMA --}}
        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200">

            {{-- ATASAN & PEMBERI CUTI (PALING ATAS, FULL WIDTH) --}}
            <div class="col-span-2">
                <div class="grid grid-cols-2 gap-2 bg-blue-50/50 p-2 rounded-lg border border-blue-100">
                    <div>
                        <label class="block font-medium text-gray-700 mb-0.5">
                            Atasan Langsung <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="atasan"
                            {{-- Menggunakan old() agar input tidak hilang jika validasi lain gagal --}}
                            value="{{ old('atasan') }}" 
                            class="block w-full border rounded-lg p-1 @error('atasan') border-red-500 @enderror"
                            placeholder="Nama Atasan"
                            {{-- Tambahkan atribut required di bawah ini --}}
                            required 
                            oninput="this.value = this.value.replace(/[^a-zA-Z\s.,]/g, '')"
                            pattern="^[a-zA-Z\s.,]+$"
                            title="Hanya diperbolehkan huruf, spasi, titik, dan koma">
                        
                        {{-- Menampilkan pesan error dari Laravel --}}
                        @error('atasan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Pejabat Pemberi Cuti</label>
                    <input type="text" 
                        name="pemberi_cuti" 
                        value="Kanafi, S.IP, MM"
                        readonly 
                        class="block w-full border border-gray-200 rounded-lg p-1" 
                        placeholder="Nama Pejabat">
                </div>
                </div>
            </div>

            {{-- NAMA LENGKAP --}}
            <div>
                <label class="block font-medium text-gray-700 mb-0.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" required
                    class="block w-full border rounded-lg p-1 focus:ring-1 focus:ring-sky-500 outline-none"
                    placeholder="Nama Pegawai"
                    {{-- Mengizinkan huruf, spasi, titik, dan koma --}}
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s.,]/g, '')"
                    {{-- Validasi pola agar menyertakan titik dan koma --}}
                    pattern="^[a-zA-Z\s.,]+$"
                    {{-- Pesan peringatan diperbarui --}}
                    title="Nama hanya boleh berisi huruf, spasi, titik, dan koma">
            </div>

            {{-- Bagian NIP pada Modal Tambah --}}
            <div> 
                <label class="block font-medium text-gray-700 mb-0.5">
                    NIP <span class="text-red-500">*</span> {{-- Tambahkan tanda wajib isi --}}
                </label>
                <input type="text" name="nip"
                    x-model="nip"
                    required {{-- Tambahkan atribut required --}}
                    minlength="13" 
                    maxlength="18"
                    inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="block w-full border rounded-lg p-1 focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Masukkan 18 digit NIP">
            </div>

            {{-- JABATAN (KINI BERDAMPINGAN DENGAN UNIT KERJA) --}}
            <div>
                <label class="block font-medium text-gray-700 mb-0.5">
                    Jabatan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="jabatan" required
                    class="block w-full border rounded-lg p-1"
                    placeholder="Staf IT"
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                    pattern="^[a-zA-Z\s]+$"
                    title="Jabatan hanya boleh berisi huruf dan spasi">
            </div>

            {{-- UNIT KERJA (PENGHAPUSAN col-span-2 AGAR MASUK KE KOLOM SEBELAHNYA) --}}
            <div>
                <label class="block font-medium text-gray-700 mb-0.5">
                    Unit Kerja <span class="text-red-500">*</span>
                </label>
                <input type="text" name="unit_kerja" required
                    class="block w-full border rounded-lg p-1"
                    placeholder="Contoh: Bidang Informatika"
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                    pattern="^[a-zA-Z\s]+$"
                    title="Unit kerja hanya boleh berisi huruf dan spasi">
            </div>

        </div>

            {{-- ROLE + STATUS + EMAIL + PASSWORD --}}
            <div class="grid grid-cols-2 gap-2">

                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="block w-full border rounded-lg p-1">
                        <option value="">Pilih</option>
                        <option value="admin">Admin</option>
                        <option value="atasan">Atasan Langsung</option>
                        <option value="pemberi_cuti">Pejabat Pemberi Cuti</option>
                        <option value="pegawai">Pegawai</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="block w-full border rounded-lg p-1">
                        <option value="">Pilih</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                {{-- PASSWORD DENGAN VALIDASI KOMBINASI --}}
                <div x-data="{ 
                    show: false, 
                    pw: '',
                    get hasUpper() { return /[A-Z]/.test(this.pw) },
                    get hasNumber() { return /[0-9]/.test(this.pw) },
                    get hasSymbol() { return /[!@#$%^&*(),.?':{}|<>]/.test(this.pw) },
                    get isLongEnough() { return this.pw.length >= 8 }
                }">
                    <label class="block font-medium text-gray-700 mb-0.5 text-xs">
                        Password <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" 
                            name="password" 
                            x-model="pw"
                            required 
                            {{-- Regex Pattern untuk browser --}}
                            pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}"
                            title="Password harus minimal 8 karakter, mengandung huruf kapital, angka, dan simbol."
                            class="block w-full border rounded-lg p-1.5 pr-8 text-xs focus:ring-1 focus:ring-sky-500 outline-none"
                            placeholder="Kombinasi minimal 8 karakter">

                        <span @click="show = !show"
                            class="absolute inset-y-0 right-2 flex items-center cursor-pointer text-gray-400 hover:text-sky-600 transition">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'" style="font-size: 10px;"></i>
                        </span>
                    </div>

                    {{-- INDIKATOR VALIDASI REAL-TIME --}}
                    <div class="mt-2 space-y-1 text-[10px]">
                        <div class="flex items-center gap-1.5" :class="hasUpper ? 'text-green-600' : 'text-gray-400'">
                            <i class="fa-solid" :class="hasUpper ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                            <span>Huruf Kapital (A-Z)</span>
                        </div>
                        <div class="flex items-center gap-1.5" :class="hasNumber ? 'text-green-600' : 'text-gray-400'">
                            <i class="fa-solid" :class="hasNumber ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                            <span>Angka (0-9)</span>
                        </div>
                        <div class="flex items-center gap-1.5" :class="hasSymbol ? 'text-green-600' : 'text-gray-400'">
                            <i class="fa-solid" :class="hasSymbol ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                            <span>Simbol (!@#$%^&*)</span>
                        </div>
                        <div class="flex items-center gap-1.5" :class="isLongEnough ? 'text-green-600' : 'text-gray-400'">
                            <i class="fa-solid" :class="isLongEnough ? 'fa-circle-check' : 'fa-circle-dot'"></i>
                            <span>Minimal 8 Karakter</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="closeModal()"
                        class="px-3 py-1 text-sm rounded-lg bg-gray-200 hover:bg-gray-300">
                    Batal
                </button>
                
                <button type="submit"
                    {{-- Tombol mati jika NIP kosong ATAU NIP kurang dari 13 karakter --}}
                    :disabled="!nip || nip.length < 13"
                    
                    {{-- Warna berubah menjadi abu-abu jika disabled, dan biru jika siap simpan --}}
                    :class="(!nip || nip.length < 13) 
                            ? 'opacity-50 cursor-not-allowed bg-gray-400' 
                            : 'bg-sky-600 hover:bg-sky-700'"
                    class="px-3 py-1 text-sm font-medium rounded-lg text-white transition-all">
                    Simpan Data
                </button>
            </div>

            </div>

        </form>
    </div>
</div>

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
                <p x-text="selectedPegawai?.pemberi_cuti || '-'" class="font-medium"></p>
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
{{-- ================= MODAL EDIT (VERSI DIPERKECIL) ================= --}}
<div x-show="showEditModal" x-cloak @click.self="closeModal()" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-4">
        
        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h3 class="text-base font-bold text-sky-700">
                <i class="fa-solid fa-user-pen mr-2"></i> Edit Pegawai
            </h3>
            <button @click="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

<form 
    x-bind:action="selectedPegawai ? editRoute.replace(':pegawaiId', selectedPegawai.id) : '#'" 
    method="POST">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-3 text-sm">

        {{-- Nama Lengkap --}}
        <div>
            <label class="font-medium text-xs">Nama Lengkap</label>
            <input type="text" name="nama"
                x-model="selectedPegawai.nama"
                {{-- Mengizinkan huruf, spasi, titik (.), dan koma (,) --}}
                @input="selectedPegawai.nama = selectedPegawai.nama.replace(/[^a-zA-Z\s.,]/g, '')"
                {{-- Tambahkan pattern agar browser memvalidasi saat submit --}}
                pattern="^[a-zA-Z\s.,]+$"
                title="Nama hanya boleh berisi huruf, spasi, titik, dan koma"
                class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-sky-500 outline-none">
        </div>

        {{-- NIP --}}
        <div>
            <label class="font-medium text-xs">NIP</label>
            <input type="text" name="nip"
                x-model="selectedPegawai.nip"
                maxlength="18"
                @input="selectedPegawai.nip = selectedPegawai.nip.replace(/[^0-9]/g, '').slice(0,18)"
                class="w-full border rounded px-2 py-1 text-sm">
        </div>

        {{-- Role --}}
        <div>
            <label class="font-medium text-xs">Role</label>
            <select name="role"
                x-model="selectedPegawai.role"
                class="w-full border rounded px-2 py-1 text-sm">
                <option value="pegawai">Pegawai</option>
                <option value="admin">Admin</option>
                <option value="atasan">Atasan</option>
                <option value="pemberi_cuti">Pejabat Pemberi Cuti</option>
            </select>
        </div>

        {{-- Jabatan --}}
        <div>
            <label class="font-medium text-xs">Jabatan</label>
            <input type="text" name="jabatan"
                x-model="selectedPegawai.jabatan"
                @input="selectedPegawai.jabatan = selectedPegawai.jabatan.replace(/[^a-zA-Z\s]/g, '')"
                class="w-full border rounded px-2 py-1 text-sm">
        </div>

        {{-- Unit Kerja --}}
        <div>
            <label class="font-medium text-xs">Unit Kerja</label>
            <input type="text" name="unit_kerja"
                x-model="selectedPegawai.unit_kerja"
                @input="selectedPegawai.unit_kerja = selectedPegawai.unit_kerja.replace(/[^a-zA-Z\s]/g, '')"
                class="w-full border rounded px-2 py-1 text-sm">
        </div>

        {{-- Atasan Langsung --}}
        <div>
            <label class="font-medium text-xs">Atasan Langsung</label>
            <input type="text" 
                name="atasan" 
                x-model="selectedPegawai.atasan" 
                {{-- 1. Menambahkan . dan , di dalam regex --}}
                @input="selectedPegawai.atasan = selectedPegawai.atasan.replace(/[^a-zA-Z\s.,]/g, '')"
                {{-- 2. Memperbarui pattern agar mengizinkan . dan , --}}
                pattern="^[a-zA-Z\s.,]+$"
                {{-- 3. Memperbarui pesan panduan --}}
                title="Hanya diperbolehkan huruf, spasi, titik, dan koma"
                class="w-full border rounded px-2 py-1 text-sm">
        </div>

        {{-- Pejabat Pemberi Cuti --}}
        <div>
            <label class="font-medium text-xs">Pejabat Pemberi Cuti</label>
            <input type="text" 
                name="pemberi_cuti" 
                value="Kanafi, S.IP, MM" 
                readonly
                class="w-full border border-gray-200 bg-gray-100 rounded px-2 py-1 text-sm cursor-not-allowed">
        </div>

        {{-- Status --}}
        <div>
            <label class="font-medium text-xs">Status</label>
            <select name="status"
                x-model="selectedPegawai.status"
                class="w-full border rounded px-2 py-1 text-sm">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

    </div>

    {{-- FOOTER --}}
    <div class="mt-4 text-right border-t pt-3">
        <button type="button" @click="closeModal()"
            class="px-3 py-1.5 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
            Batal
        </button>

        {{-- Tombol Update pada Modal Edit --}}
        <button type="submit"
            {{-- Tombol mati jika NIP tidak valid ATAU data tidak ada yang berubah --}}
            :disabled="(selectedPegawai && selectedPegawai.nip && selectedPegawai.nip.length > 0 && selectedPegawai.nip.length < 13) || isUnchanged()"
            
            {{-- Styling abu-abu jika disabled (nip tidak valid atau data tidak berubah) --}}
            :class="((selectedPegawai && selectedPegawai.nip && selectedPegawai.nip.length > 0 && selectedPegawai.nip.length < 13) || isUnchanged()) 
                    ? 'opacity-50 cursor-not-allowed bg-gray-400' 
                    : 'bg-yellow-600 hover:bg-yellow-700'"
            
            class="px-3 py-1.5 text-white rounded text-sm transition-all duration-200">
            Update
        </button>
        
    </div>
</form>


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