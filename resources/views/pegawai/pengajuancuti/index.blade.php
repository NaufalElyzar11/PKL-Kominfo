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
    showEditModal: false, 
    showDetailPending: false, 
    showDetailRiwayat: false, 
    showDelete: false, 
    openCatatanKadis: false,
    deleteId: null,
    namaHapus: '',
    catatanContent: '',
    
    // Inisialisasi Objek (WAJIB ADA AGAR TIDAK ERROR)
    detailPending: {}, 
    detailRiwayat: {},
    
    selectedCuti: {}, // <-- Tambahkan ini

    // FORM TAMBAH
    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,
    hitungHariTambah() {
        if (!this.tanggalMulaiTambah || !this.tanggalSelesaiTambah) { this.jumlahHariTambah = 0; return; }
        const mulai = new Date(this.tanggalMulaiTambah);
        const selesai = new Date(this.tanggalSelesaiTambah);
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { this.jumlahHariTambah = 0; return; }
        const diff = Math.floor((selesai - mulai) / (1000 * 60 * 60 * 24)) + 1;
        this.jumlahHariTambah = diff > 0 ? diff : 0;
    },

    // FUNGSI EDIT
    openEditModal(data) {
        this.selectedCuti = {
            id: data.id,
            nama: data.nama,
            nip: data.nip,
            jabatan: data.jabatan,
            jenis_cuti: data.jenis_cuti,
            sisa_cuti: data.sisa_cuti,
            tanggal_mulai: data.tanggal_mulai_raw, 
            tanggal_selesai: data.tanggal_selesai_raw, 
            atasan: data.atasan,
            pejabat: data.pejabat,
            alasan_cuti: data.alasan_cuti,
            status: data.status,
            jumlah_hari: data.jumlah_hari // Tambahkan agar durasi awal langsung muncul
        };
            this.originalCuti = JSON.parse(JSON.stringify(this.selectedCuti));
            this.isChanged = false;

        this.showEditModal = true;
    },

    hitungHariEdit() {
        if (!this.selectedCuti.tanggal_mulai || !this.selectedCuti.tanggal_selesai) return;
        const mulai = new Date(this.selectedCuti.tanggal_mulai);
        const selesai = new Date(this.selectedCuti.tanggal_selesai);
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { 
            this.selectedCuti.jumlah_hari = 0; 
            return; 
        }
        const diff = Math.floor((selesai - mulai) / (1000*60*60*24)) + 1;
        this.selectedCuti.jumlah_hari = diff > 0 ? diff : 0;
    },
    checkChange() {
    this.isChanged =
        JSON.stringify(this.selectedCuti)
        !== JSON.stringify(this.originalCuti);
},


    // FUNGSI DETAIL PENDING
    showPendingDetail(data) { 
        this.detailPending = { ...data }; // Cara cepat meng-copy semua data
        this.showDetailPending = true; 
    },

    // FUNGSI DETAIL RIWAYAT
    showRiwayatDetail(data) { 
        this.detailRiwayat = { ...data }; // Cara cepat meng-copy semua data
        this.showDetailRiwayat = true; 
    },

    showCatatanKadis(pesan) { this.catatanContent = pesan; this.openCatatanKadis = true; },
    openDelete(id, nama) { this.deleteId = id; this.namaHapus = nama; this.showDelete = true; }

}" class="space-y-4 font-sans text-gray-800">
    {{-- Alert --}}
    @if(session('success'))
        <div x-init="setTimeout(() => $el.remove(), 4000)" class="fixed top-5 right-5 z-[9999] bg-green-500 text-white px-6 py-3 rounded shadow-lg text-sm">
            <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

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

        {{-- ================= TAB MENUNGGU (11 KOLOM) ================= --}}
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
                                $nip = $c->pegawai->nip ?? '-';
                                $nipMasked = (strlen($nip) > 6) ? substr($nip, 0, 3) . str_repeat('*', strlen($nip)-6) . substr($nip, -3) : $nip;
                            @endphp
                            <tr class="hover:bg-gray-50 text-gray-700">
                                <td class="px-1 py-2 text-center">{{ $no }}</td>
                                <td class="px-1 py-2">{{ $c->pegawai->nama ?? '-' }}</td>
                                <td class="px-1 py-2">{{ $nipMasked }}</td>
                                <td class="px-1 py-2">{{ $c->jenis_cuti }}</td>
                                <td class="px-1 py-2 leading-tight">
                                    {{ $c->tanggal_mulai->translatedFormat('d M Y') }} <br>
                                    s/d {{ $c->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-1 py-2 text-center font-bold">{{ $c->jumlah_hari }}</td>
                                <td class="px-1 py-2">{{ Str::limit($c->alasan_cuti, 20) }}</td>
                                <td class="px-1 py-2">{{ $c->alamat ?? '-' }}</td>
                                <td class="px-1 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">Menunggu</span>
                                </td>
                                <td class="px-1 py-2 text-center flex justify-center gap-1">
                                    <button @click="showPendingDetail({
                                        nama: '{{ $c->pegawai->nama }}', nip: '{{ $c->pegawai->nip }}', jabatan: '{{ $c->pegawai->jabatan }}',
                                        jenis_cuti: '{{ $c->jenis_cuti }}', tanggal_mulai: '{{ $c->tanggal_mulai->translatedFormat('d M Y') }}',
                                        tanggal_selesai: '{{ $c->tanggal_selesai->translatedFormat('d M Y') }}', jumlah_hari: '{{ $c->jumlah_hari }}',
                                    })" class="p-1 text-sky-600 hover:bg-sky-50 rounded"><i class="fa-solid fa-eye text-[12px]"></i></button>
                                    
                                    <button @click="openEditModal({
                                        id: {{ $c->id }},
                                        nama: @js($c->pegawai->nama),
                                        nip: @js($c->pegawai->nip),
                                        jabatan: @js($c->pegawai->jabatan),
                                        jenis_cuti: @js($c->jenis_cuti),
                                        sisa_cuti: @js($c->sisa_cuti ?? 0),
                                        tanggal_mulai_raw: @js($c->tanggal_mulai->format('Y-m-d')),
                                        tanggal_selesai_raw: @js($c->tanggal_selesai->format('Y-m-d')),
                                        alasan_cuti: @js($c->alasan_cuti),
                                        jumlah_hari: @js($c->jumlah_hari)
                                    })"
                                    class="p-1 text-yellow-600 hover:bg-yellow-50 rounded">
                                        <i class="fa-solid fa-pen-to-square text-[12px]"></i>
                                    </button>


                                    <button @click="openDelete({{ $c->id }}, '{{ $c->pegawai->nama }}')" class="p-1 text-red-600 hover:bg-red-50 rounded"><i class="fa-solid fa-trash text-[12px]"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="px-2 py-4 text-center text-gray-500 italic">Tidak ada data pending</td></tr>
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

