<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Verifikasi OTP - Siap Cuti</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
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
    <style>
        /* OTP Input Styling */
        .otp-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        @media (min-width: 640px) {
            .otp-input {
                width: 3.5rem;
                height: 4rem;
                font-size: 1.75rem;
            }
        }
    </style>
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
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-600">
                        <span class="material-symbols-outlined text-[28px]">verified</span>
                    </div>
                    <h2 class="text-[28px] font-bold leading-tight tracking-tight text-[#0d141b] dark:text-white">
                        Verifikasi OTP
                    </h2>
                </div>
                <p class="text-base text-slate-500 dark:text-slate-400">
                    Masukkan kode 6 digit yang telah dikirim ke WhatsApp 
                    <span class="font-semibold text-[#0d141b] dark:text-white">{{ $masked_phone ?? '********' }}</span>
                </p>
            </div>
            
            <!-- Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <span class="material-symbols-outlined text-green-600 mt-0.5">check_circle</span>
                    <div class="text-sm text-green-700">
                        <p class="font-bold">Berhasil</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
                    <div class="text-sm text-red-700">
                        <p class="font-bold">Terjadi Kesalahan</p>
                        <p>{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('password.verify.post') }}" class="flex flex-col gap-6" method="POST" id="otp-form">
                @csrf
                <input type="hidden" name="telepon" value="{{ $telepon ?? '' }}">
                <input type="hidden" name="otp" id="otp-hidden">
                
                <!-- OTP Input -->
                <div class="space-y-4">
                    <label class="block text-sm font-medium leading-6 text-[#0d141b] dark:text-white text-center">
                        Kode OTP
                    </label>
                    <div class="flex justify-center gap-2 sm:gap-3">
                        @for($i = 1; $i <= 6; $i++)
                        <input type="text" 
                            maxlength="1" 
                            class="otp-input rounded-xl border-0 ring-1 ring-inset ring-[#cfdbe7] focus:ring-2 focus:ring-inset focus:ring-primary dark:bg-slate-800 dark:ring-slate-700 dark:text-white"
                            data-index="{{ $i }}"
                            inputmode="numeric"
                            pattern="[0-9]"
                            autocomplete="one-time-code"
                            {{ $i === 1 ? 'autofocus' : '' }}>
                        @endfor
                    </div>
                    <p class="text-center text-sm text-slate-500">
                        Kode berlaku selama 15 menit
                    </p>
                </div>
                
                <!-- Submit Button -->
                <button class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-8 py-4 text-base font-bold text-white shadow-sm hover:bg-blue-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors" type="submit">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                    Verifikasi Kode
                </button>
            </form>
            
            <!-- Resend OTP -->
            <div class="mt-6 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Tidak menerima kode?</p>
                <form action="{{ route('password.resend') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="telepon" value="{{ $telepon ?? '' }}">
                    <button type="submit" class="text-sm font-semibold text-primary hover:text-blue-600 transition-colors">
                        Kirim Ulang OTP
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="mt-10 text-center">
                <a class="group inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-primary dark:text-slate-400 dark:hover:text-primary" href="{{ route('password.request') }}">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1">
                        arrow_back
                    </span>
                    Ganti Nomor
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otp-form');
    const hiddenInput = document.getElementById('otp-hidden');
    
    inputs.forEach((input, index) => {
        // Auto move to next input
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Only allow numbers
            if (!/^\d*$/.test(value)) {
                e.target.value = '';
                return;
            }
            
            if (value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            
            updateHiddenInput();
        });
        
        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
        
        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            
            pasteData.split('').forEach((char, i) => {
                if (inputs[i]) {
                    inputs[i].value = char;
                }
            });
            
            const lastFilledIndex = Math.min(pasteData.length - 1, inputs.length - 1);
            if (lastFilledIndex >= 0) {
                inputs[lastFilledIndex].focus();
            }
            
            updateHiddenInput();
        });
    });
    
    function updateHiddenInput() {
        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });
        hiddenInput.value = otp;
    }
    
    form.addEventListener('submit', function(e) {
        updateHiddenInput();
        if (hiddenInput.value.length !== 6) {
            e.preventDefault();
            alert('Mohon masukkan kode OTP 6 digit lengkap.');
        }
    });
});
</script>
</body>
</html>
