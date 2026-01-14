{{-- resources/views/landingpage.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAP CUTI | DISKOMINFO BANJARBARU</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .welcome-text {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.05em;
        }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-800">

    {{-- 1. Top Utility Bar --}}
    <div class="bg-slate-900 text-white text-[11px] py-2 px-6 hidden md:block tracking-wide">
        <div class="max-w-7xl mx-auto flex justify-between items-center font-medium">
            <div class="flex gap-6">
                <span><i class="ph-fill ph-map-pin text-sky-400"></i> Banjarbaru, Kalimantan Selatan</span>
                <span><i class="ph-fill ph-clock text-sky-400"></i> Jam Kerja: 08:00 — 16:00 WITA</span>
            </div>
            <div class="flex gap-4 items-center">
                <a href="{{ route('login') }}" class="bg-sky-600 hover:bg-sky-700 px-3 py-1 rounded-sm font-bold transition uppercase">
                    <i class="ph ph-lock-key"></i> Portal Akses Pegawai
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Main Brand Header --}}
<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-6">
        
        <div class="flex items-center gap-5">
            <img src="{{ asset('image/diskominfobjb.jpg') }}"
                 class="h-16 w-auto border border-slate-200 rounded-lg p-1 bg-white"
                 alt="Logo Diskominfo">

            <div class="leading-tight">
                <h1 class="text-2xl md:text-3xl font-extrabold uppercase text-slate-900">
                    Dinas Komunikasi dan Informatika
                </h1>
                <p class="text-sky-600 font-bold tracking-[0.25em] text-sm mt-1">
                    KOTA BANJARBARU
                </p>
            </div>
        </div>

        <div class="hidden lg:block text-right text-slate-500 italic text-base max-w-sm border-l-4 border-sky-500 pl-5">
            “Melayani dengan Teknologi,<br>Membangun Negeri”
        </div>

    </div>
