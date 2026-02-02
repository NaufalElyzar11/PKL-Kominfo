@extends('layouts.admin')

@section('title', 'Edit Data Cuti')

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
                        <i class="fa-solid fa-calendar-pen text-white text-lg sm:text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Edit Data Cuti</h3>
                        <p class="text-sky-100 text-[10px] sm:text-xs">Perbarui informasi pengajuan cuti</p>
                    </div>
                </div>
                <a href="{{ route('admin.cuti.index') }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                    <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                </a>
            </div>
        </div>

        {{-- ========== FORM CONTENT ========== --}}
        <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
            <form action="{{ route('admin.cuti.update', $cuti->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_pegawai" value="{{ $cuti->id_pegawai }}">

                {{-- ========== 2-COLUMN LAYOUT ========== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                    
                    {{-- ===== KOLOM KIRI: DATA PEGAWAI ===== --}}
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
                                {{-- Nama --}}
                                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                                    <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-id-badge text-sky-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Nama Lengkap</p>
                                        <p class="text-[12px] sm:text-sm font-semibold text-gray-800 truncate">{{ $cuti->pegawai->nama ?? '-' }}</p>
                                    </div>
                                </div>
                                {{-- Info Grid --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">NIP</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $cuti->pegawai->nip ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Jabatan</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $cuti->pegawai->jabatan ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN CUTI --}}
                        <div class="hidden lg:block bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                <i class="fa-solid fa-chart-pie text-sky-600 text-sm"></i>
                                <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Ringkasan Cuti</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-calendar-week text-sky-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Jumlah Hari</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-gray-600">Hari kerja yang diajukan</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-sky-600">{{ $cuti->jumlah_hari }}</span>
                                    <span class="text-[10px] sm:text-xs text-gray-400 ml-1">hari</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-check-circle text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] sm:text-[10px] text-emerald-500 uppercase tracking-wide">Status</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-emerald-700">{{ $cuti->status ?? 'Menunggu' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== KOLOM KANAN: FORM INPUT ===== --}}
                    <div class="space-y-4">
                        {{-- JENIS CUTI --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-tag text-sky-500 text-[10px] sm:text-xs"></i>
                                Jenis Cuti
                            </label>
                            <div class="relative">
                                <select name="jenis_cuti"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                               focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                    <option value="Cuti Tahunan" {{ $cuti->jenis_cuti == 'Cuti Tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        {{-- ALASAN --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-pen-fancy text-sky-500 text-[10px] sm:text-xs"></i>
                                Alasan Cuti
                            </label>
                            <textarea name="alasan_cuti" rows="2"
                                      class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                             focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 resize-none"
                                      placeholder="Masukkan alasan cuti">{{ $cuti->alasan_cuti }}</textarea>
                        </div>

                        {{-- PERIODE CUTI --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-calendar-days text-sky-500 text-[10px] sm:text-xs"></i>
                                Periode Cuti <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative group">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
                                        <i class="fa-regular fa-calendar text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                    </div>
                                    <input type="date" name="tanggal_mulai" value="{{ $cuti->tanggal_mulai->format('Y-m-d') }}"
                                           class="w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                </div>
                                <div class="relative group">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
                                        <i class="fa-regular fa-calendar-check text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                    </div>
                                    <input type="date" name="tanggal_selesai" value="{{ $cuti->tanggal_selesai->format('Y-m-d') }}"
                                           class="w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs
                                                  focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200">
                                </div>
                            </div>
                        </div>

                        {{-- JUMLAH HARI --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-calendar-week text-sky-500 text-[10px] sm:text-xs"></i>
                                Jumlah Hari
                            </label>
                            <div class="relative">
                                <input type="number" name="jumlah_hari" value="{{ $cuti->jumlah_hari }}" readonly
                                       class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-gray-100/50 text-[11px] sm:text-xs text-gray-500 cursor-not-allowed">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <span class="px-2 py-0.5 bg-sky-100 text-sky-700 text-[9px] font-bold rounded-full uppercase">Auto</span>
                                </div>
                            </div>
                        </div>

                        {{-- WARNING --}}
                        <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            </div>
                            <div class="text-[11px] sm:text-xs">
                                <p class="font-bold text-amber-800">Perhatian</p>
                                <p class="text-amber-700">Mengubah tanggal cuti akan mempengaruhi catatan kehadiran pegawai.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MOBILE RINGKASAN --}}
                <div class="lg:hidden mt-4 bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">Hari Cuti</p>
                            <span class="text-2xl font-black text-sky-600">{{ $cuti->jumlah_hari }}</span>
                            <span class="text-[9px] text-gray-400">hari kerja</span>
                        </div>
                        <div class="flex flex-col items-center p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                            <p class="text-[9px] text-emerald-500 uppercase tracking-wide mb-1">Status</p>
                            <span class="text-sm font-bold text-emerald-600">{{ $cuti->status ?? 'Menunggu' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('admin.cuti.index') }}"
                       class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark"></i>
                        Kembali
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg
                                   bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-200">
                        <i class="fa-solid fa-paper-plane"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
