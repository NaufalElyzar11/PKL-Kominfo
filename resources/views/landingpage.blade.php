{{-- resources/views/landingpage.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Cuti Diskominfo</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; transition: background 0.3s, color 0.3s; }
        .logo-container img { object-fit: contain; height: 48px; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    {{-- Navbar --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">

            {{-- Logo --}}
            <div class="logo-container">
                <img src="{{ asset('image/diskominfobjb.jpg') }}" 
                     alt="Diskominfo Logo" 
                     class="w-auto h-12">
            </div>

            {{-- Nav Menu --}}
            <nav class="hidden md:flex gap-6 font-medium">
                <a href="#beranda" class="hover:text-sky-500">Beranda</a>
                <a href="#tentang" class="hover:text-sky-500">Tentang Sistem</a>
                <a href="#fitur" class="hover:text-sky-500">Fitur</a>
            </nav>

            {{-- Login --}}
            <a href="{{ route('login') }}"
               class="hidden md:inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-blue-600 text-white px-5 py-2 rounded-lg shadow hover:scale-105 transition">
                <i class="ph ph-sign-in"></i> Login
            </a>

        </div>
    </header>

    {{-- Hero Section --}}
    <section id="beranda" class="pt-32 pb-32 bg-gradient-to-br from-sky-50 via-white to-blue-50 relative overflow-hidden">

        {{-- Efek lembut --}}
        <div class="absolute top-10 -left-20 w-72 h-72 bg-sky-300 rounded-full blur-3xl opacity-25 animate-pulse"></div>
        <div class="absolute bottom-10 -right-20 w-80 h-80 bg-blue-300 rounded-full blur-3xl opacity-25 animate-pulse"></div>

        <div class="max-w-7xl mx-auto grid md:grid-cols-2 items-center gap-12 px-6 relative z-10">

            {{-- Kiri --}}
            <div>
                <h1 class="text-4xl md:text-5xl font-bold leading-tight">
                    Sistem Cuti Tahunan <br>
                    <span class="text-sky-600">Pegawai Diskominfo</span>
                </h1>

                <p class="mt-4 text-lg text-gray-600 max-w-md">
                    Mudah, cepat, dan transparan dalam pengelolaan cuti pegawai.
                </p>

                <a href="{{ route('login') }}" 
                   class="mt-6 inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-blue-600 text-white px-8 py-3 rounded-lg shadow-lg hover:scale-105 transition">
                    <i class="ph ph-sign-in"></i> Masuk ke Sistem
                </a>
            </div>

            {{-- Kanan --}}
            <div class="flex justify-center">
                <img src="{{ asset('image/diskominfo.jpg') }}" 
                     alt="Foto Diskominfo"
                     class="w-[460px] md:w-[520px] rounded-3xl shadow-xl border border-gray-200 hover:scale-105 transition-transform duration-500 object-cover">
            </div>
        </div>
    </section>

    {{-- Tentang Sistem --}}
    <section id="tentang" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-6">Tentang Sistem</h2>

            <p class="text-gray-600 max-w-2xl mx-auto mb-12">
                Sistem ini dirancang khusus untuk internal Diskominfo agar proses pengajuan, validasi, 
                dan rekapitulasi cuti pegawai lebih efisien serta terintegrasi.
            </p>

            <div class="grid md:grid-cols-3 gap-8">

                @php
                    $cards = [
                        ['icon' => 'ph-user-circle', 'title' => 'Pegawai', 'desc' => 'Ajukan cuti dengan mudah dan pantau status pengajuan.'],
                        ['icon' => 'ph-seal-check', 'title' => 'Kepala Dinas', 'desc' => 'Validasi dan setujui pengajuan cuti secara digital.'],
                        ['icon' => 'ph-database', 'title' => 'Admin', 'desc' => 'Kelola data dan buat laporan cuti yang komprehensif.'],
                    ];
                @endphp

                @foreach($cards as $c)
                    <div class="p-6 bg-gradient-to-br from-sky-50 to-white rounded-2xl shadow hover:shadow-xl hover:-translate-y-2 transition">
                        <i class="ph {{ $c['icon'] }} text-5xl text-sky-600 mb-3"></i>
                        <h3 class="font-semibold text-lg mb-2">{{ $c['title'] }}</h3>
                        <p class="text-gray-600 text-sm">{{ $c['desc'] }}</p>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-6 text-center">

            <h2 class="text-3xl font-bold mb-12">Fitur Utama</h2>

            <div class="grid md:grid-cols-4 gap-6">

                @php
                    $fitur = [
                        ['icon' => 'ph-file-text', 'color' => 'text-sky-600', 'title' => 'Pengajuan Cuti Online', 'desc' => 'Form cuti lengkap dengan detail informasi yang mudah digunakan.'],
                        ['icon' => 'ph-check-circle', 'color' => 'text-green-600', 'title' => 'Persetujuan Cepat', 'desc' => 'Kepala Dinas dapat memvalidasi cuti langsung dari sistem.'],
                        ['icon' => 'ph-chart-line', 'color' => 'text-purple-600', 'title' => 'Monitoring & Rekap', 'desc' => 'Admin dapat melihat laporan cuti secara real-time.'],
                        ['icon' => 'ph-shield-check', 'color' => 'text-orange-500', 'title' => 'Keamanan Data', 'desc' => 'Kontrol akses berbasis peran untuk menjaga keamanan data.']
                    ];
                @endphp

                @foreach($fitur as $f)
                    <div class="p-6 bg-white rounded-2xl shadow hover:-translate-y-2 hover:shadow-xl transition">
                        <i class="ph {{ $f['icon'] }} text-4xl {{ $f['color'] }} mb-3"></i>
                        <h3 class="font-semibold mb-2">{{ $f['title'] }}</h3>
                        <p class="text-sm text-gray-600">{{ $f['desc'] }}</p>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-r from-sky-700 to-blue-700 text-white py-24 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.toptal.com/designers/subtlepatterns/patterns/double-bubble-outline.png')]"></div>
        <div class="relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Siap Memulai?</h2>

            <p class="mb-8 text-lg md:text-xl">
                Masuk ke sistem cuti digital Diskominfo dan nikmati kemudahan pengelolaan cuti pegawai.
            </p>

            <a href="{{ route('login') }}" 
               class="inline-flex items-center gap-2 bg-white text-sky-700 font-semibold px-8 py-3 rounded-lg shadow-lg hover:scale-105 transition">
                <i class="ph ph-sign-in"></i> Masuk Ke Sistem
            </a>

        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 py-6 text-center text-sm">
        &copy; {{ date('Y') }} Diskominfo Banjarbaru. Hak cipta dilindungi undang-undang.
    </footer>

    {{-- Tombol Back to Top --}}
    <button id="backToTop"
    class="fixed bottom-5 right-5 bg-sky-600 hover:bg-sky-700 text-white p-2.5 rounded-full shadow-lg 
           hidden transition-all duration-300">
    <i class="ph ph-arrow-up text-lg"></i>
</button>


    <script>
        // Munculkan tombol ketika scroll > 300px
        window.addEventListener('scroll', function () {
            const btn = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });

        // Scroll ke atas
        document.getElementById('backToTop').addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

</body>
</html>
