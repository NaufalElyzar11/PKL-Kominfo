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
        this.selectedPegawai = pegawai;
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
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md p-5 border border-gray-200 space-y-4">
        <h1 class="text-xl font-bold text-gray-800">Daftar Data Pegawai</h1>

        {{-- 🔍 Filter + Tombol Aksi --}}
        <div class="flex flex-wrap justify-between items-center gap-2">

            {{-- 🔍 Filter --}}
            <form method="GET" action="{{ route('admin.pegawai.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/NIP/email"
                       class="px-2 py-1 border border-gray-300 rounded-md w-40 focus:ring-1 focus:ring-blue-400 focus:outline-none">

                <button type="submit"
                        class="px-3 py-1 bg-[#039BE5] text-white rounded-md hover:bg-[#0288D1] transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>

                @if(request('search') || request('unit_kerja'))
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="px-3 py-1 bg-gray-400 text-white rounded-md hover:bg-gray-500 transition">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </a>
                @endif
            </form>

            <div class="flex items-center">
                <button @click="showCreateModal = true"
                        class="px-3 py-1 bg-green-600 text-white rounded-md text-xs hover:bg-green-700 transition flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
                </button>
            </div>

       {{-- 📑 Table Pegawai --}}
<div>

    <div class="overflow-x-auto overflow-y-auto max-h-[430px] text-xs mt-2 border border-gray-200 rounded-lg shadow-sm">
        <table class="w-full border-collapse bg-white">
            <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
                <tr>
                    <th class="px-2 py-1 border">No</th>
                    <th class="px-2 py-1 border">Nama</th>
                    <th class="px-2 py-1 border">NIP</th>
                    <th class="px-2 py-1 border">Email</th>
                    <th class="px-2 py-1 border">Role</th>
                    <th class="px-2 py-1 border">Jabatan</th>
                    <th class="px-2 py-1 border">Unit</th>
                    <th class="px-2 py-1 border">Telepon</th>
                    <th class="px-2 py-1 border">Status</th>
                    <th class="px-2 py-1 border text-center">Aksi</th>
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
                            'telepon' => $p->telepon,
                            'status' => $p->status
                        ];

                        $nip = $p->nip ? substr($p->nip, 0, 4) . str_repeat('*', max(strlen($p->nip) - 8, 0)) . substr($p->nip, -4) : '-';

                        $email = optional($p->user)->email ?? '-';
                        if ($email !== '-') {
                            $parts = explode('@', $email);
                            $email = substr($parts[0], 0, 3) . str_repeat('*', max(strlen($parts[0]) - 3, 0)) . '@' . $parts[1];
                        }

                        $telepon = $p->telepon ? substr($p->telepon, 0, 3) . str_repeat('*', max(strlen($p->telepon) - 3, 0)) : '-';
                    @endphp

                    <tr class="border hover:bg-gray-50" data-pegawai='@json($pegawaiData)'>
                        <td class="px-2 py-1 border text-center">{{ $i + $pegawai->firstItem() }}</td>
                        <td class="px-2 py-1 border">{{ $p->nama }}</td>
                        <td class="px-2 py-1 border font-mono">{{ $nip }}</td>
                        <td class="px-2 py-1 border">{{ $email }}</td>
                        <td class="px-2 py-1 border capitalize">{{ optional($p->user)->role ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $p->jabatan ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $p->unit_kerja ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $telepon }}</td>
                        <td class="px-2 py-1 border text-center">{{ $p->status ?? '-' }}</td>

                        <td class="px-2 py-1 border text-center">
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

        {{-- Pagination --}}
        <div x-data="{ 
                currentPage: {{ $pegawai->currentPage() }},
                totalPages: {{ $pegawai->lastPage() }},
                goToPage(page) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', page);
                    window.location.href = url.toString();
                }
             }" 
             class="mt-4 flex items-center justify-end space-x-2 text-sm select-none">

            <button @click="if(currentPage > 1) goToPage(currentPage - 1)"
                    class="px-2 py-1 border rounded hover:bg-gray-200"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''">
                &lt;
            </button>

            <template x-for="page in totalPages">
                <button @click="goToPage(page)" x-text="page"
                        :class="page === currentPage 
                            ? 'px-3 py-1 bg-blue-600 text-white rounded' 
                            : 'px-3 py-1 border rounded hover:bg-gray-200'">
                </button>
            </template>

            <button @click="if(currentPage < totalPages) goToPage(currentPage + 1)"
                    class="px-2 py-1 border rounded hover:bg-gray-200"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''">
                &gt;
            </button>
        </div>
    </div>

    <hr>

    {{-- ================= MODAL TAMBAH PEGAWAI (LEBIH KECIL) ================= --}}
