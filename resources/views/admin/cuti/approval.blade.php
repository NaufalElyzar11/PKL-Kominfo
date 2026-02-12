@extends('layouts.admin')

@section('title', 'Approval Cuti Pegawai')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<div class="flex flex-col gap-6" x-data="{ showRejectModal: false, rejectId: null }">

    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-gray-800 text-2xl md:text-3xl font-black leading-tight tracking-[-0.033em]">Approval Cuti Pegawai</h1>
            <p class="text-gray-500 text-sm font-normal">Tinjau dan kelola pengajuan cuti pegawai yang sudah disetujui atasan.</p>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Card Menunggu --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-hourglass-half text-amber-500 text-6xl"></i>
            </div>
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Menunggu Konfirmasi</p>
            <div class="flex items-baseline gap-2">
                <p class="text-gray-800 text-3xl font-bold">{{ $stats['menunggu'] }}</p>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Permintaan Baru</span>
            </div>
        </div>

        {{-- Card Disetujui --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-check-circle text-emerald-500 text-6xl"></i>
            </div>
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Telah Disetujui</p>
            <div class="flex items-baseline gap-2">
                <p class="text-gray-800 text-3xl font-bold">{{ $stats['disetujui'] }}</p>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Total Approved</span>
            </div>
        </div>

        {{-- Card Ditolak --}}
        <div class="flex flex-col gap-1 rounded-xl p-5 bg-white border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-times-circle text-rose-500 text-6xl"></i>
            </div>
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Ditolak</p>
            <div class="flex items-baseline gap-2">
                <p class="text-gray-800 text-3xl font-bold">{{ $stats['ditolak'] }}</p>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">Total Rejected</span>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div>
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-inbox text-sky-500"></i>
            Daftar Pengajuan Menunggu (Lolos Tahap 1)
        </h3>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-gray-800">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-[10px] uppercase border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold w-12 text-center">No</th>
                            <th class="px-4 py-3 font-semibold min-w-[180px]">Pegawai</th>
                            <th class="px-4 py-3 font-semibold hidden md:table-cell">Jabatan</th>
                            <th class="px-4 py-3 font-semibold">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold min-w-[130px]">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-center">Durasi</th>
                            <th class="px-4 py-3 font-semibold text-center">Status</th>
                            <th class="px-4 py-3 font-semibold text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pengajuan as $index => $c)
                        <tr class="bg-white hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 py-3 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    {{-- Avatar Initials --}}
                                    <div class="h-8 w-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center font-bold shrink-0 text-xs">
                                        {{ strtoupper(substr($c->pegawai->nama ?? 'P', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800">{{ $c->pegawai->nama ?? 'Unknown' }}</span>
                                        <span class="text-[10px] text-gray-500 font-mono">{{ $c->pegawai->nip ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500">{{ $c->pegawai->jabatan ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    <span>{{ $c->jenis_cuti }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col text-[10px] font-medium">
                                    <span class="text-gray-800">{{ optional($c->tanggal_mulai)->format('d M Y') }}</span>
                                    <span class="text-gray-400">s.d. {{ optional($c->tanggal_selesai)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-[10px] font-bold border border-gray-200">
                                    {{ $c->jumlah_hari }} Hari
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Approve Button --}}
                                    <form id="form-approve-{{ $c->id }}" action="{{ route('admin.cuti.approve', $c->id) }}" method="POST">
                                        @csrf
                                        <button type="button" onclick="confirmApprove('{{ $c->id }}')" class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Setujui">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </form>
                                    
                                    {{-- Reject Button --}}
                                    <button type="button" 
                                            @click.stop="rejectId = {{ $c->id }}; showRejectModal = true" 
                                            class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" 
                                            title="Tolak">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                                    <p class="text-xs">Tidak ada pengajuan cuti yang menunggu konfirmasi saat ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- Modal Alasan Penolakan --}}
<div x-show="showRejectModal" 
    x-transition.opacity 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" 
    x-cloak>
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="showRejectModal = false">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            Alasan Penolakan
        </h3>

        <form :action="'{{ route('admin.cuti.reject', ['id' => '_id']) }}'.replace('_id', rejectId)" method="POST">
            @csrf

        <div x-data="{ count: 0 }">
            <textarea name="catatan_penolakan"
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


    {{-- Riwayat Section --}}
    <div>
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-sky-500"></i>
            Riwayat Pengajuan (Processed)
        </h3>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-gray-800">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white text-[10px] uppercase border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold w-12 text-center">No</th>
                            <th class="px-4 py-3 font-semibold">Pegawai</th>
                            <th class="px-4 py-3 font-semibold">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-center">Status</th>
                            <th class="px-4 py-3 font-semibold">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayat as $index => $r)
                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r->pegawai->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $r->jenis_cuti }}</td>
                            <td class="px-4 py-3">
                                {{ optional($r->tanggal_mulai)->format('d M Y') }} s.d. {{ optional($r->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($r->status == 'Disetujui' || $r->status == 'Disetujui Atasan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">
                                        {{ $r->status }}
                                    </span>
                                @elseif($r->status == 'Ditolak')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">
                                        {{ $r->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($r->status == 'Ditolak')
                                    <div class="bg-red-50 border border-red-200 text-red-700 text-[10px] p-2 rounded-md">
                                        <span class="font-semibold">Alasan:</span>
                                        {{ $r->catatan_final }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                Belum ada riwayat pengajuan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Notifikasi Sukses
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {!! \Illuminate\Support\Js::from(session('success')) !!}, 
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
            text: {!! \Illuminate\Support\Js::from($errors->first()) !!},
            confirmButtonColor: '#ef4444'
        });
    @endif

    // 3. Fungsi Approve
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
