<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }" x-cloak>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        [x-cloak] { display: none; }
        .smooth-transition { transition: all 0.35s ease-in-out; }
        .active-shadow { box-shadow: inset 3px 0 0 #008fd3; }
        .icon-color-dashboard { color: #007bff; }
        .icon-color-users { color: #00b894; }
        .icon-color-cuti { color: #e17055; }
        .icon-color-profile { color: #6c63ff; }
    </style>
</head>

<body class="bg-[#e6f5fb] text-gray-800 font-sans text-[14px]">
<div class="flex min-h-screen relative">

    {{-- === SIDEBAR ADMIN === --}}
    <aside class="flex flex-col justify-between fixed left-0 top-0 bottom-0 bg-[#f9f9f9] border-r border-gray-200 shadow-sm smooth-transition z-50"
           :class="sidebarOpen ? 'w-56' : 'w-16'">

        {{-- Logo + Toggle --}}
        <div>
            <div class="flex items-center justify-between px-3 py-3 border-b border-gray-100">
                <img x-show="sidebarOpen" x-transition src="{{ asset('image/diskominfobjb.jpg') }}" alt="Logo"
                     class="h-9 w-auto rounded" x-cloak>

                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-1 rounded-lg hover:bg-gray-200 smooth-transition">
                    <i class="fa-solid fa-chevron-left text-gray-600 text-sm"
                       :class="sidebarOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            {{-- Menu Navigasi --}}
            <nav class="mt-3 space-y-1 text-[14px]">

                @php
                    $menus = [
                        ['route' => 'admin.dashboard', 'icon' => 'fa-solid fa-chart-line', 'label' => 'Dashboard', 'color' => 'icon-color-dashboard'],
                        ['route' => 'admin.pegawai.index', 'icon' => 'fa-solid fa-users', 'label' => 'Data Pegawai', 'color' => 'icon-color-users'],
                        ['route' => 'admin.cuti.index', 'icon' => 'fa-solid fa-file-signature', 'label' => 'Daftar Cuti', 'color' => 'icon-color-cuti'],
                        ['route' => 'admin.profile.index', 'icon' => 'fa-solid fa-user-circle', 'label' => 'Profil Saya', 'color' => 'icon-color-profile'],
                    ];
                @endphp

                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center px-3 py-2 rounded-md hover:bg-[#e6f5fb] smooth-transition relative group
                              {{ request()->routeIs($menu['route']) ? 'bg-[#e6f5fb] font-semibold text-[#008fd3] active-shadow' : 'text-gray-700' }}">

                        <i class="{{ $menu['icon'] }} text-[16px] w-5 {{ $menu['color'] }}"></i>

                        <span x-show="sidebarOpen" x-transition x-cloak class="ml-2">{{ $menu['label'] }}</span>

                        {{-- Tooltip --}}
                        <span x-show="!sidebarOpen"
                              x-transition
                              class="absolute left-16 bg-gray-900 text-white text-xs rounded-md px-2 py-1 opacity-0 
                                     group-hover:opacity-100 smooth-transition whitespace-nowrap">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach

            </nav>
        </div>

        {{-- === Profil Admin === --}}
      <div class="p-3 border-t border-gray-200">
    @php
        $admin = Auth::user();
        // Ambil nama dari relasi pegawai kalau ada, jika tidak ambil dari users.name
        $namaAdmin = $admin->pegawai->nama ?? $admin->name ?? 'Admin';
    @endphp

    <a href="{{ route('admin.profile.index') }}"
       class="flex items-center space-x-2 px-2 py-1 rounded-md hover:bg-[#e6f5fb] smooth-transition">

        {{-- Avatar: huruf pertama dari nama --}}
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-[#008fd3] text-white text-sm font-semibold">
            {{ strtoupper(substr($namaAdmin, 0, 1)) }}
        </div>

        {{-- Nama Admin + Jabatan --}}
        <div x-show="sidebarOpen" x-transition x-cloak>
            <p class="text-[13px] font-semibold">{{ $namaAdmin }}</p>
            <p class="text-[11px] text-gray-500">Admin</p>
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

            <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 text-[13px] px-4 py-1.5 text-red-600 border border-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </header>

        {{-- Konten --}}
        <main class="p-6 max-w-6xl mx-auto">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
