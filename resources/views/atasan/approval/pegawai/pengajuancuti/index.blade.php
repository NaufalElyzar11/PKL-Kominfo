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
        const dateStr = date.toISOString().split('T')[0];
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
            alamat: data.alamat
        };
        this.originalCuti = JSON.parse(JSON.stringify(this.selectedCuti));
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
                                <td class="px-1 py-2">{{ $c->pegawai->nip ?? '-' }}</td>
                                <td class="px-1 py-2">{{ $c->jenis_cuti }}</td>
                                <td class="px-1 py-2 leading-tight">
                                    {{ $c->tanggal_mulai->translatedFormat('d M Y') }} <br>
                                    s/d {{ $c->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-1 py-2 text-center font-bold">{{ $c->jumlah_hari }}</td>
                                <td class="px-1 py-2">{{ Str::limit($c->alasan_cuti, 20) }}</td>
                                <td class="px-1 py-2">{{ $c->alamat ?? '-' }}</td>
                                <td class="px-1 py-2 text-center">
                                    @if($c->status == 'Menunggu')
                                        <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">Menunggu</span>
                                    @elseif($c->status == 'Disetujui Atasan')
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">
                                            <i class="fa-solid fa-user-check mr-1"></i> Disetujui Atasan
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[10px]">{{ $c->status }}</span>
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
                                    
                                    {{-- Tambahkan data alamat di bagian paling bawah parameter @click --}}
                                    <button @click="openEditModal({
                                        id: {{ $c->id }},
                                        nama: @js($c->pegawai->nama),
                                        nip: @js($c->pegawai->nip),
                                        jabatan: @js($c->pegawai->jabatan),
                                        jenis_cuti: @js($c->jenis_cuti),
                                        sisa_cuti: @js($c->sisa_cuti ?? 0),
                                        tanggal_mulai_raw: @js($c->tanggal_mulai->format('Y-m-d')),
                                        tanggal_selesai_raw: @js($c->tanggal_selesai->format('Y-m-d')),
                                        alasan_cuti: @js($c->keterangan ?? $c->alasan_cuti),
                                        jumlah_hari: @js($c->jumlah_hari),
                                        alamat: @js($c->alamat) {{-- <-- BARIS INI WAJIB ADA --}}
                                    })"
                                    class="p-1 text-yellow-600 hover:bg-yellow-50 rounded">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>


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
                    <th class="px-1 py-1 text-center font-semibold">Pengganti (Delegasi)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($riwayat as $index => $r)
                    @php
                        $noR = ($riwayatCurrent - 1) * $riwayatPerPage + $index + 1;
                        $status = strtolower(trim($r->status ?? ''));
                        $nipR = $r->pegawai->nip ?? '-';
                        $nipMaskedR = (strlen($nipR) > 6) ? substr($nipR, 0, 3) . '***' . substr($nipR, -3) : $nipR;

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
                        <td class="px-1 py-2 text-center">{{ $nipMaskedR }}</td>
                        <td class="px-1 py-2 text-center">{{ $r->jenis_cuti }}</td>
                        <td class="px-1 py-2 text-center leading-tight">
                            {{ optional($r->tanggal_mulai)->format('d/m/Y') }} <br> s/d {{ optional($r->tanggal_selesai)->format('d/m/Y') }}
                        </td>
                        <td class="px-1 py-2 text-center font-bold">{{ $r->jumlah_hari }}</td>
                        <td class="px-1 py-2 text-center font-bold">
                            <span class="{{ $sisa_final <= 3 ? 'text-red-600' : 'text-sky-600' }}">{{ $sisa_final }}</span>
                        </td>
                        <td class="px-1 py-2">{{ Str::limit($r->alamat, 15) }}</td>
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
                                    nama: @js($r->pegawai->nama ?? '-'),
                                    nip: @js($r->pegawai->nip ?? '-'),
                                    jabatan: @js($r->pegawai->jabatan ?? '-'),
                                    jenis_cuti: @js($r->jenis_cuti),
                                    status: @js($r->status),
                                    tanggal_mulai: @js(optional($r->tanggal_mulai)->format('d/m/Y')),
                                    tanggal_selesai: @js(optional($r->tanggal_selesai)->format('d/m/Y')),
                                    jumlah_hari: @js($r->jumlah_hari),
                                    sisa_cuti: @js($sisa_final),
                                    atasan: @js($r->atasanLangsung->nama_atasan ?? $r->atasan_nama ?? '-'),
                                    pejabat: @js($r->pejabatPemberiCuti->nama_pejabat ?? $r->pejabat_nama ?? '-'),
                                    alasan_cuti: @js($r->alasan_cuti),
                                    alamat: @js($r->alamat),
                                    tahun: @js($r->tahun)
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
<!-- MODAL AJUKAN CUTI -->
<template x-if="showModal">
    <div
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999] p-3"
        @click.self="showModal = false"
        x-cloak
    >
        <div
            class="bg-white rounded-xl shadow-xl w-full max-w-sm p-4 border border-gray-200"
            @click.stop
        >

            <h3 class="text-sm font-bold text-sky-600 mb-2 pb-1.5 border-b flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-xs"></i> AJUKAN CUTI
            </h3>

            <div class="bg-yellow-100 p-1 text-[8px]">
                Unit Kerja Anda: {{ $pegawai->unit_kerja }} | 
                Jumlah Rekan Ditemukan: {{ $rekanSebidang->count() }}
            </div>

            <form action="{{ route('pegawai.cuti.store') }}" method="POST" class="space-y-2 text-[10px]">
                @csrf

                <!-- WARNING PENDING -->
                <div x-show="hasPendingCuti"
                     class="p-2 bg-red-50 border border-red-200 text-red-700 rounded text-[9px] leading-tight">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                    Ada pengajuan yang masih <b>Menunggu Persetujuan</b>.
                </div>

                <!-- INFO PEGAWAI -->
                <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2 rounded border border-gray-200 text-gray-500">
                    <div class="col-span-2 border-b border-gray-200 pb-1 mb-1">
                        <span class="font-bold">Nama:</span> {{ $pegawai->nama ?? '-' }}
                    </div>
                    <div><span class="font-bold">NIP:</span> {{ $pegawai->nip ?? '-' }}</div>
                    <div><span class="font-bold">Jabatan:</span> {{ $pegawai->jabatan ?? '-' }}</div>

                    {{-- TAMBAHKAN DUA BARIS INI --}}
                    <div><span class="font-bold">Atasan:</span> {{ $pegawai->atasan ?? '-' }}</div>
                    <div><span class="font-bold">Kadis:</span> {{ $pegawai->pemberi_cuti ?? '-' }}</div>

                    {{-- INPUT HIDDEN AGAR MASUK KE DATABASE SAAT STORE --}}
                    <input type="hidden" name="atasan" value="{{ $pegawai->atasan }}">
                    <input type="hidden" name="pemberi_cuti" value="{{ $pegawai->pemberi_cuti }}">
                </div>

                <fieldset :disabled="hasPendingCuti" class="space-y-2">

                    <!-- JENIS CUTI -->
                    <div>
                        <label class="font-bold text-gray-600">Jenis Cuti *</label>
                        <input type="text"
                               value="Tahunan"
                               class="w-full mt-0.5 p-1 rounded border border-gray-300 bg-gray-100"
                               disabled>
                        <input type="hidden" name="jenis_cuti" value="Tahunan">
                    </div>

                    <!-- TANGGAL -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="font-bold text-gray-600">Mulai *</label>
                            <div class="relative">
                                <input type="text"
                                       name="tanggal_mulai"
                                       x-model="tanggalMulaiTambah"
                                       x-ref="tambahMulai"
                                       placeholder="Pilih Tanggal"
                                       class="flatpickr w-full mt-0.5 p-1 rounded border border-gray-300 bg-white"
                                       required>
                                <div class="absolute right-2 top-2 pointer-events-none text-gray-400">
                                    <i class="fa-regular fa-calendar"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-gray-600">Selesai *</label>
                             <div class="relative">
                                <input type="text"
                                       name="tanggal_selesai"
                                       x-model="tanggalSelesaiTambah"
                                       x-ref="tambahSelesai"
                                        placeholder="Pilih Tanggal"
                                       class="flatpickr w-full mt-0.5 p-1 rounded border border-gray-300 bg-white"
                                       required>
                                <div class="absolute right-2 top-2 pointer-events-none text-gray-400">
                                    <i class="fa-regular fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Watcher untuk re-init atau update logic -->
                    <div x-effect="
                        if(holidaysLoaded) {
                            // Re-init khusus start date dengan minDate
                            if($refs.tambahMulai) {
                                flatpickr($refs.tambahMulai, {
                                    locale: 'id',
                                    dateFormat: 'Y-m-d',
                                    minDate: new Date().fp_incr(3), // Hari ini + 3 hari
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
                                        tanggalMulaiTambah = dateStr;
                                        hitungHariTambah();
                                        // Update minDate untuk tanggal selesai
                                        if ($refs.tambahSelesai._flatpickr) {
                                            $refs.tambahSelesai._flatpickr.set('minDate', dateStr);
                                        }
                                    }
                                });
                            }
                            
                            // Re-init khusus end date
                            if($refs.tambahSelesai) {
                                flatpickr($refs.tambahSelesai, {
                                    locale: 'id',
                                    dateFormat: 'Y-m-d',
                                    minDate: tanggalMulaiTambah || new Date().fp_incr(3),
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
                                        tanggalSelesaiTambah = dateStr;
                                        hitungHariTambah();
                                    }
                                });
                            }
                        }
                    "></div>

                    <!-- ALAMAT -->
                    <div>
                        <label class="font-bold text-gray-600">Alamat *</label>
                        <textarea 
                            name="alamat"
                            rows="1"
                            class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none resize-none"
                            placeholder="Contoh: Komplek Bukit Permata Indah Jl. Permata Jamrud Blok E No 127"
                            required
                            oninput="this.value = this.value.replace(/[^A-Za-z0-9\s]/g,'')"></textarea>
                    </div>


                     <!-- ALASAN -->
                        <div>
                            <label class="font-bold text-gray-600">Alasan *</label>
                            <textarea 
                                name="keterangan" 
                                rows="1" 
                                class="w-full mt-0.5 p-1 rounded border border-gray-300 outline-none resize-none" 
                                placeholder="Contoh: Menghadiri acara keluarga atau keperluan mendesak lainnya.." 
                                pattern="[A-Za-z\s]+"
                                title="Alasan cuti hanya boleh huruf"
                                required
                                {{-- Mencegah input angka secara real-time --}}
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                                {{-- Validasi regex: hanya huruf dan spasi, minimal 5 karakter --}}
                                pattern="^[a-zA-Z\s]+$"
                                title="Alasan hanya boleh berisi huruf dan tidak boleh ada angka"></textarea>
                        </div>

                     <!-- DELEGASI PEGAWAI -->
                    <div>
                        <label class="font-bold text-gray-600">Pegawai Pengganti (Delegasi) *</label>
                        <select 
                            name="id_delegasi" 
                            class="w-full mt-0.5 p-1 rounded border border-gray-300 bg-white outline-none focus:ring-1 focus:ring-sky-400"
                            required>
                            <option value="" disabled selected>-- Pilih Pegawai Pengganti --</option>
                            @forelse($rekanSebidang as $rekan)
                                <option value="{{ $rekan->id }}">{{ $rekan->nama }} ({{ $rekan->jabatan }})</option>
                            @empty
                                <option value="" disabled>Tidak ada rekan sebidang tersedia</option>
                            @endforelse
                        </select>
                        <p class="text-[8px] text-gray-400 mt-0.5 italic">* Pegawai ini yang akan menggantikan tugas Anda selama cuti.</p>
                    </div>


                    <!-- JUMLAH HARI DAN SISA CUTI -->
                    <div class="space-y-2">
                        <!-- Jumlah Hari Cuti -->
                        <div class="flex justify-between items-center bg-sky-50 p-1.5 rounded border border-sky-100">
                            <span class="font-bold text-sky-700 uppercase tracking-tighter text-[9px]">
                                Jumlah Cuti (Hari Kerja)
                            </span>
                            <div class="text-sky-800 font-black">
                                <span x-text="jumlahHariTambah"></span> Hari
                            </div>
                            <input type="hidden" name="jumlah_hari" :value="jumlahHariTambah">
                        </div>

                        <!-- Sisa Cuti Setelah Pengajuan -->
                        <div class="flex justify-between items-center p-1.5 rounded border"
                             :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">
                            <span class="font-bold uppercase tracking-tighter text-[9px]"
                                  :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-700' : 'text-green-700'">
                                Sisa Cuti Setelah Pengajuan
                            </span>
                            <div class="font-black"
                                 :class="(sisaCutiTersedia - jumlahHariTambah) < 0 ? 'text-red-800' : 'text-green-800'">
                                <span x-text="Math.max(0, sisaCutiTersedia - jumlahHariTambah)"></span> Hari
                            </div>
                        </div>

                        <!-- Warning jika melebihi kuota -->
                        <div x-show="jumlahHariTambah > sisaCutiTersedia" 
                             class="p-2 bg-red-50 border border-red-200 text-red-700 rounded text-[9px] leading-tight">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Pengajuan cuti (<span x-text="jumlahHariTambah"></span> hari) melebihi sisa kuota Anda (<span x-text="sisaCutiTersedia"></span> hari).
                        </div>
                    </div>

                </fieldset>

                <!-- BUTTON -->
                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button type="button"
                            @click="showModal=false"
                            class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg font-bold">
                        Batal
                    </button>

                    <button type="submit"
                            :disabled="hasPendingCuti || jumlahHariTambah > sisaCutiTersedia || jumlahHariTambah === 0"
                            class="px-3 py-1 text-white rounded-lg font-bold transition"
                            :class="hasPendingCuti || jumlahHariTambah > sisaCutiTersedia || jumlahHariTambah === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-sky-600 hover:bg-sky-700'">
                        Kirim
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>

