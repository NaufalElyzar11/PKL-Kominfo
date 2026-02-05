@extends('layouts.admin')

@section('content')
{{-- MODAL-STYLE CONTAINER --}}
<div x-data="{ show: false }"
     x-init="setTimeout(() => show = true, 100)"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4">

    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100">
        
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
                <a href="{{ route('admin.pegawai.index') }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                    <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                </a>
            </div>
        </div>

        {{-- ========== FORM CONTENT ========== --}}
        <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
            
            {{-- PETUNJUK PENGISIAN --}}
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-[11px] sm:text-xs font-semibold text-amber-700">Petunjuk Pengisian</p>
                        <p class="text-[10px] sm:text-[11px] text-amber-600 mt-1">
                            Isi data dari <strong>Nama</strong> hingga <strong>Password</strong> secara berurutan. 
                            Field bertanda <span class="text-red-500">*</span> wajib diisi.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.pegawai.store') }}" method="POST">
                @csrf

                {{-- ========== 2-COLUMN LAYOUT ========== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                    
                    {{-- ===== KOLOM KIRI ===== --}}
                    <div class="space-y-4">
                        {{-- 1. NAMA --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-user text-sky-500 text-[10px]"></i>
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama" value="{{ old('nama') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Masukkan nama lengkap pegawai" required>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Nama sesuai KTP atau data resmi
                            </p>
                            @error('nama') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 2. NIP --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-hashtag text-sky-500 text-[10px]"></i>
                                NIP
                            </label>
                            <input type="text" name="nip" value="{{ old('nip') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Masukkan NIP (13-18 digit)">
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Nomor Induk Pegawai, kosongkan jika belum ada
                            </p>
                            @error('nip') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 3. ROLE --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-shield-halved text-sky-500 text-[10px] sm:text-xs"></i>
                                Role <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="role"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                               focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                        required>
                                    <option value="" disabled selected>— Pilih Role —</option>
                                    <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="atasan" {{ old('role') == 'atasan' ? 'selected' : '' }}>Atasan Langsung</option>
                                    <option value="pejabat" {{ old('role') == 'pejabat' ? 'selected' : '' }}>Pejabat Pemberi Cuti</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Menentukan hak akses pengguna di sistem
                            </p>
                            @error('role') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 4. STATUS --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-toggle-on text-sky-500 text-[10px] sm:text-xs"></i>
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="status"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                               focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                        required>
                                    <option value="" disabled selected>— Pilih Status —</option>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Pegawai nonaktif tidak dapat login
                            </p>
                            @error('status') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- ===== KOLOM KANAN ===== --}}
                    <div class="space-y-4">
                        {{-- 5. UNIT KERJA --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-building text-sky-500 text-[10px] sm:text-xs"></i>
                                Unit Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Contoh: Bidang Aplikasi Informatika" required>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Bagian/divisi tempat pegawai bekerja
                            </p>
                            @error('unit_kerja') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 6. JABATAN --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-user-tie text-sky-500 text-[10px] sm:text-xs"></i>
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Contoh: Kepala Seksi, Staf, dll" required>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Posisi atau jabatan pegawai dalam instansi
                            </p>
                            @error('jabatan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 7. ATASAN LANGSUNG --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-user-check text-sky-500 text-[10px] sm:text-xs"></i>
                                Atasan Langsung <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="atasan" value="{{ old('atasan') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Nama atasan langsung pegawai" required>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Pejabat yang menyetujui cuti tahap pertama
                            </p>
                            @error('atasan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 8. PASSWORD --}}
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-key text-sky-500 text-[10px] sm:text-xs"></i>
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="password"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="••••••••" required>
                                    @error('password') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-check-double text-sky-500 text-[10px] sm:text-xs"></i>
                                        Konfirmasi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="password_confirmation"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="••••••••" required>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Password minimal 8 karakter, digunakan untuk login
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark"></i>
                        Batal
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg
                                   bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-200">
                        <i class="fa-solid fa-paper-plane"></i>
                        Simpan Pegawai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