<div x-show="showCreateModal" x-cloak @click.self="closeModal()"
     class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-4 text-sm">
        
       <h3 class="text-base font-bold text-sky-600 border-b pb-2 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-sky-600"></i>
            Formulir Tambah Pegawai Baru
        </h3>

        <form action="{{ route('admin.pegawai.store') }}" method="POST" class="space-y-3" autocomplete="off">
            @csrf

            {{-- FORM UTAMA --}}
            <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200">
                
                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Nama Akun <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="block w-full border rounded-lg p-1"
                           placeholder="Nama Login">
                </div>

                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required
                        class="block w-full border rounded-lg p-1"
                        placeholder="Nama Pegawai"
                        {{-- Menghapus angka DAN simbol secara otomatis saat diketik --}}
                        oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                        {{-- Validasi HTML5 untuk memastikan hanya huruf dan spasi yang dikirim --}}
                        pattern="^[a-zA-Z\s]+$"
                        title="Nama hanya boleh berisi huruf dan spasi">
                    @error('nama')
                        <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">NIP</label>
                    <input type="text" name="nip" 
                        {{-- Batasi maksimal 18 karakter --}}
                        maxlength="18" 
                        {{-- Paksa keyboard angka pada perangkat mobile --}}
                        inputmode="numeric"
                        {{-- Hapus karakter selain angka secara otomatis saat diketik --}}
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="block w-full border rounded-lg p-1"
                        placeholder="Masukkan 18 digit NIP"
                        required>
                    @error('nip')
                        <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                    @enderror
                </div>

            <div>
                <label class="block font-medium text-gray-700 mb-0.5">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="jabatan" required
                    class="block w-full border rounded-lg p-1"
                    placeholder="Staf IT"
                    {{-- Menghapus APAPUN yang BUKAN huruf dan BUKAN spasi secara otomatis --}}
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                    {{-- Pola validasi untuk memastikan hanya huruf dan spasi yang dikirim --}}
                    pattern="^[a-zA-Z\s]+$"
                    title="Jabatan hanya boleh berisi huruf dan spasi">
                @error('jabatan')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

           <div>
                <label class="block font-medium text-gray-700 mb-0.5">Unit Kerja <span class="text-red-500">*</span></label>
                <input type="text" name="unit_kerja" required
                    class="block w-full border rounded-lg p-1"
                    placeholder="Contoh: Bidang Informatika"
                    {{-- Menghapus APAPUN yang BUKAN huruf dan BUKAN spasi secara otomatis --}}
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                    {{-- Pola validasi untuk memastikan hanya huruf dan spasi yang dikirim --}}
                    pattern="^[a-zA-Z\s]+$"
                    title="Unit kerja hanya boleh berisi huruf dan spasi">
                
                @error('unit_kerja')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
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

                <div>
                    <label class="block font-medium text-gray-700 mb-0.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required
                           class="block w-full border rounded-lg p-1"
                           placeholder="email@mail.com">
                </div>

                {{-- PASSWORD --}}
                <div x-data="{ show: false }">
                    <label class="block font-medium text-gray-700 mb-0.5">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required minlength="8"
                               class="block w-full border rounded-lg p-1 pr-8"
                               placeholder="Minimal 8 karakter">

                        <span @click="show = !show"
                              class="absolute inset-y-0 right-2 flex items-center cursor-pointer text-gray-500">
                            <template x-if="!show">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </template>
                            <template x-if="show">
                                <i class="fa-solid fa-eye-slash text-xs"></i>
                            </template>
                        </span>
                    </div>
                </div>

            </div>

            {{-- TELEPON --}}
            <div>
                <label class="block font-medium text-gray-700 mb-0.5">Telepon <span class="text-red-500">*</span></label>
                <input type="text" 
                    name="telepon" 
                    {{-- Membatasi maksimal 13 digit --}}
                    maxlength="13" 
                    {{-- Mengaktifkan keyboard angka pada perangkat mobile --}}
                    inputmode="numeric"
                    {{-- Menghapus huruf, simbol, dan spasi secara otomatis --}}
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="block w-full border rounded-lg p-1"
                    placeholder="Contoh: 081234567890"
                    required>
                
                {{-- Menampilkan pesan error dari server --}}
                @error('telepon')
                    <p class="text-xs text-red-500 mt-1 italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="closeModal()"
                        class="px-3 py-1 text-sm rounded-lg bg-gray-200 hover:bg-gray-300">
                    Batal
                </button>
                
                <button type="submit"
                        class="px-3 py-1 text-sm font-medium rounded-lg text-white bg-sky-600 hover:bg-sky-700">
                    Simpan Data
                </button>
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

        {{-- Nama --}}
        <div>
            <label class="font-medium text-xs">Nama</label>
            <input type="text" name="nama"
                x-model="selectedPegawai.nama"
                @input="selectedPegawai.nama = selectedPegawai.nama.replace(/[^a-zA-Z\s]/g, '')"
                class="w-full border rounded px-2 py-1 text-sm">
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

        {{-- Email --}}
        <div>
            <label class="font-medium text-xs">Email</label>
            <input type="email" name="email"
                x-model="selectedPegawai.email"
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

        {{-- Telepon --}}
        <div>
            <label class="font-medium text-xs">Telepon</label>
            <input type="text" name="telepon"
                x-model="selectedPegawai.telepon"
                maxlength="13"
                @input="selectedPegawai.telepon = selectedPegawai.telepon.replace(/[^0-9]/g, '').slice(0,13)"
                class="w-full border rounded px-2 py-1 text-sm">
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

        <button type="submit"
            class="px-3 py-1.5 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
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