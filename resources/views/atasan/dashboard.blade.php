@extends('layouts.pegawai')

@section('title', 'Dashboard Atasan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<div class="flex flex-col gap-6">

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
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-[#0d141b]">
                    <thead class="text-xs text-[#4c739a] uppercase bg-[#f8f9fa] border-b border-[#e7edf3]">
                        <tr>
                            <th class="px-6 py-4 font-semibold w-16 text-center">No</th>
                            <th class="px-6 py-4 font-semibold min-w-[200px]">Pegawai</th>
                            <th class="px-6 py-4 font-semibold hidden md:table-cell">Jabatan</th>
                            <th class="px-6 py-4 font-semibold">Jenis Cuti</th>
                            <th class="px-6 py-4 font-semibold min-w-[150px]">Tanggal</th>
                            <th class="px-6 py-4 font-semibold text-center">Durasi</th>
                            <th class="px-6 py-4 font-semibold text-center">Status</th>
                            <th class="px-6 py-4 font-semibold text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7edf3]">
                        @forelse($pengajuan as $index => $c)
                        <tr class="bg-white hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4 text-center font-medium text-[#9aaabb]">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
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
                            <td class="px-6 py-4 hidden md:table-cell text-[#4c739a]">{{ $c->pegawai->jabatan ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    <span>{{ $c->jenis_cuti }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-xs font-medium">
                                    <span class="text-[#0d141b]">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d M Y') }}</span>
                                    <span class="text-[#9aaabb]">s.d. {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200">
                                    {{ $c->jumlah_hari }} Hari
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Approve Button --}}
                                    {{-- Approve Button --}}
                                    <form id="form-approve-{{ $c->id }}" action="{{ route('atasan.approval.approve', $c->id) }}" method="POST">
                                        @csrf
                                        <button type="button" onclick="confirmApprove('{{ $c->id }}')" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Setujui">
                                            <span class="material-symbols-outlined text-[18px]">check</span>
                                        </button>
                                    </form>
                                    
                                    {{-- Reject Button --}}
                                    <form id="form-reject-{{ $c->id }}" action="{{ route('atasan.approval.reject', $c->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="catatan" value="Ditolak via Dashboard">
                                        <button type="button" onclick="confirmReject('{{ $c->id }}')" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Tolak">
                                            <span class="material-symbols-outlined text-[18px]">close</span>
                                        </button>
                                    </form>
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

            {{-- View All Link --}}
            @if(isset($pengajuan) && $pengajuan->count() > 0)
            <div class="flex items-center justify-center p-4 bg-white border-t border-[#e7edf3] rounded-b-xl border-x border-b">
                <a href="{{ route('atasan.approval.index') }}" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-primary/80 transition-colors">
                    Lihat Semua Pengajuan
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            @endif
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

    function confirmReject(id) {
        Swal.fire({
            title: 'Tolak Pengajuan?',
            text: "Pengajuan ini akan ditolak secara langsung.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48', // Rose 600
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-reject-' + id).submit();
            }
        })
    }
</script>
@endpush
@endsection