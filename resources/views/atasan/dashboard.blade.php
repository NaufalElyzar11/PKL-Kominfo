@extends('layouts.pegawai')

@section('title', 'Dashboard Atasan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#008fd3", {{-- Warna Kominfo --}}
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
</style>
@endpush

@section('content')
<div class="flex flex-col gap-6" 
x-data="{ 
    showReviewModal: false, 
    showTolakDelegasi: false,
    showRejectModal: false,
    showCutiModal: false,
    selectedCuti: null,
    delegasiStatus: 'pending',
    
    openReview(data) {
        this.selectedCuti = data;
        this.delegasiStatus = data.status_delegasi || 'pending';
        this.showReviewModal = true;
    }, // <--- TAMBAHKAN KOMA DI SINI

    async submitApproveDelegasi() {
        try {
            const response = await fetch(`/atasan/approval/${this.selectedCuti.id}/approve-delegasi`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' // Tambahkan ini agar Laravel mengirim respon JSON
                },
                body: JSON.stringify({}) // Fetch POST biasanya butuh body (walaupun kosong)
            });
            
            if (response.ok) {
                this.delegasiStatus = 'disetujui';
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Delegasi telah disetujui.',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                throw new Error('Gagal memproses');
            }
        } catch (error) {
            Swal.fire('Error', 'Gagal memproses delegasi.', 'error');
        }
    }
}">


    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-[#0d141b] text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Dashboard Atasan</h1>
            <p class="text-[#4c739a] text-base font-normal">Tinjau dan kelola pengajuan cuti pegawai Anda.</p>
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
            Daftar Pengajuan Menunggu
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
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
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
                                    <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($c->pegawai->nama ?? 'P', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[#0d141b]">{{ $c->pegawai->nama ?? 'Unknown' }}</span>
                                        <span class="text-xs text-[#4c739a] font-mono">{{ $c->pegawai->nip ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-[#4c739a]">{{ $c->pegawai->jabatan ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    <span>{{ $c->jenis_cuti }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-col text-xs font-medium">
                                    <span class="text-[#0d141b]">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d M Y') }}</span>
                                    <span class="text-[#9aaabb]">s.d. {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d M Y') }}</span>
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
                            <button type="button" 
                                    @click="openReview(@js($c))"
                                    class="px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 mx-auto">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Tinjau Pengajuan
                            </button>
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
                            {{ strtoupper(substr($c->pegawai->nama ?? 'P', 0, 1)) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-[#0d141b] text-sm">{{ $c->pegawai->nama ?? 'Unknown' }}</span>
                            <span class="text-xs text-[#4c739a] font-mono">{{ $c->pegawai->nip ?? '-' }}</span>
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
                                {{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-[#e7edf3]">
                        <button type="button" @click="openReview(@js($c))" class="w-full px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-xl text-xs font-bold transition-all flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            Tinjau Pengajuan
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
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-xs uppercase border-b border-[#e7edf3] whitespace-nowrap">
                        <tr>
                            <th class="px-4 py-3 font-semibold w-16 text-center">No</th>
                            <th class="px-4 py-3 font-semibold min-w-[200px]">Pegawai</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7edf3]">
                        @forelse($riwayat as $index => $r)
                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-center font-medium text-[#9aaabb]">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r->pegawai->nama ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $r->jenis_cuti }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
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
                            <td class="px-4 py-3 text-gray-500 italic">
                                @if($r->status == 'Ditolak' || $r->status_delegasi == 'ditolak')
                                    <div class="flex flex-col gap-2">
                                        {{-- Catatan Penolakan Delegasi --}}
                                        @if($r->catatan_tolak_delegasi)
                                            <div class="bg-amber-50 p-2 rounded-lg border border-amber-100">
                                                <p class="text-[10px] text-amber-600 font-bold uppercase">Delegasi Ditolak:</p>
                                                <p class="text-xs text-amber-800">{{ $r->catatan_tolak_delegasi }}</p>
                                            </div>
                                        @endif

                                        {{-- Catatan Penolakan Atasan --}}
                                        @if($r->catatan_tolak_atasan)
                                            <div class="bg-rose-50 p-2 rounded-lg border border-rose-100">
                                                <p class="text-[10px] text-rose-600 font-bold uppercase">Penolakan Atasan:</p>
                                                <p class="text-xs text-rose-800">{{ $r->catatan_tolak_atasan }}</p>
                                            </div>
                                        @endif

                                        {{-- Catatan Penolakan Pejabat (Sinkronisasi) --}}
                                        @if($r->catatan_tolak_pejabat)
                                            <div class="bg-blue-50 p-2 rounded-lg border border-blue-200 shadow-sm">
                                                <p class="text-[10px] text-blue-700 font-bold uppercase flex items-center gap-1">
                                                    <i class="fa-solid fa-user-shield text-[10px]"></i> Keputusan Kadis:
                                                </p>
                                                <p class="text-xs text-blue-900 font-medium">{{ $r->catatan_tolak_pejabat }}</p>
                                            </div>
                                        @endif
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
                        @if($r->status == 'Ditolak' || $r->status_delegasi == 'ditolak')
                            <div class="flex flex-col gap-2">
                                @if($r->catatan_tolak_delegasi)
                                    <div class="bg-amber-50 p-2 rounded-lg border border-amber-100 text-[10px]"><span class="font-bold text-amber-600">Delegasi Ditolak:</span> {{ $r->catatan_tolak_delegasi }}</div>
                                @endif
                                @if($r->catatan_tolak_atasan)
                                    <div class="bg-rose-50 p-2 rounded-lg border border-rose-100 text-[10px]"><span class="font-bold text-rose-600">Penolakan Atasan:</span> {{ $r->catatan_tolak_atasan }}</div>
                                @endif
                                @if($r->catatan_tolak_pejabat)
                                    <div class="bg-blue-50 p-2 rounded-lg border border-blue-200 text-[10px]"><span class="font-bold text-blue-700">Keputusan Kadis:</span> {{ $r->catatan_tolak_pejabat }}</div>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 italic">-</span>
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

        {{-- Modal Review Cuti (Premium Design) --}}
        <template x-if="showReviewModal">
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showReviewModal = false"></div>
                
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative z-10 border border-gray-100" 
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-primary to-blue-600 px-6 py-4 flex justify-between items-center text-white">
                        <div>
                            <h3 class="font-black text-lg">Tinjau Pengajuan Cuti</h3>
                            <p class="text-xs opacity-80" x-text="'NIP: ' + selectedCuti.pegawai.nip"></p>
                        </div>
                        <button @click="showReviewModal = false" class="hover:rotate-90 transition-transform">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- STEP 1: PERSETUJUAN DELEGASI (WAJIB) --}}
                        <div class="p-4 rounded-xl border-2 transition-all"
                            :class="delegasiStatus === 'disetujui' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                                <h4 class="font-bold text-gray-700 text-sm">Persetujuan Delegasi Tugas</h4>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-lg border border-gray-200 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-amber-500">handshake</span>
                                    </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Penerima Delegasi</p>
                                
                                {{-- Menampilkan Nama --}}
                                <p class="text-sm font-bold text-gray-800" 
                                x-text="selectedCuti.delegasi ? selectedCuti.delegasi.nama : 'Data tidak ditemukan'">
                                </p>

                                {{-- Menampilkan Jabatan & Unit Kerja --}}
                                <div class="flex flex-col mt-0.5" x-show="selectedCuti.delegasi">
                                    <p class="text-[10px] text-primary font-medium" 
                                    x-text="selectedCuti.delegasi.jabatan">
                                    </p>
                                    <p class="text-[10px] text-gray-500 italic" 
                                    x-text="selectedCuti.delegasi.unit_kerja">
                                    </p>
                                </div>
                            </div>
                                </div>
                                
                                {{-- Tombol Aksi Delegasi --}}
                                <div class="flex gap-2" x-show="delegasiStatus === 'pending'">
                                   <button @click="submitApproveDelegasi()" 
                                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition-all">
                                        Setujui
                                    </button>
                                    <button @click="showTolakDelegasi = true" class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-600 hover:text-white transition-all">Tolak</button>
                                </div>
                                
                                <div x-show="delegasiStatus === 'disetujui'" class="flex items-center gap-1 text-emerald-600 font-bold text-xs">
                                    <span class="material-symbols-outlined text-sm">check_circle</span> Terverifikasi
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2: KEPUTUSAN FINAL CUTI --}}
                        <div class="space-y-4" :class="delegasiStatus !== 'disetujui' && 'opacity-40 grayscale pointer-events-none'">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                                <h4 class="font-bold text-gray-700 text-sm">Keputusan Akhir Atasan</h4>
                            </div>

                            <div class="flex gap-3">
                                <form :action="'{{ url('atasan/approval') }}/' + selectedCuti.id + '/approve'" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-100 transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-sm">check</span> Setujui Cuti
                                    </button>
                                </form>
                                <button @click="showRejectModal = true" class="flex-1 py-3 border-2 border-rose-500 text-rose-500 rounded-xl font-bold hover:bg-rose-50 transition-all">
                                    Tolak Cuti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Modal Penolakan Delegasi --}}
<div x-show="showTolakDelegasi" class="fixed inset-0 z-[10000] flex items-center justify-center p-4" x-cloak>
    <div class="fixed inset-0 bg-black/50" @click="showTolakDelegasi = false"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 border border-rose-100">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Alasan Tolak Delegasi</h3>
        <p class="text-xs text-gray-500 mb-4">Berikan alasan mengapa orang yang ditunjuk tidak cocok sebagai delegasi.</p>
        
        <form :action="'{{ url('atasan/approval') }}/' + selectedCuti.id + '/tolak-delegasi'" method="POST">
            @csrf
            <textarea name="catatan_tolak_delegasi" rows="3" required
                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-rose-500 focus:border-rose-500 p-3 border text-sm"
                placeholder="Misal: Ybs sedang menangani proyek besar lainnya..."></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showTolakDelegasi = false" class="text-gray-500 text-sm font-bold">Batal</button>
                <button type="submit" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-rose-700 shadow-lg shadow-rose-100">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Penolakan Cuti --}}
<div x-show="showRejectModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4" x-cloak>
    <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 border border-rose-100">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Alasan Penolakan Cuti</h3>
        <p class="text-xs text-gray-500 mb-4">Jelaskan alasan pengajuan cuti pegawai ini ditolak secara final.</p>
        
        <form :action="'{{ url('atasan/approval') }}/' + selectedCuti.id + '/tolak'" method="POST">
            @csrf
            <textarea 
                name="catatan_tolak_atasan" 
                rows="3" 
                required
                {{-- Logika Alpine.js: Hapus semua karakter yang BUKAN huruf (a-z) atau spasi (\s) --}}
                @input="$el.value = $el.value.replace(/[^a-zA-Z\s]/g, '')"
                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-rose-500 focus:border-rose-500 p-3 border text-sm"
                placeholder="Misal: Mohon tunda cuti karena agenda dinas mendesak"></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showRejectModal = false" class="text-gray-500 text-sm font-bold">Batal</button>
                <button type="submit" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-rose-700 shadow-lg shadow-rose-100">Tolak Cuti</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ajukan Cuti untuk Atasan --}}
<div x-show="showCutiModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4" x-cloak>
    <div class="fixed inset-0 bg-black/50" @click="showCutiModal = false"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 border border-primary/20 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-2xl">beach_access</span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Ajukan Cuti</h3>
                <p class="text-xs text-gray-500">Pengajuan Anda akan langsung diteruskan ke Kadis</p>
            </div>
        </div>
        
        <form action="{{ route('atasan.cuti.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Pegawai Pengganti --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai Pengganti (Delegasi) <span class="text-red-500">*</span></label>
                <select name="id_delegasi" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm">
                    <option value="">-- Pilih Pegawai Pengganti --</option>
                    @foreach($rekanSebidang ?? [] as $rekan)
                        <option value="{{ $rekan->id }}">{{ $rekan->nama }} - {{ $rekan->jabatan }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jenis Cuti --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Cuti</label>
                <select name="jenis_cuti" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm">
                    <option value="Tahunan" selected>Cuti Tahunan</option>
                </select>
            </div>

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" required 
                        min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" required 
                        min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm">
                </div>
            </div>

            {{-- Alamat Selama Cuti --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Selama Cuti <span class="text-red-500">*</span></label>
                <input type="text" name="alamat" required maxlength="255" 
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm"
                    placeholder="Contoh: Jl. Merdeka No. 123, Jakarta">
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Cuti <span class="text-red-500">*</span></label>
                <textarea name="keterangan" rows="3" required maxlength="500"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary p-2.5 text-sm"
                    placeholder="Contoh: Keperluan keluarga, acara pernikahan, dll..."></textarea>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                <p class="font-semibold mb-1">ℹ️ Informasi:</p>
                <p>Pengajuan cuti Anda akan langsung masuk ke halaman Kadis untuk approval final karena Anda adalah Atasan.</p>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showCutiModal = false" 
                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                    Batal
                </button>
                <button type="submit" 
                    class="px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 shadow-md">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmApprove(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: "Apakah Anda yakin ingin menyetujui pengajuan cuti ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981', // Emerald 500
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

<script>
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
</script>

@endpush
@endsection