<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }" x-cloak>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pegawai')</title>

    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome (Ikon Utama) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none; }
        .smooth-transition { transition: all 0.35s ease-in-out; }

        /* Warna Ikon di Sidebar */
        .icon-dashboard { color: #007bff; }
        .icon-cuti { color: #00b894; }
        .icon-profile { color: #e17055; }

        /* Garis Biru di Sidebar */
        .active-shadow {
            box-shadow: inset 3px 0 0 #008fd3;
            background: #e6f5fb;
        }

        .menu-icon {
            width: 20px;
            text-align: center;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-[#e6f5fb] text-gray-800 font-sans text-[14px]">
<div class="flex min-h-screen relative">

    {{-- === SIDEBAR === --}}
    <aside class="flex flex-col justify-between fixed left-0 top-0 bottom-0
                 bg-[#f9f9f9] border-r border-gray-200 shadow-sm smooth-transition z-50"
           :class="sidebarOpen ? 'w-56' : 'w-16'">

        {{-- Logo + Button Toggle --}}
        <div>
            <div class="flex items-center justify-between px-3 py-3 border-b border-gray-100">
                <img x-show="sidebarOpen" x-transition src="{{ asset('image/diskominfobjb.jpg') }}"
                     class="h-9 w-auto rounded" x-cloak>

                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-1 rounded-lg hover:bg-gray-200 smooth-transition">
                    <i class="fa-solid fa-chevron-left text-gray-600 text-sm"
                       :class="sidebarOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            {{-- Menu --}}
            @php
                $menus = [
                    ['route' => 'pegawai.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                    ['route' => 'pegawai.cuti.index', 'icon' => 'fa-file-signature icon-cuti', 'label' => 'Pengajuan Cuti'],
                    ['route' => 'pegawai.profile.show', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                ];
            @endphp

            <nav class="mt-3 space-y-1 text-[14px]">
                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center px-3 py-2 rounded-md hover:bg-[#e6f5fb] group smooth-transition relative
                        {{ request()->routeIs($menu['route']) ? 'font-semibold text-[#008fd3] active-shadow' : 'text-gray-700' }}">
                        
                        <i class="fa-solid {{ $menu['icon'] }} text-[16px] menu-icon"></i>

                        <span x-show="sidebarOpen" x-transition x-cloak class="ml-3">{{ $menu['label'] }}</span>

                        {{-- Tooltip saat sidebar tertutup --}}
                        <span x-show="!sidebarOpen"
                              class="absolute left-16 bg-gray-900 text-white text-xs rounded-md px-2 py-1 opacity-0 
                                     group-hover:opacity-100 smooth-transition whitespace-nowrap z-50">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- === Profil Pegawai === --}}
        <div class="p-3 border-t border-gray-200">
            @php
                $pegawai = Auth::user()->pegawai;
                $namaPegawai = $pegawai->nama ?? 'Pegawai';
            @endphp

            <a href="{{ route('pegawai.profile.show') }}"
               class="flex items-center px-2 py-1 rounded-md hover:bg-[#e6f5fb] smooth-transition">

                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-[#008fd3] text-white text-sm font-semibold">
                    {{ strtoupper(substr($namaPegawai, 0, 1)) }}
                </div>

                <div x-show="sidebarOpen" x-transition x-cloak class="ml-3">
                    <p class="text-[13px] font-semibold">{{ $namaPegawai }}</p>
                    <p class="text-[11px] text-gray-500">Pegawai</p>
                </div>
            </a>
        </div>

    </aside>

    {{-- === MAIN CONTENT === --}}
    <div class="flex-1 smooth-transition" :class="sidebarOpen ? 'ml-56' : 'ml-16'">

        {{-- HEADER --}}
        <header class="sticky top-0 bg-[#f4f4f4] border-b border-gray-300
                       flex justify-between items-center px-6 py-3 shadow-sm z-20">
            <h1 class="text-[15px] font-semibold text-gray-800">@yield('title')</h1>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 text-[13px] px-4 py-1.5 text-red-600 
                               border border-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Keluar
                </button>
            </form>
        </header>

        {{-- CONTENT --}}
        <main class="p-6 max-w-6xl mx-auto">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')
</body>
</html>
