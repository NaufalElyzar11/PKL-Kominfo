{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIAP CUTI Diskominfo Banjarbaru</title>

    @vite('resources/css/app.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.1/cdn.min.js" defer></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-image:
            radial-gradient(circle at 1px 1px, rgba(0,0,0,0.04) 1px, transparent 0);
        background-size: 24px 24px;
    }
</style>

</head>

<body class="min-h-screen bg-gradient-to-br from-slate-200 via-slate-100 to-sky-200 flex items-center justify-center px-4 text-slate-800">

{{-- ========================================================= --}}
{{-- MODAL NOTIFIKASI --}}
{{-- ========================================================= --}}
@if(session('email_error') || session('password_error') || session('error') || session('success'))
<div 
    x-data="{ open: true }"
    x-show="open"
    x-init="setTimeout(() => open = false, 3000)"
    class="fixed inset-0 flex items-center justify-center bg-black/40 z-50"
    x-transition.opacity>

    <div class="bg-white p-6 rounded-xl shadow-xl w-80 text-center"
         x-transition.scale>

        @if(session('success'))
            <i class="ph-fill ph-check-circle text-green-600 text-5xl mb-3"></i>
        @else
            <i class="ph-fill ph-warning-circle text-red-600 text-5xl mb-3"></i>
        @endif

        <p class="text-slate-700 font-medium text-sm">
            {{ session('email_error') ?? session('password_error') ?? session('error') ?? session('success') }}
        </p>

        <button 
            class="mt-4 px-5 py-2 bg-sky-600 text-white rounded-lg text-sm font-semibold hover:bg-sky-700"
            @click="open = false">
            OK
        </button>
    </div>
</div>
@endif

{{-- ========================================================= --}}
{{-- LOGIN CARD --}}
{{-- ========================================================= --}}
<div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <img src="{{ asset('image/diskominfobjb.jpg') }}"
             class="h-16 mx-auto mb-4"
             alt="Diskominfo Banjarbaru">

        <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tight">
            SIAP CUTI
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Sistem Informasi Pengajuan Cuti Pegawai<br>
            <span class="font-semibold text-sky-600">Diskominfo Kota Banjarbaru</span>
        </p>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
        @csrf

        {{-- Nama Pegawai --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Nama Lengkap Pegawai
            </label>
            <div class="relative">
                <input type="text"
                    name="name" {{-- Nama field diubah menjadi 'name' --}}
                    value="{{ old('name') }}"
                    required
                    autofocus
                    placeholder="Masukkan Nama Lengkap"
                    class="w-full px-4 py-3 pl-11 rounded-lg border border-slate-300
                            focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

                <span class="absolute inset-y-0 left-3 flex items-center text-sky-500">
                    <i class="ph ph-user text-lg"></i>
                </span>
            </div>
        </div>

        {{-- Password --}}
        <div x-data="{ show: false }">
            <label class="block text-sm font-semibold text-slate-700 mb-1">
                Kata Sandi
            </label>

            <div class="relative">
                <input :type="show ? 'text' : 'password'"
                       name="password"
                       required
                       placeholder="••••••••"
                       class="w-full px-4 py-3 pl-11 pr-12 rounded-lg border border-slate-300
                              focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

                <span class="absolute inset-y-0 left-3 flex items-center text-sky-500">
                    <i class="ph ph-lock-key text-lg"></i>
                </span>

                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-sky-600">
                    <i x-show="!show" class="ph ph-eye text-xl"></i>
                    <i x-show="show" class="ph ph-eye-slash text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-3 bg-sky-600 hover:bg-sky-700
                       text-white font-bold rounded-lg shadow-md
                       transition-all tracking-wide">
            Masuk ke Sistem
        </button>
    </form>

    {{-- Footer --}}
    <div class="mt-6 text-center">
        <a href="{{ url('/') }}"
           class="text-sm text-slate-500 hover:text-sky-600 flex items-center justify-center gap-1">
            <i class="ph ph-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

</div>

</body>
</html>
