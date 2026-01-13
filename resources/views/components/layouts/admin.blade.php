{{-- resources/views/components/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true, darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dasbor Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="flex flex-col justify-between h-screen fixed left-0 top-0 bottom-0 z-50
                  bg-white dark:bg-gray-900 shadow-lg border-r border-gray-200 dark:border-gray-800
                  transition-all duration-500"
           :class="sidebarOpen ? 'w-56' : 'w-16'">

        <!-- Bagian Atas -->
        <div>
            <!-- Logo + Toggle -->
            <div class="flex items-center justify-between px-3 py-4">
                <div class="flex items-center space-x-2">
                    <span x-show="sidebarOpen" class="font-bold text-lg tracking-wide" x-transition>Dashboard</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <span class="material-icons transition-transform duration-500" 
                          :class="sidebarOpen ? 'rotate-180' : ''">chevron_left</span>
                </button>
            </div>

            <!-- Menu -->
            <nav class="mt-3 space-y-1">
                @php
                    $menus = [
                        ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'color' => 'text-blue-500', 'label' => 'Ringkasan'],
                        ['route' => 'admin.pegawai.index', 'icon' => 'group', 'color' => 'text-green-500', 'label' => 'Data Pegawai'],
                        ['route' => 'admin.cuti.index', 'icon' => 'event_note', 'color' => 'text-yellow-500', 'label' => 'Data Cuti'],
                        // ✅ Revisi di bawah ini — gunakan route yang benar
                        ['route' => 'admin.profile.show', 'icon' => 'account_circle', 'color' => 'text-purple-500', 'label' => 'Profil Saya'],
                    ];
                @endphp

                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}" 
                       class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition relative group">
                        <span class="material-icons {{ $menu['color'] }}">{{ $menu['icon'] }}</span>
                        <span x-show="sidebarOpen" x-transition class="ml-2 font-medium">{{ $menu['label'] }}</span>

                        <!-- Tooltip saat collapse -->
                        <span x-show="!sidebarOpen" 
                              class="absolute left-16 bg-gray-900 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition">
                          {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Bagian Bawah -->
        <div class="p-3 border-t border-gray-200 dark:border-gray-800 space-y-3">
            <!-- Profil User -->
            <div class="flex items-center space-x-2 px-1" x-show="sidebarOpen" x-transition>
                <img src="https://i.pravatar.cc/40" class="w-9 h-9 rounded-full border-2 border-green-500">
                <div>
                    <p class="text-sm font-semibold">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email ?? '-' }}</p>
                </div>
            </div>

            <!-- Tombol Keluar -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center justify-center px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    <span class="material-icons">logout</span>
                    <span x-show="sidebarOpen" class="ml-2 font-medium" x-transition>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div class="flex-1 ml-16 transition-all duration-500" :class="sidebarOpen ? 'ml-56' : 'ml-16'">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 p-4">
            <h1 class="text-xl font-semibold">{{ $title ?? 'Dasbor Admin' }}</h1>
        </header>

        <!-- Konten -->
        <main class="p-6">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
