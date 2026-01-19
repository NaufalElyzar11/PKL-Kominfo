@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="p-6 space-y-8">

    {{-- 🌟 KARTU SELAMAT DATANG --}}
    <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white p-6 rounded-2xl shadow flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">
                Selamat Datang, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-sky-100 mt-1">
                Pantau dan kelola pengajuan cuti pegawai
            </p>
        </div>
        <div class="hidden sm:block bg-white/20 p-4 rounded-full">
            <i class="fa-solid fa-clipboard-user text-4xl"></i>
        </div>
    </div>

    {{-- 🧩 GRID STATISTIK (DESKTOP-FIRST) --}}
    <div class="grid grid-cols-12 gap-6 items-start">

        {{-- ⬅️ STATISTIK SISA CUTI --}}
        <div class="col-span-8 bg-white rounded-xl p-6 flex flex-col shadow border border-gray-200">
            <h2 class="text-sm font-semibold text-sky-700 mb-4">
                Statistik Sisa Cuti
            </h2>

            <div class="h-[360px] min-h-[260px]">
                <canvas id="sisaCutiChart"></canvas>
            </div>
        </div>

        {{-- ➡️ KANAN --}}
        <div class="col-span-4 flex flex-col gap-6 items-stretch">

            {{-- STATUS CUTI (3 SEJAJAR) --}}
            <div class="grid grid-cols-3 gap-4">

                <div class="bg-yellow-100 rounded-xl p-4 text-center h-28 flex flex-col items-center justify-center">
                    <p class="text-xs text-yellow-700">Menunggu</p>
                    <div class="flex items-center gap-2 justify-center mt-2">
                        <i class="fa-solid fa-hourglass-end text-yellow-500 text-xl"></i>
                        <p class="text-2xl font-bold text-yellow-700">
                            {{ $cutiPending ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="bg-green-100 rounded-xl p-4 text-center h-28 flex flex-col items-center justify-center">
                    <p class="text-xs text-green-700">Disetujui</p>
                    <div class="flex items-center gap-2 justify-center mt-2">
                        <i class="fa-solid fa-check text-green-500 text-xl"></i>
                        <p class="text-2xl font-bold text-green-700">
                            {{ $cutiDisetujui ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="bg-red-100 rounded-xl p-4 text-center h-28 flex flex-col items-center justify-center">
                    <p class="text-xs text-red-700">Ditolak</p>
                    <div class="flex items-center gap-2 justify-center mt-2">
                        <i class="fa-solid fa-xmark text-red-500 text-xl"></i>
                        <p class="text-2xl font-bold text-red-700">
                            {{ $cutiDitolak ?? 0 }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- JUMLAH PEGAWAI --}}
            <div class="bg-white rounded-xl py-6 flex flex-col items-center justify-center h-36 shadow border border-gray-200">
                <p class="text-sm font-semibold text-sky-700 mb-4">Jumlah Pegawai</p>
                <div class="flex items-center gap-2 justify-center mt-2">
                    <i class="fa-solid fa-users text-sky-500 text-2xl"></i>
                    <p class="text-3xl font-extrabold text-sky-700">
                        {{ $totalPegawai ?? 0 }}
                    </p>
                </div>
            </div>

            {{-- PEGAWAI YANG SEDANG CUTI --}}
            <div class="bg-white rounded-xl py-6 flex flex-col items-center justify-center h-36 shadow border border-gray-200">
                <p class="text-sm font-semibold text-sky-700 mb-4">Pegawai Sedang Cuti</p>
                <div class="flex items-center gap-2 justify-center mt-2">
                    <i class="fa-solid fa-person-hiking text-purple-500 text-2xl"></i>
                    <p class="text-3xl font-extrabold text-purple-600">
                        {{ $pegawaiSedangCuti ?? 0 }}
                    </p>
                </div>
            </div>

        </div>
    </div>


    {{-- 📋 TABEL RIWAYAT CUTI (TETAP) --}}
    <div class="bg-white p-5 rounded-2xl shadow border border-gray-200">
        <h2 class="text-lg font-bold text-sky-700 mb-3 border-b pb-2">
            Riwayat Cuti Pegawai
        </h2>

        <div class="overflow-auto max-h-[28rem] text-xs">
            <table class="min-w-full border-collapse">
                <thead class="bg-sky-600 text-white sticky top-0">
                    <tr>
                        <th class="border px-2 py-1">No</th>
                        <th class="border px-2 py-1">Nama</th>
                        <th class="border px-2 py-1">NIP</th>
                        <th class="border px-2 py-1">Jabatan</th>
                        <th class="border px-2 py-1">Jenis Cuti</th>
                        <th class="border px-2 py-1">Mulai</th>
                        <th class="border px-2 py-1">Selesai</th>
                        <th class="border px-2 py-1">Hari</th>
                        <th class="border px-2 py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestCuti ?? [] as $i => $c)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $c->pegawai->nama ?? '-' }}</td>
                        <td class="border px-2 py-1 text-center">{{ $c->pegawai->nip ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $c->pegawai->jabatan ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $c->jenis_cuti }}</td>
                        <td class="border px-2 py-1 text-center">{{ $c->tanggal_mulai?->format('d-m-Y') }}</td>
                        <td class="border px-2 py-1 text-center">{{ $c->tanggal_selesai?->format('d-m-Y') }}</td>
                        <td class="border px-2 py-1 text-center">{{ $c->jumlah_hari }}</td>
                        <td class="border px-2 py-1 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px]
                                {{ $c->status === 'disetujui' ? 'bg-green-100 text-green-700' :
                                   ($c->status === 'ditolak' ? 'bg-red-100 text-red-700' :
                                   'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($c->status) }}
                            </span>
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

    {{-- 📅 KALENDER --}}
    <div class="bg-white p-5 rounded-2xl shadow border border-gray-200">
        <h2 class="text-lg font-bold text-sky-700 mb-4 border-b pb-2">
            Kalender Cuti & Hari Libur
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kalender -->
            <div class="lg:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <button id="prevMonth" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                        <i class="fa-solid fa-chevron-left"></i> Bulan Lalu
                    </button>
                    <h3 id="monthYear" class="text-lg font-bold text-sky-700"></h3>
                    <button id="nextMonth" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                        Bulan Depan <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div id="calendar" class="bg-gray-50 p-4 rounded-lg border border-gray-200"></div>
            </div>

            <!-- Legenda & Info -->
            <div class="flex flex-col gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-3">Keterangan</h4>
                    <div class="space-y-3 text-sm">

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

                <div id="holidayInfo" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-3">Hari Libur Bulan Ini</h4>
                    <div id="holidayList" class="text-sm space-y-2">
                        <p class="text-gray-500">Memuat hari libur...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('sisaCutiChart'), {
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
    let html = '<div class="grid grid-cols-7 gap-2 text-center">';

    dayNames.forEach(day => {
        html += `<div class="font-bold text-sky-700 py-2">${day}</div>`;
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
        <div class="p-2 border rounded ${bgColor} ${borderColor}
                    cursor-pointer hover:shadow-md transition"
             title="${title}">
            <div class="font-semibold text-sm">${day}</div>
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