{{-- ================= TAB RIWAYAT (13 KOLOM) ================= --}}
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
                    <th class="px-1 py-1 text-center font-semibold">Sisa</th>
                    <th class="px-1 py-1 font-semibold text-left">Alamat</th>
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
                        $nipR = $r->pegawai->nip ?? '-';
                        $nipMaskedR = (strlen($nipR) > 6) ? substr($nipR, 0, 3) . '***' . substr($nipR, -3) : $nipR;

                        /** * LOGIKA SISA CUTI:
                         * 1. Ambil jatah dasar (12)
                         * 2. Hitung total pemakaian yang 'disetujui' di tahun tersebut 
                         * sampai dengan baris ini (berdasarkan ID)
                         */
                        $kuotaDasar = 12;
                        $pemakaianKumulatif = \App\Models\Cuti::where('pegawai_id', $r->pegawai_id)
                            ->where('tahun', $r->tahun)
                            ->where('status', 'disetujui')
                            ->where('id', '<=', $r->id)
                            ->sum('jumlah_hari');
                        
                        $sisa_final = $kuotaDasar - $pemakaianKumulatif;
                    @endphp
                    <tr class="hover:bg-gray-50 text-gray-700">
                        <td class="px-1 py-2 text-center">{{ $noR }}</td>
                        <td class="px-1 py-2">{{ $r->pegawai->nama ?? '-' }}</td>
                        <td class="px-1 py-2 text-center">{{ $nipMaskedR }}</td>
                        <td class="px-1 py-2 text-center">{{ $r->jenis_cuti }}</td>
                        <td class="px-1 py-2 text-center leading-tight">
                            {{ optional($r->tanggal_mulai)->format('d/m/Y') }} <br> s/d {{ optional($r->tanggal_selesai)->format('d/m/Y') }}
                        </td>
                        <td class="px-1 py-2 text-center font-bold">{{ $r->jumlah_hari }}</td>
                        
                        {{-- REVISI KOLOM SISA --}}
                        <td class="px-1 py-2 text-center font-bold">
                            <span class="{{ $sisa_final <= 3 ? 'text-red-600' : 'text-sky-600' }}">
                                {{ $sisa_final }}
                            </span>
                        </td>

                        <td class="px-1 py-2">{{ Str::limit($r->alamat, 20) }}</td>
                        <td class="px-1 py-2">{{ Str::limit($r->alasan_cuti, 20) }}</td>
                        <td class="px-1 py-2 text-center">
                            @if($status == 'disetujui')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Disetujui</span>
                            @elseif($status == 'ditolak')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Ditolak</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px]">{{ $r->status }}</span>
                            @endif
                        </td>
                        <td class="px-1 py-2 text-center flex justify-center gap-1">
                            {{-- Tombol Detail --}}
                            <button @click="
                                detailRiwayat = {
                                    id: '{{ $r->id }}',
                                    nama: '{{ $r->pegawai->nama ?? '-' }}',
                                    nip: '{{ $r->pegawai->nip ?? '-' }}',
                                    jabatan: '{{ $r->pegawai->jabatan ?? '-' }}',
                                    jenis_cuti: '{{ $r->jenis_cuti }}',
                                    status: '{{ $r->status }}',
                                    tanggal_mulai: '{{ optional($r->tanggal_mulai)->format('d/m/Y') }}',
                                    tanggal_selesai: '{{ optional($r->tanggal_selesai)->format('d/m/Y') }}',
                                    jumlah_hari: '{{ $r->jumlah_hari }}',
                                    sisa_cuti: '{{ $sisa_final }}',
                                    atasan: '{{ $r->atasanLangsung->nama_atasan ?? $r->atasan_nama ?? '-' }}',
                                    pejabat: '{{ $r->pejabatPemberiCuti->nama_pejabat ?? $r->pejabat_nama ?? '-' }}',
                                    alasan_cuti: '{{ addslashes($r->alasan_cuti) }}',
                                    alamat: '{{ addslashes($r->alamat) }}'
                                };
                                showDetailRiwayat = true;
                            " class="p-1 text-sky-600 hover:bg-sky-100 rounded">
                                <i class="fa-solid fa-eye text-[12px]"></i>
                            </button>

                            @if ($status === 'ditolak')
                                <button @click="
                                    detail = { nama: '{{ $r->pegawai->nama }}' };
                                    catatanContent = '{{ addslashes($r->catatan_penolakan ?? 'Tidak ada catatan.') }}';
                                    openCatatanKadis = true;
                                " class="p-1 text-yellow-600 hover:bg-yellow-100 rounded">
                                    <i class="fa-solid fa-note-sticky text-[12px]"></i>
                                </button>
                            @endif

                            <button @click="openDelete({{ $r->id }}, '{{ $r->pegawai->nama }}')" class="p-1 text-red-600 hover:bg-red-100 rounded">
                                <i class="fa-solid fa-trash text-[12px]"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center py-4 text-gray-400 italic font-medium">Tidak ada riwayat cuti</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($riwayatIsPaginator && $riwayat->lastPage() > 1)
        <div class="flex justify-between items-center text-xs text-gray-700">
            <p>Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} hasil</p>
            <div>{{ $riwayat->links('vendor.pagination.tailwind') }}</div>
        </div>
    @endif
