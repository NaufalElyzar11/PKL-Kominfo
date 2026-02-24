@extends('layouts.pegawai')

@section('title', 'Pengajuan Cuti')

@section('content')
@php
    use Illuminate\Pagination\LengthAwarePaginator;
    
    $cutiIsPaginator = isset($cuti) && $cuti instanceof LengthAwarePaginator;
    $riwayatIsPaginator = isset($riwayat) && $riwayat instanceof LengthAwarePaginator;

    $cutiCurrent = $cutiIsPaginator ? $cuti->currentPage() : 1;
    $cutiPerPage = $cutiIsPaginator ? $cuti->perPage() : 10;

    $riwayatCurrent = $riwayatIsPaginator ? $riwayat->currentPage() : 1;
    $riwayatPerPage = $riwayatIsPaginator ? $riwayat->perPage() : 10;

    $pegawai = $pegawai ?? (object)['id' => 1, 'nama' => '-', 'nip' => '-', 'jabatan' => '-', 'kuota_cuti' => 12];
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="{
    tab: 'menunggu',
    showModal: false, 
    showDetailPending: false, 
    showDetailRiwayat: false,
    showEditModal: false,
    selectedCuti: {},
    isChanged: false,

    jenisCutiTambah: '',
    alasanCutiTambah: '',
    
    detailPending: {}, 
    detailRiwayat: {},
    hasPendingCuti: @json($hasPendingCuti ?? false),

    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,
    jumlahHariEdit: 0,

    hitungHariTambah() {
        if (!this.tanggalMulaiTambah || !this.tanggalSelesaiTambah) { this.jumlahHariTambah = 0; return; }
        const mulai = new Date(this.tanggalMulaiTambah);
        const selesai = new Date(this.tanggalSelesaiTambah);
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { this.jumlahHariTambah = 0; return; }
        let count = 0, current = new Date(mulai);
        while (current <= selesai) { const d = current.getDay(); if (d !== 0 && d !== 6) count++; current.setDate(current.getDate() + 1); }
        this.jumlahHariTambah = count;
    },

    hitungHariEdit() {
        if (!this.selectedCuti.tanggal_mulai || !this.selectedCuti.tanggal_selesai) { this.jumlahHariEdit = 0; return; }
        const mulai = new Date(this.selectedCuti.tanggal_mulai);
        const selesai = new Date(this.selectedCuti.tanggal_selesai);
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { this.jumlahHariEdit = 0; return; }
        let count = 0, current = new Date(mulai);
        while (current <= selesai) { const d = current.getDay(); if (d !== 0 && d !== 6) count++; current.setDate(current.getDate() + 1); }
        this.jumlahHariEdit = count;
        this.selectedCuti.jumlah_hari = count;
    },

    showPendingDetail(data) { this.detailPending = { ...data }; this.showDetailPending = true; },
    showRiwayatDetail(data) { this.detailRiwayat = { ...data }; this.showDetailRiwayat = true; },
    openEdit(data) {
        this.selectedCuti = { ...data };
        this.jumlahHariEdit = data.jumlah_hari;
        this.isChanged = false;
        this.showEditModal = true;
    }

}" class="space-y-4 font-sans text-gray-800">

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Daftar Pengajuan Cuti</h2>
            <div class="flex items-center gap-2">
                <form method="GET" class="inline">
                    <select name="tahun" onchange="this.form.submit()" class="border border-gray-300 rounded-md text-xs px-2 py-1">
                        <option value="semua" {{ request('tahun') == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="2025" {{ request('tahun') == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2026" {{ request('tahun') == '2026' ? 'selected' : '' }}>2026</option>
                    </select>
                </form>
                <button @click="showModal = true" class="text-white bg-green-600 hover:bg-green-700 text-xs px-2 py-1 rounded-md flex items-center gap-1 shadow-sm transition">
                    <i class="fa-solid fa-plus-circle text-[10px]"></i>
                    <span>Ajukan Cuti</span>
                </button>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex gap-4 mb-4 border-b border-gray-200">
            <button @click="tab='menunggu'" :class="tab==='menunggu' ? 'text-sky-600 border-b-2 border-sky-600 font-semibold' : 'text-gray-500 hover:text-sky-500'" class="py-2 px-1 text-sm transition">Menunggu Persetujuan</button>
            <button @click="tab='riwayat'" :class="tab==='riwayat' ? 'text-sky-600 border-b-2 border-sky-600 font-semibold' : 'text-gray-500 hover:text-sky-500'" class="py-2 px-1 text-sm transition">Riwayat Cuti</button>
        </div>

        {{-- ================= TAB MENUNGGU ================= --}}
        <div x-show="tab === 'menunggu'" class="space-y-2">
            <div class="overflow-x-auto rounded border border-gray-300 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                    <thead class="bg-sky-600 text-white">
                        <tr>
                            <th class="px-1 py-1 text-center font-semibold">No</th>
                            <th class="px-1 py-1 font-semibold text-left">Nama</th>
                            <th class="px-1 py-1 font-semibold text-left">NIP</th>
                            <th class="px-1 py-1 font-semibold text-left">Jenis</th>
                            <th class="px-1 py-1 font-semibold text-left">Tanggal</th>
                            <th class="px-1 py-1 text-center font-semibold">Hari</th>
                            <th class="px-1 py-1 font-semibold text-left">Alasan</th>
                            <th class="px-1 py-1 text-center font-semibold">Status</th>
                            <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($cuti as $index => $c)
                            @php
                                $no = ($cutiCurrent - 1) * $cutiPerPage + $index + 1;
                            @endphp
                            <tr class="hover:bg-gray-50 text-gray-700">
                                <td class="px-1 py-2 text-center">{{ $no }}</td>
                                <td class="px-1 py-2">{{ $c->pegawai->nama ?? '-' }}</td>
                                <td class="px-1 py-2">{{ $c->pegawai->nip ?? '-' }}</td>
                                <td class="px-1 py-2">{{ $c->jenis_cuti }}</td>
                                <td class="px-1 py-2 leading-tight">
                                    {{ $c->tanggal_mulai->translatedFormat('d M Y') }} <br>
                                    s/d {{ $c->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-1 py-2 text-center font-bold">{{ $c->jumlah_hari }}</td>
                                <td class="px-1 py-2">{{ Str::limit($c->keterangan ?? $c->alasan_cuti, 20) }}</td>
                                <td class="px-1 py-2 text-center">
                                    @if($c->status == 'Menunggu')
                                        <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">Menunggu</span>
                                    @elseif($c->status == 'Disetujui Atasan')
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">
                                            <i class="fa-solid fa-clock mr-1"></i> Menunggu Pejabat
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[10px]">{{ $c->status }}</span>
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Detail --}}
                                        <button @click="showPendingDetail({
                                            nama: {{ Js::from($c->pegawai->nama ?? '-') }}, 
                                            nip: {{ Js::from($c->pegawai->nip ?? '-') }}, 
                                            jabatan: {{ Js::from($c->pegawai->jabatan ?? '-') }},
                                            jenis_cuti: {{ Js::from($c->jenis_cuti ?? '') }}, 
                                            tanggal_mulai: {{ Js::from($c->tanggal_mulai ? $c->tanggal_mulai->translatedFormat('d M Y') : '-') }},
                                            tanggal_selesai: {{ Js::from($c->tanggal_selesai ? $c->tanggal_selesai->translatedFormat('d M Y') : '-') }}, 
                                            jumlah_hari: {{ Js::from($c->jumlah_hari ?? 0) }},
                                            alasan_cuti: {{ Js::from($c->keterangan ?? $c->alasan_cuti ?? '') }},
                                            status: {{ Js::from($c->status ?? '') }}
                                        })" class="w-7 h-7 rounded-full bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white transition-all flex items-center justify-center" title="Detail">
                                            <i class="fa-solid fa-eye text-[11px]"></i>
                                        </button>
                                        {{-- Edit & Delete hanya untuk status Menunggu --}}
                                        @if($c->status === 'Menunggu')
                                        <button @click="openEdit({
                                            id: {{ $c->id }},
                                            jenis_cuti: {{ Js::from($c->jenis_cuti) }},
                                            tanggal_mulai: '{{ $c->tanggal_mulai->format('Y-m-d') }}',
                                            tanggal_selesai: '{{ $c->tanggal_selesai->format('Y-m-d') }}',
                                            jumlah_hari: {{ $c->jumlah_hari }},
                                            keterangan: {{ Js::from($c->keterangan ?? $c->alasan_cuti ?? '') }},
                                            nama: {{ Js::from($c->pegawai->nama ?? '-') }},
                                            nip: {{ Js::from($c->pegawai->nip ?? '-') }},
                                            jabatan: {{ Js::from($c->pegawai->jabatan ?? '-') }}
                                        })" class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center" title="Edit">
                                            <i class="fa-solid fa-pen text-[11px]"></i>
                                        </button>
                                        <button onclick="confirmDelete({{ $c->id }})" class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center" title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                        <form id="form-delete-{{ $c->id }}" action="{{ route('atasan.cuti.destroy', $c->id) }}" method="POST" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-2 py-4 text-center text-gray-500 italic">Tidak ada pengajuan menunggu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($cutiIsPaginator && $cuti->lastPage() > 1)
                <div class="flex justify-between items-center text-xs text-gray-700">
                    <p>Menampilkan {{ $cuti->firstItem() }} - {{ $cuti->lastItem() }} dari {{ $cuti->total() }} hasil</p>
                    <div>{{ $cuti->links('vendor.pagination.tailwind') }}</div>
                </div>
            @endif
        </div>

        {{-- ================= TAB RIWAYAT ================= --}}
        <div x-show="tab === 'riwayat'" x-cloak class="space-y-2">
            <div class="overflow-x-auto rounded border border-gray-300 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                    <thead class="bg-sky-600 text-white">
                        <tr>
                            <th class="px-1 py-1 text-center font-semibold">No</th>
                            <th class="px-1 py-1 font-semibold text-left">Nama</th>
                            <th class="px-1 py-1 text-center font-semibold">NIP</th>
                            <th class="px-1 py-1 text-center font-semibold">Jenis</th>
                            <th class="px-1 py-1 text-center font-semibold">Tanggal</th>
                            <th class="px-1 py-1 text-center font-semibold">Hari</th>
                            <th class="px-1 py-1 font-semibold text-left">Alasan</th>
                            <th class="px-1 py-1 text-center font-semibold">Status</th>
                            <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($riwayat as $index => $r)
                            @php
                                $noR = ($riwayatCurrent - 1) * $riwayatPerPage + $index + 1;
                                $status = strtolower(trim($r->status ?? ''));
                            @endphp
                            <tr class="hover:bg-gray-50 text-gray-700">
                                <td class="px-1 py-2 text-center">{{ $noR }}</td>
                                <td class="px-1 py-2">{{ $r->pegawai->nama ?? '-' }}</td>
                                <td class="px-1 py-2 text-center">{{ $r->pegawai->nip ?? '-' }}</td>
                                <td class="px-1 py-2 text-center">{{ $r->jenis_cuti }}</td>
                                <td class="px-1 py-2 text-center leading-tight">
                                    {{ optional($r->tanggal_mulai)->format('d/m/Y') }} <br> s/d {{ optional($r->tanggal_selesai)->format('d/m/Y') }}
                                </td>
                                <td class="px-1 py-2 text-center font-bold">{{ $r->jumlah_hari }}</td>
                                <td class="px-1 py-2">{{ Str::limit($r->keterangan ?? $r->alasan_cuti, 15) }}</td>
                                
                                <td class="px-1 py-2 text-center">
                                    @if($status == 'disetujui' || $status == 'disetujui kadis')
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Disetujui</span>
                                    @elseif($status == 'ditolak')
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Ditolak</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-center">
                                    <button @click="showRiwayatDetail({
                                        nama: {{ Js::from($r->pegawai->nama ?? '-') }},
                                        nip: {{ Js::from($r->pegawai->nip ?? '-') }},
                                        jabatan: {{ Js::from($r->pegawai->jabatan ?? '-') }},
                                        pengganti_jabatan: {{ Js::from($r->delegasi->jabatan ?? '') }},
                                        jenis_cuti: {{ Js::from($r->jenis_cuti ?? '') }},
                                        status: {{ Js::from($r->status ?? '') }},
                                        tanggal_mulai: {{ Js::from($r->tanggal_mulai ? $r->tanggal_mulai->format('d/m/Y') : '-') }},
                                        tanggal_selesai: {{ Js::from($r->tanggal_selesai ? $r->tanggal_selesai->format('d/m/Y') : '-') }},
                                        jumlah_hari: {{ Js::from($r->jumlah_hari ?? 0) }},
                                        alasan_cuti: {{ Js::from($r->keterangan ?? $r->alasan_cuti ?? '') }},
                                        catatan: {{ Js::from($r->catatan_final ?? '') }}
                                    })" class="p-1 text-sky-600 hover:bg-sky-100 rounded">
                                        <i class="fa-solid fa-eye text-[12px]"></i>
                                    </button>
                                </td>

                                <td class="px-1 py-2 border-l text-[10px] text-gray-700">
                                    <div class="font-bold text-sky-700">{{ $r->delegasi->nama ?? '-' }}</div>
                                    <div class="text-[9px] text-gray-400">{{ $r->delegasi->jabatan ?? '' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center py-4 text-gray-400 italic font-medium">Tidak ada riwayat cuti</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- MODAL DETAIL PENDING - PREMIUM --}}
    <div x-show="showDetailPending" x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="showDetailPending = false"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100" @click.stop
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><i class="fa-solid fa-file-circle-check text-white text-lg"></i></div>
                        <div><h3 class="text-white font-bold text-base">Detail Pengajuan Cuti</h3><p class="text-sky-100 text-[10px]">Menunggu persetujuan Pejabat</p></div>
                    </div>
                    <button @click="showDetailPending=false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center group"><i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform"></i></button>
                </div>
            </div>
            <div class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                <div class="flex justify-center">
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 flex items-center gap-2"><i class="fa-solid fa-hourglass-half"></i><span x-text="detailPending.status"></span></span>
                </div>
                <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100 flex items-center gap-2"><i class="fa-solid fa-user-tie text-sky-600 text-sm"></i><span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span></div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <div class="col-span-2 flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-id-badge text-sky-600"></i></div>
                            <div><p class="text-[9px] text-gray-400 uppercase">Nama Lengkap</p><p class="text-sm font-semibold text-gray-800" x-text="detailPending.nama"></p></div>
                        </div>
                        <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">NIP</p><p class="text-xs font-medium text-gray-700" x-text="detailPending.nip"></p></div>
                        <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">Jabatan</p><p class="text-xs font-medium text-gray-700" x-text="detailPending.jabatan"></p></div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 rounded-xl border border-sky-100 overflow-hidden">
                    <div class="px-4 py-2.5 bg-sky-100/50 border-b border-sky-100 flex items-center gap-2"><i class="fa-solid fa-calendar-days text-sky-600 text-sm"></i><span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider">Detail Cuti</span></div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Jenis Cuti</span><span class="text-xs font-bold text-sky-700" x-text="detailPending.jenis_cuti"></span></div>
                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Periode</span><div class="text-right"><p class="text-xs font-semibold text-gray-700" x-text="detailPending.tanggal_mulai"></p><p class="text-[10px] text-gray-400">s/d <span x-text="detailPending.tanggal_selesai"></span></p></div></div>
                        <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100"><p class="text-[9px] text-gray-400 uppercase">Total Hari</p><span class="text-3xl font-black text-sky-600" x-text="detailPending.jumlah_hari"></span><span class="text-[9px] text-gray-400">hari kerja</span></div>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2"><i class="fa-solid fa-pen-fancy text-sky-500 text-sm"></i><span class="text-[10px] font-bold text-gray-500 uppercase">Alasan Cuti</span></div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 italic" x-text="detailPending.alasan_cuti"></div>
                </div>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-t flex justify-end">
                <button @click="showDetailPending=false" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-xmark"></i> Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL RIWAYAT - PREMIUM --}}
    <div x-show="showDetailRiwayat" x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="showDetailRiwayat = false"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100" @click.stop
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="px-5 py-4" :class="detailRiwayat.status?.toLowerCase()==='disetujui' ? 'bg-gradient-to-r from-emerald-500 to-green-600' : detailRiwayat.status?.toLowerCase()==='ditolak' ? 'bg-gradient-to-r from-red-500 to-rose-600' : 'bg-gradient-to-r from-sky-500 to-blue-600'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><i class="fa-solid fa-clock-rotate-left text-white text-lg"></i></div>
                        <div><h3 class="text-white font-bold text-base">Detail Riwayat Cuti</h3><p class="text-white/70 text-[10px]">Informasi lengkap riwayat cuti</p></div>
                    </div>
                    <button @click="showDetailRiwayat=false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center group"><i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform"></i></button>
                </div>
            </div>
            <div class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                <div class="flex justify-center">
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold flex items-center gap-2"
                          :class="detailRiwayat.status?.toLowerCase()==='disetujui' ? 'bg-green-100 text-green-700' : detailRiwayat.status?.toLowerCase()==='ditolak' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'">
                        <i :class="detailRiwayat.status?.toLowerCase()==='disetujui' ? 'fa-solid fa-circle-check' : detailRiwayat.status?.toLowerCase()==='ditolak' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-hourglass-half'"></i>
                        <span x-text="detailRiwayat.status"></span>
                    </span>
                </div>
                <template x-if="detailRiwayat.catatan">
                    <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-comment text-blue-600"></i></div>
                        <div class="text-xs"><p class="font-bold text-blue-700 mb-1">Catatan Pejabat</p><p class="text-blue-600 italic" x-text="detailRiwayat.catatan"></p></div>
                    </div>
                </template>
                <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100 flex items-center gap-2"><i class="fa-solid fa-user-tie text-sky-600 text-sm"></i><span class="text-[10px] font-bold text-gray-500 uppercase">Data Pegawai</span></div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <div class="col-span-2 flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-id-badge text-sky-600"></i></div>
                            <div><p class="text-[9px] text-gray-400 uppercase">Nama Lengkap</p><p class="text-sm font-semibold text-gray-800" x-text="detailRiwayat.nama"></p></div>
                        </div>
                        <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">NIP</p><p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.nip"></p></div>
                        <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">Jabatan</p><p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.jabatan"></p></div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 rounded-xl border border-sky-100 overflow-hidden">
                    <div class="px-4 py-2.5 bg-sky-100/50 border-b border-sky-100 flex items-center gap-2"><i class="fa-solid fa-calendar-days text-sky-600 text-sm"></i><span class="text-[10px] font-bold text-sky-600 uppercase">Detail Cuti</span></div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Jenis Cuti</span><span class="text-xs font-bold text-sky-700" x-text="detailRiwayat.jenis_cuti"></span></div>
                        <div class="flex items-center justify-between"><span class="text-xs text-gray-500">Periode</span><div class="text-right"><p class="text-xs font-semibold text-gray-700" x-text="detailRiwayat.tanggal_mulai"></p><p class="text-[10px] text-gray-400">s/d <span x-text="detailRiwayat.tanggal_selesai"></span></p></div></div>
                        <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100"><p class="text-[9px] text-gray-400 uppercase">Total Hari</p><span class="text-3xl font-black text-sky-600" x-text="detailRiwayat.jumlah_hari"></span><span class="text-[9px] text-gray-400">hari kerja</span></div>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2"><i class="fa-solid fa-pen-fancy text-sky-500 text-sm"></i><span class="text-[10px] font-bold text-gray-500 uppercase">Alasan Cuti</span></div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 italic" x-text="detailRiwayat.alasan_cuti"></div>
                </div>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-t flex justify-end">
                <button @click="showDetailRiwayat=false" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-xmark"></i> Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT CUTI - PREMIUM --}}
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
         @click.self="showEditModal = false"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100" @click.stop
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><i class="fa-solid fa-pen-to-square text-white text-lg"></i></div>
                        <div><h3 class="text-white font-bold text-base">Edit Pengajuan Cuti</h3><p class="text-amber-100 text-[10px]">Ubah data pengajuan yang masih menunggu</p></div>
                    </div>
                    <button @click="showEditModal=false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center group"><i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform"></i></button>
                </div>
            </div>
            <form :action="'/atasan/cuti/' + selectedCuti.id" method="POST">
                @csrf @method('PUT')
                <div class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    {{-- Info Pegawai --}}
                    <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl border border-gray-100 overflow-hidden">
                        <div class="px-4 py-2.5 bg-gray-100/50 border-b border-gray-100 flex items-center gap-2"><i class="fa-solid fa-user-tie text-amber-600 text-sm"></i><span class="text-[10px] font-bold text-gray-500 uppercase">Data Pegawai</span></div>
                        <div class="p-4 grid grid-cols-2 gap-3">
                            <div class="col-span-2 flex items-center gap-3 pb-3 border-b border-gray-100">
                                <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-id-badge text-amber-600"></i></div>
                                <div><p class="text-[9px] text-gray-400 uppercase">Nama Lengkap</p><p class="text-sm font-semibold text-gray-800" x-text="selectedCuti.nama"></p></div>
                            </div>
                            <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">NIP</p><p class="text-xs font-medium text-gray-700" x-text="selectedCuti.nip"></p></div>
                            <div><p class="text-[9px] text-gray-400 uppercase mb-0.5">Jabatan</p><p class="text-xs font-medium text-gray-700" x-text="selectedCuti.jabatan"></p></div>
                        </div>
                    </div>
                    {{-- Jenis Cuti --}}
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600"><i class="fa-solid fa-tag text-amber-500 text-[10px]"></i>Jenis Cuti</label>
                        <div class="relative">
                            <select name="jenis_cuti" x-model="selectedCuti.jenis_cuti" @change="isChanged=true"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-xs outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 appearance-none">
                                <option value="Tahunan">Cuti Tahunan</option>
                                <option value="Alasan Penting">Cuti Alasan Penting</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i class="fa-solid fa-chevron-down text-[10px]"></i></div>
                        </div>
                    </div>
                    {{-- Periode Cuti --}}
                    <div class="bg-gradient-to-br from-sky-50 to-blue-50 rounded-xl border border-sky-100 overflow-hidden">
                        <div class="px-4 py-2.5 bg-sky-100/50 border-b border-sky-100 flex items-center gap-2"><i class="fa-solid fa-calendar-days text-sky-600 text-sm"></i><span class="text-[10px] font-bold text-sky-600 uppercase">Periode Cuti</span></div>
                        <div class="p-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-semibold text-gray-500">Mulai</label>
                                    <input type="date" name="tanggal_mulai" x-model="selectedCuti.tanggal_mulai" @change="hitungHariEdit(); isChanged=true"
                                           class="w-full bg-white border border-sky-200 rounded-lg px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-sky-100 focus:border-sky-400">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-semibold text-gray-500">Selesai</label>
                                    <input type="date" name="tanggal_selesai" x-model="selectedCuti.tanggal_selesai" @change="hitungHariEdit(); isChanged=true" :min="selectedCuti.tanggal_mulai"
                                           class="w-full bg-white border border-sky-200 rounded-lg px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-sky-100 focus:border-sky-400">
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-white rounded-xl border border-sky-100 px-4 py-2.5">
                                <span class="text-xs text-gray-500 font-medium">Total Hari Kerja</span>
                                <div class="flex items-baseline gap-1"><span class="text-xl font-black text-sky-600" x-text="jumlahHariEdit"></span><span class="text-[10px] text-gray-400">hari</span></div>
                                <input type="hidden" name="jumlah_hari" :value="jumlahHariEdit">
                            </div>
                        </div>
                    </div>
                    {{-- Alasan --}}
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600"><i class="fa-solid fa-pen-fancy text-amber-500 text-[10px]"></i>Alasan Cuti</label>
                        <textarea name="keterangan" x-model="selectedCuti.keterangan" @input="isChanged=true; $event.target.value = $event.target.value.replace(/[^A-Za-z\s]/g, '')"
                                  rows="2" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none resize-none"
                                  placeholder="Jelaskan alasan cuti..."></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t flex items-center justify-end gap-2">
                    <button type="button" @click="showEditModal=false" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" :disabled="!isChanged"
                            :class="!isChanged ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600 shadow-lg'"
                            class="px-6 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =====================================
        MODAL AJUKAN CUTI
    ===================================== --}}
    <template x-if="showModal">
        <div
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
            @click.self="showModal = false"
            x-cloak
            x-transition
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-xl overflow-hidden border border-gray-100"
                @click.stop
                x-transition
            >
                {{-- HEADER --}}
                <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fa-solid fa-calendar-plus text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-base tracking-wide">Ajukan Cuti</h3>
                                <p class="text-sky-100 text-[10px]">Pengajuan langsung ke Pejabat</p>
                            </div>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                            <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                        </button>
                    </div>
                </div>

                {{-- FORM CONTENT --}}
                <div class="p-4 sm:p-6 max-h-[80vh] overflow-y-auto">
                    <form action="{{ route('atasan.cuti.store') }}" method="POST">
                        @csrf

                        {{-- WARNING PENDING --}}
                        <div x-show="hasPendingCuti" x-transition
                             class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-clock text-amber-600"></i>
                            </div>
                            <div class="text-[11px]">
                                <p class="font-bold text-amber-800">Pengajuan Pending</p>
                                <p class="text-amber-700">Ada pengajuan yang masih menunggu persetujuan.</p>
                            </div>
                        </div>

                        <fieldset :disabled="hasPendingCuti" class="space-y-4">
                            {{-- INFO PEGAWAI --}}
                            <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fa-solid fa-user-tie text-sky-600 text-sm"></i>
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Data Pegawai</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div>
                                        <p class="text-gray-400">Nama</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">NIP</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->nip ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Jabatan</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->jabatan ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Pejabat Pemberi Cuti</p>
                                        <p class="font-semibold text-gray-800">{{ $pegawai->pemberi_cuti ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- JENIS CUTI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-tag text-sky-500 text-[10px]"></i>
                                    Jenis Cuti <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                <select name="jenis_cuti" 
                                        x-model="jenisCutiTambah" 
                                        {{-- LOGIKA: Jika pilih Tahunan, set alasan otomatis ke 'Hak ASN' --}}
                                        @change="if(jenisCutiTambah === 'Tahunan') { alasanCutiTambah = 'Hak ASN' } else { alasanCutiTambah = '' }"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] font-medium text-gray-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition-all appearance-none"
                                        required>
                                    <option value="" disabled selected>— Pilih jenis cuti —</option>
                                    <option value="Tahunan">Cuti Tahunan</option>
                                    <option value="Alasan Penting">Cuti Alasan Penting</option>
                                </select>
                                    
                                    {{-- Ikon Panah Kecil --}}
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-[9px]"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- TANGGAL --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-calendar-days text-sky-500 text-[10px]"></i>
                                    Periode Cuti <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="date" name="tanggal_mulai" 
                                        x-model="tanggalMulaiTambah"
                                        @change="hitungHariTambah()"
                                        min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none"
                                        required>
                                    <input type="date" name="tanggal_selesai" 
                                        x-model="tanggalSelesaiTambah"
                                        @change="hitungHariTambah()"
                                        :min="tanggalMulaiTambah"
                                        :disabled="!tanggalMulaiTambah"
                                        :class="!tanggalMulaiTambah ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none"
                                        required>
                                </div>
                                <p class="text-[9px] text-gray-400 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Jumlah hari kerja: <span class="font-bold text-sky-600" x-text="jumlahHariTambah">0</span> hari
                                </p>
                            </div>

                            {{-- ALASAN --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-pen text-sky-500 text-[10px]"></i>
                                    Alasan Cuti <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="keterangan" 
                                    rows="2" 
                                    required
                                    {{-- 1. Hubungkan ke variabel Alpine --}}
                                    x-model="alasanCutiTambah"
                                    {{-- 2. Kunci input jika jenis cuti adalah Tahunan --}}
                                    :readonly="jenisCutiTambah === 'Tahunan'"
                                    {{-- 3. Beri warna abu-abu jika terkunci --}}
                                    :class="jenisCutiTambah === 'Tahunan' ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                    {{-- 4. Filter hanya huruf dan spasi --}}
                                    @input="alasanCutiTambah = $event.target.value.replace(/[^A-Za-z\s]/g, '')"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none resize-none"
                                    placeholder="Jelaskan alasan pengajuan cuti..."></textarea>
                            </div>
                        </fieldset>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2.5 text-[11px] font-semibold text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-100 transition-all">
                                Batal
                            </button>
                            <button type="submit" :disabled="hasPendingCuti"
                                class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white text-[11px] font-bold rounded-xl shadow-lg shadow-sky-200 hover:shadow-xl hover:from-sky-600 hover:to-blue-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <i class="fa-solid fa-paper-plane"></i>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), showConfirmButton: false, timer: 2500 });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')) });
    @endif

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: 'Pengajuan cuti ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
