<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Reset Password - Siap Cuti</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer></script>
    
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
        <div class="absolute inset-0 z-0 h-full w-full bg-cover bg-center" style="background-image: url('{{ asset('image/diskominfo.jpg') }}');"></div>
        <div class="absolute inset-0 z-10 bg-primary/50 mix-blend-multiply"></div>
        <div class="relative z-20 flex flex-col items-center justify-center max-w-lg">
            <div class="mb-8 flex h-20 w-50 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                <img src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo" class="h-16 w-25 object-contain rounded-full">
            </div>
            <h1 class="mb-4 text-5xl font-black tracking-tight text-white">Siap Cuti</h1>
            <p class="text-lg font-medium text-blue-100 leading-relaxed">
                Sistem Informasi Pengajuan Cuti Pegawai<br/>
                Diskominfo Kota Banjarbaru
            </p>
        </div>
        <div class="absolute bottom-10 z-20 text-sm text-blue-200">
            © {{ date('Y') }} Pemerintah Kota Banjarbaru
        </div>
    </div>
    
    <!-- Right Side: Form -->
    <div class="flex w-full flex-col justify-center bg-white dark:bg-background-dark px-4 py-12 sm:px-6 lg:w-1/2 lg:px-20 xl:px-24">
        <div class="mx-auto w-full max-w-[480px]">
            <!-- Mobile Branding -->
            <div class="mb-10 lg:hidden text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary text-white">
                     <img src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo" class="h-12 w-12 object-contain rounded-full">
                </div>
                <h2 class="text-2xl font-black text-[#0d141b] dark:text-white">Siap Cuti</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Diskominfo Kota Banjarbaru</p>
            </div>
            
            <!-- Form Header -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <span class="material-symbols-outlined text-[28px]">password</span>
                    </div>
                    <h2 class="text-[28px] font-bold leading-tight tracking-tight text-[#0d141b] dark:text-white">
                        Buat Password Baru
                    </h2>
                </div>
                <p class="text-base text-slate-500 dark:text-slate-400">
                    Silakan buat password baru untuk akun Anda. Password minimal 8 karakter.
                </p>
            </div>
            
            <!-- Alerts -->
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
                    <div class="text-sm text-red-700">
                        <p class="font-bold">Terjadi Kesalahan</p>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('password.update') }}" class="flex flex-col gap-6" method="POST" x-data="{ showPassword: false, showConfirm: false }">
                @csrf
                <input type="hidden" name="telepon" value="{{ $telepon ?? '' }}">
                <input type="hidden" name="reset_token" value="{{ $reset_token ?? '' }}">
                
                <!-- New Password Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium leading-6 text-[#0d141b] dark:text-white" for="password">
                        Password Baru
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                        </div>
                        <input class="block w-full rounded-xl border-0 py-4 pl-11 pr-12 text-[#0d141b] ring-1 ring-inset ring-[#cfdbe7] placeholder:text-[#4c739a] focus:ring-2 focus:ring-inset focus:ring-primary sm:text-base sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white dark:placeholder:text-slate-500" 
                            id="password" 
                            name="password" 
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan password baru (min 8 karakter)" 
                            required
                            minlength="8"
                            autofocus>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                             @click="showPassword = !showPassword">
                            <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </div>
                    </div>
                </div>
                
                <!-- Confirm Password Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium leading-6 text-[#0d141b] dark:text-white" for="password_confirmation">
                        Konfirmasi Password
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">lock_clock</span>
                        </div>
                        <input class="block w-full rounded-xl border-0 py-4 pl-11 pr-12 text-[#0d141b] ring-1 ring-inset ring-[#cfdbe7] placeholder:text-[#4c739a] focus:ring-2 focus:ring-inset focus:ring-primary sm:text-base sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white dark:placeholder:text-slate-500" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            :type="showConfirm ? 'text' : 'password'"
                            placeholder="Ulangi password baru" 
                            required
                            minlength="8">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                             @click="showConfirm = !showConfirm">
                            <span class="material-symbols-outlined text-[20px]" x-text="showConfirm ? 'visibility_off' : 'visibility'">visibility</span>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-8 py-4 text-base font-bold text-white shadow-sm hover:bg-blue-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors" type="submit">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    Simpan Password Baru
                </button>
            </form>
            
            <!-- Footer -->
            <div class="mt-10 text-center">
                <a class="group inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-primary dark:text-slate-400 dark:hover:text-primary" href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1">
                        arrow_back
                    </span>
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