</div>

{{-- =====================================
    MODAL CATATAN KADIS (HANYA DITOLAK)
===================================== --}}
<div x-show="openCatatanKadis" 
    x-cloak 
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-4 backdrop-blur-sm">
    
    <div @click.outside="openCatatanKadis = false" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden text-gray-800 border-t-4 border-red-500">
        
        <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50">
            <h3 class="text-[12px] font-bold text-red-600 uppercase flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> Alasan Penolakan Kadis
            </h3>
            <button @click="openCatatanKadis = false" class="text-gray-400 hover:text-black text-xl">&times;</button>
        </div>

        <div class="p-6 text-[13px]">
            <div class="mb-4">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pegawai:</span>
                <p class="font-bold text-gray-700" x-text="detail.nama"></p>
            </div>

            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-wider">Pesan/Catatan Penolakan:</label>
            
            <div class="bg-red-50 border border-red-100 p-4 rounded-lg text-red-900 leading-relaxed italic shadow-sm" 
                 x-text="catatanContent">
            </div>
        </div>

        <div class="px-4 py-3 bg-gray-50 border-t flex justify-end">
            <button @click="openCatatanKadis = false" 
                class="px-4 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded text-[11px] font-bold transition-colors shadow-sm">
                Tutup
            </button>
        </div>
    </div>
