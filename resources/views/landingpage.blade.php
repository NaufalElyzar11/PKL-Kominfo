{{-- resources/views/landingpage.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAP CUTI | Dinas Komunikasi dan Informatika Kota Banjarbaru</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary: #0f766e;
            --accent: #f59e0b;
        }
        body { 
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; 
            line-height: 1.6;
        }
        h1,h2,h3,h4,h5,h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
        .navbar-shadow {
            box-shadow: 0 10px 25px -3px rgba(0, 0,0, 0.1), 0 4px 6px -2px rgba(0, 0,0, 0.05);
        }
    </style>
</head>

<body class="bg-white text-slate-900 antialiased">

    {{-- HEADER UTILITY --}}
    <div class="bg-slate-900 text-white text-xs py-3 px-4 md:px-8 hidden md:flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-2">
                <i class="ph-fill ph-map-pin text-blue-400 w-4 h-4"></i>
                <span>Banjarbaru, Kalimantan Selatan</span>
            </div>
            <div class="flex items-center gap-2">
                <i class="ph-fill ph-clock text-blue-400 w-4 h-4"></i>
                <span>08:00 - 16:00 WITA</span>
            </div>
        </div>
    </div>

    {{-- MAIN HEADER + NAVBAR COMBINED (ALWAYS VISIBLE) --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 navbar-shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between py-4 lg:py-5 gap-4 lg:gap-0">
                <!-- Logo Section -->
                <div class="flex items-center gap-4 order-1 lg:order-1">
                    <div class="relative">
                        <img src="{{ asset('image/diskominfobjb.jpg') }}" 
                             class="h-14 w-14 md:h-16 md:w-16 border-2 border-slate-200 rounded-lg shadow-md object-cover"
                             alt="Logo Diskominfo" loading="lazy">
                        <div class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-lg">ID</div>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 leading-tight">
                            Dinas Komunikasi dan Informatika
                        </h1>
                        <p class="text-blue-600 font-semibold text-sm uppercase tracking-wide">Kota Banjarbaru</p>
                    </div>
                </div>

                <!-- Navbar -->
                <nav class="order-3 lg:order-2 flex justify-center flex-1 px-4 lg:px-0">
                    <div class="flex space-x-1 md:space-x-8 bg-slate-50/50 rounded-2xl p-1 backdrop-blur-sm">
                        <a href="#beranda" class="nav-item flex items-center gap-2 px-4 py-3 lg:px-6 lg:py-3 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-white rounded-xl border-b-2 border-transparent hover:border-blue-500 transition-all duration-200 whitespace-nowrap">
                            <i class="ph ph-house w-5 h-5"></i>
                            <span>Beranda</span>
                        </a>
                        <a href="#tentang" class="nav-item flex items-center gap-2 px-4 py-3 lg:px-6 lg:py-3 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-white rounded-xl border-b-2 border-transparent hover:border-blue-500 transition-all duration-200 whitespace-nowrap">
                            <i class="ph ph-info w-5 h-5"></i>
                            <span>Tentang</span>
                        </a>
                        <a href="#fitur" class="nav-item flex items-center gap-2 px-4 py-3 lg:px-6 lg:py-3 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-white rounded-xl border-b-2 border-transparent hover:border-blue-500 transition-all duration-200 whitespace-nowrap">
                            <i class="ph ph-sparkle w-5 h-5"></i>
                            <span>Fitur</span>
                        </a>
                    </div>
                </nav>

                <!-- CTA Button (SATU SAJA) -->
                <div class="order-2 lg:order-3">
                    <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl text-base shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 whitespace-nowrap">
                        <i class="ph ph-sign-in w-5 h-5"></i>
                        Masuk Sistem
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- HERO BANNER DENGAN GAMBAR FULL --}}
    <section id="beranda" class="relative overflow-hidden">
        <!-- Background Image FULL -->
        <div class="absolute inset-0">
            <img src="{{ asset('image/diskominfo.jpg') }}" 
                 alt="Hero Banner Diskominfo" 
                 class="w-full h-[70vh] md:h-screen object-cover brightness-50">
            <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-slate-900/40 to-black/60"></div>
        </div>
        
        <!-- Hero Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-24 md:pt-40 md:pb-32 text-white">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center bg-white/20 backdrop-blur-sm border border-white/30 text-white px-8 py-4 rounded-2xl font-semibold uppercase tracking-wide text-lg md:text-xl mb-12 shadow-2xl">
                    <i class="ph ph-rocket w-6 h-6 mr-3"></i>
                    Selamat Datang di Sistem Cuti Digital
                </div>
                <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight bg-gradient-to-r from-white to-blue-100 bg-clip-text">
                    SIAP CUTI
                </h2>
                <h3 class="text-2xl md:text-3xl lg:text-4xl font-semibold mb-8 max-w-3xl mx-auto leading-relaxed opacity-95">
                    Transformasi Digital Pengelolaan Cuti Pegawai
                </h3>
                <p class="text-lg md:text-xl lg:text-2xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed opacity-90">
                    Sistem informasi terintegrasi untuk pengajuan, persetujuan, dan monitoring cuti yang transparan, efisien, dan akuntabel.
                </p>
                <!-- SATU BUTTON SAJA -->
                <div class="flex justify-center">
                    <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-6 px-16 rounded-2xl text-xl shadow-2xl hover:shadow-3xl transition-all duration-400 flex items-center gap-3 backdrop-blur-sm">
                        <i class="ph ph-sign-in w-6 h-6"></i>
                        Akses Sistem Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 -mt-16 lg:-mt-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            
            <!-- Main Content -->
            <div class="lg:col-span-8 space-y-20 lg:space-y-28">
                
                <!-- Tentang Section -->
                <section id="tentang" class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-slate-200/50 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-12 md:px-12 md:py-16 text-white">
                        <h3 class="text-3xl md:text-4xl font-bold flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-2xl backdrop-blur-sm">
                                <i class="ph-fill ph-target"></i>
                            </div>
                            Fokus & Tujuan
                        </h3>
                        <p class="mt-4 text-lg md:text-xl font-medium opacity-90">
                            Inovasi Digital Diskominfo Banjarbaru
                        </p>
                    </div>
                    <div class="p-10 md:p-12 lg:p-16 space-y-10">
                        <p class="text-xl lg:text-2xl text-slate-800 leading-relaxed font-medium text-center md:text-left max-w-3xl mx-auto md:mx-0">
                            <strong class="text-4xl lg:text-5xl text-blue-600 block mb-6 font-bold">SIAP CUTI</strong> 
                            merupakan sistem informasi pengajuan cuti terintegrasi yang mendukung tata kelola 
                            kepegawaian yang <strong class="text-blue-600">modern, transparan, dan akuntabel</strong>.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group p-10 rounded-3xl bg-gradient-to-br from-blue-50 to-slate-50 border-l-6 border-blue-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 cursor-pointer backdrop-blur-sm">
                                <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl mb-8 group-hover:scale-110 transition-transform shadow-xl mx-auto">
                                    <i class="ph-fill ph-lightning"></i>
                                </div>
                                <h4 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 text-center">Proses Digital</h4>
                                <p class="text-slate-700 text-lg leading-relaxed text-center">Pengajuan dan persetujuan cuti digital tanpa kertas.</p>
                            </div>
                            <div class="group p-10 rounded-3xl bg-gradient-to-br from-emerald-50 to-slate-50 border-l-6 border-emerald-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 cursor-pointer backdrop-blur-sm">
                                <div class="w-20 h-20 bg-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl mb-8 group-hover:scale-110 transition-transform shadow-xl mx-auto">
                                    <i class="ph-fill ph-eye"></i>
                                </div>
                                <h4 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 text-center">Transparansi</h4>
                                <p class="text-slate-700 text-lg leading-relaxed text-center">Monitoring real-time sisa kuota dan histori cuti.</p>
                            </div>
                            <div class="group p-10 rounded-3xl bg-gradient-to-br from-purple-50 to-slate-50 border-l-6 border-purple-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 cursor-pointer backdrop-blur-sm">
                                <div class="w-20 h-20 bg-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl mb-8 group-hover:scale-110 transition-transform shadow-xl mx-auto">
                                    <i class="ph-fill ph-hard-drive"></i>
                                </div>
                                <h4 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 text-center">Akurat</h4>
                                <p class="text-slate-700 text-lg leading-relaxed text-center">Hilangkan kesalahan administrasi manual.</p>
                            </div>
                            <div class="group p-10 rounded-3xl bg-gradient-to-br from-amber-50 to-slate-50 border-l-6 border-amber-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 cursor-pointer backdrop-blur-sm">
                                <div class="w-20 h-20 bg-amber-600 rounded-2xl flex items-center justify-center text-white text-2xl mb-8 group-hover:scale-110 transition-transform shadow-xl mx-auto">
                                    <i class="ph-fill ph-chart-line"></i>
                                </div>
                                <h4 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 text-center">Data-Driven</h4>
                                <p class="text-slate-700 text-lg leading-relaxed text-center">Keputusan berbasis analisis data akurat.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Fitur Section -->
                <section id="fitur" class="space-y-20">
                    <div class="text-center max-w-4xl mx-auto space-y-8">
                        <div class="inline-flex items-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-10 py-6 rounded-3xl font-bold text-xl lg:text-2xl shadow-2xl backdrop-blur-sm">
                            <i class="ph-fill ph-sparkle text-3xl mr-4"></i>
                            Fitur Unggulan SIAP CUTI
                        </div>
                        <h3 class="text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Sistem Cuti Terlengkap & Modern
                        </h3>
                    </div>

                    @php
                        $fitur = [
                            ['icon' => 'ph-fill ph-file-text', 'title' => 'Pengajuan Online', 'desc' => 'Form digital intuitif dengan auto-save dan validasi otomatis.', 'color' => 'blue'],
                            ['icon' => 'ph-fill ph-check-circle', 'title' => 'Approval Cepat', 'desc' => 'Alur persetujuan berjenjang dengan notifikasi real-time.', 'color' => 'emerald'],
                            ['icon' => 'ph-fill ph-chart-line-up', 'title' => 'Monitoring Kuota', 'desc' => 'Dashboard visual sisa cuti dan histori lengkap.', 'color' => 'purple'],
                            ['icon' => 'ph-fill ph-shield-check', 'title' => 'Keamanan Tinggi', 'desc' => 'Enkripsi end-to-end dan autentikasi 2-faktor.', 'color' => 'amber']
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-12">
                        @foreach($fitur as $f)
                        <div class="group bg-white/90 backdrop-blur-sm rounded-3xl p-12 border border-slate-200/50 hover:shadow-3xl hover:-translate-y-4 transition-all duration-700 hover:border-{{ $f['color'] }}-200/50 cursor-pointer">
                            <div class="w-28 h-28 bg-gradient-to-br from-{{ $f['color'] }}-500 to-{{ $f['color'] }}-600 rounded-3xl flex items-center justify-center mb-10 mx-auto group-hover:scale-110 transition-all duration-500 shadow-2xl">
                                <i class="{{ $f['icon'] }} text-4xl text-white"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-900 text-center mb-8 group-hover:text-{{ $f['color'] }}-600 transition-colors">{{ $f['title'] }}</h4>
                            <p class="text-slate-700 text-center text-xl leading-relaxed mb-10 opacity-90">{{ $f['desc'] }}</p>
                            <div class="flex items-center justify-center text-{{ $f['color'] }}-600 font-bold text-lg group-hover:translate-x-4 transition-transform">
                                <i class="ph-fill ph-arrow-right mr-3 w-6 h-6"></i>
                                Pelajari Lebih Lanjut
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Sidebar CTA (SATU SAJA) -->
            <div class="lg:col-span-4 space-y-10 lg:sticky lg:top-32 self-start lg:h-screen lg:flex lg:flex-col lg:justify-center">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-12 lg:p-16 text-white shadow-3xl border-t-8 border-blue-600 relative overflow-hidden">
                    <div class="absolute top-8 right-8 text-7xl opacity-10">
                        <i class="ph ph-lock-simple"></i>
                    </div>
                    <div class="relative z-10 text-center lg:text-left">
                        <h3 class="text-3xl lg:text-4xl font-bold mb-8 bg-gradient-to-r from-blue-300 to-blue-100 bg-clip-text text-transparent">Portal Pegawai</h3>
                        <p class="text-slate-200 mb-12 leading-relaxed text-xl lg:text-2xl opacity-95">
                            Akses sistem pengajuan cuti dengan akun resmi pegawai Anda
                        </p>
                        <!-- SATU BUTTON SAJA -->
                        <a href="{{ route('login') }}" class="block w-full max-w-sm mx-auto lg:mx-0 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold py-6 px-12 rounded-2xl text-xl uppercase tracking-wide shadow-2xl hover:shadow-3xl transition-all duration-400 flex items-center justify-center gap-4 backdrop-blur-sm mx-auto lg:mx-0">
                            <i class="ph ph-sign-in w-7 h-7"></i>
                            Masuk ke SIAP CUTI
                        </a>
                        <div class="text-sm text-slate-400 mt-8 text-center lg:text-left font-medium tracking-wide">
                            ✅ Sistem Resmi • 🔒 Terjamin Aman • ⚡ Cepat & Mudah
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="bg-gradient-to-t from-slate-900 via-slate-900 to-slate-950 border-t-8 border-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-16 mb-16">
                <!-- Brand -->
                <div class="space-y-8 text-slate-200">
                    <div class="flex items-center gap-6">
                        <img src="{{ asset('image/diskominfobjb.jpg') }}" 
                             class="h-24 w-24 rounded-2xl border-4 border-blue-500/50 p-3 bg-white shadow-2xl object-cover" 
                             alt="Logo Diskominfo" loading="lazy">
                    </div>
                    <h4 class="text-3xl font-bold text-white leading-tight">Diskominfo Banjarbaru</h4>
                    <p class="text-blue-300 text-xl italic leading-relaxed font-medium">
                        Melayani dengan Teknologi,<br>Membangun Negeri
                    </p>
                    <div class="flex items-start gap-4 p-8 bg-slate-800/60 backdrop-blur-sm rounded-3xl border border-slate-700/50 hover:bg-slate-800/80 transition-all group">
                        <i class="ph-fill ph-map-pin text-blue-400 text-3xl mt-1 flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                        <p class="text-lg leading-relaxed">Jl. Pangeran Suriansyah No. 5<br>Kel. Komet, Kec. Banjarbaru Utara<br>Kota Banjarbaru 70711</p>
                    </div>
                </div>

                <!-- Links -->
                <div class="space-y-8">
                    <h4 class="text-2xl font-bold text-white mb-10 border-l-6 border-blue-500 pl-6 pb-4 tracking-tight">
                        Tautan Penting
                    </h4>
                    <ul class="space-y-6">
                        <li>
                            <a href="https://banjarbarukota.go.id" target="_blank" class="group flex items-center gap-4 p-6 bg-slate-800/50 rounded-2xl hover:bg-blue-500/20 hover:text-blue-300 border border-slate-700/50 hover:border-blue-500/50 backdrop-blur-sm transition-all duration-300 font-semibold text-xl">
                                <i class="ph ph-external-link text-blue-400 group-hover:translate-x-2 transition-transform text-2xl w-9 h-9 flex-shrink-0"></i>
                                Pemerintah Kota Banjarbaru
                            </a>
                        </li>
                        <li>
                            <a href="https://diskominfo.banjarbarukota.go.id" target="_blank" class="group flex items-center gap-4 p-6 bg-slate-800/50 rounded-2xl hover:bg-blue-500/20 hover:text-blue-300 border border-slate-700/50 hover:border-blue-500/50 backdrop-blur-sm transition-all duration-300 font-semibold text-xl">
                                <i class="ph ph-external-link text-blue-400 group-hover:translate-x-2 transition-transform text-2xl w-9 h-9 flex-shrink-0"></i>
                                Portal Resmi Dinas Komunikasi dan Informatika
                            </a>
                        </li>
                        <li>
                            <a href="https://lapor.go.id" target="_blank" class="group flex items-center gap-4 p-6 bg-slate-800/50 rounded-2xl hover:bg-blue-500/20 hover:text-blue-300 border border-slate-700/50 hover:border-blue-500/50 backdrop-blur-sm transition-all duration-300 font-semibold text-xl">
                                <i class="ph ph-external-link text-blue-400 group-hover:translate-x-2 transition-transform text-2xl w-9 h-9 flex-shrink-0"></i>
                                LAPOR! Aspirasi Masyarakat
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="space-y-8">
                    <h4 class="text-2xl font-bold text-white mb-10 border-l-6 border-blue-500 pl-6 pb-4 tracking-tight">
                        Kontak Resmi
                    </h4>
                    <div class="space-y-8">
                        <a href="tel:+625114772022" class="group flex items-center gap-6 p-8 bg-slate-800/60 rounded-3xl hover:bg-blue-500/20 hover:text-blue-300 border border-slate-700/50 hover:border-blue-500/50 backdrop-blur-sm transition-all duration-400">
                            <div class="w-20 h-20 bg-blue-500/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:bg-blue-500/50 transition-all">
                                <i class="ph-fill ph-phone text-blue-400 text-3xl group-hover:text-blue-300 transition-colors"></i>
                            </div>
                            <div>
                                <p class="text-lg text-slate-400 font-semibold uppercase tracking-wide mb-2">Telepon Kantor</p>
                                <div class="font-black text-3xl text-white group-hover:text-blue-300 transition-colors">(0511) 4772022</div>
                            </div>
                        </a>
                        <a href="mailto:diskominfo@banjarbarukota.go.id" class="group flex items-center gap-6 p-8 bg-slate-800/60 rounded-3xl hover:bg-blue-500/20 hover:text-blue-300 border border-slate-700/50 hover:border-blue-500/50 backdrop-blur-sm transition-all duration-400">
                            <div class="w-20 h-20 bg-blue-500/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:bg-blue-500/50 transition-all">
                                <i class="ph-fill ph-envelope text-blue-400 text-3xl group-hover:text-blue-300 transition-colors"></i>
                            </div>
                            <div class="break-words">
                                <p class="text-lg text-slate-400 font-semibold uppercase tracking-wide mb-2">Email Resmi</p>
                                <div class="font-black text-2xl text-white group-hover:text-blue-300 transition-colors break-all">diskominfo@banjarbarukota.go.id</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-800/50 pt-12 mt-12 text-center">
                <p class="text-2xl md:text-3xl text-slate-100 font-bold tracking-wide">
                    © {{ date('Y') }} Dinas Komunikasi dan Informatika Kota Banjarbaru
                </p>
                <p class="text-lg text-slate-500 mt-4 font-medium">Semua Hak Dilindungi Undang-Undang</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll dengan offset yang tepat
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', e => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    const headerOffset = 140; // Tinggi header + navbar
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            });
        });

        // Navbar active state dengan IntersectionObserver
        const observerOptions = {
            threshold: 0.3,
            rootMargin: '-140px 0px -60% 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelectorAll('.nav-item').forEach(nav => {
                        nav.classList.remove('text-blue-600', 'bg-white', 'border-blue-500', 'shadow-md');
                    });
                    const activeLink = document.querySelector(`a[href="#${entry.target.id}"]`);
                    if (activeLink) {
                        activeLink.classList.add('text-blue-600', 'bg-white', 'border-blue-500', 'shadow-md');
                    }
                }
            });
        }, observerOptions);

        // Observe semua section
        document.querySelectorAll('section[id]').forEach(section => observer.observe(section));

        // Navbar scroll enhancement
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('shadow-2xl', 'bg-white/100');
            } else {
                header.classList.remove('shadow-2xl', 'bg-white/100');
            }
        });
    </script>

</body>
</html>