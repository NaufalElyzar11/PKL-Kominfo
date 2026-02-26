@extends('layouts.pegawai')

@section('title', 'Dashboard Kadis')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#008fd3", //Warna Kominfo
                    "background-light": "#f6f7f8",
                    "background-dark": "#101922",
                },
                fontFamily: {
                    "display": ["Public Sans", "sans-serif"],
                    "body": ["Public Sans", "sans-serif"]
                },
                borderRadius: {"DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px"},
            },
        },
    }
</script>
<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

[x-cloak] {
    display: none !important;
}
</style>
@endpush

@section('content')
<div class="flex flex-col gap-6" x-data="{ showRejectModal: false, rejectId: null, showResetModal: false, resetId: null, showDetailPejabat: false, detailCuti: {} }">

    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-[#0d141b] text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Dashboard Kadis</h1>
            <p class="text-[#4c739a] text-base font-normal">Tinjau dan kelola pengajuan cuti pegawai (Tahap Akhir).</p>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Card Menunggu --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-[#e7edf3] shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[80px] text-amber-500">pending</span>
            </div>
            <p class="text-[#4c739a] text-sm font-medium uppercase tracking-wide">Menunggu Konfirmasi</p>
            <div class="flex items-baseline gap-2">
                <p class="text-[#0d141b] text-3xl font-bold">{{ $stats['menunggu'] }}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Permintaan Baru</span>
            </div>
        </div>

        {{-- Card Disetujui --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-[#e7edf3] shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[80px] text-emerald-500">check_circle</span>
            </div>
            <p class="text-[#4c739a] text-sm font-medium uppercase tracking-wide">Telah Disetujui</p>
            <div class="flex items-baseline gap-2">
                <p class="text-[#0d141b] text-3xl font-bold">{{ $stats['disetujui'] }}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Total Approved</span>
            </div>
        </div>

        {{-- Card Ditolak --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-[#e7edf3] shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[80px] text-rose-500">cancel</span>
            </div>
            <p class="text-[#4c739a] text-sm font-medium uppercase tracking-wide">Ditolak</p>
            <div class="flex items-baseline gap-2">
                <p class="text-[#0d141b] text-3xl font-bold">{{ $stats['ditolak'] }}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">Total Rejected</span>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div>
        <h3 class="text-xl font-bold text-[#0d141b] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">inbox</span>
            Daftar Pengajuan Menunggu (Lolos Tahap 1)
        </h3>
        
        <div class="bg-white rounded-xl shadow-sm border border-[#e7edf3] overflow-hidden">
            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-[#0d141b]">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-xs uppercase border-b border-[#e7edf3]">
                        <tr>
                            <th class="px-4 py-3 font-semibold w-16 text-center">No</th>
                            <th class="px-4 py-3 font-semibold min-w-[200px]">Pegawai</th>
                            <th class="px-4 py-3 font-semibold hidden md:table-cell">Jabatan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap hidden sm:table-cell">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap hidden lg:table-cell">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Durasi</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7edf3]">
                        @forelse($pengajuan as $index => $c)
                        <tr class="bg-white hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 py-3 text-center font-medium text-[#9aaabb]">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar Initials --}}
                                    @php
                                        $nama = $c->pegawai->nama ?? $c->nama ?? $c->user->name ?? 'Unknown';
                                        $nip = $c->pegawai->nip ?? $c->nip ?? '-';
                                        $jabatan = $c->pegawai->jabatan ?? $c->jabatan ?? '-';
                                    @endphp
                                    <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($nama, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[#0d141b]">{{ $nama }}</span>
                                        <span class="text-xs text-[#4c739a] font-mono">{{ $nip }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-[#4c739a]">{{ $jabatan }}</td>
                            <td class="px-4 py-3 whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    <span>{{ $c->jenis_cuti }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                <div class="flex flex-col text-xs font-medium">
                                    <span class="text-[#0d141b]">{{ optional($c->tanggal_mulai)->format('d M Y') }}</span>
                                    <span class="text-[#9aaabb]">s.d. {{ optional($c->tanggal_selesai)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200">
                                    {{ $c->jumlah_hari }} Hari
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Detail Button --}}
                                    <button type="button"
                                            @click.stop="
                                                detailCuti = {
                                                    nama: '{{ $c->pegawai->nama ?? '-' }}',
                                                    nip: '{{ $c->pegawai->nip ?? '-' }}',
                                                    jabatan: '{{ $jabatan }}',
                                                    jenis_cuti: '{{ $c->jenis_cuti }}',
                                                    tanggal_mulai: '{{ optional($c->tanggal_mulai)->format('d M Y') }}',
                                                    tanggal_selesai: '{{ optional($c->tanggal_selesai)->format('d M Y') }}',
                                                    jumlah_hari: '{{ $c->jumlah_hari }}',
                                                    alasan_cuti: @js($c->alasan_cuti ?? '-'),
                                                    pengganti_nama: '{{ $c->delegasi->nama ?? '-' }}',
                                                    pengganti_jabatan: '{{ $c->delegasi->jabatan ?? '' }}',
                                                    status: '{{ $c->status }}'
                                                };
                                                showDetailPejabat = true"
                                            class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white transition-all flex items-center justify-center shadow-sm"
                                            title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">info</span>
                                    </button>

                                    {{-- Approve Button --}}
                                    <form id="form-approve-{{ $c->id }}" action="{{ route('pejabat.approval.approve', $c->id) }}" method="POST">
                                        @csrf
                                        <button type="button" onclick="confirmApprove('{{ $c->id }}')" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Setujui">
                                            <span class="material-symbols-outlined text-[18px]">check</span>
                                        </button>
                                    </form>
                                    
                                    {{-- Reject Button --}}
                                    <button type="button" 
                                            @click.stop="rejectId = {{ $c->id }}; showRejectModal = true" 
                                            class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" 
                                            title="Tolak">
                                        {{-- pointer-events-none memastikan klik masuk ke button, bukan ke ikon --}}
                                        <span class="material-symbols-outlined text-[18px] pointer-events-none">close</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                                    <p class="text-sm">Tidak ada pengajuan cuti yang menunggu konfirmasi saat ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile View Cards --}}
            <div class="md:hidden flex flex-col gap-4 p-4 bg-gray-50/50">
                @forelse($pengajuan as $index => $c)
                @php
                    $nama = $c->pegawai->nama ?? $c->nama ?? $c->user->name ?? 'Unknown';
                    $nip = $c->pegawai->nip ?? $c->nip ?? '-';
                    $jabatan = $c->pegawai->jabatan ?? $c->jabatan ?? '-';
                @endphp
                <div class="bg-white rounded-xl border border-[#e7edf3] p-4 shadow-sm flex flex-col gap-3">
                    <div class="flex justify-between items-center border-b border-[#e7edf3] pb-3">
                        <span class="text-xs font-bold text-[#4c739a]">#{{ $index + 1 }}</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $c->status }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0">
                            {{ strtoupper(substr($nama, 0, 1)) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-[#0d141b] text-sm">{{ $nama }}</span>
                            <span class="text-xs text-[#4c739a] font-mono">{{ $nip }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                        <div>
                            <span class="text-[#9aaabb] block mb-0.5">Jenis Cuti</span>
                            <span class="font-semibold text-[#0d141b] flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                {{ $c->jenis_cuti }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[#9aaabb] block mb-0.5">Durasi</span>
                            <span class="font-semibold text-[#0d141b]">{{ $c->jumlah_hari }} Hari</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[#9aaabb] block mb-0.5">Tanggal</span>
                            <span class="font-semibold text-[#0d141b]">
                                {{ optional($c->tanggal_mulai)->format('d M Y') }} - {{ optional($c->tanggal_selesai)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-[#e7edf3] flex justify-center gap-2">
                        {{-- Detail Button --}}
                        <button type="button"
                                @click.stop="
                                    detailCuti = {
                                        nama: '{{ $c->pegawai->nama ?? '-' }}',
                                        nip: '{{ $c->pegawai->nip ?? '-' }}',
                                        jabatan: '{{ $jabatan }}',
                                        jenis_cuti: '{{ $c->jenis_cuti }}',
                                        tanggal_mulai: '{{ optional($c->tanggal_mulai)->format('d M Y') }}',
                                        tanggal_selesai: '{{ optional($c->tanggal_selesai)->format('d M Y') }}',
                                        jumlah_hari: '{{ $c->jumlah_hari }}',
                                        alasan_cuti: @js($c->alasan_cuti ?? '-'),
                                        pengganti_nama: '{{ $c->delegasi->nama ?? '-' }}',
                                        pengganti_jabatan: '{{ $c->delegasi->jabatan ?? '' }}',
                                        status: '{{ $c->status }}'
                                    };
                                    showDetailPejabat = true"
                                class="flex flex-1 items-center justify-center gap-1.5 py-2 rounded-xl bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white transition-all text-[11px] font-bold"
                                title="Detail">
                            <span class="material-symbols-outlined text-[16px]">info</span> Detail
                        </button>

                        {{-- Approve Button --}}
                        <form id="form-approve-mobile-{{ $c->id }}" action="{{ route('pejabat.approval.approve', $c->id) }}" method="POST" class="flex flex-1">
                            @csrf
                            <button type="button" onclick="confirmApprove('{{ $c->id }}')" class="w-full flex items-center justify-center gap-1.5 py-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all text-[11px] font-bold" title="Setujui">
                                <span class="material-symbols-outlined text-[16px]">check</span> Setuju
                            </button>
                        </form>
                        
                        {{-- Reject Button --}}
                        <button type="button" 
                                @click.stop="rejectId = {{ $c->id }}; showRejectModal = true" 
                                class="flex flex-1 items-center justify-center gap-1.5 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all text-[11px] font-bold" 
                                title="Tolak">
                            <span class="material-symbols-outlined text-[16px] pointer-events-none">close</span> Tolak
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                    <p class="text-sm">Tidak ada pengajuan cuti yang menunggu konfirmasi saat ini.</p>
                </div>
                @endforelse
            </div>

            @if($pengajuan->hasPages())
            <div class="p-4 border-t border-[#e7edf3] bg-gray-50 overflow-x-auto">
                {{ $pengajuan->links() }}
            </div>
            @endif
        </div>
    </div>

{{-- Modal Alasan Penolakan --}}
<div x-show="showRejectModal" 
    x-transition.opacity 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" 
    x-cloak>
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="showRejectModal = false">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-rose-500">report_problem</span>
            Alasan Penolakan
        </h3>

        <form :action="'{{ route('pejabat.approval.reject', ['id' => '_id']) }}'.replace('_id', rejectId)" method="POST">
            @csrf

        <div x-data="{ count: 0 }">
            <textarea name="catatan_tolak_pejabat"
                rows="4"
                required
                maxlength="100"
                pattern="[A-Za-z\s]+"
                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, ''); count = this.value.length"
                x-on:input="count = $event.target.value.length"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-rose-500 focus:border-rose-500 p-2 border text-sm"
                placeholder="Contoh: Pekerjaan sedang menumpuk, mohon tunda cuti..."></textarea>

            <div class="text-xs text-gray-500 text-right mt-1">
                <span x-text="count"></span>/100 karakter
            </div>
        </div>

            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                    @click="showRejectModal = false" 
                    class="text-gray-600 px-4 py-2 text-sm font-medium">
                    Batal
                </button>

                <button type="submit" 
                    class="bg-rose-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-rose-700 shadow-md">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reset Persetujuan --}}
<div x-show="showResetModal" 
    x-transition.opacity 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" 
    x-cloak>
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="showResetModal = false">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500">rotate_left</span>
            Ulang Persetujuan
        </h3>

        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
            <p class="font-semibold">⚠️ Perhatian:</p>
            <p>Status akan dikembalikan ke "Disetujui Atasan" dan akan muncul kembali di daftar persetujuan.</p>
        </div>

        <form :action="'{{ route('pejabat.approval.reset', ['id' => '_id']) }}'.replace('_id', resetId)" method="POST">
            @csrf

        <div x-data="{ count: 0 }">
            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Ulang <span class="text-red-500">*</span></label>
            <textarea name="alasan_reset"
                rows="4"
                required
                maxlength="100"
                pattern="[A-Za-z\s]+"
                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, ''); count = this.value.length"
                x-on:input="count = $event.target.value.length"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 p-2 border text-sm"
                placeholder="Contoh: Data pegawai perlu diverifikasi ulang..."></textarea>

            <div class="text-xs text-gray-500 text-right mt-1">
                <span x-text="count"></span>/100 karakter
            </div>
        </div>

            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                    @click="showResetModal = false" 
                    class="text-gray-600 px-4 py-2 text-sm font-medium">
                    Batal
                </button>

                <button type="submit" 
                    class="bg-amber-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-amber-700 shadow-md">
                    Ulang Persetujuan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DETAIL CUTI PEJABAT - PREMIUM --}}
<div x-show="showDetailPejabat" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
     @click.self="showDetailPejabat = false"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100"
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">

        {{-- HEADER GRADIENT --}}
        <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fa-solid fa-file-circle-check text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-base tracking-wide">Detail Pengajuan Cuti</h3>
                        <p class="text-sky-100 text-[10px]">Menunggu persetujuan Kadis</p>
                    </div>
                </div>
                <button @click="showDetailPejabat = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                    <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                </button>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4">

            {{-- Status Badge --}}
            <div class="flex justify-center">
                <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 flex items-center gap-2">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span x-text="detailCuti.status || 'Menunggu Persetujuan'"></span>
                </span>
            </div>

            {{-- INFO PEGAWAI --}}
            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-tie text-sky-600 text-sm"></i>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    <div class="col-span-2 flex items-center gap-3 pb-3 border-b border-gray-100">
                        <div class="w-9 h-9 bg-sky-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-id-badge text-sky-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide">Nama Lengkap</p>
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="detailCuti.nama || '-'"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">NIP</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailCuti.nip || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Jabatan</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailCuti.jabatan || '-'"></p>
                    </div>
                </div>
            </div>

            {{-- INFO CUTI --}}
            <div class="bg-gradient-to-br from-sky-50 to-blue-50 rounded-xl border border-sky-100 overflow-hidden">
                <div class="px-4 py-2.5 bg-sky-100/50 border-b border-sky-100 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-sky-600 text-sm"></i>
                    <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider">Detail Cuti</span>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-tag text-sky-600 text-[10px]"></i>
                            </div>
                            <span class="text-xs text-gray-500">Jenis Cuti</span>
                        </div>
                        <span class="text-xs font-bold text-sky-700" x-text="detailCuti.jenis_cuti || '-'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center">
                                <i class="fa-regular fa-calendar text-sky-600 text-[10px]"></i>
                            </div>
                            <span class="text-xs text-gray-500">Periode Cuti</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-700" x-text="detailCuti.tanggal_mulai || '-'"></p>
                            <p class="text-[10px] text-gray-400">s/d <span x-text="detailCuti.tanggal_selesai || '-'"></span></p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">Total Hari Cuti</p>
                        <span class="text-3xl font-black text-sky-600" x-text="detailCuti.jumlah_hari || '0'"></span>
                        <span class="text-[9px] text-gray-400">hari kerja</span>
                    </div>
                </div>
            </div>

            {{-- DELEGASI --}}
            <template x-if="detailCuti.pengganti_nama && detailCuti.pengganti_nama !== '-'">
                <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl border border-violet-100 overflow-hidden">
                    <div class="px-4 py-2.5 bg-violet-100/50 border-b border-violet-100 flex items-center gap-2">
                        <i class="fa-solid fa-user-group text-violet-600 text-sm"></i>
                        <span class="text-[10px] font-bold text-violet-600 uppercase tracking-wider">Pegawai Pengganti</span>
                    </div>
                    <div class="p-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-violet-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user-check text-violet-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-violet-700" x-text="detailCuti.pengganti_nama"></p>
                            <p class="text-[10px] text-gray-400" x-text="detailCuti.pengganti_jabatan || ''"></p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ALASAN --}}
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-pen-fancy text-sky-500 text-sm"></i>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Alasan Cuti</span>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 italic leading-relaxed" x-text="detailCuti.alasan_cuti || '-'"></div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button @click="showDetailPejabat = false"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>
</div>

    {{-- Riwayat Section --}}
    <div>
        <h3 class="text-xl font-bold text-[#0d141b] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Riwayat Pengajuan (Processed)
        </h3>
        
        <div class="bg-white rounded-xl shadow-sm border border-[#e7edf3] overflow-hidden">
            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-[#0d141b]">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-xs uppercase border-b border-[#e7edf3]">
                        <tr>
                            <th class="px-4 py-3 font-semibold w-16 text-center">No</th>
                            <th class="px-4 py-3 font-semibold min-w-[200px]">Pegawai</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap hidden sm:table-cell">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap hidden lg:table-cell">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold">Catatan / Keterangan</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7edf3]">
                        @forelse($riwayat as $index => $r)
                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-center font-medium text-[#9aaabb]">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r->pegawai->nama ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap hidden sm:table-cell">{{ $r->jenis_cuti }}</td>
                            <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                {{ optional($r->tanggal_mulai)->format('d M Y') }} s.d. {{ optional($r->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($r->status == 'Disetujui' || $r->status == 'Disetujui Atasan')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $r->status }}
                                    </span>
                                @elseif($r->status == 'Ditolak')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $r->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($r->status == 'Ditolak')
                                    <div class="bg-red-50 border border-red-200 text-red-700 text-xs p-2 rounded-md">
                                        <span class="font-semibold">Alasan:</span>
                                        {{ $r->catatan_final }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- KOLOM AKSI - Reset dengan SweetAlert & Modal --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @php
                                    // Cek apakah sudah lewat 8 jam sejak updated_at
                                    $canReset = false;
                                    if (($r->status === 'Disetujui' || $r->status === 'Ditolak') && $r->updated_at) {
                                        $hoursSinceUpdate = $r->updated_at->diffInHours(now());
                                        $canReset = $hoursSinceUpdate < 8;
                                    }
                                @endphp
                                
                                @if($canReset)
                                    <button type="button" 
                                        @click.stop="resetId = {{ $r->id }}; showResetModal = true" 
                                        class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center shadow-sm mx-auto" 
                                        title="Ulang Persetujuan">
                                        <span class="material-symbols-outlined text-[18px] pointer-events-none">rotate_left</span>
                                    </button>
                                @elseif($r->status === 'Disetujui' || $r->status === 'Ditolak')
                                    {{-- Tampilkan icon disabled jika sudah lewat 8 jam --}}
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-300 flex items-center justify-center shadow-sm mx-auto" title="Ulang tidak tersedia (Sudah lewat 8 jam)">
                                        <span class="material-symbols-outlined text-[18px]">lock</span>
                                    </div>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Belum ada riwayat pengajuan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile View Cards --}}
            <div class="md:hidden flex flex-col gap-4 p-4 bg-gray-50/50">
                @forelse($riwayat as $index => $r)
                <div class="bg-white rounded-xl border border-[#e7edf3] p-4 shadow-sm flex flex-col gap-3">
                    <div class="flex justify-between items-center border-b border-[#e7edf3] pb-3">
                        <span class="text-xs font-bold text-[#4c739a]">#{{ $index + 1 }}</span>
                        <div>
                            @if($r->status == 'Disetujui' || $r->status == 'Disetujui Atasan')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">{{ $r->status }}</span>
                            @elseif($r->status == 'Ditolak')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Ditolak</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">{{ $r->status }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-1">
                        <span class="font-bold text-[#0d141b] text-sm">{{ $r->pegawai->nama ?? '-' }}</span>
                        <span class="text-xs text-[#4c739a] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>{{ $r->jenis_cuti }}</span>
                    </div>
                    
                    <div class="text-xs mt-1">
                        <span class="text-[#9aaabb] block mb-0.5">Tanggal</span>
                        <span class="font-semibold text-[#0d141b]">
                            {{ optional($r->tanggal_mulai)->format('d M Y') }} s.d. {{ optional($r->tanggal_selesai)->format('d M Y') }}
                        </span>
                    </div>

                    <div class="mt-2 text-xs">
                        <span class="text-[#9aaabb] block mb-1">Catatan / Keterangan</span>
                        @if($r->status == 'Ditolak')
                            <div class="bg-red-50 border border-red-200 text-red-700 text-[10px] p-2 rounded-md">
                                <span class="font-bold">Alasan:</span> {{ $r->catatan_final }}
                            </div>
                        @else
                            <span class="text-gray-400 italic">-</span>
                        @endif
                    </div>

                    <div class="mt-3 pt-3 border-t border-[#e7edf3] flex justify-end">
                        @php
                            $canReset = false;
                            if (($r->status === 'Disetujui' || $r->status === 'Ditolak') && $r->updated_at) {
                                $hoursSinceUpdate = $r->updated_at->diffInHours(now());
                                $canReset = $hoursSinceUpdate < 8;
                            }
                        @endphp
                        
                        @if($canReset)
                            <button type="button" 
                                @click.stop="resetId = {{ $r->id }}; showResetModal = true" 
                                class="px-4 py-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center gap-2 text-xs font-bold" 
                                title="Ulang Persetujuan">
                                <span class="material-symbols-outlined text-[16px] pointer-events-none">rotate_left</span> Ulang
                            </button>
                        @elseif($r->status === 'Disetujui' || $r->status === 'Ditolak')
                            <div class="px-4 py-2 rounded-xl bg-gray-100 text-gray-400 flex items-center gap-2 text-xs font-medium" title="Ulang tidak tersedia (Sudah lewat 8 jam)">
                                <span class="material-symbols-outlined text-[16px]">lock</span> Ulang Terkunci
                            </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <span class="material-symbols-outlined text-4xl mb-2">history</span>
                    <p class="text-sm">Belum ada riwayat pengajuan.</p>
                </div>
                @endforelse
            </div>

            @if($riwayat->hasPages())
            <div class="p-4 border-t border-[#e7edf3] bg-gray-50 overflow-x-auto">
                {{ $riwayat->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Notifikasi Sukses
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {{ \Illuminate\Support\Js::from(session('success')) }}, 
            showConfirmButton: false,
            timer: 3000,
            borderRadius: '15px'
        });
    @endif

    // 2. Notifikasi Gagal/Error Validasi
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: {{ \Illuminate\Support\Js::from($errors->first()) }},
            confirmButtonColor: '#ef4444'
        });
    @endif

    // 3. Fungsi Approve (Tetap di sini)
    function confirmApprove(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: "Apakah Anda yakin ingin menyetujui pengajuan cuti ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-approve-' + id).submit();
            }
        })
    }
</script>
@endpush
@endsection
