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
            <form action="{{ route('admin.pegawai.store') }}" method="POST">
                @csrf

                {{-- ========== 2-COLUMN LAYOUT ========== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                    
                    {{-- ===== KOLOM KIRI: DATA PRIBADI ===== --}}
                    <div class="space-y-4">
                        {{-- DATA PRIBADI SECTION --}}
                        <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                            <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-id-card text-sky-600 text-sm"></i>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Data Pribadi</span>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                {{-- Nama --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-user text-sky-500 text-[10px]"></i>
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama" value="{{ old('nama') }}"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="Masukkan nama lengkap" required>
                                    @error('nama') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- NIP --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-hashtag text-sky-500 text-[10px]"></i>
                                        NIP
                                    </label>
                                    <input type="text" name="nip" value="{{ old('nip') }}"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="Masukkan NIP">
                                    @error('nip') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-envelope text-sky-500 text-[10px]"></i>
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="contoh@email.com" required>
                                    @error('email') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Telepon --}}
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                        <i class="fa-solid fa-phone text-sky-500 text-[10px]"></i>
                                        Telepon
                                    </label>
                                    <input type="text" name="telepon" value="{{ old('telepon') }}"
                                           class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                           placeholder="08xxxxxxxxxx">
                                    @error('telepon') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN INFO (Like Ringkasan Pengajuan) --}}
                        <div class="hidden lg:block bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                <i class="fa-solid fa-circle-info text-sky-600 text-sm"></i>
                                <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Informasi</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-shield-halved text-sky-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Role Akses</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-gray-600">Hak akses sistem</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-check-circle text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-emerald-500 uppercase tracking-wide">Status Default</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-emerald-700">Pegawai Aktif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== KOLOM KANAN: FORM INPUT ===== --}}
                    <div class="space-y-4">
                        {{-- JABATAN --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-user-tie text-sky-500 text-[10px] sm:text-xs"></i>
                                Jabatan
                            </label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Masukkan jabatan">
                            @error('jabatan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- UNIT KERJA --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-building text-sky-500 text-[10px] sm:text-xs"></i>
                                Unit Kerja
                            </label>
                            <input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}"
                                   class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                          focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                   placeholder="Masukkan unit kerja">
                            @error('unit_kerja') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- ROLE --}}
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
                                    <option value="kadis" {{ old('role') == 'kadis' ? 'selected' : '' }}>Kadis</option>
                                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                </div>
                            </div>
                            @error('role') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- STATUS --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-toggle-on text-sky-500 text-[10px] sm:text-xs"></i>
                                Status
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
                            @error('status') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- PASSWORD --}}
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
                        <p class="text-[9px] sm:text-[10px] text-gray-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info"></i>
                            Password minimal 8 karakter
                        </p>
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
