{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Cuti Diskominfo</title>

    @vite('resources/css/app.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer></script>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="antialiased bg-gradient-to-br from-indigo-200 via-white to-blue-100 text-gray-800">

{{-- ========================================================= --}}
{{-- MODAL NOTIFIKASI --}}
{{-- ========================================================= --}}
@if(session('email_error') || session('password_error') || session('error') || session('success'))
<div 
    x-data="{ open: true }"
    x-show="open"
    x-init="setTimeout(() => open = false, 2500)"
    class="fixed inset-0 flex items-center justify-center bg-black/40 z-50"
    x-transition.opacity>
    
    <div class="bg-white p-6 rounded-xl shadow-xl w-80 text-center"
         x-transition.scale>

        {{-- IKON --}}
        @if(session('success'))
            <i class="ph ph-check-circle text-green-600 text-5xl mb-3"></i>
        @else
            <i class="ph ph-warning-circle text-red-600 text-5xl mb-3"></i>
        @endif

        {{-- PESAN --}}
        <p class="text-gray-700 font-medium text-sm">
            {{ session('email_error') ?? session('password_error') ?? session('error') ?? session('success') }}
        </p>

        {{-- Tombol OK --}}
        <button 
            class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700"
            @click="open = false">
            OK
        </button>
    </div>
</div>
@endif

<section class="min-h-screen flex items-center justify-center">

    {{-- Card Login --}}
    <div class="bg-white/90 backdrop-blur-xl border border-white/50 rounded-2xl shadow-xl w-full max-w-sm p-7">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Selamat Datang</h2>
            <p class="text-gray-500 text-xs">Sistem Cuti Tahunan Pegawai Diskominfo</p>
        </div>

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <input type="email" name="email" required autofocus
                           value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 pl-11 rounded-lg shadow-sm border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white"
                           placeholder="nama@email.com">
                    <span class="absolute inset-y-0 left-3 flex items-center text-indigo-500">
                        <i class="ph ph-envelope-simple text-lg"></i>
                    </span>
                </div>
            </div>

            {{-- Password --}}
<div x-data="{ show: false }">
    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>

    <div class="relative">
        <input :type="show ? 'text' : 'password'" 
               name="password" 
               required
               class="w-full px-4 py-2.5 pl-11 pr-12 rounded-lg shadow-sm border border-gray-300 
                      focus:ring-2 focus:ring-indigo-500 bg-white"
               placeholder="••••••••">

        {{-- Icon kiri --}}
        <span class="absolute inset-y-0 left-3 flex items-center text-indigo-500">
            <i class="ph ph-lock-key text-lg"></i>
        </span>

        {{-- Tombol Eye --}}
        <button type="button"
                @click="show = !show"
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-indigo-600">

            <i x-show="!show" class="ph ph-eye text-xl"></i>
            <i x-show="show" class="ph ph-eye-slash text-xl"></i>
        </button>
    </div>
</div>


            {{-- Tombol --}}
            <div>
                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600
                        text-white py-2.5 rounded-lg font-semibold shadow-md transition-all">
                    Masuk
                </button>
            </div>
        </form>

        {{-- Back --}}
        <div class="mt-5 text-center">
            <a href="{{ url('/') }}" class="text-xs text-gray-600 hover:text-indigo-600">
                <i class="ph ph-house"></i> Kembali ke Beranda
            </a>
        </div>

    </div>
</section>

</body>
</html>
