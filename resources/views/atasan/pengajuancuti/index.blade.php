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
    
    detailPending: {}, 
    detailRiwayat: {},
    hasPendingCuti: @json($hasPendingCuti ?? false),

    // FORM TAMBAH
    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,

    hitungHariTambah() {
        if (!this.tanggalMulaiTambah || !this.tanggalSelesaiTambah) { 
            this.jumlahHariTambah = 0; 
            return; 
        }
        
        const mulai = new Date(this.tanggalMulaiTambah);
        const selesai = new Date(this.tanggalSelesaiTambah);
        
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { 
            this.jumlahHariTambah = 0; 
            return; 
        }

        // Hitung hari kerja (tanpa weekend)
        let count = 0;
        let current = new Date(mulai);
        while (current <= selesai) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) count++;
            current.setDate(current.getDate() + 1);
        }
        this.jumlahHariTambah = count;
    },

    // FUNGSI DETAIL PENDING
    showPendingDetail(data) { 
        this.detailPending = { ...data };
        this.showDetailPending = true; 
    },

    // FUNGSI DETAIL RIWAYAT
    showRiwayatDetail(data) { 
        this.detailRiwayat = { ...data };
        this.showDetailRiwayat = true; 
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
                            <th class="px-1 py-1 font-semibold text-left">Alamat</th>
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
                                <td class="px-1 py-2">{{ $c->alamat ?? '-' }}</td>
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
                                    <button @click="showPendingDetail({
                                        nama: {{ Js::from($c->pegawai->nama ?? '-') }}, 
                                        nip: {{ Js::from($c->pegawai->nip ?? '-') }}, 
                                        jabatan: {{ Js::from($c->pegawai->jabatan ?? '-') }},
                                        pengganti_nama: {{ Js::from($c->delegasi->nama ?? '-') }}, 
                                        pengganti_jabatan: {{ Js::from($c->delegasi->jabatan ?? '') }}, 
                                        jenis_cuti: {{ Js::from($c->jenis_cuti ?? '') }}, 
                                        tanggal_mulai: {{ Js::from($c->tanggal_mulai ? $c->tanggal_mulai->translatedFormat('d M Y') : '-') }},
                                        tanggal_selesai: {{ Js::from($c->tanggal_selesai ? $c->tanggal_selesai->translatedFormat('d M Y') : '-') }}, 
                                        jumlah_hari: {{ Js::from($c->jumlah_hari ?? 0) }},
                                        alasan_cuti: {{ Js::from($c->keterangan ?? $c->alasan_cuti ?? '') }},
                                        alamat: {{ Js::from($c->alamat ?? '') }},
                                        status: {{ Js::from($c->status ?? '') }}
                                    })" class="p-1 text-sky-600 hover:bg-sky-50 rounded">
                                        <i class="fa-solid fa-eye text-[12px]"></i>
                                    </button>
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
                            <th class="px-1 py-1 font-semibold text-left">Alamat</th>
                            <th class="px-1 py-1 font-semibold text-left">Alasan</th>
                            <th class="px-1 py-1 text-center font-semibold">Status</th>
                            <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                            <th class="px-1 py-1 text-center font-semibold">Pengganti</th>
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
                                <td class="px-1 py-2">{{ Str::limit($r->alamat, 15) }}</td>
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
                                        pengganti_nama: {{ Js::from($r->delegasi->nama ?? '-') }},
                                        pengganti_jabatan: {{ Js::from($r->delegasi->jabatan ?? '') }},
                                        jenis_cuti: {{ Js::from($r->jenis_cuti ?? '') }},
                                        status: {{ Js::from($r->status ?? '') }},
                                        tanggal_mulai: {{ Js::from($r->tanggal_mulai ? $r->tanggal_mulai->format('d/m/Y') : '-') }},
                                        tanggal_selesai: {{ Js::from($r->tanggal_selesai ? $r->tanggal_selesai->format('d/m/Y') : '-') }},
                                        jumlah_hari: {{ Js::from($r->jumlah_hari ?? 0) }},
                                        alasan_cuti: {{ Js::from($r->keterangan ?? $r->alasan_cuti ?? '') }},
                                        alamat: {{ Js::from($r->alamat ?? '') }},
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

    {{-- =====================================
        MODAL DETAIL PENDING
    ===================================== --}}
    <div x-show="showDetailPending" 
        x-cloak 
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-4 backdrop-blur-sm">
        
        <div @click.outside="showDetailPending = false" 
            x-transition
            class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden text-gray-800 border-t-4 border-sky-500">
            
            <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50">
                <h3 class="text-[12px] font-bold text-sky-600 uppercase flex items-center gap-2">
                    <i class="fa-solid fa-info-circle"></i> Detail Pengajuan
                </h3>
                <button @click="showDetailPending = false" class="text-gray-400 hover:text-black text-xl">&times;</button>
            </div>

            <div class="p-4 space-y-3 text-[12px]">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Nama</p>
                        <p class="font-semibold" x-text="detailPending.nama"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">NIP</p>
                        <p class="font-semibold" x-text="detailPending.nip"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Jabatan</p>
                        <p class="font-semibold" x-text="detailPending.jabatan"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Jenis Cuti</p>
                        <p class="font-semibold" x-text="detailPending.jenis_cuti"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Tanggal Mulai</p>
                        <p class="font-semibold" x-text="detailPending.tanggal_mulai"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Tanggal Selesai</p>
                        <p class="font-semibold" x-text="detailPending.tanggal_selesai"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Jumlah Hari</p>
                        <p class="font-semibold" x-text="detailPending.jumlah_hari"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Status</p>
                        <p class="font-semibold" x-text="detailPending.status"></p>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Pengganti (Delegasi)</p>
                    <p class="font-semibold" x-text="detailPending.pengganti_nama + ' - ' + detailPending.pengganti_jabatan"></p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Alamat Selama Cuti</p>
                    <p class="font-semibold" x-text="detailPending.alamat"></p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Alasan Cuti</p>
                    <p class="font-semibold" x-text="detailPending.alasan_cuti"></p>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="showDetailPending = false" 
                    class="px-4 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded text-[11px] font-bold transition-colors shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- =====================================
        MODAL DETAIL RIWAYAT
    ===================================== --}}
    <div x-show="showDetailRiwayat" 
        x-cloak 
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-4 backdrop-blur-sm">
        
        <div @click.outside="showDetailRiwayat = false" 
            x-transition
            class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden text-gray-800 border-t-4 border-emerald-500">
            
            <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50">
                <h3 class="text-[12px] font-bold text-emerald-600 uppercase flex items-center gap-2">
                    <i class="fa-solid fa-history"></i> Detail Riwayat
                </h3>
                <button @click="showDetailRiwayat = false" class="text-gray-400 hover:text-black text-xl">&times;</button>
            </div>

            <div class="p-4 space-y-3 text-[12px]">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Nama</p>
                        <p class="font-semibold" x-text="detailRiwayat.nama"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Status</p>
                        <p class="font-semibold" x-text="detailRiwayat.status"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Jenis Cuti</p>
                        <p class="font-semibold" x-text="detailRiwayat.jenis_cuti"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Jumlah Hari</p>
                        <p class="font-semibold" x-text="detailRiwayat.jumlah_hari"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Tanggal Mulai</p>
                        <p class="font-semibold" x-text="detailRiwayat.tanggal_mulai"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Tanggal Selesai</p>
                        <p class="font-semibold" x-text="detailRiwayat.tanggal_selesai"></p>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Pengganti</p>
                    <p class="font-semibold" x-text="detailRiwayat.pengganti_nama"></p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Alamat</p>
                    <p class="font-semibold" x-text="detailRiwayat.alamat"></p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Alasan</p>
                    <p class="font-semibold" x-text="detailRiwayat.alasan_cuti"></p>
                </div>
                <div x-show="detailRiwayat.catatan">
                    <p class="text-[10px] text-gray-400 uppercase">Catatan Pejabat</p>
                    <p class="font-semibold text-emerald-700" x-text="detailRiwayat.catatan"></p>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="showDetailRiwayat = false" 
                    class="px-4 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded text-[11px] font-bold transition-colors shadow-sm">
                    Tutup
                </button>
            </div>
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

                            {{-- DELEGASI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-user-group text-sky-500 text-[10px]"></i>
                                    Pegawai Pengganti (Delegasi) <span class="text-red-500">*</span>
                                </label>
                                <select name="id_delegasi" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none">
                                    <option value="">-- Pilih Pegawai Pengganti --</option>
                                    @foreach($rekanSebidang ?? [] as $rekan)
                                        <option value="{{ $rekan->id }}">{{ $rekan->nama }} - {{ $rekan->jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- JENIS CUTI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-tag text-sky-500 text-[10px]"></i>
                                    Jenis Cuti
                                </label>
                                <input type="text" value="Cuti Tahunan" disabled
                                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-[11px] font-medium text-gray-600 cursor-not-allowed">
                                <input type="hidden" name="jenis_cuti" value="Tahunan">
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

                            {{-- ALAMAT --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-location-dot text-sky-500 text-[10px]"></i>
                                    Alamat Selama Cuti <span class="text-red-500">*</span>
                                </label>
                                <textarea name="alamat" rows="2" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none resize-none"
                                    placeholder="Masukkan alamat lengkap selama cuti..."></textarea>
                            </div>

                            {{-- ALASAN --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] font-semibold text-gray-600">
                                    <i class="fa-solid fa-pen text-sky-500 text-[10px]"></i>
                                    Alasan Cuti <span class="text-red-500">*</span>
                                </label>
                                <textarea name="keterangan" rows="2" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-[11px] focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none resize-none"
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
@endpush
@endsection
