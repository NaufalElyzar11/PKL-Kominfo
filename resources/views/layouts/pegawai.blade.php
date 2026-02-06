<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }" x-cloak>
<head>
    {{-- 1. HAPUS SweetAlert dari sini (pindahkan semua ke bawah) --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Cuti')</title>

    <link rel="icon" href="{{ asset('image/diskominfobjb.jpg') }}" type="image/jpg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- 2. TAMBAHKAN Material Symbols secara global agar ikon arrow_back tidak rusak --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none; }
        .smooth-transition { transition: all 0.35s ease-in-out; }
        .icon-dashboard { color: #007bff; }
        .icon-cuti { color: #00b894; }
        .icon-profile { color: #e17055; }
        .active-shadow {
            box-shadow: inset 3px 0 0 #008fd3;
            background: #e6f5fb;
        }
        .menu-icon { width: 20px; text-align: center; }
    </style>

    @stack('styles')
</head>

<body class="bg-[#e6f5fb] text-gray-800 font-sans text-[14px]">
<div class="flex min-h-screen relative">

    {{-- SIDEBAR --}}
    <aside class="flex flex-col justify-between fixed left-0 top-0 bottom-0 bg-[#f9f9f9] border-r border-gray-200 shadow-sm smooth-transition z-50"
           :class="sidebarOpen ? 'w-56' : 'w-16'">

        <div>
            <div class="flex items-center justify-between px-3 py-3 border-b border-gray-100">
                <img x-show="sidebarOpen" x-transition src="{{ asset('image/diskominfobjb.jpg') }}" class="h-9 w-auto rounded" x-cloak>
                <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded-lg hover:bg-gray-200 smooth-transition">
                    <i class="fa-solid fa-chevron-left text-gray-600 text-sm" :class="sidebarOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            {{-- 3. PERBAIKAN LOGIKA MENU: Tambahkan role 'admin' --}}
            @php
                $user = Auth::user();
                $menus = [];

                if ($user->role === 'admin') {
                    $menus = [
                        ['route' => 'admin.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                        ['route' => 'admin.pegawai.index', 'icon' => 'fa-users icon-cuti', 'label' => 'Data Pegawai'],
                        ['route' => 'admin.cuti.index', 'icon' => 'fa-file-signature icon-cuti', 'label' => 'Data Cuti'],
                        ['route' => 'admin.profile.index', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                    ];
                } elseif ($user->role === 'atasan') {
                    $menus = [
                        ['route' => 'atasan.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                        ['route' => 'atasan.profile.show', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                    ];
                } elseif ($user->role === 'pejabat') {
                    $menus = [
                        ['route' => 'pejabat.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                        ['route' => 'pejabat.profile.show', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                    ];
                } else {
                    $menus = [
                        ['route' => 'pegawai.dashboard', 'icon' => 'fa-chart-line icon-dashboard', 'label' => 'Dashboard'],
                        ['route' => 'pegawai.cuti.index', 'icon' => 'fa-file-signature icon-cuti', 'label' => 'Pengajuan Cuti'],
                        ['route' => 'pegawai.profile.show', 'icon' => 'fa-user-circle icon-profile', 'label' => 'Profil Saya'],
                    ];
                }

                $profileRoute = match($user->role) {
                    'admin'   => 'admin.profile.index',
                    'atasan'  => 'atasan.profile.show',
                    'pejabat' => 'pejabat.profile.show',
                    default   => 'pegawai.profile.show',
                };
            @endphp

            <nav class="mt-3 space-y-1">
                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center px-3 py-2 rounded-md hover:bg-[#e6f5fb] group smooth-transition relative
                        {{ request()->routeIs($menu['route']) ? 'font-semibold text-[#008fd3] active-shadow' : 'text-gray-700' }}">
                        <i class="fa-solid {{ $menu['icon'] }} text-[16px] menu-icon"></i>
                        <span x-show="sidebarOpen" x-transition x-cloak class="ml-3">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="p-3 border-t border-gray-200">
            <a href="{{ route($profileRoute) }}" class="flex items-center px-2 py-1 rounded-md hover:bg-[#e6f5fb] smooth-transition">
                <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full overflow-hidden bg-[#008fd3] text-white text-sm font-semibold border border-gray-100">
                    @if($user->pegawai && $user->pegawai->foto)
                        <img src="{{ asset('storage/' . $user->pegawai->foto) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div x-show="sidebarOpen" x-transition x-cloak class="ml-3 min-w-0">
                    <p class="text-[13px] font-semibold truncate">{{ $user->name }}</p>
                    <p class="text-[11px] text-gray-500">{{ ucfirst($user->role) }}</p>
                </div>
            </a>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 smooth-transition" :class="sidebarOpen ? 'ml-56' : 'ml-16'">
        <header class="sticky top-0 bg-[#f4f4f4] border-b border-gray-300 flex justify-between items-center px-6 py-3 shadow-sm z-20">
            <h1 class="text-[15px] font-semibold text-gray-800">@yield('title')</h1>
            {{-- Ganti form logout lama Anda dengan ini --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>

            <button type="button" 
                    onclick="confirmLogout()"
                    class="flex items-center gap-2 px-4 py-2 text-[13px] font-bold text-rose-600 bg-rose-50 border border-rose-100 rounded-xl hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all duration-300 group shadow-sm">
                <i class="fa-solid fa-right-from-bracket group-hover:translate-x-1 transition-transform"></i>
                <span>Keluar</span>
            </button>
        </header>

        <main class="p-6 max-w-6xl mx-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- 4. POSISI TERBAIK UNTUK SCRIPT: Sebelum tutup body --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Pastikan session 'success' terpanggil dengan benar
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{!! session('success') !!}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: { popup: 'rounded-2xl' }
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>- {{ $error }}</li>@endforeach</ul>',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-2xl' }
        });
    @endif
</script>
@stack('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Yakin ingin keluar?',
            text: "Sesi Anda akan diakhiri dan Anda harus login kembali.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48', // Warna rose-600
            cancelButtonColor: '#64748b',  // Warna slate-500
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px',
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-gray-100'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Jalankan submit form jika user klik 'Ya'
                document.getElementById('logout-form').submit();
            }
        })
    }
</script>

</body>
</html>