</div>

<div x-data="{
    hasPendingCuti: @json($hasPendingCuti ?? false), 
    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,
    hitungHariTambah() {
        if (!this.tanggalMulaiTambah || !this.tanggalSelesaiTambah) { this.jumlahHariTambah = 0; return; }
        const mulai = new Date(this.tanggalMulaiTambah);
        const selesai = new Date(this.tanggalSelesaiTambah);
        if (mulai > selesai) { this.jumlahHariTambah = 0; return; }
        this.jumlahHariTambah = Math.floor((selesai - mulai) / (1000 * 60 * 60 * 24)) + 1;
    }
}"
x-show="showModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-3">

    <div @click.away="showModal=false" 
     x-transition.scale
     class="bg-white rounded-xl shadow-xl w-full max-w-sm p-4 border border-gray-200">

    <h3 class="text-sm font-bold text-sky-600 mb-2 pb-1.5 border-b flex items-center gap-2">
        <i class="fa-solid fa-calendar-plus text-xs"></i> Ajukan Cuti Baru
    </h3>

    <form action="{{ route('pegawai.cuti.store') }}" method="POST" class="space-y-2 text-[10px]">
        @csrf
        
        <div x-show="hasPendingCuti" class="p-2 bg-red-50 border border-red-200 text-red-700 rounded text-[9px] leading-tight">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> Ada pengajuan yang masih <b>Menunggu Persetujuan</b>.
        </div>

        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2 rounded border border-gray-200 text-gray-500">
            <div class="col-span-2 border-b border-gray-200 pb-1 mb-1">
                <span class="font-bold">Nama:</span> <span x-text="'{{ $pegawai->nama ?? '-' }}'"></span>
            </div>
            <div><span class="font-bold">NIP:</span> {{ $pegawai->nip ?? '-' }}</div>
            <div><span class="font-bold">Jabatan:</span> {{ $pegawai->jabatan ?? '-' }}</div>
        </div>

        <fieldset :disabled="hasPendingCuti && !showEditModal" class="space-y-2">
            <div>
                <label class="font-bold text-gray-600">Jenis Cuti *</label>
                <input type="text"
                    value="Tahunan"
                    class="w-full mt-0.5 p-1 rounded border border-gray-300 bg-gray-100"
                    disabled>

                <!-- Nilai yang dikirim ke backend -->
                <input type="hidden" name="jenis_cuti" value="Tahunan">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="font-bold text-gray-600">Mulai *</label>
                    <input type="date" name="tanggal_mulai" x-model="tanggalMulaiTambah" min="{{ \Carbon\Carbon::today()->addDays(3)->toDateString() }}" @change="hitungHariTambah()" class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none" required>
                </div>
                <div>
                    <label class="font-bold text-gray-600">Selesai *</label>
                    <input type="date" name="tanggal_selesai" x-model="tanggalSelesaiTambah" :min="tanggalMulaiTambah" @change="hitungHariTambah()" class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <div>
                <label class="font-bold text-gray-600">Alamat *</label>
                <textarea 
                    name="alamat"
                    rows="1"
                    class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none resize-none"
                    placeholder="Alamat..."
                    required
                    oninput="this.value = this.value.replace(/[^A-Za-z0-9\s]/g,'')"></textarea>
                </div>

            </div>
                <div>
                    <label class="font-bold text-gray-600">Alasan *</label>
                    <textarea 
                        name="keterangan" 
                        rows="1" 
                        class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none resize-none" 
                        placeholder="Alasan..." 
                        pattern="[A-Za-z\s]+"
                        title="Alasan cuti hanya boleh huruf"
                        required
                        {{-- Mencegah input angka secara real-time --}}
                        oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                        {{-- Validasi regex: hanya huruf dan spasi, minimal 5 karakter --}}
                        pattern="^[a-zA-Z\s]+$"
                        title="Alasan hanya boleh berisi huruf dan tidak boleh ada angka"></textarea>
                </div>
            </div>

            <div class="flex justify-between items-center bg-sky-50 p-1.5 rounded border border-sky-100 mt-1">
                <span class="font-bold text-sky-700 uppercase tracking-tighter text-[9px]">
                    Jumlah Hari Cuti
                </span>

                <div class="text-sky-800 font-black">
                    <span x-text="jumlahHariTambah"></span> Hari
                </div>

                <input type="hidden" name="jumlah_hari" x-bind:value="jumlahHariTambah">
            </div>

        </fieldset>

        <div class="flex justify-end gap-2 pt-2 border-t mt-1">
            <button type="button" @click="showModal=false" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
            <button type="submit" 
                    :disabled="hasPendingCuti"
                    class="px-3 py-1 text-white rounded-lg font-bold flex items-center gap-1 transition active:scale-95"
                    :class="hasPendingCuti ? 'bg-gray-400' : 'bg-sky-600 hover:bg-sky-700'">
                <i class="fa-solid fa-paper-plane text-[9px]"></i> 
                <span x-text="hasPendingCuti ? 'Terkunci' : 'Kirim'"></span>
            </button>
        </div>
    </form>
