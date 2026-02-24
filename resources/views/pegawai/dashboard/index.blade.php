@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="p-4 sm:p-6 space-y-6 sm:space-y-8">

    {{-- 🌟 KARTU SELAMAT DATANG --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-4 sm:p-6 rounded-2xl shadow flex justify-between items-center">
        <div class="flex-1 min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold">
                Selamat Datang, {{ Auth::user()->name }}
            </h1>
            <p class="text-xs sm:text-sm text-sky-100 mt-1">
                Pantau dan kelola pengajuan cuti pegawai
            </p>
        </div>
        <div class="hidden sm:block bg-white/20 p-4 rounded-full flex-shrink-0 ml-4">
            <i class="fa-solid fa-clipboard-user text-4xl"></i>
        </div>
    </div>

    {{-- 🧩 GRID STATISTIK & NOTIFIKASI --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-stretch">

        {{-- ⬅️ STATISTIK & NOTIFIKASI (GABUNGAN) --}}
        <div class="lg:col-span-8 bg-white rounded-xl shadow border border-gray-200 overflow-hidden lg:h-[28rem] h-full flex flex-col">
            <div class="flex flex-col h-full divide-y divide-gray-100">
                
                {{-- BAGIAN ATAS: STATISTIK --}}
                <div class="p-4 sm:p-6 flex flex-col justify-center flex-1">
                    <h2 class="text-sm font-semibold text-sky-700 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie"></i> Statistik Sisa Cuti
                    </h2>

                    <div class="space-y-6">
                        @php
                            $hakCutiDisplay = $hakCuti ?? 12;
                            $terpakai = $cutiTerpakai ?? 0;
                            $sisa = $sisaCuti ?? max(0, $hakCutiDisplay - $terpakai);
                            $persenTerpakai = $hakCutiDisplay > 0 ? min(100, ($terpakai / $hakCutiDisplay) * 100) : 0;
                        @endphp

                        {{-- Main Number --}}
                        <div class="text-center space-y-2">
                            <div>
                                <p class="text-4xs text-gray-400 uppercase tracking-widest font-semibold">Sisa Cuti</p>
                                <p class="text-6xl font-black {{ $sisa > 0 ? 'text-sky-600' : 'text-red-500' }} tracking-tight">
                                    {{ $sisa }}
                                    <span class="text-3xl font-medium text-gray-400">Hari</span>
                                </p>
                            </div>
                        </div>

                        {{-- Progress --}}
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <div class="flex justify-between text-[10px] items-center mb-1.5 uppercase font-bold text-gray-400">
                                <span>Terpakai</span>
                                <span>Total Hak</span>
                            </div>
                            <div class="flex justify-between text-xs font-bold text-gray-700 mb-2">
                                <span>{{ $terpakai }} Hari</span>
                                <span>{{ $hakCutiDisplay }} Hari</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out 
                                            {{ $sisa == 0 ? 'bg-red-500' : ($sisa <= 3 ? 'bg-orange-400' : 'bg-gradient-to-r from-sky-400 to-blue-500') }}"
                                     style="width: {{ $persenTerpakai }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN BAWAH: NOTIFIKASI --}}
                <div class="p-4 sm:p-6 bg-gray-50/50 flex flex-col flex-1 overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-bell text-yellow-500"></i> Pemberitahuan
                        </h2>
                        @if(isset($notif) && $notif->count() > 0)
                            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                {{ $notif->count() }} Baru
                            </span>
                        @endif
                    </div>

                    <div id="notifikasi-list" class="flex-1 overflow-y-auto min-h-0 pr-1 space-y-3 custom-scrollbar">
                        {{-- Notifikasi dari DB --}}
                        @php 
                            // Gabung notif unread ($notif) dengan 5 notif terakhir read untuk history? 
                            // Untuk sekarang pakai $notif (unread) saja sesuai request, atau ambil latest mixed
                            // User request: "menampilkan notifikasi... supaya terlihat"
                            // Kita pakai logic: Tampilkan $notif (unread) dahulu. Jika kosong, tampilkan placeholder.
                            // Agar lebih robust, kita ambil latest 5 notifications via Auth user langsung di View atau controller.
                            // Di controller sudah ada $notif (unread only). Kita pakai itu dulu.
                            // UPDATE: User ingin "tanpa klik ikon". 
                        @endphp

                        @forelse($notif as $n)
                            <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group relative">
                                <form action="{{ route('pegawai.notif.read', $n->id) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    <button type="submit" class="text-gray-300 hover:text-sky-500" title="Tandai dibaca">
                                        <i class="fa-solid fa-check-double"></i>
                                    </button>
                                </form>
                                
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        @if(Str::contains(strtolower($n->title), 'setuju'))
                                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xs">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                        @elseif(Str::contains(strtolower($n->title), 'tolak'))
                                            <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xs">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        @elseif(Str::contains(strtolower($n->title), 'delegasi'))
                                            <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xs">
                                                <i class="fa-solid fa-user-friends"></i>
                                            </div>
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs">
                                                <i class="fa-solid fa-info"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-[11px] font-bold text-gray-800 mb-0.5 truncate">{{ $n->title }}</h4>
                                        <p class="text-[10px] leading-relaxed line-clamp-2 {{ Str::contains($n->message, 'Admin') ? 'text-blue-600 font-medium' : 'text-gray-500' }}">
                                            {{ $n->message }}
                                        </p>
                                        <p class="text-[9px] text-gray-400 mt-1.5 flex items-center gap-1">
                                            <i class="fa-regular fa-clock"></i> {{ $n->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-center py-8 opacity-50">
                                <i class="fa-regular fa-bell-slash text-2xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-400">Tidak ada notifikasi baru</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer Notif --}}
                    @if(isset($notif) && $notif->count() > 0)
                    <div class="mt-3 text-center border-t border-gray-200/50 pt-2">
                        <button class="text-[10px] text-sky-600 hover:text-sky-700 font-semibold transition-colors">
                            Lihat Semua Riwayat
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ➡️ KANAN --}}
        <div class="lg:col-span-4 flex flex-col gap-4 sm:gap-6 items-stretch">

            {{-- STATUS CUTI (3 SEJAJAR) --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-4">

                <div class="bg-yellow-100 rounded-xl p-2 sm:p-4 text-center h-24 sm:h-28 flex flex-col items-center justify-center">
                    <p class="text-[10px] sm:text-xs text-yellow-700">Menunggu</p>
                    <div class="flex items-center gap-1 sm:gap-2 justify-center mt-1 sm:mt-2">
                        <i class="fa-solid fa-hourglass-end text-yellow-500 text-lg sm:text-xl"></i>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-700">
                            {{ $cutiPending ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="bg-green-100 rounded-xl p-2 sm:p-4 text-center h-24 sm:h-28 flex flex-col items-center justify-center">
                    <p class="text-[10px] sm:text-xs text-green-700">Disetujui</p>
                    <div class="flex items-center gap-1 sm:gap-2 justify-center mt-1 sm:mt-2">
                        <i class="fa-solid fa-check text-green-500 text-lg sm:text-xl"></i>
                        <p class="text-xl sm:text-2xl font-bold text-green-700">
                            {{ $cutiDisetujui ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="bg-red-100 rounded-xl p-2 sm:p-4 text-center h-24 sm:h-28 flex flex-col items-center justify-center">
                    <p class="text-[10px] sm:text-xs text-red-700">Ditolak</p>
                    <div class="flex items-center gap-1 sm:gap-2 justify-center mt-1 sm:mt-2">
                        <i class="fa-solid fa-xmark text-red-500 text-lg sm:text-xl"></i>
                        <p class="text-xl sm:text-2xl font-bold text-red-700">
                            {{ $cutiDitolak ?? 0 }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- JUMLAH PEGAWAI --}}
            <div class="bg-white rounded-xl py-4 sm:py-6 flex flex-col items-center justify-center h-32 sm:h-36 shadow border border-gray-200">
                <p class="text-xs sm:text-sm font-semibold text-sky-700 mb-3 sm:mb-4">Jumlah Pegawai</p>
                <div class="flex items-center gap-2 justify-center">
                    <i class="fa-solid fa-users text-sky-500 text-xl sm:text-2xl"></i>
                    <p class="text-2xl sm:text-3xl font-extrabold text-sky-700">
                        {{ $totalPegawai ?? 0 }}
                    </p>
                </div>
            </div>

            {{-- PEGAWAI YANG SEDANG CUTI --}}
            <div class="bg-white rounded-xl py-4 sm:py-6 flex flex-col items-center justify-center h-32 sm:h-36 shadow border border-gray-200">
                <p class="text-xs sm:text-sm font-semibold text-sky-700 mb-3 sm:mb-4">Pegawai Sedang Cuti</p>
                <div class="flex items-center gap-2 justify-center">
                    <i class="fa-solid fa-person-hiking text-purple-500 text-xl sm:text-2xl"></i>
                    <p class="text-2xl sm:text-3xl font-extrabold text-purple-600">
                        {{ $pegawaiSedangCuti ?? 0 }}
                    </p>
                </div>
            </div>

        </div>
    </div>


    {{-- 📋 TABEL RIWAYAT CUTI (TETAP) --}}
    <div class="bg-white p-3 sm:p-5 rounded-2xl shadow border border-gray-200">
        <h2 class="text-base sm:text-lg font-bold text-sky-700 mb-3 border-b pb-2">
            Riwayat Cuti Pegawai
        </h2>

        <div class="overflow-x-auto overflow-y-auto max-h-[28rem] -mx-3 sm:mx-0">
            <div class="inline-block min-w-full align-middle px-3 sm:px-0">
                <table class="min-w-full border-collapse text-[10px] sm:text-xs">
                    <thead class="bg-sky-600 text-white sticky top-0">
                        <tr>
                            <th class="border px-1.5 sm:px-2 py-1 text-left">No</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left">Nama</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left hidden sm:table-cell">NIP</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left hidden md:table-cell">Jabatan</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left">Jenis Cuti</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left">Mulai</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left hidden lg:table-cell">Selesai</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-center">Hari</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-center">Status</th>
                            <th class="border px-1.5 sm:px-2 py-1 text-left">Catatan / Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestCuti ?? [] as $i => $c)
                        <tr class="hover:bg-gray-100">
                            <td class="border px-1.5 sm:px-2 py-1 text-center">{{ $i + 1 }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 font-medium">{{ $c->pegawai->nama ?? '-' }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 text-center hidden sm:table-cell">{{ $c->pegawai->nip ?? '-' }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 hidden md:table-cell">{{ $c->pegawai->jabatan ?? '-' }}</td>
                            <td class="border px-1.5 sm:px-2 py-1">{{ $c->jenis_cuti }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 text-center whitespace-nowrap">{{ $c->tanggal_mulai?->format('d-m-Y') }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 text-center whitespace-nowrap hidden lg:table-cell">{{ $c->tanggal_selesai?->format('d-m-Y') }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 text-center">{{ $c->jumlah_hari }}</td>
                            <td class="border px-1.5 sm:px-2 py-1 text-center">
                                @php
                                    $statusLower = strtolower($c->status);
                                    $badgeClass = str_contains($statusLower, 'disetujui') 
                                        ? 'bg-green-100 text-green-700' 
                                        : (str_contains($statusLower, 'ditolak') 
                                            ? 'bg-red-100 text-red-700' 
                                            : 'bg-yellow-100 text-yellow-700');
                                @endphp
                                <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] {{ $badgeClass }}">
                                    {{ ucfirst($c->status) }}
                                </span>
                                {{-- TAMBAHAN: Cek jika disetujui admin --}}
                                @if($c->status == 'Disetujui' && $c->catatan_final == 'Disetujui Admin atas izin pejabat')
                                    <div class="text-[8px] text-blue-500 mt-0.5 leading-none italic font-bold">Oleh Admin</div>
                                @endif
                            </td>
<td class="border px-1.5 sm:px-2 py-2">
    {{-- MODIFIKASI: Tampilkan catatan jika Ditolak ATAU jika ada Catatan Final dari Admin --}}
    @if(str_contains(strtolower($c->status), 'ditolak') || !empty($c->catatan_final))
        <div class="flex flex-col gap-2 min-w-[200px]">
            
            {{-- TAMPILKAN CATATAN ADMIN JIKA ADA --}}
            @if(!empty($c->catatan_final))
                <div class="bg-blue-50 p-2 rounded-lg border border-blue-200 shadow-sm">
                    <p class="text-[9px] text-blue-700 font-bold uppercase">Keterangan Sistem:</p>
                    <p class="text-[11px] text-blue-900 font-medium italic">"{{ $c->catatan_final }}"</p>
                </div>
            @endif

        <div class="flex flex-col gap-2 min-w-[200px]">
            
            {{-- PRIORITAS 1: JIKA DITOLAK PEJABAT (KADIS) --}}
            @if(!empty(trim($c->catatan_tolak_pejabat ?? '')))
                <div class="bg-rose-50 p-2 rounded-lg border border-rose-200 shadow-sm">
                    <p class="text-[9px] text-rose-700 font-bold uppercase">Catatan Pejabat (Kadis):</p>
                    <p class="text-[11px] text-rose-900 font-medium italic">"{{ $c->catatan_tolak_pejabat }}"</p>
                </div>

            {{-- PRIORITAS 2: JIKA DITOLAK ATASAN --}}
            @elseif(!empty(trim($c->catatan_tolak_atasan ?? '')))
                <div class="bg-orange-50 p-2 rounded-lg border border-orange-200 shadow-sm">
                    <p class="text-[9px] text-orange-700 font-bold uppercase">Catatan Atasan:</p>
                    <p class="text-[11px] text-orange-900 font-medium italic">"{{ $c->catatan_tolak_atasan }}"</p>
                </div>

            {{-- PRIORITAS 3: CATATAN UMUM (JIKA KOLOM KHUSUS DI ATAS KOSONG) --}}
            @elseif(!empty(trim($c->catatan_penolakan ?? '')))
                <div class="bg-gray-50 p-2 rounded-lg border border-gray-200 shadow-sm">
                    <p class="text-[9px] text-gray-700 font-bold uppercase">Catatan Penolakan:</p>
                    <p class="text-[11px] text-gray-900 font-medium italic">"{{ $c->catatan_penolakan }}"</p>
                </div>
            @else
                <span class="text-gray-400 italic text-[10px]">Tidak ada catatan</span>
            @endif

        </div>
    @else
        <span class="text-gray-400 italic text-[10px]">Tidak ada catatan</span>
    @endif
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-gray-500 py-4">
                                Tidak ada data cuti
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 📅 KALENDER --}}
    <div class="bg-white p-3 sm:p-5 rounded-2xl shadow border border-gray-200">
        <h2 class="text-base sm:text-lg font-bold text-sky-700 mb-4 border-b pb-2">
            Kalender Cuti & Hari Libur
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Kalender -->
            <div class="lg:col-span-2">
                <div class="flex justify-between items-center mb-4 gap-2">
                    <button id="prevMonth" class="px-2 sm:px-4 py-1.5 sm:py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-xs sm:text-sm">
                        <i class="fa-solid fa-chevron-left"></i> <span class="hidden sm:inline">Bulan Lalu</span>
                    </button>
                    <h3 id="monthYear" class="text-sm sm:text-lg font-bold text-sky-700 text-center flex-1"></h3>
                    <button id="nextMonth" class="px-2 sm:px-4 py-1.5 sm:py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-xs sm:text-sm">
                        <span class="hidden sm:inline">Bulan Depan</span> <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div id="calendar" class="bg-gray-50 p-2 sm:p-4 rounded-lg border border-gray-200"></div>
            </div>

            <!-- Legenda & Info -->
            <div class="flex flex-col gap-4">
                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-3 text-sm sm:text-base">Keterangan</h4>
                    <div class="space-y-2 sm:space-y-3 text-xs sm:text-sm">

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center
                            w-6 h-6 rounded-md
                            bg-red-500 text-white text-xs font-bold">
                    N
                </span>
                <span class="font-medium text-gray-700">Hari Libur Nasional</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center
                            w-6 h-6 rounded-md
                            bg-orange-400 text-white text-xs font-bold">
                    W
                </span>
                <span class="font-medium text-gray-700">Hari Libur</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center
                            w-6 h-6 rounded-md
                            bg-yellow-400 text-white text-xs font-bold">
                    C
                </span>
                <span class="font-medium text-gray-700">Ada Pengajuan Cuti</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center
                            w-6 h-6 rounded-md
                            bg-white border border-gray-400
                            text-xs font-bold text-gray-600">
                    K
                </span>
                <span class="font-medium text-gray-700">Hari Kerja Biasa</span>
            </div>

        </div>
                </div>

                <div id="holidayInfo" class="bg-gray-50 p-3 sm:p-4 rounded-lg border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-3 text-sm sm:text-base">Hari Libur Bulan Ini</h4>
                    <div id="holidayList" class="text-xs sm:text-sm space-y-2">
                        <p class="text-gray-500">Memuat hari libur...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Check if chart element exists before initializing
const chartElement = document.getElementById('sisaCutiChart');
if (chartElement) {
    new Chart(chartElement, {
        type: 'bar',
        data: {
            labels: ['Total Hak', 'Terpakai', 'Sisa'],
            datasets: [{
                data: [
                    {{ $totalCuti ?? 0 }},
                    {{ $cutiTerpakai ?? 0 }},
                    {{ $sisaCuti ?? 0 }}
                ],
                backgroundColor: ['#94a3b8', '#facc15', '#0ea5e9'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// ===================== KALENDER =====================
let currentDate = new Date();
let holidays = [];
let userCuti = [];

// ===================== AMBIL HARI LIBUR (DAYOFF API) =====================
async function fetchHolidays(year, month = null) {
    try {
        let url = 'https://dayoffapi.vercel.app/api';

        if (year && month) {
            url += `?year=${year}&month=${month}`;
        } else if (year) {
            url += `?year=${year}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        holidays = data.map(h => ({
            date: h.tanggal,
            display: h.tanggal_display,
            name: h.keterangan,
            isCuti: h.is_cuti
        }));

        renderCalendar();
    } catch (error) {
        console.error('Gagal mengambil hari libur:', error);
        renderCalendar();
    }
}

// ===================== DATA CUTI USER =====================
function loadUserCuti() {
    const cutiData = @json($latestCuti ?? []);
    userCuti = [];

    cutiData.forEach(c => {
        const start = new Date(c.tanggal_mulai);
        const end = new Date(c.tanggal_selesai);

        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            userCuti.push(d.toISOString().split('T')[0]);
        }
    });
}

// ===================== RENDER KALENDER =====================
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    document.getElementById('monthYear').textContent =
        `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    let html = '<div class="grid grid-cols-7 gap-1 sm:gap-2 text-center">';

    dayNames.forEach(day => {
        html += `<div class="font-bold text-sky-700 py-1 sm:py-2 text-xs sm:text-sm">${day}</div>`;
    });

    for (let i = 0; i < startingDayOfWeek; i++) {
        html += '<div></div>';
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateStr = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');

        const dayOfWeek = date.getDay();

        let bgColor = 'bg-white';
        let borderColor = 'border-gray-300';
        let title = dateStr;

        const holiday = holidays.find(h => h.date === dateStr);
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const isCuti = userCuti.includes(dateStr);

        if (holiday) {
            bgColor = 'bg-red-200';
            borderColor = 'border-red-400'; 
            title = `${holiday.display} - ${holiday.name}`;
        } else if (isWeekend) {
            bgColor = 'bg-orange-200';
            borderColor = 'border-orange-400';
        } else if (isCuti) {
            bgColor = 'bg-yellow-200';
            borderColor = 'border-yellow-400';
        }

        html += `
        <div class="p-1 sm:p-2 border rounded ${bgColor} ${borderColor}
                    cursor-pointer hover:shadow-md transition"
             title="${title}">
            <div class="font-semibold text-xs sm:text-sm">${day}</div>
        </div>`;
    }

    html += '</div>';
    document.getElementById('calendar').innerHTML = html;

    updateHolidayList(month, year);
}

// ===================== LIST HARI LIBUR =====================
function updateHolidayList(month, year) {
    const monthHolidays = holidays.filter(h => {
        const d = new Date(h.date);
        return d.getMonth() === month && d.getFullYear() === year;
    });

    let html = '';

    if (monthHolidays.length === 0) {
        html = '<p class="text-gray-500">Tidak ada hari libur nasional</p>';
    } else {
        monthHolidays.forEach(h => {
            html += `
            <div class="text-xs">
                <span class="font-semibold text-red-600">${h.display}</span>
                <p class="text-gray-600">${h.name}</p>
            </div>`;
        });
    }

    document.getElementById('holidayList').innerHTML = html;
}

// ===================== EVENT =====================
document.getElementById('prevMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    fetchHolidays(currentDate.getFullYear(), currentDate.getMonth() + 1);
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    fetchHolidays(currentDate.getFullYear(), currentDate.getMonth() + 1);
});

// ===================== INIT =====================
loadUserCuti();
fetchHolidays(currentDate.getFullYear(), currentDate.getMonth() + 1);
</script>
@endsection