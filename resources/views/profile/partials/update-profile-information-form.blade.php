<section>
    <header class="mb-8 border-b border-gray-100 pb-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
            <i class="fa-solid fa-user-pen text-sky-600"></i>
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __("Pastikan data nama, email, dan nomor telepon Anda selalu dalam kondisi terbaru.") }}
        </p>
    </header>

    <form method="post" action="{{ route('pegawai.profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        {{-- Grid Layout agar rapi (2 Kolom di Desktop) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            
            {{-- Nama Lengkap --}}
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-user text-sky-500 w-4"></i> Nama Lengkap
                </label>
                <input type="text" name="nama" value="{{ old('nama', $user->name) }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" required>
                <x-input-error :messages="$errors->get('nama')" />
            </div>

            {{-- Email --}}
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-sky-500 w-4"></i> Alamat Email
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" required>
                <x-input-error :messages="$errors->get('email')" />
            </div>

            {{-- Telepon --}}
            <div class="space-y-2 md:col-span-2 lg:col-span-1">
                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-phone text-sky-500 w-4"></i> Nomor Telepon / WhatsApp
                </label>
                <input type="text" 
                    name="telepon" 
                    {{-- Mengambil data lama atau data dari database --}}
                    value="{{ old('telepon', $pegawai->telepon ?? '') }}" 
                    {{-- Memicu keyboard angka pada perangkat mobile --}}
                    inputmode="numeric"
                    {{-- Menghapus semua karakter yang bukan angka secara real-time --}}
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    {{-- Validasi panjang karakter --}}
                    minlength="12"
                    maxlength="13"
                    {{-- Pesan bantuan saat hover atau validasi gagal --}}
                    title="Nomor telepon harus berupa angka antara 12 hingga 13 digit"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" 
                    placeholder="Contoh: 081234567890">
                
                <x-input-error :messages="$errors->get('telepon')" />
            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div class="flex items-center justify-end pt-6">
            <button type="submit" 
                class="flex items-center gap-2 px-8 py-3 bg-sky-600 text-white font-bold rounded-xl hover:bg-sky-700 shadow-lg hover:shadow-sky-200 transition-all active:scale-95">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('SIMPAN PERUBAHAN') }}
            </button>
        </div>
    </form>
</section>