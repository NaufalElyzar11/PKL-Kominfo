<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">
    <title>Login Siap Cuti</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#0d141b] antialiased">
<div class="flex min-h-screen w-full flex-row overflow-hidden">
    <!-- Left Side: Image & Branding -->
    <div class="hidden w-1/2 relative lg:flex flex-col items-center justify-center p-12 text-center text-white">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0 h-full w-full bg-cover bg-center" data-alt="Modern government office building exterior with glass facade" style="background-image: url('{{ asset('image/diskominfo.jpg') }}');">
        </div>
        <!-- Blue Overlay -->
        <div class="absolute inset-0 z-10 bg-primary/50 mix-blend-multiply"></div>
        <!-- Content -->
        <div class="relative z-20 flex flex-col items-center justify-center max-w-lg">
            <div class="mb-8 flex h-20 w-50 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                {{-- Menggunakan logo dari sistem jika ada, atau ikon default --}}
                <img src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo" class="h-16 w-25 object-contain rounded-full">
            </div>
            <h1 class="mb-4 text-5xl font-black tracking-tight text-white">
                Siap Cuti
            </h1>
            <p class="text-lg font-medium text-blue-100 leading-relaxed">
                Sistem Informasi Pengajuan Cuti Pegawai<br/>
                Diskominfo Kota Banjarbaru
            </p>
        </div>
        <div class="absolute bottom-10 z-20 text-sm text-blue-200">
            © {{ date('Y') }} Pemerintah Kota Banjarbaru
        </div>
    </div>
    
    <!-- Right Side: Login Form -->
    <div class="flex w-full flex-col justify-center bg-white dark:bg-background-dark px-4 py-12 sm:px-6 lg:w-1/2 lg:px-20 xl:px-24">
        <div class="mx-auto w-full max-w-[480px]">
            <!-- Mobile Branding (Visible only on small screens) -->
            <div class="mb-10 lg:hidden text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary text-white">
                     <img src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo" class="h-12 w-12 object-contain rounded-full">
                </div>
                <h2 class="text-2xl font-black text-[#0d141b] dark:text-white">Siap Cuti</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Diskominfo Kota Banjarbaru</p>
            </div>
            
            <!-- Form Header -->
            <div class="mb-10">
                <h2 class="text-[28px] font-bold leading-tight tracking-tight text-[#0d141b] dark:text-white mb-2">
                    Selamat Datang Kembali
                </h2>
                <p class="text-base text-slate-500 dark:text-slate-400">
                    Silakan masuk ke akun Anda untuk melanjutkan.
                </p>
            </div>
            
            <!-- Alerts / Notifications -->
            @if(session('error') || $errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
                    <div class="text-sm text-red-700">
                        <p class="font-bold">Login Gagal</p>
                        <p>{{ session('error') ?? $errors->first() }}</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <span class="material-symbols-outlined text-green-600 mt-0.5">check_circle</span>
                    <div class="text-sm text-green-700">
                        <p class="font-bold">Berhasil</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.post') }}" class="flex flex-col gap-6" method="POST">
                @csrf
                
                <!-- Login Identifier Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium leading-6 text-[#0d141b] dark:text-white" for="login_identifier">
                        Nama Lengkap / NIP
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">
                                person
                            </span>
                        </div>
                        <input class="block w-full rounded-xl border-0 py-4 pl-11 pr-4 text-[#0d141b] ring-1 ring-inset ring-[#cfdbe7] placeholder:text-[#4c739a] focus:ring-2 focus:ring-inset focus:ring-primary sm:text-base sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white dark:placeholder:text-slate-500" 
                            id="login_identifier" 
                            name="login_identifier" 
                            value="{{ old('login_identifier') }}"
                            placeholder="Masukkan Nama Lengkap atau NIP" 
                            type="text" 
                            required 
                            autofocus>
                    </div>
                </div>
                
                <!-- Password Input -->
                <div class="space-y-2" x-data="{ show: false }">
                    <label class="block text-sm font-medium leading-6 text-[#0d141b] dark:text-white" for="password">
                        Kata Sandi
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">
                                lock
                            </span>
                        </div>
                        <input class="block w-full rounded-xl border-0 py-4 pl-11 pr-12 text-[#0d141b] ring-1 ring-inset ring-[#cfdbe7] placeholder:text-[#4c739a] focus:ring-2 focus:ring-inset focus:ring-primary sm:text-base sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white dark:placeholder:text-slate-500" 
                            id="password" 
                            name="password" 
                            :type="show ? 'text' : 'password'"
                            placeholder="Masukkan kata sandi" 
                            required>
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                             @click="show = !show">
                            <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">
                                visibility
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <a class="text-sm font-medium text-primary hover:text-blue-600" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button class="mt-2 flex w-full items-center justify-center rounded-xl bg-primary px-8 py-4 text-base font-bold text-white shadow-sm hover:bg-blue-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors" type="submit">
                    Masuk
                </button>
            </form>
            
            <!-- Footer / Secondary Action -->
            <div class="mt-10 text-center">
                <a class="group inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-primary dark:text-slate-400 dark:hover:text-primary" href="{{ url('/') }}">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1">
                        arrow_back
                    </span>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Alpine.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer></script>
</body>
</html>