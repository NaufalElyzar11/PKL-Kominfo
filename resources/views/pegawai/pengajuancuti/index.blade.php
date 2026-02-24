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
    
    /* Flatpickr Custom Style */
    .flatpickr-day.holiday {
        background: #fee2e2;
        color: #ef4444;
        border-color: #fecaca;
    }
    .flatpickr-day.holiday:hover {
        background: #fecaca;
        color: #dc2626;
    }
</style>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
@endpush

<div x-data="{
    showAddModal: false, 
    jenisCutiTambah: '',   {{-- WAJIB ADA --}}
    alasanCutiTambah: '',
    tab: 'menunggu',
    showModal: false, 
    showEditModal: false, 
    showDetailPending: false, 
    showDetailRiwayat: false, 
    openCatatanKadis: false,
    catatanContent: '',
    
    // Inisialisasi Objek (WAJIB ADA AGAR TIDAK ERROR)
    detailPending: {}, 
    detailRiwayat: {},
    hasPendingCuti: @json($hasPendingCuti ?? false),
    sisaCutiTersedia: @json($sisaCuti ?? 12),

    // Data libur nasional
    holidays: [],
    holidaysLoaded: false,
    availableDelegates: [], // List delegasi tersedia


    // FORM TAMBAH
    tanggalMulaiTambah: '',
    tanggalSelesaiTambah: '',
    jumlahHariTambah: 0,
    
    async loadHolidays() {
        if (this.holidaysLoaded) return;
        try {
            const year = new Date().getFullYear();
            const response = await fetch(`https://dayoffapi.vercel.app/api?year=${year}`);
            const data = await response.json();
            // Simpan sebagai object agar bisa akses keterangan/deskripsi
            this.holidays = data.map(h => ({
                date: h.tanggal,
                desc: h.keterangan,
                is_cuti: h.is_cuti
            }));
            this.holidaysLoaded = true;
        } catch (error) {
            console.error('Gagal load holidays:', error);
            this.holidays = [];
            this.holidaysLoaded = true;
        }
    },

    isWeekend(date) {
        const day = date.getDay();
        return day === 0 || day === 6; // 0 = Minggu, 6 = Sabtu
    },

    isHoliday(date) {
        const dateStr = date.toLocaleDateString('en-CA');
        return this.holidays.some(h => h.date === dateStr);
    },

    calculateWorkingDays(start, end) {
        if (!start || !end) return 0;
        let count = 0;
        let current = new Date(start);
        const endDate = new Date(end);
        
        while (current <= endDate) {
            if (!this.isWeekend(current) && !this.isHoliday(current)) {
                count++;
            }
            current.setDate(current.getDate() + 1);
        }
        return count;
    },

    async loadDelegates(start, end) {
        if (!start || !end) return;
        
        // Cek validitas tanggal
        const d1 = new Date(start);
        const d2 = new Date(end);
        if (isNaN(d1) || isNaN(d2) || d1 > d2) {
            this.availableDelegates = [];
            return;
        }

        try {
            // Kirim request ke backend
            const s = d1.toISOString().split('T')[0];
            const e = d2.toISOString().split('T')[0];
            const response = await fetch(`{{ route('pegawai.cuti.available-delegates') }}?tanggal_mulai=${s}&tanggal_selesai=${e}`);
            this.availableDelegates = await response.json();
            
            // Debugging
            console.log('Delegates loaded:', this.availableDelegates);
        } catch (error) {
            console.error('Gagal load delegasi:', error);
            this.availableDelegates = [];
        }
    },


    async hitungHariTambah() {
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

        // Pastikan holidays sudah di-load
        if (!this.holidaysLoaded) {
            await this.loadHolidays();
        }

        // Hitung hari kerja
        this.jumlahHariTambah = this.calculateWorkingDays(mulai, selesai);
        
        // Load Delegasi Tersedia
        this.loadDelegates(this.tanggalMulaiTambah, this.tanggalSelesaiTambah);
    },


    selectedCuti: {},

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
            jumlah_hari: data.jumlah_hari,
            catatan_tolak_delegasi: data.catatan_tolak_delegasi, 
            id_delegasi: data.id_delegasi || '',
            rejectedDelegateId: (data.status === 'Revisi Delegasi') ? data.id_delegasi : null
        };

        // PENTING: Panggil fungsi ini agar daftar teman muncul di dropdown saat edit
        this.loadDelegates(data.tanggal_mulai_raw, data.tanggal_selesai_raw);

        this.originalCuti = JSON.parse(JSON.stringify(this.selectedCuti));
        
        // Load delegasi berdasarkan tanggal yang ada
        this.loadDelegates(data.tanggal_mulai_raw, data.tanggal_selesai_raw);
        
        this.isChanged = false;
        this.showEditModal = true;
    },


    async hitungHariEdit() {
        if (!this.selectedCuti.tanggal_mulai || !this.selectedCuti.tanggal_selesai) return;
        
        const mulai = new Date(this.selectedCuti.tanggal_mulai);
        const selesai = new Date(this.selectedCuti.tanggal_selesai);
        
        if (isNaN(mulai) || isNaN(selesai) || mulai > selesai) { 
            this.selectedCuti.jumlah_hari = 0; 
            return; 
        }

        // Pastikan holidays sudah di-load
        if (!this.holidaysLoaded) {
            await this.loadHolidays();
        }

        this.selectedCuti.jumlah_hari = this.calculateWorkingDays(mulai, selesai);
        
        // Load ulang delegasi saat tanggal edit berubah
        this.loadDelegates(this.selectedCuti.tanggal_mulai, this.selectedCuti.tanggal_selesai);
    },


    checkChange() {
        this.isChanged = JSON.stringify(this.selectedCuti) !== JSON.stringify(this.originalCuti);
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
    },

    showCatatanKadis(pesan) { 
        this.catatanContent = pesan; 
        this.openCatatanKadis = true; 
    },

}" x-init="loadHolidays()" class="space-y-4 font-sans text-gray-800">
    {{-- Alert --}}

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
                            <th class="px-1 py-1 text-center font-semibold">Status</th>
                            <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($cuti as $index => $c)
                        @php
                            $no = ($cutiCurrent - 1) * $cutiPerPage + $index + 1;
                            $nip = $c->pegawai->nip ?? '-';
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
                                <td class="px-1 py-2">{{ Str::limit($c->alasan_cuti, 20) }}</td>
                                <td class="px-1 py-2 text-center">
                                    @if($c->status == 'Menunggu')
                                        @if(!empty($c->catatan_tolak_delegasi))
                                            {{-- Jika sudah pernah ditolak tapi status sudah 'Menunggu' lagi --}}
                                            <span class="px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-700 text-[10px] font-bold">
                                                <i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu Hasil Revisi
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">Menunggu</span>
                                        @endif
                                    @elseif($c->status == 'Revisi Delegasi')
                                        <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[10px] font-bold animate-pulse">
                                            <i class="fa-solid fa-rotate mr-1"></i> Butuh Revisi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-center flex justify-center gap-1">

                                {{-- Tambahkan baris alasan_cuti di dalam parameter showPendingDetail --}}
                                <button @click="showPendingDetail({
                                    nama: {{ Js::from($c->pegawai->nama ?? '-') }}, 
                                    nip: {{ Js::from($c->pegawai->nip ?? '-') }}, 
                                    jabatan: {{ Js::from($c->pegawai->jabatan ?? '-') }},
                                    atasan: {{ Js::from($c->pegawai->atasan ?? '-') }},
                                    pejabat: {{ Js::from($c->pegawai->pemberi_cuti ?? '-') }},
                                    
                                    {{-- Data Delegasi: Gunakan null-safe operator ?-> --}}
                                    pengganti_nama: {{ Js::from($c->delegasi->nama ?? '-') }}, 
                                    pengganti_jabatan: {{ Js::from($c->delegasi->jabatan ?? '') }}, 

                                    jenis_cuti: {{ Js::from($c->jenis_cuti ?? '') }}, 
                                    tanggal_mulai: {{ Js::from($c->tanggal_mulai ? $c->tanggal_mulai->translatedFormat('d M Y') : '-') }},
                                    tanggal_selesai: {{ Js::from($c->tanggal_selesai ? $c->tanggal_selesai->translatedFormat('d M Y') : '-') }}, 
                                    jumlah_hari: {{ Js::from($c->jumlah_hari ?? 0) }},
                                    sisa_cuti: {{ Js::from($sisaCuti ?? 0) }},
                                    
                                    {{-- Alasan Cuti seringkali menjadi penyebab NULL --}}
                                    alasan_cuti: {{ Js::from($c->alasan_cuti ?? '') }} 
                                })" class="p-1 text-sky-600 hover:bg-sky-50 rounded">
                                    <i class="fa-solid fa-eye text-[12px]"></i>
                                </button>

                                {{-- Memunculkan tombol edit --}}
                                @if($c->status == 'Revisi Delegasi' || $c->status == 'Menunggu')
                                <button @click="openEditModal({
                                    id: '{{ $c->id }}',
                                    nama: '{{ $c->pegawai->nama }}',
                                    nip: '{{ $c->pegawai->nip }}',
                                    jabatan: '{{ $c->pegawai->jabatan }}',
                                    jenis_cuti: '{{ $c->jenis_cuti }}',
                                    tanggal_mulai_raw: '{{ $c->tanggal_mulai->format('Y-m-d') }}',
                                    tanggal_selesai_raw: '{{ $c->tanggal_selesai->format('Y-m-d') }}',
                                    alasan_cuti: '{{ $c->keterangan }}',
                                    status: '{{ $c->status }}',
                                    jumlah_hari: '{{ $c->jumlah_hari }}',
                                    id_delegasi: '{{ $c->id_delegasi }}',
                                    {{-- BARIS KRUSIAL: Agar alasan muncul di modal --}}
                                    catatan_tolak_delegasi: '{{ $c->catatan_tolak_delegasi ?? "-" }}'
                                })" class="p-1 text-orange-600 hover:bg-orange-50 rounded">
                                    <i class="fa-solid fa-pen-to-square text-[12px]"></i>
                                </button>
                                @endif
                                    
                                    <form action="{{ route('pegawai.cuti.destroy', $c->id) }}"
                                        method="POST"
                                        class="form-delete inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                data-nama="{{ $c->pegawai->nama }}"
                                                class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <i class="fa-solid fa-trash text-[12px]"></i>
                                        </button>
                                    </form>

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

    {{-- ================= TAB RIWAYAT (12 KOLOM SINKRON) ================= --}}
    <div x-show="tab === 'riwayat'" x-cloak class="space-y-4"> {{-- Ubah space-y-2 jadi 4 agar ada jarak --}}
        
        {{-- HEADER TAB RIWAYAT DENGAN TOMBOL EXPORT --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-200">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-sky-600 text-sm"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-700">Data Riwayat Cuti</h2>
            </div>

            {{-- FORM EXPORT EXCEL --}}
            <form action="{{ route('pegawai.cuti.export-excel') }}" method="GET" class="w-full sm:w-auto">
                {{-- Mengambil tahun dari dropdown filter yang ada di atas --}}
                <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                
                <button type="submit" 
                    class="w-full sm:w-auto px-4 py-2 text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex items-center justify-center gap-2 shadow-sm transition-all active:scale-95 font-bold">
                    <i class="fa-solid fa-file-excel text-xs"></i> 
                    <span>Export Laporan ({{ request('tahun') == 'semua' ? 'Semua Tahun' : request('tahun', date('Y')) }})</span>
                </button>
            </form>
        </div>
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
                    <th class="px-1 py-1 font-semibold text-left">Alasan</th>
                    <th class="px-1 py-1 text-center font-semibold">Status</th>
                    <th class="px-1 py-1 text-center font-semibold">Aksi</th>
                    <th class="px-1 py-1 text-center font-semibold">Pengganti (Delegasi)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($riwayat as $index => $r)
                    @php
                        $noR = ($riwayatCurrent - 1) * $riwayatPerPage + $index + 1;
                        $status = strtolower(trim($r->status ?? ''));
                        $nipR = $r->pegawai->nip ?? '-';

                        $kuotaDasar = 12;
                        $pemakaianKumulatif = \App\Models\Cuti::where('user_id', $r->user_id)
                            ->where('tahun', $r->tahun)
                            ->whereIn('status', ['disetujui', 'disetujui atasan'])
                            ->where('id', '<=', $r->id)
                            ->sum('jumlah_hari');
                        
                        $sisa_final = $kuotaDasar - $pemakaianKumulatif;
                    @endphp
                    <tr class="hover:bg-gray-50 text-gray-700">
                        <td class="px-1 py-2 text-center">{{ $noR }}</td>
                        <td class="px-1 py-2">{{ $r->pegawai->nama ?? '-' }}</td>
                        <td class="px-1 py-2 text-center">{{ $nipR }}</td>
                        <td class="px-1 py-2 text-center">{{ $r->jenis_cuti }}</td>
                        <td class="px-1 py-2 text-center leading-tight">
                            {{ optional($r->tanggal_mulai)->format('d/m/Y') }} <br> s/d {{ optional($r->tanggal_selesai)->format('d/m/Y') }}
                        </td>
                        <td class="px-1 py-2 text-center font-bold">{{ $r->jumlah_hari }}</td>
                        <td class="px-1 py-2 text-center font-bold">
                            <span class="{{ $sisa_final <= 3 ? 'text-red-600' : 'text-sky-600' }}">{{ $sisa_final }}</span>
                        </td>
                        <td class="px-1 py-2">{{ Str::limit($r->alasan_cuti, 15) }}</td>
                        
                        {{-- STATUS WARNA OTOMATIS --}}
                        <td class="px-1 py-2 text-center">
                            @if($status == 'disetujui' || $status == 'disetujui kadis')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Disetujui</span>
                            @elseif($status == 'disetujui atasan')
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold">Disetujui Atasan</span>
                            @elseif($status == 'ditolak')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Ditolak</span>
                            @else
                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold">Menunggu</span>
                            @endif
                        </td>

                        <td class="px-1 py-2 text-center flex justify-center gap-1">
                            {{-- Tombol Detail: Menggunakan @js agar aman dari karakter aneh/newline --}}
                        <button @click="
                            detailRiwayat = {
                                id: '{{ $r->id }}',
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
                                sisa_cuti: {{ Js::from($sisa_final ?? 0) }},
                                atasan: {{ Js::from($r->atasanLangsung->nama_atasan ?? $r->atasan_nama ?? '-') }},
                                pejabat: {{ Js::from($r->pejabatPemberiCuti->nama_pejabat ?? $r->pejabat_nama ?? '-') }},
                                alasan_cuti: {{ Js::from($r->alasan_cuti ?? '') }},
                                alasan_cuti: {{ Js::from($r->alasan_cuti ?? '') }},
                                tahun: {{ Js::from($r->tahun ?? date('Y')) }},
                                catatan_tolak_atasan: {{ Js::from($r->catatan_tolak_atasan ?? '-') }},
                                catatan_tolak_delegasi: {{ Js::from($r->catatan_tolak_delegasi ?? '-') }},
                                delegasi: {{ Js::from($r->delegasi ?? null) }}
                            };
                            showDetailRiwayat = true;

                        " class="p-1 text-sky-600 hover:bg-sky-100 rounded">
                            <i class="fa-solid fa-eye text-[12px]"></i>
                        </button>

                            {{-- Tombol Hapus: Pastikan data-nama merujuk ke $r --}}
                            <form action="{{ route('pegawai.cuti.destroy', $r->id) }}" method="POST" class="form-delete inline">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" data-nama="{{ $r->pegawai->nama ?? 'Pengajuan' }}" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                    <i class="fa-solid fa-trash text-[12px]"></i>
                                </button>
                            </form>
                        </td>

                        {{-- PINDAHKAN DATA PENGGANTI KE PALING AKHIR AGAR SINKRON --}}
                        <td class="px-1 py-2 border-l text-[10px] text-gray-700">
                            <div class="font-bold text-sky-700">{{ $r->delegasi->nama ?? '-' }}</div>
                            <div class="text-[9px] text-gray-400">{{ $r->delegasi->jabatan ?? '' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center py-4 text-gray-400 italic font-medium">Tidak ada riwayat cuti</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
<!-- MODAL AJUKAN CUTI - REDESIGNED (WIDER + RESPONSIVE) -->
<template x-if="showModal">
    <div
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
        @click.self="showModal = false"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-3xl overflow-hidden border border-gray-100"
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        >
            {{-- ========== HEADER DENGAN GRADIENT ========== --}}
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-calendar-plus text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Ajukan Cuti</h3>
                            <p class="text-sky-100 text-[10px] sm:text-xs">Isi formulir pengajuan cuti tahunan</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                        <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>

            {{-- ========== FORM CONTENT ========== --}}
            <div class="p-4 sm:p-6 max-h-[85vh] lg:max-h-[80vh] overflow-y-auto">
                <form action="{{ route('pegawai.cuti.store') }}" method="POST">
                    @csrf

                    {{-- WARNING PENDING (Full Width) --}}
                    <div x-show="hasPendingCuti" x-transition
                         class="flex items-start gap-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl mb-4">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-clock text-amber-600"></i>
                        </div>
                        <div class="text-[11px] sm:text-xs">
                            <p class="font-bold text-amber-800">Pengajuan Pending</p>
                            <p class="text-amber-700">Ada pengajuan yang masih menunggu persetujuan. Harap tunggu hingga selesai diproses.</p>
                        </div>
                    </div>

                    {{-- ========== 2-COLUMN LAYOUT (Desktop) / 1-COLUMN (Mobile) ========== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        
                        {{-- ===== KOLOM KIRI: DATA PEGAWAI + RINGKASAN ===== --}}
                        <div class="space-y-4">
                            {{-- INFO PEGAWAI SECTION --}}
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
                                            <p class="text-[12px] sm:text-sm font-semibold text-gray-800 truncate">{{ $pegawai->nama ?? '-' }}</p>
                                        </div>
                                    </div>
                                    {{-- Info Grid --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">NIP</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $pegawai->nip ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Jabatan</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $pegawai->jabatan ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Atasan Langsung</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $pegawai->atasan ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Pejabat Pemberi Cuti</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-700">{{ $pegawai->pemberi_cuti ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="atasan" value="{{ $pegawai->atasan }}">
                                    <input type="hidden" name="pemberi_cuti" value="{{ $pegawai->pemberi_cuti }}">
                                </div>
                            </div>

                            {{-- RINGKASAN CUTI (display on desktop, hidden on mobile - will show at bottom) --}}
                            <div class="hidden lg:block bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                    <i class="fa-solid fa-chart-pie text-sky-600 text-sm"></i>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Ringkasan Pengajuan</span>
                                </div>
                                
                                {{-- Jumlah Hari --}}
                                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-calendar-week text-sky-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wide">Jumlah Hari Cuti</p>
                                            <p class="text-[11px] sm:text-xs font-medium text-gray-600">Hari kerja yang diajukan</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-black text-sky-600" x-text="jumlahHariTambah">0</span>
                                        <span class="text-[10px] sm:text-xs text-gray-400 ml-1">hari</span>
                                    </div>
                                </div>

                                {{-- Sisa Cuti --}}
                                <div class="flex items-center justify-between p-3 rounded-xl border shadow-sm transition-all duration-300"
                                     :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                             :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'bg-red-100' : 'bg-emerald-100'">
                                            <i class="fa-solid fa-wallet" :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-600' : 'text-emerald-600'"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] uppercase tracking-wide" :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-400' : 'text-emerald-500'">Sisa Kuota</p>
                                            {{-- TAMBAHKAN / GANTI DI SINI --}}
                                            <p class="text-[11px] sm:text-xs font-medium" 
                                            :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-600' : 'text-emerald-700'">
                                            Sisa Kuota (Termasuk Akumulasi Tahun Lalu)
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-black" 
                                              :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-600' : 'text-emerald-600'"
                                              x-text="Math.max(0, sisaCutiTersedia - jumlahHariTambah)">12</span>
                                        <span class="text-[10px] sm:text-xs ml-1" :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-400' : 'text-emerald-500'">hari</span>
                                    </div>
                                </div>

                                {{-- Warning --}}
                                <div x-show="jumlahHariTambah > sisaCutiTersedia" 
                                     x-transition
                                     class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                    <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fa-solid fa-exclamation text-red-600 text-[10px]"></i>
                                    </div>
                                    <p class="text-[10px] sm:text-xs text-red-700 leading-relaxed">
                                        <span class="font-bold">Kuota tidak mencukupi!</span><br>
                                        Pengajuan (<span x-text="jumlahHariTambah"></span> hari) melebihi sisa kuota (<span x-text="sisaCutiTersedia"></span> hari).
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ===== KOLOM KANAN: FORM INPUT ===== --}}
                        <fieldset :disabled="hasPendingCuti" class="space-y-4">
                        {{-- JENIS CUTI --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                <i class="fa-solid fa-tag text-sky-500 text-[10px] sm:text-xs"></i>
                                Jenis Cuti <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="jenis_cuti" 
                                        x-model="jenisCutiTambah" 
                                        {{-- LOGIKA BARU: Jika pilih Tahunan, set alasan otomatis --}}
                                        @change="if(jenisCutiTambah === 'Tahunan') { alasanCutiTambah = 'Hak ASN' } else { alasanCutiTambah = '' }"
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[12px] sm:text-sm font-medium text-gray-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition-all appearance-none"
                                        required>
                                    <option value="" disabled selected>— Pilih jenis cuti —</option>
                                    <option value="Tahunan">Cuti Tahunan</option>
                                    <option value="Alasan Penting">Cuti Alasan Penting</option>
                                </select>
                                {{-- Ikon panah dropdown --}}
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                            {{-- TANGGAL --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-calendar-days text-sky-500 text-[10px] sm:text-xs"></i>
                                    Periode Cuti <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- TANGGAL MULAI --}}
                                    <div class="relative group">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
                                            <i class="fa-regular fa-calendar text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                        </div>
                                        <input type="text"
                                            name="tanggal_mulai"
                                            x-model="tanggalMulaiTambah"
                                            x-ref="tambahMulai"
                                            placeholder="Tanggal Mulai"
                                            class="flatpickr w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                            required>
                                    </div>
                                    <div class="relative group">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
                                            <i class="fa-regular fa-calendar-check text-gray-400 group-focus-within:text-sky-500 transition-colors text-xs"></i>
                                        </div>
                                        <input type="text"
                                            name="tanggal_selesai"
                                            x-model="tanggalSelesaiTambah"
                                            x-ref="tambahSelesai"
                                            :disabled="!tanggalMulaiTambah" {{-- <-- INI PERUBAHANNYA: Kunci jika mulai kosong --}}
                                            :class="!tanggalMulaiTambah ? 'bg-gray-100 cursor-not-allowed opacity-60' : 'bg-white cursor-pointer'"
                                            placeholder="Tanggal Selesai"
                                            class="flatpickr w-full pl-9 pr-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                            required>
                                    </div>
                                </div>
                                <p class="text-[9px] sm:text-[10px] text-gray-400 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Tanggal merah & akhir pekan otomatis dilewati
                                </p>
                            </div>

                            {{-- Watcher untuk re-init Flatpickr --}}
                            <div x-effect="
                                if(holidaysLoaded) {
                                    // Konfigurasi dasar untuk disable (Weekend + Libur Nasional)
                                    const disabledDates = [
                                        function(date) { 
                                            // Return true untuk Sabtu (6) dan Minggu (0)
                                            return (date.getDay() === 0 || date.getDay() === 6); 
                                        },
                                        ...holidays.map(h => h.date) // Array string tanggal 'YYYY-MM-DD'
                                    ];

                                    const commonConfig = {
                                        locale: 'id',
                                        dateFormat: 'Y-m-d',
                                        disable: disabledDates,
                                        onDayCreate: (dObj, dStr, fp, dayElem) => {
                                            // Menyamakan zona waktu agar pembandingan tanggal akurat
                                            const dateStr = dayElem.dateObj.toLocaleDateString('en-CA'); 
                                            const holiday = holidays.find(h => h.date === dateStr);
                                            if (holiday) {
                                                dayElem.classList.add('holiday');
                                                dayElem.title = holiday.desc;
                                            }
                                        }
                                    };

                                    // Init Kalender MULAI
                                    if($refs.tambahMulai) {
                                        flatpickr($refs.tambahMulai, {
                                            ...commonConfig,
                                            minDate: 'today',
                                            onChange: (selectedDates, dateStr) => {
                                                tanggalMulaiTambah = dateStr;
                                                tanggalSelesaiTambah = ''; // Reset selesai jika mulai berubah
                                                hitungHariTambah();
                                                
                                                if ($refs.tambahSelesai._flatpickr) {
                                                    $refs.tambahSelesai._flatpickr.set('minDate', dateStr);
                                                }
                                            }
                                        });
                                    }

                                    // Init Kalender SELESAI
                                    if($refs.tambahSelesai) {
                                        flatpickr($refs.tambahSelesai, {
                                            ...commonConfig,
                                            minDate: tanggalMulaiTambah || 'today',
                                            onChange: (selectedDates, dateStr) => {
                                                tanggalSelesaiTambah = dateStr;
                                                hitungHariTambah();
                                            }
                                        });
                                    }
                                }
                            "></div>

                            {{-- ALASAN --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-pen-fancy text-sky-500 text-[10px] sm:text-xs"></i>
                                    Alasan Cuti <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="keterangan" 
                                    rows="2" 
                                    {{-- 1. Tambahkan x-model agar bisa diisi otomatis oleh Select --}}
                                    x-model="alasanCutiTambah"
                                    {{-- 2. Kunci input jika jenis cuti adalah Tahunan --}}
                                    :readonly="jenisCutiTambah === 'Tahunan'"
                                    {{-- 3. Beri warna abu-abu jika terkunci (readonly) --}}
                                    :class="jenisCutiTambah === 'Tahunan' ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                    class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 text-[11px] sm:text-xs
                                        focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 resize-none" 
                                    placeholder="Jelaskan alasan pengajuan cuti Anda..." 
                                    required
                                    @input="alasanCutiTambah = $event.target.value.replace(/[^A-Za-z\s]/g, '')"></textarea>
                            </div>

                            {{-- DELEGASI --}}
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-[11px] sm:text-xs font-semibold text-gray-600">
                                    <i class="fa-solid fa-user-group text-sky-500 text-[10px] sm:text-xs"></i>
                                    Pegawai Pengganti <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select 
                                        name="id_delegasi" 
                                        class="w-full px-3 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-white text-[11px] sm:text-xs appearance-none
                                               focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200"
                                        required>
                                        <option value="" disabled selected>— Pilih pegawai pengganti —</option>
                                        <template x-for="delegate in availableDelegates.filter(d => d.id != selectedCuti.rejectedDelegateId)" :key="delegate.id">
                                            <option :value="delegate.id" x-text="delegate.nama + ' — ' + delegate.jabatan"></option>
                                        </template>
                                        <option x-show="availableDelegates.length === 0" value="" disabled>--- Pilih tanggal dulu / Tidak ada rekan tersedia ---</option>

                                    </select>
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                        <i class="fa-solid fa-chevron-down text-gray-400 text-[10px]"></i>
                                    </div>
                                </div>
                                <p class="text-[9px] sm:text-[10px] text-gray-400 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Pegawai ini akan menggantikan tugas selama Anda cuti
                                </p>
                            </div>
                            
                            <input type="hidden" name="jumlah_hari" :value="jumlahHariTambah">
                        </fieldset>
                    </div>

                    {{-- RINGKASAN CUTI - MOBILE ONLY (ditampilkan di bawah form pada layar kecil) --}}
                    <div class="lg:hidden mt-4 bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl border border-gray-100 p-4 space-y-3">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                            <i class="fa-solid fa-chart-pie text-sky-600 text-sm"></i>
                            <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">Ringkasan Pengajuan</span>
                        </div>
                        
                        {{-- Jumlah Hari + Sisa Cuti in a row for mobile --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                                <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">Hari Cuti</p>
                                <span class="text-2xl font-black text-sky-600" x-text="jumlahHariTambah">0</span>
                                <span class="text-[9px] text-gray-400">hari kerja</span>
                            </div>
                            <div class="flex flex-col items-center p-3 rounded-xl border shadow-sm transition-all duration-300"
                                :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200'">
                                {{-- GANTI BARIS LABEL DI BAWAH INI --}}
                                <p class="text-[9px] uppercase tracking-wide mb-1 text-center" :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-400' : 'text-emerald-500'">
                                    Sisa Kuota (Termasuk Akumulasi Tahun Lalu)
                                </p>
                                <span class="text-2xl font-black" 
                                    :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-600' : 'text-emerald-600'"
                                    x-text="Math.max(0, sisaCutiTersedia - jumlahHariTambah)">12</span>
                                <span class="text-[9px]" :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-400' : 'text-emerald-500'">hari tersisa</span>
                            </div>
                        </div>

                        {{-- Warning --}}
                        <div x-show="jumlahHariTambah > sisaCutiTersedia" 
                             x-transition
                             class="flex items-center gap-2 p-2 bg-red-50 border border-red-200 rounded-xl">
                            <i class="fa-solid fa-exclamation-circle text-red-600"></i>
                            <p class="text-[10px] text-red-700">
                                <span class="font-bold">Kuota tidak mencukupi!</span>
                            </p>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 sm:gap-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button"
                                @click="showModal = false"
                                class="w-full sm:w-auto px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i>
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="hasPendingCuti || jumlahHariTambah > sisaCutiTersedia || jumlahHariTambah === 0"
                                class="w-full sm:w-auto px-6 py-2.5 sm:py-3 rounded-xl text-[11px] sm:text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                                :class="hasPendingCuti || jumlahHariTambah > sisaCutiTersedia || jumlahHariTambah === 0 
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' 
                                    : 'bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-200'">
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

{{-- 2. MODAL DETAIL (PENDING) - REDESIGNED --}}
<div x-show="showDetailPending"
     x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
     @click.self="showDetailPending = false"
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
                        <i class="fa-solid fa-file-lines text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-base tracking-wide">Detail Pengajuan Cuti</h3>
                        <p class="text-sky-100 text-[10px]">Informasi lengkap pengajuan cuti</p>
                    </div>
                </div>
                <button @click="showDetailPending = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                    <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                </button>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4">

            {{-- Status Badge --}}
            <div class="flex justify-center">
                <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 flex items-center gap-2">
                    <i class="fa-solid fa-hourglass-half"></i> Menunggu Persetujuan
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
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="detailPending.nama || '-'"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">NIP</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailPending.nip || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Jabatan</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailPending.jabatan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Atasan Langsung</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailPending.atasan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Pejabat Pemberi Cuti</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailPending.pejabat || '-'"></p>
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
                        <span class="text-xs font-bold text-sky-700" x-text="detailPending.jenis_cuti || '-'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center">
                                <i class="fa-regular fa-calendar text-sky-600 text-[10px]"></i>
                            </div>
                            <span class="text-xs text-gray-500">Periode Cuti</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-700" x-text="detailPending.tanggal_mulai || '-'"></p>
                            <p class="text-[10px] text-gray-400">s/d <span x-text="detailPending.tanggal_selesai || '-'"></span></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">Total Hari</p>
                            <span class="text-2xl font-black text-sky-600" x-text="detailPending.jumlah_hari || '0'"></span>
                            <span class="text-[9px] text-gray-400">hari kerja</span>
                        </div>
                        <div class="flex flex-col items-center p-3 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">
                            <p class="text-[9px] text-emerald-500 uppercase tracking-wide mb-1">Sisa Kuota</p>
                            <span class="text-2xl font-black text-emerald-600" x-text="detailPending.sisa_cuti || '0'"></span>
                            <span class="text-[9px] text-emerald-400">hari tersisa</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DELEGASI --}}
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
                        <p class="text-sm font-bold text-violet-700" x-text="detailPending.pengganti_nama || '-'"></p>
                        <p class="text-[10px] text-gray-400" x-text="detailPending.pengganti_jabatan || ''"></p>
                    </div>
                </div>
            </div>

            {{-- ALASAN --}}
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-pen-fancy text-sky-500 text-sm"></i>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Alasan Cuti</span>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 italic leading-relaxed" x-text="detailPending.alasan_cuti || '-'"></div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button @click="showDetailPending = false"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>
</div>

<div x-show="showEditModal" x-cloak
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
            @csrf 
            @method('PUT')

            <div class="bg-gray-50 p-2.5 rounded-lg text-[10px] border border-gray-200 space-y-2 text-gray-700">

                <template x-if="selectedCuti.status === 'Revisi Delegasi'">
                <div class="mb-3 p-3 bg-orange-100 border-l-4 border-orange-500 rounded-r shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-circle-exclamation text-orange-600"></i>
                        <span class="text-[10px] font-black text-orange-800 uppercase tracking-tighter">Perlu Revisi Delegasi</span>
                    </div>
                    <p class="text-[11px] text-orange-700 leading-tight">
                        <span class="font-bold">Alasan Penolakan:</span> 
                        <span class="italic" x-text="selectedCuti.catatan_tolak_delegasi"></span>
                    </p>
                </div>
            </template>

                <div class="border-b border-gray-200 pb-2">
                    <label class="font-bold text-gray-500 block mb-0.5">Nama Pegawai:</label>
                    <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium" x-text="selectedCuti.nama"></div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">NIP:</label>
                        <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium" x-text="selectedCuti.nip"></div>
                    </div>
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Jabatan:</label>
                        <div class="bg-gray-100 px-2 py-1.5 rounded border border-gray-200 text-gray-500 font-medium" x-text="selectedCuti.jabatan"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Mulai:</label>
                        <div class="relative">
                            <input type="text" name="tanggal_mulai" 
                                   x-model="selectedCuti.tanggal_mulai" 
                                   x-ref="editMulai"
                                   class="w-full bg-white border border-gray-300 rounded px-1 py-1 outline-none focus:ring-1 focus:ring-sky-400">
                             <div class="absolute right-2 top-2 pointer-events-none text-gray-400">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="font-bold text-gray-500 block mb-0.5">Selesai:</label>
                        <div class="relative">
                            <input type="text" name="tanggal_selesai" 
                                   x-model="selectedCuti.tanggal_selesai" 
                                   x-ref="editSelesai"
                                   class="w-full bg-white border border-gray-300 rounded px-1 py-1 outline-none focus:ring-1 focus:ring-sky-400">
                            <div class="absolute right-2 top-2 pointer-events-none text-gray-400">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Watcher untuk Edit Modal -->
                    <div x-effect="
                        if(showEditModal && holidaysLoaded && selectedCuti) {
                            // Init Edit Start
                            if($refs.editMulai) {
                                if ($refs.editMulai._flatpickr) {
                                    $refs.editMulai._flatpickr.setDate(selectedCuti.tanggal_mulai);
                                } else {
                                    flatpickr($refs.editMulai, {
                                        locale: 'id',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: selectedCuti.tanggal_mulai,
                                        disable: [
                                            function(date) { return (date.getDay() === 0 || date.getDay() === 6); },
                                            ...holidays.map(h => h.date)
                                        ],
                                        onDayCreate: (dObj, dStr, fp, dayElem) => {
                                            const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                                            const holiday = holidays.find(h => h.date === dateStr);
                                            if (holiday) {
                                                dayElem.className += ' holiday';
                                                dayElem.title = holiday.desc;
                                            }
                                        },
                                        onChange: (selectedDates, dateStr) => {
                                            selectedCuti.tanggal_mulai = dateStr;
                                            hitungHariEdit();
                                            isChanged = true;
                                            if ($refs.editSelesai._flatpickr) {
                                                $refs.editSelesai._flatpickr.set('minDate', dateStr);
                                            }
                                        }
                                    });
                                }
                            }

                            // Init Edit End
                            if($refs.editSelesai) {
                                if ($refs.editSelesai._flatpickr) {
                                    $refs.editSelesai._flatpickr.setDate(selectedCuti.tanggal_selesai);
                                    $refs.editSelesai._flatpickr.set('minDate', selectedCuti.tanggal_mulai);
                                } else {
                                    flatpickr($refs.editSelesai, {
                                        locale: 'id',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: selectedCuti.tanggal_selesai,
                                        minDate: selectedCuti.tanggal_mulai,
                                        disable: [
                                            function(date) { return (date.getDay() === 0 || date.getDay() === 6); },
                                            ...holidays.map(h => h.date)
                                        ],
                                        onDayCreate: (dObj, dStr, fp, dayElem) => {
                                            const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                                            const holiday = holidays.find(h => h.date === dateStr);
                                            if (holiday) {
                                                dayElem.className += ' holiday';
                                                dayElem.title = holiday.desc;
                                            }
                                        },
                                        onChange: (selectedDates, dateStr) => {
                                            selectedCuti.tanggal_selesai = dateStr;
                                            hitungHariEdit();
                                            isChanged = true;
                                        }
                                    });
                                }
                            }
                        }
                    "></div>
                </div>

                {{-- DELEGASI EDIT (NEW) --}}
                <div class="mt-2">
                    <label class="font-bold text-gray-500 block mb-0.5">Pegawai Pengganti:</label>
                    <div class="relative">
                        <select name="id_delegasi" 
                                x-model="selectedCuti.id_delegasi"
                                @change="isChanged = true"
                                class="w-full bg-white border border-gray-300 rounded px-2 py-1 outline-none text-[10px] focus:ring-1 focus:ring-sky-400 appearance-none">
                            <option value="" disabled>— Pilih Pegawai Pengganti —</option>
                            <template x-for="delegate in availableDelegates" :key="delegate.id">
                                <option :value="delegate.id" 
                                        x-text="delegate.nama + ' — ' + delegate.jabatan"
                                        :selected="selectedCuti.id_delegasi == delegate.id"></option>
                            </template>
                             <option x-show="availableDelegates.length === 0" value="" disabled>Tidak ada rekan tersedia pada tanggal ini</option>
                        </select>
                        <div class="absolute right-2 top-1.5 pointer-events-none text-gray-400">
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>


                <div>
                    <label class="font-bold text-gray-500 block mb-0.5">Alasan Cuti:</label>
                    <textarea 
                        name="keterangan" {{-- WAJIB: Sama dengan Controller dan Database --}}
                        x-model="selectedCuti.alasan_cuti"
                        @input="isChanged = true"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                        class="w-full bg-white border border-gray-300 rounded px-2 py-1 outline-none resize-none italic min-h-[60px] text-[10px] focus:ring-1 focus:ring-sky-400"
                        placeholder="Contoh: Menghadiri acara keluarga atau keperluan mendesak lainnya.."></textarea>
                </div>

                <div class="flex justify-between items-center bg-sky-100/50 p-1.5 rounded border border-sky-200">
                    <span class="font-bold text-sky-700">Total Hari:</span>
                    <span class="font-black text-sky-800"><span x-text="selectedCuti.jumlah_hari"></span> Hari</span>
                    <input type="hidden" name="jumlah_hari" :value="selectedCuti.jumlah_hari">
                </div>
            </div>

            <div class="flex justify-end mt-3 gap-2">
                <button type="button" @click="showEditModal=false" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded text-[10px] font-bold">Batal</button>
                <button type="submit"
                    :disabled="!isChanged"
                    :class="!isChanged ? 'bg-gray-400 cursor-not-allowed' : 'bg-sky-600 hover:bg-sky-700'"
                    class="px-3 py-1.5 text-white rounded text-[10px] font-bold shadow-sm transition">
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. MODAL DETAIL RIWAYAT - REDESIGNED --}}
<div x-show="showDetailRiwayat" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4"
     @click.self="showDetailRiwayat = false"
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
        <div class="px-5 py-4"
             :class="detailRiwayat.status?.toLowerCase() === 'disetujui' ? 'bg-gradient-to-r from-emerald-500 to-green-600' :
                     detailRiwayat.status?.toLowerCase() === 'ditolak'   ? 'bg-gradient-to-r from-red-500 to-rose-600' :
                                                                            'bg-gradient-to-r from-sky-500 to-blue-600'">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fa-solid fa-clock-rotate-left text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-base tracking-wide">Detail Riwayat Cuti</h3>
                        <p class="text-white/70 text-[10px]">Informasi lengkap riwayat cuti</p>
                    </div>
                </div>
                <button @click="showDetailRiwayat = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                    <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                </button>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4">

            {{-- Status Badge --}}
            <div class="flex justify-center">
                <span class="px-4 py-1.5 rounded-full text-xs font-bold flex items-center gap-2"
                      :class="detailRiwayat.status?.toLowerCase() === 'disetujui' ? 'bg-green-100 text-green-700' :
                              detailRiwayat.status?.toLowerCase() === 'ditolak'   ? 'bg-red-100 text-red-700' :
                              detailRiwayat.status?.toLowerCase() === 'disetujui atasan' ? 'bg-blue-100 text-blue-700' :
                                                                                         'bg-yellow-100 text-yellow-700'">
                    <i :class="detailRiwayat.status?.toLowerCase() === 'disetujui' ? 'fa-solid fa-circle-check' :
                               detailRiwayat.status?.toLowerCase() === 'ditolak'   ? 'fa-solid fa-circle-xmark' :
                                                                                     'fa-solid fa-hourglass-half'"></i>
                    <span x-text="detailRiwayat.status || '-'"></span>
                </span>
            </div>

            {{-- ALASAN PENOLAKAN (jika ditolak) --}}
            <template x-if="detailRiwayat.status?.toLowerCase() === 'ditolak'">
                <div class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-red-700 mb-1">Alasan Penolakan</p>
                        <ul class="space-y-0.5 text-red-600 italic">
                            <template x-if="detailRiwayat.catatan_tolak_delegasi && detailRiwayat.catatan_tolak_delegasi !== '-'">
                                <li>• <span class="font-semibold not-italic">Delegasi:</span> <span x-text="detailRiwayat.catatan_tolak_delegasi"></span></li>
                            </template>
                            <template x-if="detailRiwayat.catatan_tolak_atasan && detailRiwayat.catatan_tolak_atasan !== '-'">
                                <li>• <span class="font-semibold not-italic">Atasan:</span> <span x-text="detailRiwayat.catatan_tolak_atasan"></span></li>
                            </template>
                            <template x-if="(!detailRiwayat.catatan_tolak_delegasi || detailRiwayat.catatan_tolak_delegasi === '-') && (!detailRiwayat.catatan_tolak_atasan || detailRiwayat.catatan_tolak_atasan === '-')">
                                <li>• Tidak ada catatan spesifik</li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>

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
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="detailRiwayat.nama || '-'"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">NIP</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.nip || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Jabatan</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.jabatan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Atasan Langsung</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.atasan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-0.5">Pejabat Pemberi Cuti</p>
                        <p class="text-xs font-medium text-gray-700" x-text="detailRiwayat.pejabat || '-'"></p>
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
                        <span class="text-xs font-bold text-sky-700" x-text="detailRiwayat.jenis_cuti || '-'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center">
                                <i class="fa-regular fa-calendar text-sky-600 text-[10px]"></i>
                            </div>
                            <span class="text-xs text-gray-500">Periode Cuti</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-700" x-text="detailRiwayat.tanggal_mulai || '-'"></p>
                            <p class="text-[10px] text-gray-400">s/d <span x-text="detailRiwayat.tanggal_selesai || '-'"></span></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col items-center p-3 bg-white rounded-xl border border-sky-100 shadow-sm">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">Total Hari</p>
                            <span class="text-2xl font-black text-sky-600" x-text="detailRiwayat.jumlah_hari || '0'"></span>
                            <span class="text-[9px] text-gray-400">hari kerja</span>
                        </div>
                        <div class="flex flex-col items-center p-3 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">
                            <p class="text-[9px] text-emerald-500 uppercase tracking-wide mb-1">Sisa Kuota</p>
                            <span class="text-2xl font-black text-emerald-600" x-text="detailRiwayat.sisa_cuti || '0'"></span>
                            <span class="text-[9px] text-emerald-400">hari tersisa</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DELEGASI --}}
            <template x-if="detailRiwayat.delegasi">
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
                            <p class="text-sm font-bold text-violet-700" x-text="detailRiwayat.pengganti_nama || '-'"></p>
                            <p class="text-[10px] text-gray-400" x-text="detailRiwayat.pengganti_jabatan || ''"></p>
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
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 italic leading-relaxed" x-text="detailRiwayat.alasan_cuti || '-'"></div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button @click="showDetailRiwayat = false"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>
</div>


{{-- 5. MODAL DELETE FINAL (showDelete) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const nama = form.querySelector('button').dataset.nama;

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: `Pengajuan cuti milik ${nama} akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        borderRadius: '15px'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Perhatian',
        html: "{!! session('error') !!}", 
        confirmButtonColor: '#ef4444',
        borderRadius: '15px'
    });
</script>
@endif

@if($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Update',
        html: '{!! implode("<br>", $errors->all()) !!}',
        confirmButtonColor: '#0288D1'
    });
</script>
@endif

<script>
document.addEventListener('submit', function (e) {
    // Cari apakah yang di-submit adalah form dengan class .form-delete
    if (e.target.classList.contains('form-delete')) {
        e.preventDefault(); // Stop submit otomatis
        
        const form = e.target;
        // Ambil nama dari atribut data-nama pada button di dalam form tersebut
        const nama = form.querySelector('button').getAttribute('data-nama') || 'Pengajuan';

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: `Pengajuan cuti milik ${nama} akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Kirim form jika user setuju
            }
        });
    }
});
</script>

@endsection