</header>

    {{-- 3. Navigation Bar --}}
    <nav class="bg-sky-600 text-white sticky top-0 z-50 shadow-lg shadow-sky-900/10">
        <div class="max-w-7xl mx-auto flex overflow-x-auto md:overflow-visible font-bold text-xs tracking-widest">
            <a href="#beranda" class="px-8 py-4 hover:bg-sky-700 transition border-r border-sky-500 whitespace-nowrap">BERANDA</a>
            <a href="#tentang" class="px-8 py-4 hover:bg-sky-700 transition border-r border-sky-500 whitespace-nowrap uppercase">Tentang SIAP CUTI</a>
            <a href="#fitur" class="px-8 py-4 hover:bg-sky-700 transition border-r border-sky-500 whitespace-nowrap">FITUR LAYANAN</a>
        </div>
    </nav>

    {{-- 4. Hero Banner --}}
    <section id="beranda" class="relative">
        <div class="h-[400px] md:h-[550px] overflow-hidden relative">
            <img src="{{ asset('image/diskominfo.jpg') }}" alt="Banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent flex items-end">
                <div class="max-w-7xl mx-auto px-6 pb-16 w-full text-white">
                    <div class="bg-sky-700/80 backdrop-blur-sm p-8 rounded-sm max-w-2xl border-l-8 border-white shadow-2xl">
                        <div class="welcome-text uppercase font-bold text-sky-300 tracking-[0.3em] text-[10px] mb-2">
                            Selamat Datang di Sistem Cuti Digital
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black mb-3 uppercase tracking-tighter italic">TRANSFORMASI <br>CUTI DIGITAL</h2>
                        <p class="text-lg opacity-95 leading-relaxed font-medium">Wujudkan birokrasi modern melalui pengelolaan cuti yang transparan, akurat, dan terintegrasi dalam satu platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Main Content --}}
    <main class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <div class="lg:col-span-8 space-y-12">
                {{-- Box Tentang --}}
                <div id="tentang" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                    <div class="bg-sky-600 px-8 py-5">
                        <h3 class="text-white text-xl md:text-2xl font-extrabold uppercase tracking-wide flex items-center gap-3">
                            <i class="ph-fill ph-target"></i>
                            Fokus & Tujuan Sistem
                        </h3>
                        <p class="text-sky-100 mt-1 text-sm md:text-base">
                            Arah dan manfaat utama penerapan SIAP CUTI
                        </p>
                    </div>

                    <div class="p-10 space-y-6">
                        <p class="text-lg leading-relaxed text-slate-700">
                            <strong class="text-slate-900">SIAP CUTI</strong> (Sistem Informasi Pengajuan Cuti Terintegrasi)
                            merupakan inovasi digital Diskominfo Kota Banjarbaru dalam mendukung
                            tata kelola kepegawaian yang modern, transparan, dan akuntabel.
                        </p>

                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-6 text-base">
                            <li class="flex gap-4 items-start">
                                <i class="ph-fill ph-check-circle text-sky-600 text-2xl"></i>
                                <span>Menyederhanakan proses pengajuan dan persetujuan cuti secara digital</span>
                            </li>
                            <li class="flex gap-4 items-start">
                                <i class="ph-fill ph-check-circle text-sky-600 text-2xl"></i>
                                <span>Meningkatkan transparansi data sisa dan histori cuti pegawai</span>
                            </li>
                            <li class="flex gap-4 items-start">
                                <i class="ph-fill ph-check-circle text-sky-600 text-2xl"></i>
                                <span>Mengurangi kesalahan administrasi dan pengolahan manual</span>
                            </li>
                            <li class="flex gap-4 items-start">
                                <i class="ph-fill ph-check-circle text-sky-600 text-2xl"></i>
                                <span>Mendukung pengambilan keputusan berbasis data</span>
                            </li>
                        </ul>
                    </div>

                </div>

                {{-- Box Fitur (Bagian yang diperbarui sesuai permintaan) --}}
                <div id="fitur" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $fitur = [
                            ['icon' => 'ph-file-text', 'color' => 'text-sky-600', 'title' => 'Pengajuan Online', 'desc' => 'Form cuti digital yang memudahkan penginputan data kapan saja.'],
                            ['icon' => 'ph-check-circle', 'color' => 'text-green-600', 'title' => 'Persetujuan Cepat', 'desc' => 'Validasi berjenjang oleh pimpinan yang terpantau secara real-time.'],
                            ['icon' => 'ph-chart-line', 'color' => 'text-purple-600', 'title' => 'Monitoring Kuota', 'desc' => 'Transparansi sisa kuota cuti tahunan tanpa rekapitulasi manual.'],
                            ['icon' => 'ph-shield-check', 'color' => 'text-orange-500', 'title' => 'Integritas Data', 'desc' => 'Keamanan data administrasi yang terpusat dan terenkripsi aman.']
                        ];
                    @endphp
                    @foreach($fitur as $f)
                        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 group-hover:bg-sky-50 transition-colors">
                                <i class="ph {{ $f['icon'] }} text-4xl {{ $f['color'] }}"></i>
                            </div>
                            <h4 class="font-bold text-slate-900 uppercase text-xs mb-2 tracking-wider">{{ $f['title'] }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $f['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Sidebar Widget --}}
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-slate-900 rounded-sm p-8 text-white shadow-xl relative overflow-hidden group border-b-4 border-sky-600">
                    <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform text-9xl">
                        <i class="ph ph-lock-key"></i>
                    </div>
                    <h3 class="font-bold mb-3 uppercase tracking-widest text-sky-400">Portal Akses</h3>
                    <p class="text-sm opacity-80 mb-6 leading-relaxed text-slate-300 italic">Gunakan akun resmi untuk masuk ke sistem pengajuan dan validasi.</p>
                    <a href="{{ route('login') }}" class="block w-full bg-sky-600 text-white text-center py-3 font-extrabold rounded-sm uppercase hover:bg-sky-500 transition shadow-lg tracking-wider">
                        Masuk ke Sistem <i class="ph ph-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- 6. Footer (Informasi Dinas Lengkap) --}}
        <footer class="bg-slate-900 text-slate-300 pt-20 pb-10 px-6 border-t-4 border-sky-600">

        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-16 pb-14 border-b border-slate-800">

            <!-- Profil -->
            <div class="space-y-6 text-base">
                <img src="{{ asset('image/diskominfobjb.jpg') }}" class="h-20 bg-white p-2 rounded-lg">

                <h4 class="text-white font-extrabold uppercase tracking-widest text-lg">
                    Diskominfo Banjarbaru
                </h4>

                <p class="italic text-slate-400">
                    “Melayani dengan Teknologi, Membangun Negeri”
                </p>

                <p class="flex gap-3 items-start">
                    <i class="ph-fill ph-map-pin text-sky-500 text-xl"></i>
                    <span>
                        Jl. Jenderal Sudirman No.1, Banjarbaru, Kalimantan Selatan
                    </span>
                </p>
            </div>

            <!-- Link -->
            <div>
                <h4 class="text-white font-bold uppercase tracking-widest text-lg mb-6 border-l-4 border-sky-500 pl-4">
                    Tautan Terkait
                </h4>
<ul class="text-sm space-y-4 font-medium">
                    <li><a href="https://banjarbarukota.go.id" target="_blank" class="hover:text-sky-400 transition flex items-center gap-2"><i class="ph ph-caret-right"></i> Pemerintah Kota Banjarbaru</a></li>
                    <li><a href="https://diskominfo.banjarbarukota.go.id" target="_blank" class="hover:text-sky-400 transition flex items-center gap-2"><i class="ph ph-caret-right"></i> Portal Resmi Diskominfo</a></li>
                    <li><a href="https://lapor.go.id" target="_blank" class="hover:text-sky-400 transition flex items-center gap-2"><i class="ph ph-caret-right"></i> Layanan Aspirasi (LAPOR!)</a></li>
                </ul>
            </div>

            <!-- Kontak -->
            <div>
                <h4 class="text-white font-bold uppercase tracking-widest text-lg mb-6 border-l-4 border-sky-500 pl-4">
                    Kontak Resmi
                </h4>
                <div class="space-y-5 text-base">
                    <p class="flex gap-4 items-center">
                        <i class="ph-fill ph-phone text-sky-500 text-xl"></i> (0511) 4772022
                    </p>
                    <p class="flex gap-4 items-center">
                        <i class="ph-fill ph-envelope text-sky-500 text-xl"></i> diskominfo@banjarbarukota.go.id
                    </p>
                </div>
            </div>

        </div>

        <div class="text-center mt-8 text-sm text-slate-500 tracking-widest uppercase">
            © {{ date('Y') }} Pemerintah Kota Banjarbaru — Diskominfo
        </div>
    </footer>

</body>
</html>