</div>

{{-- 2. MODAL DETAIL (PENDING) --}}
<div x-show="showDetailPending"
     x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-3">

    <div @click.away="showDetailPending=false"
         x-transition.scale
         class="bg-white rounded-xl p-4 w-full max-w-sm shadow-xl text-[11px]">

        <h3 class="text-sm font-bold mb-2 text-sky-600 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i> Detail Pengajuan Cuti
        </h3>

        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 space-y-1.5 text-gray-700">
            {{-- Menggunakan opsional chaining atau fallback object {} --}}
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Nama:</span> 
                <span class="text-right" x-text="detailPending.nama || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">NIP:</span> 
                <span x-text="detailPending.nip || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Jabatan:</span> 
                <span x-text="detailPending.jabatan || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Jenis Cuti:</span> 
                <span class="font-bold text-sky-700" x-text="detailPending.jenis_cuti || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Mulai:</span> 
                <span x-text="detailPending.tanggal_mulai || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Selesai:</span> 
                <span x-text="detailPending.tanggal_selesai || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Total Hari:</span> 
                <span><span class="font-bold text-orange-600" x-text="detailPending.jumlah_hari || '0'"></span> Hari</span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Sisa Kuota:</span> 
                <span><span x-text="detailPending.sisa_cuti || '0'"></span> Hari</span>
            </p>
            <div class="pt-1">
                <p class="font-semibold text-gray-500 mb-1">Alasan:</p>
                <div class="bg-white p-2 rounded border text-gray-600 italic leading-relaxed" x-text="detailPending.alasan_cuti || '-'"></div>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button @click="showDetailPending=false"
                    class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<<div x-show="showEditModal" x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-3">

    <div x-show="showEditModal && selectedCuti"
         @click.stop
         x-transition.scale
         class="bg-white rounded-xl p-4 w-full max-w-sm shadow-xl border border-gray-200">
         
        <div class="flex justify-between items-center border-b pb-2 mb-2">
            <h3 class="text-[12px] font-bold text-sky-600 uppercase tracking-tight">
                <i class="fa-solid fa-pen-to-square"></i> Edit Data Cuti
            </h3>
            <button @click="showEditModal=false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form :action="'/pegawai/cuti/' + selectedCuti.id" method="POST">
            @csrf @method('PUT')

            <div class="bg-gray-50 p-2.5 rounded-lg text-[10px] border border-gray-200 space-y-2 text-gray-700">
                <div class="border-b border-gray-200 pb-2">
                    <label class="font-bold text-gray-500 block mb-0.5">Nama Pegawai:</label>
                    <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium" x-text="selectedCuti.nama"></div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">NIP:</label>
                        <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium"
                            x-text="selectedCuti.nip"></div>
                        <input type="hidden" name="nip" :value="selectedCuti.nip">
                    </div>
                <div>
                    <label class="font-bold text-gray-500 block mb-0.5">Jabatan:</label>
                    <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium"
                        x-text="selectedCuti.jabatan"></div>
                    <input type="hidden" name="jabatan" :value="selectedCuti.jabatan">
                </div>

                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Jenis Cuti:</label>

                        <!-- Tampilan saja (tidak bisa diedit) -->
                        <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200
                                    font-bold text-sky-700">
                            Tahunan
                        </div>

                        <!-- Nilai tetap dikirim ke backend -->
                        <input type="hidden" name="jenis_cuti" value="Tahunan">
                    </div>
                <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Sisa Kuota:</label>
                        <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-sky-600 font-bold"
                            x-text="selectedCuti.sisa_cuti"></div>
                        <input type="hidden" name="sisa_cuti" :value="selectedCuti.sisa_cuti">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Mulai:</label>
                        <input type="date" name="tanggal_mulai" x-model="selectedCuti.tanggal_mulai" min="{{ \Carbon\Carbon::today()->addDays(3)->toDateString() }}" @change="hitungHariEdit(); checkChange()" class="w-full bg-white border border-gray-300 rounded px-1 py-1 outline-none">
                    </div>
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Selesai:</label>
                        <input type="date" name="tanggal_selesai" x-model="selectedCuti.tanggal_selesai" :min="selectedCuti.tanggal_mulai" @change="hitungHariEdit(); checkChange()" class="w-full bg-white border border-gray-300 rounded px-1 py-1 outline-none">
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-500 block mb-0.5">Alasan Cuti:</label>
                    <textarea name="alasan_cuti"
                        x-model="selectedCuti.alasan_cuti"
                        @input="checkChange()"
                        oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                        class="w-full bg-white border border-gray-300 rounded px-2 py-1 outline-none resize-none italic">
                    </textarea>
                </div>
                <div class="flex justify-between items-center bg-sky-100/50 p-1.5 rounded border border-sky-200">
                    <span class="font-bold text-sky-700">Total Hari:</span>
                    <span class="font-black text-sky-800"><span x-text="selectedCuti.jumlah_hari || '0'"></span> Hari</span>
                    <input type="hidden" name="jumlah_hari" :value="selectedCuti.jumlah_hari">
                </div>
            </div>

            <div class="flex justify-end mt-3 gap-2">
                <button type="submit"
                    :disabled="!isChanged"
                    :class="!isChanged 
                        ? 'bg-gray-400 cursor-not-allowed' 
                        : 'bg-sky-600 hover:bg-sky-700'"
                    class="px-3 py-1.5 text-white rounded text-[10px] font-bold shadow-sm transition">
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>

