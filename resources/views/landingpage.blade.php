<!DOCTYPE html>
<html class="light scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">
    <title>SIAP CUTI | Dinas Komunikasi dan Informatika Kota Banjarbaru</title>
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#1f2937]">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-50 w-full bg-white border-b border-[#e7edf3] dark:bg-[#1a2632] dark:border-slate-700 shadow-sm transition-all duration-300">
            <div class="max-w-[1280px] mx-auto px-4 lg:px-10 h-[80px] flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center h-10 w-auto rounded-lg bg-white overflow-hidden">
                        <img src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo" class="h-full w-auto object-contain">
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-[#0d141b] dark:text-white text-lg font-bold leading-tight tracking-[-0.015em]">SIAP CUTI</h1>
                        <span class="text-xs text-slate-500 font-medium">Diskominfo Banjarbaru</span>
                    </div>
                </div>
                <div class="flex items-center gap-4 md:gap-8">
                    <nav class="hidden md:flex items-center gap-8">
                        <a class="text-[#0d141b] dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors" href="#beranda">Beranda</a>
                        <a class="text-[#0d141b] dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors" href="#tentang">Tentang</a>
                        <a class="text-[#0d141b] dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors" href="#fitur">Fitur</a>
                        <a class="text-[#0d141b] dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors" href="#panduan">Panduan</a>
                        <a class="text-[#0d141b] dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors" href="#kontak">Kontak</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="beranda" class="relative w-full bg-[#003366] text-white py-16 lg:py-24 overflow-hidden">
            <div class="absolute inset-0 z-0 opacity-10" data-alt="Abstract dotted pattern background" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
            <!-- Decorative gradient blobs -->
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-[500px] h-[500px] bg-primary/30 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-[100px]"></div>

            <div class="layout-container flex h-full grow flex-col relative z-10">
                <div class="max-w-[1280px] mx-auto px-4 lg:px-10 flex flex-col md:flex-row items-center gap-12 lg:gap-20">
                    <div class="flex flex-col gap-6 max-w-[640px] text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 w-fit mx-auto md:mx-0 border border-white/20 backdrop-blur-sm animate-fade-in-up">
                            <span class="size-2 rounded-full bg-green-400 animate-pulse"></span>
                            <span class="text-xs font-bold tracking-wide uppercase text-white/90">Sistem Cuti Digital Terintegrasi</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.15] tracking-tight">
                            SIAP CUTI
                            <span class="block text-primary-300 bg-clip-text text-transparent bg-gradient-to-r from-blue-200 to-white">Diskominfo Banjarbaru</span>
                        </h1>
                        <p class="text-lg md:text-xl text-slate-200 font-light leading-relaxed max-w-[540px] mx-auto md:mx-0">
                            Transformasi digital pengelolaan cuti pegawai. Transparan, efisien, dan akuntabel untuk tata kelola kepegawaian yang lebih baik.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 pt-4 justify-center md:justify-start">
                            <a href="{{ route('login') }}" class="h-12 px-8 rounded-lg bg-primary hover:bg-blue-500 text-white font-bold text-base transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <span>Ajukan Cuti Sekarang</span>
                                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                            </a>
                            <a href="#panduan" class="h-12 px-8 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-base backdrop-blur-sm transition-all flex items-center justify-center">
                                Pelajari Panduan
                            </a>
                        </div>
                    </div>
                    <div class="flex-1 w-full max-w-[500px] hidden md:block relative group">
                        <!-- Card decoration -->
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-purple-400 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                        
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-2xl relative transform rotate-1 group-hover:rotate-0 transition-transform duration-500">
                            <!-- User Profile Mock -->
                            <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-6">
                                <div class="size-12 rounded-full bg-slate-200/20 border-2 border-white/10"></div>
                                <div class="flex flex-col gap-2 w-full">
                                    <div class="h-3 w-32 bg-slate-200/20 rounded-full animate-pulse"></div>
                                    <div class="h-2 w-20 bg-slate-200/10 rounded-full"></div>
                                </div>
                            </div>
                            <!-- Status Items -->
                            <div class="space-y-4">
                                <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-lg flex items-center gap-4 transition-all duration-300 hover:scale-105 hover:bg-green-500/30 hover:border-green-400 hover:shadow-[0_8px_20px_rgba(34,197,94,0.25)] cursor-default">
                                    <div class="size-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-green-400">check_circle</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">Cuti Tahunan Disetujui</div>
                                        <div class="text-xs text-white/60">Bapak Kepala Dinas • Baru saja</div>
                                    </div>
                                </div>
                                <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg flex items-center gap-4 opacity-80 transition-all duration-300 hover:scale-105 hover:opacity-100 hover:bg-yellow-500/20 hover:border-yellow-400 hover:shadow-[0_8px_20px_rgba(234,179,8,0.25)] cursor-default">
                                    <div class="size-10 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-yellow-400">schedule</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">Menunggu Validasi</div>
                                        <div class="text-xs text-white/60">Sedang diproses Kasubag</div>
                                    </div>
                                </div>
                                <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg flex items-center gap-4 opacity-70 transition-all duration-300 hover:scale-105 hover:opacity-100 hover:bg-blue-500/20 hover:border-blue-400 hover:shadow-[0_8px_20px_rgba(59,130,246,0.25)] cursor-default">
                                    <div class="size-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-blue-400">notifications</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">Sisa Cuti: 6 Hari</div>
                                        <div class="text-xs text-white/60">Periode 2024</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Focus/About Section -->
        <section id="tentang" class="w-full bg-white dark:bg-slate-900 py-20 lg:py-28">
            <div class="max-w-[960px] mx-auto px-4 lg:px-10 flex flex-col items-center text-center">
                <span class="text-primary font-bold tracking-wider uppercase text-sm mb-3">Tentang SIAP CUTI</span>
                <h2 class="text-[#0d141b] dark:text-white text-3xl md:text-4xl font-bold leading-tight tracking-tight mb-8">
                    Fokus & Tujuan
                </h2>
                <p class="text-slate-600 dark:text-slate-300 text-lg md:text-xl font-normal leading-relaxed max-w-[800px]">
                    SIAP CUTI merupakan sistem informasi pengajuan cuti terintegrasi yang mendukung tata kelola kepegawaian yang modern, transparan, dan akuntabel. Kami menghilangkan kerumitan birokrasi manual demi efisiensi kerja.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 w-full">
                    <div class="flex flex-col items-center gap-4 p-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="size-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-primary flex items-center justify-center mb-2 shadow-sm">
                            <span class="material-symbols-outlined text-4xl">description</span>
                        </div>
                        <h3 class="font-bold text-xl text-[#0d141b] dark:text-white">Paperless</h3>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Mengurangi penggunaan kertas secara signifikan dengan digitalisasi penuh seluruh dokumen pengajuan.</p>
                    </div>
                    <div class="flex flex-col items-center gap-4 p-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="size-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-primary flex items-center justify-center mb-2 shadow-sm">
                            <span class="material-symbols-outlined text-4xl">speed</span>
                        </div>
                        <h3 class="font-bold text-xl text-[#0d141b] dark:text-white">Cepat & Tepat</h3>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Proses validasi real-time tanpa penundaan berkas fisik, mempercepat persetujuan cuti.</p>
                    </div>
                    <div class="flex flex-col items-center gap-4 p-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="size-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-primary flex items-center justify-center mb-2 shadow-sm">
                            <span class="material-symbols-outlined text-4xl">database</span>
                        </div>
                        <h3 class="font-bold text-xl text-[#0d141b] dark:text-white">Terpusat</h3>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Database cuti terintegrasi satu pintu, memudahkan rekapitulasi dan monitoring data.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="fitur" class="w-full bg-background-light dark:bg-[#101922] py-24 border-y border-[#e7edf3] dark:border-slate-800">
            <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
                <div class="flex flex-col gap-4 mb-16 text-center md:text-left">
                    <h2 class="text-[#0d141b] dark:text-white text-3xl md:text-4xl font-bold leading-tight">Fitur Unggulan</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl">Layanan lengkap untuk kebutuhan administrasi cuti Anda.</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Feature 1 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">devices</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">devices</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Pengajuan Online</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Ajukan permohonan cuti kapan saja dan di mana saja melalui perangkat desktop maupun mobile.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">verified_user</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">verified_user</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Approval Cepat</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Sistem validasi otomatis yang diteruskan kepada atasan langsung, mempercepat proses persetujuan.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">visibility</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">visibility</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Monitoring Kuota</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Pantau sisa kuota cuti tahunan dan status pengajuan secara real-time dan transparan.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 4 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">lock</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">lock</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Keamanan Tinggi</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Data kepegawaian dilindungi dengan enkripsi standar pemerintah dan autentikasi aman.</p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">analytics</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">analytics</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Rekapitulasi Otomatis</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Laporan cuti pegawai dapat digenerate secara otomatis untuk kebutuhan administrasi.</p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group flex flex-col gap-5 p-8 bg-white dark:bg-[#1a2632] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-125 duration-500">
                            <span class="material-symbols-outlined text-6xl">notifications_active</span>
                        </div>
                        <div class="size-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-3xl">notifications_active</span>
                        </div>
                        <div>
                            <h3 class="text-[#0d141b] dark:text-white text-lg font-bold mb-3">Notifikasi Real-time</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Dapatkan pembaruan status pengajuan Anda secara langsung melalui sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Workflow / Steps Section -->
        <section class="w-full bg-white dark:bg-slate-900 py-24" id="panduan">
            <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
                <div class="text-center mb-20">
                    <span class="text-primary font-bold tracking-wider uppercase text-sm mb-3">Panduan Penggunaan</span>
                    <h2 class="text-[#0d141b] dark:text-white text-3xl md:text-4xl font-bold mb-4">Alur Pengajuan Cuti</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-lg">Proses mudah dalam 4 langkah sederhana.</p>
                </div>
                <div class="relative grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Connector Line (Desktop) -->
                    <div class="hidden md:block absolute top-[2.25rem] left-0 w-full h-1 bg-slate-100 dark:bg-slate-800 -z-0"></div>
                    
                    <!-- Step 1 -->
                    <div class="relative flex flex-col items-center text-center z-10 group">
                        <div class="size-20 rounded-full bg-white dark:bg-slate-900 border-[6px] border-slate-100 dark:border-slate-800 group-hover:border-primary transition-colors flex items-center justify-center mb-8 shadow-sm group-hover:scale-110 duration-300">
                            <span class="text-2xl font-bold text-slate-400 group-hover:text-primary">1</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-3">Login Pegawai</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed px-4">Masuk ke sistem menggunakan NIP atau nama dan password yang telah terdaftar.</p>
                    </div>
                    <!-- Step 2 -->
                    <div class="relative flex flex-col items-center text-center z-10 group">
                        <div class="size-20 rounded-full bg-white dark:bg-slate-900 border-[6px] border-slate-100 dark:border-slate-800 group-hover:border-primary transition-colors flex items-center justify-center mb-8 shadow-sm group-hover:scale-110 duration-300">
                            <span class="text-2xl font-bold text-slate-400 group-hover:text-primary">2</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-3">Isi Formulir</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed px-4">Pilih jenis cuti, tentukan tanggal, dan alasan pengajuan cuti Anda.</p>
                    </div>
                    <!-- Step 3 -->
                    <div class="relative flex flex-col items-center text-center z-10 group">
                        <div class="size-20 rounded-full bg-white dark:bg-slate-900 border-[6px] border-slate-100 dark:border-slate-800 group-hover:border-primary transition-colors flex items-center justify-center mb-8 shadow-sm group-hover:scale-110 duration-300">
                            <span class="text-2xl font-bold text-slate-400 group-hover:text-primary">3</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-3">Validasi Atasan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed px-4">Pengajuan akan diperiksa dan divalidasi oleh atasan berwenang.</p>
                    </div>
                    <!-- Step 4 -->
                    <div class="relative flex flex-col items-center text-center z-10 group">
                        <div class="size-20 rounded-full bg-white dark:bg-slate-900 border-[6px] border-slate-100 dark:border-slate-800 group-hover:border-primary transition-colors flex items-center justify-center mb-8 shadow-sm group-hover:scale-110 duration-300">
                            <span class="text-2xl font-bold text-slate-400 group-hover:text-primary">4</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-3">Selesai</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed px-4">Pegawai dapat melihat hasil pengajuan cuti.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Banner -->
        <section class="w-full bg-slate-50 dark:bg-[#15202b] py-16 border-y border-[#e7edf3] dark:border-slate-800">
            <div class="max-w-[1280px] mx-auto px-4 lg:px-10 flex flex-col md:flex-row items-center justify-between gap-10">
                <div class="flex items-start gap-6 max-w-[700px]">
                    <div class="p-4 bg-blue-100 dark:bg-blue-900/30 rounded-2xl text-primary shrink-0">
                        <span class="material-symbols-outlined text-4xl">lock</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-[#0d141b] dark:text-white mb-2">Akses Terbatas & Aman</h3>
                        <p class="text-base text-slate-600 dark:text-slate-400 leading-relaxed">
                            Sistem ini hanya diperuntukkan bagi pegawai internal Dinas Komunikasi dan Informatika. Seluruh data dilindungi dengan protokol keamanan standar pemerintah.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400 font-mono shadow-sm">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    System Operational: Normal
                </div>
            </div>
        </section>
        
        <!-- Closing CTA -->
        <section class="w-full py-24 px-4 bg-white dark:bg-slate-900">
            <div class="max-w-[1000px] mx-auto bg-gradient-to-br from-[#003366] to-[#004b93] rounded-3xl p-10 md:p-20 text-center text-white shadow-2xl relative overflow-hidden group">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 p-20 opacity-5 transform translate-x-1/3 -translate-y-1/3 transition-transform duration-700 group-hover:scale-110">
                    <span class="material-symbols-outlined text-[400px]">description</span>
                </div>
                
                <div class="relative z-10 flex flex-col items-center gap-8">
                    <h2 class="text-3xl md:text-5xl font-bold tracking-tight leading-tight">Siap Mengajukan Cuti?</h2>
                    <p class="text-blue-100 text-lg md:text-xl max-w-[600px] leading-relaxed">
                        Pastikan Anda telah berkoordinasi dengan tim kerja Anda sebelum mengajukan permohonan cuti.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mt-4 w-full justify-center">
                         <a href="{{ route('login') }}" class="flex items-center justify-center gap-3 h-14 px-10 rounded-xl bg-white text-[#003366] text-lg font-bold hover:bg-slate-100 transition-colors shadow-lg hover:shadow-xl hover:-translate-y-1">
                            <span>Masuk ke Sistem</span>
                            <span class="material-symbols-outlined">login</span>
                        </a>
                    </div>
                    <p class="text-sm text-blue-200/80 mt-4">Butuh bantuan login? <a class="underline hover:text-white transition-colors" href="#">Hubungi Admin</a></p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="w-full bg-[#0d141b] text-slate-400 py-16 border-t border-slate-800" id="kontak">
            <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 lg:gap-16 mb-16">
                    <div class="col-span-1 md:col-span-2 flex flex-col gap-6">
                        <div class="flex items-center gap-4 text-white">
                            <div class="h-10 w-auto bg-white rounded flex items-center justify-center p-0.5">
                                <img src="{{ asset('image/diskominfobjb.jpg') }}" class="h-full w-auto object-contain rounded-sm" alt="Logo">
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-xl tracking-tight leading-none">SIAP CUTI</span>
                                <span class="text-xs text-slate-500 uppercase tracking-wider mt-1">Diskominfo Banjarbaru</span>
                            </div>
                        </div>
                        <p class="text-base leading-relaxed max-w-[400px]">
                            Sistem Informasi Manajemen Kepegawaian (SIMPEG) Sub-modul Cuti untuk lingkungan Dinas Komunikasi dan Informatika Kota Banjarbaru.
                        </p>
                    </div>
                    <div class="flex flex-col gap-6">
                        <h4 class="text-white font-bold text-lg">Tautan Cepat</h4>
                        <ul class="flex flex-col gap-3 text-sm">
                            <li><a class="hover:text-primary transition-colors flex items-center gap-2" href="#beranda"><span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Beranda</a></li>
                            <li><a class="hover:text-primary transition-colors flex items-center gap-2" href="#panduan"><span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Panduan Pengguna</a></li>
                            <li><a class="hover:text-primary transition-colors flex items-center gap-2" href="https://banjarbarukota.go.id" target="_blank"><span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Pemko Banjarbaru</a></li>
                            <li><a class="hover:text-primary transition-colors flex items-center gap-2" href="https://diskominfo.banjarbarukota.go.id" target="_blank"><span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Portal Diskominfo</a></li>
                        </ul>
                    </div>
                    <div class="flex flex-col gap-6">
                        <h4 class="text-white font-bold text-lg">Kontak</h4>
                        <ul class="flex flex-col gap-4 text-sm">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-xl pt-0.5 text-primary">location_on</span>
                                <span>Jl. Pangeran Suriansyah No. 5, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru 70711</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-xl text-primary">call</span>
                                <span>(0511) 4772022</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-xl text-primary">mail</span>
                                <span>diskominfo@banjarbarukota.go.id</span>
                            </li>
                        </ul>
                        <!-- Google Maps Embed -->
                        <div class="rounded-xl overflow-hidden shadow-lg border border-slate-700/50 h-48 w-full">
                            <iframe 
                                title="Lokasi Diskominfo Banjarbaru"
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                loading="lazy" 
                                allowfullscreen
                                src="https://maps.google.com/maps?q=-3.4403273915116355,114.83246726484806&z=15&output=embed">
                            </iframe>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                    <p>© {{ date('Y') }} Dinas Komunikasi dan Informatika Kota Banjarbaru.</p>
                    <p class="opacity-60">Hak Cipta Dilindungi Undang-Undang</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>