{{-- 2. MODAL DETAIL --}}
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
                <span class="font-semibold text-gray-500">Atasan:</span> 
                <span x-text="detailPending.atasan || '-'"></span>
            </p>
            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Kadis:</span> 
                <span x-text="detailPending.pejabat || '-'"></span>
            </p>

            <p class="flex justify-between border-b border-gray-100 pb-1">
                <span class="font-semibold text-gray-500">Pegawai Pengganti:</span> 
                <span class="text-right">
                    {{-- Nama Pengganti --}}
                    <span class="font-bold text-sky-700" x-text="detailPending.pengganti_nama || '-'"></span><br>
                    {{-- Jabatan Pengganti (Opsional) --}}
                    <small class="text-gray-400" x-text="detailPending.pengganti_jabatan || ''"></small>
                </span>
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

                <div>
                    <label class="font-bold text-gray-500 block mb-0.5 text-[10px]">Alamat:</label>
                    <textarea 
                        name="alamat" 
                        x-model="selectedCuti.alamat"
                        @input="isChanged = true"
                        required
                        class="w-full bg-white border border-gray-300 rounded px-2 py-1 outline-none resize-none italic min-h-[40px] text-[10px] focus:ring-1 focus:ring-sky-400"
                        placeholder="Masukkan alamat lengkap..."></textarea>
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