<div x-show="showDetailRiwayat" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-3">
    <div @click.away="showDetailRiwayat=false" x-transition.scale class="bg-white rounded-xl p-4 w-full max-w-sm shadow-xl border border-gray-200">
        <div class="flex justify-between items-center border-b pb-2 mb-2">
            <h3 class="text-sm font-bold text-sky-600">
                <i class="fa-solid fa-circle-info text-xs"></i> Detail Riwayat Cuti
            </h3>
            <form :action="'{{ route('pegawai.cuti.export-excel') }}'" method="GET" x-show="detailRiwayat.id">
                {{-- Input hidden untuk mengirim tahun ke Controller --}}
                <input type="hidden" name="tahun" :value="detailRiwayat.tahun">
                
                <button type="submit" 
                    class="px-3 py-1.5 text-[11px] bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-2 shadow-sm transition-all active:scale-95 font-bold">
                    <i class="fa-solid fa-file-excel text-xs"></i> 
                    <span>Export Laporan <span x-text="detailRiwayat.tahun"></span></span>
                </button>
            </form>
        </div>

        <div class="bg-gray-50 p-3 rounded-lg text-[11px] border border-gray-200 space-y-1.5 text-gray-700">
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Nama:</span> 
                <span x-text="detailRiwayat.nama || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">NIP:</span> 
                <span x-text="detailRiwayat.nip || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Jabatan:</span> 
                <span x-text="detailRiwayat.jabatan || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Jenis Cuti:</span> 
                <span class="font-bold text-sky-700" x-text="detailRiwayat.jenis_cuti || '-'"></span>
            </p>
            
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Alamat:</span> 
                <span class="text-right" x-text="detailRiwayat.alamat || '-'"></span>
            </p>

            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Status:</span>
                <span :class="{
                        'text-green-600 font-bold' : detailRiwayat.status?.toLowerCase() === 'disetujui',
                        'text-red-600 font-bold'   : detailRiwayat.status?.toLowerCase() === 'ditolak',
                        'text-yellow-600 font-bold': detailRiwayat.status?.toLowerCase() === 'menunggu'
                    }" x-text="detailRiwayat.status"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Mulai:</span> 
                <span x-text="detailRiwayat.tanggal_mulai || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Selesai:</span> 
                <span x-text="detailRiwayat.tanggal_selesai || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Total Hari:</span> 
                <span><span class="font-bold" x-text="detailRiwayat.jumlah_hari || '0'"></span> Hari</span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-0.5">
                <span class="font-semibold text-gray-500">Sisa Kuota:</span> 
                <span><span class="font-bold text-sky-600" x-text="detailRiwayat.sisa_cuti || '0'"></span> Hari</span>
            </p>
            <div class="pt-1">
                <p class="font-semibold text-gray-500 mb-1">Alasan:</p>
                <div class="bg-white p-2 rounded border border-gray-100 italic leading-tight" x-text="detailRiwayat.alasan_cuti || '-'"></div>
            </div>
        </div>
        <div class="flex justify-end mt-4">
            <button @click="showDetailRiwayat=false" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px] font-bold transition">Tutup</button>
        </div>
    </div>
</div>


{{-- 5. MODAL DELETE FINAL (showDelete) --}}
<div x-show="showDelete"
      x-cloak
      x-transition.opacity
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]">

    <div x-transition.scale
          class="bg-white w-full max-w-xs p-4 rounded-xl shadow-xl text-center">

        <i class="fa-solid fa-triangle-exclamation text-red-600 text-4xl mb-2"></i>

        <h2 class="text-sm font-semibold mb-2">Hapus Pengajuan Cuti?</h2>

        <p class="text-[11px] text-gray-600 mb-4">
            Pengajuan milik <b x-text="namaHapus"></b> akan dihapus permanen.
        </p>

        <div class="flex items-center justify-between gap-2">
            <button @click="showDelete=false"
                    class="flex-1 py-1 rounded bg-gray-300 text-gray-800 text-[11px] hover:bg-gray-400">
                Batal
            </button>

            <form :action="'/pegawai/cuti/' + deleteId" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full py-1 rounded bg-red-600 text-white text-[11px] hover:bg-red-700">
                    Hapus
                </button>
            </form>
        </div>

    </div>
</div>

@endsection