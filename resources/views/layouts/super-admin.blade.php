<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }" x-cloak>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin')</title>

    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        [x-cloak] { display: none; }
        .smooth-transition { transition: all 0.35s ease-in-out; }
        .active-shadow { box-shadow: inset 3px 0 0 #008fd3; }
        .menu-icon { width: 20px; text-align: center; }

        /* Icon Colors */
        .icon-dashboard { color: #007bff; }
        .icon-setting { color: #00b894; }
        .icon-log { color: #6c5ce7; }
        .icon-profile { color: #e17055; }
    </style>
</head>

<body class="bg-[#e6f5fb] text-gray-800 font-sans text-[14px]">
<div class="flex min-h-screen relative">

    {{-- === SIDEBAR === --}}
    <aside class="flex flex-col justify-between fixed left-0 top-0 bottom-0 bg-[#f9f9f9] 
                 border-r border-gray-200 shadow-sm smooth-transition z-50"
           :class="sidebarOpen ? 'w-56' : 'w-16'">

        {{-- Logo + Toggle --}}
        <div>
            <div class="flex items-center justify-between px-3 py-3 border-b border-gray-100">
                <img x-show="sidebarOpen" x-transition src="{{ asset('image/diskominfobjb.jpg') }}" 
                     alt="Logo" class="h-9 w-auto rounded" x-cloak>

                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-1 rounded-lg hover:bg-gray-200 smooth-transition">
                    <i class="fa-solid fa-chevron-left text-gray-600 text-sm"
                       :class="sidebarOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            {{-- Menu --}}
            @php
                $menus = [
                    ['route' => 'super.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                    ['route' => 'super.pengaturan.index', 'icon' => 'fa-gear icon-setting', 'label' => 'Pengaturan'],
                    ['route' => 'super.profile.index', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                ];
            @endphp

            <nav class="mt-3 space-y-1 text-[14px]">
                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center px-3 py-2 rounded-md hover:bg-[#e6f5fb] smooth-transition group
                              {{ request()->routeIs($menu['route']) ? 'bg-[#e6f5fb] active-shadow font-semibold text-[#008fd3]' : 'text-gray-700' }}">

                        <i class="fa-solid {{ $menu['icon'] }} text-[16px] menu-icon"></i>

                        <span x-show="sidebarOpen" x-transition x-cloak class="ml-2">{{ $menu['label'] }}</span>

                        {{-- Tooltip --}}
                        <span x-show="!sidebarOpen"
                              class="absolute left-16 bg-gray-900 text-white text-xs rounded-md px-2 py-1 opacity-0
                                     group-hover:opacity-100 smooth-transition whitespace-nowrap">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

     {{-- === Profil Super Admin === --}}
<div class="p-3 border-t border-gray-200">
    @php
        $user = Auth::user();
        $pegawai = $user->pegawai; // pastikan relasi sudah benar
        $nama = $pegawai->nama ?? 'Super Admin';
    @endphp

    <a href="{{ route('super.profile.index') }}"
       class="flex items-center px-2 py-1 rounded-md hover:bg-[#e6f5fb] smooth-transition">

        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-[#008fd3] text-white text-sm font-bold">
            {{ strtoupper(substr($nama, 0, 1)) }}
        </div>

        <div x-show="sidebarOpen" x-transition x-cloak class="ml-2">
            <p class="text-[13px] font-semibold">{{ $nama }}</p>
            <p class="text-[11px] text-gray-500">Super Admin</p>
        </div>
    </a>
</div>
    </aside>

    {{-- === MAIN CONTENT === --}}
    <div class="flex-1 smooth-transition" :class="sidebarOpen ? 'ml-56' : 'ml-16'">

        {{-- Header --}}
        <header class="sticky top-0 z-20 bg-[#f4f4f4] border-b border-gray-300 
                       flex justify-between items-center px-6 py-3 shadow-sm">
            <h1 class="text-[15px] font-semibold text-gray-800">@yield('title')</h1>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 text-[13px] px-4 py-1.5 text-red-600 
                               border border-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </header>

        <main class="p-6 max-w-6xl mx-auto">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
