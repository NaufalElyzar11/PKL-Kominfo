<section x-data="{ 
    {{-- Status Foto --}}
    photoPreview: null, 
    isDeleted: false,

    {{-- Nilai Awal dari Database --}}
    initNama: '{{ $user->name }}',
    initEmail: '{{ $user->email }}',
    initTelepon: '{{ $pegawai->telepon ?? '' }}',

    {{-- Nilai yang Sedang Diedit --}}
    currNama: '{{ $user->name }}',
    currEmail: '{{ $user->email }}',
    currTelepon: '{{ $pegawai->telepon ?? '' }}',

    {{-- Fungsi Cek Perubahan --}}
    hasChanges() {
        return this.currNama !== this.initNama || 
               this.currEmail !== this.initEmail || 
               this.currTelepon !== this.initTelepon || 
               this.photoPreview !== null || 
               this.isDeleted === true;
    }
}">

    <header class="mb-8 border-b border-gray-100 pb-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
            <i class="fa-solid fa-user-pen text-sky-600"></i>
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __("Pastikan data nama, email, dan nomor telepon Anda selalu dalam kondisi terbaru.") }}
        </p>
    </header>

    {{-- 1. TAMBAHKAN enctype="multipart/form-data" (WAJIB UNTUK UPLOAD FILE) --}}
    <form method="post" action="{{ route('pegawai.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('patch')

    {{-- Pastikan container utama form memiliki x-data --}}
    <div class="space-y-6">
    <div class="mb-8">
        <label class="block text-sm font-bold text-slate-700 mb-3">Foto Profil</label>
            
            <div class="flex items-center gap-6">
                {{-- 1. Preview Area --}}
                <div class="relative w-20 h-20 rounded-xl overflow-hidden border-2 border-gray-100 bg-gray-50 flex-shrink-0 shadow-sm">
                    {{-- State A: Preview Foto Baru --}}
                    <template x-if="photoPreview">
                        <img :src="photoPreview" class="w-full h-full object-cover">
                    </template>
                    
                    {{-- State B: Foto Lama (Selama belum ditandai hapus) --}}
                    <template x-if="!photoPreview && !isDeleted">
                        @if($pegawai && $pegawai->foto)
                            <img src="{{ asset('storage/' . $pegawai->foto) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-sky-50 text-sky-500 font-bold text-2xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </template>

                    {{-- State C: Inisial (Muncul jika foto lama dihapus tapi belum pilih foto baru) --}}
                    <template x-if="!photoPreview && isDeleted">
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 font-bold text-2xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </template>
                </div>

                {{-- 2. Input & Kontrol --}}
                <div class="flex-1">
                    <div class="flex flex-wrap gap-3 items-center">
                {{-- PERBAIKAN: Ganti id="fotoInput" menjadi x-ref="fotoInput" --}}
                        <input type="file" name="foto" x-ref="fotoInput" accept="image/*" class="hidden"
                            @change="
                                const file = $event.target.files[0]; 
                                if (file) { 
                                    if (file.size > 2097152) { 
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'File Terlalu Besar',
                                            text: 'Maksimal ukuran file adalah 2MB.',
                                            confirmButtonColor: '#0284c7'
                                        });
                                        $event.target.value = '';
                                        return; 
                                    } 
                                    isDeleted = false;
                                    const reader = new FileReader(); 
                                    reader.onload = (e) => { photoPreview = e.target.result; }; 
                                    reader.readAsDataURL(file); 
                                }
                            ">
                        
                        {{-- Tombol ini sekarang bisa memicu klik pada input file di atas --}}
                        <button type="button" @click="$refs.fotoInput.click()" 
                            class="px-4 py-2 bg-sky-50 text-sky-700 rounded-full text-xs font-bold hover:bg-sky-100 transition-all border border-sky-200">
                            <i class="fa-solid fa-camera mr-2"></i> Pilih Foto
                        </button>

                        <button type="button" 
                            x-show="photoPreview || (@js($pegawai && $pegawai->foto) && !isDeleted)"
                            @click="
                                Swal.fire({
                                    title: 'Hapus foto profil?',
                                    text: 'Tindakan ini akan menghapus foto Anda. Jangan lupa klik Simpan Perubahan setelahnya.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444', {{-- Warna Merah Tailwind --}}
                                    cancelButtonColor: '#6b7280',  {{-- Warna Abu-abu --}}
                                    confirmButtonText: 'Ya, Hapus!',
                                    cancelButtonText: 'Batal',
                                    customClass: {
                                        popup: 'rounded-2xl'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        if(photoPreview) {
                                            {{-- Jika yang dihapus adalah preview foto yang baru dipilih --}}
                                            photoPreview = null;
                                            $refs.fotoInput.value = '';
                                        } else {
                                            {{-- Jika yang dihapus adalah foto lama yang sudah ada di server --}}
                                            isDeleted = true;
                                        }
                                        
                                        Swal.fire({
                                            title: 'Terhapus!',
                                            text: 'Foto telah dihapus dari tampilan.',
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false,
                                            customClass: { popup: 'rounded-2xl' }
                                        });
                                    }
                                })
                            "
                            class="px-4 py-2 bg-red-50 text-red-600 rounded-full text-xs font-bold hover:bg-red-100 transition-all border border-red-100">
                            <i class="fa-solid fa-trash-can mr-2"></i> Hapus Foto
                        </button>

                        {{-- Hidden Input tetap sama untuk sinkronisasi ke Controller --}}
                        <input type="hidden" name="hapus_foto" :value="isDeleted ? '1' : '0'">

                    <p class="text-[11px] text-slate-400 mt-2 italic">Rekomendasi: Persegi (1:1), Max 2MB.</p>
                </div>
            </div>
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
    </div>
</div>

        {{-- Grid Layout (Nama, Email, Telepon) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
        {{-- Nama Lengkap --}}
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-user text-sky-500 w-4"></i> Nama Lengkap
            </label>
            <input type="text" 
                name="nama" 
                x-model="currNama"
                {{-- Logika filter: Hanya izinkan huruf (a-z, A-Z) dan spasi --}}
                @input="currNama = currNama.replace(/[^a-zA-Z\s]/g, '')"
                placeholder="Masukkan nama tanpa angka atau simbol"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" 
                required>
            
            <x-input-error :messages="$errors->get('nama')" />
        </div>

            {{-- Email --}}
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-sky-500 w-4"></i> Alamat Email
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" x-model="currEmail"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none" required>
                <x-input-error :messages="$errors->get('email')" />
            </div>

            {{-- Telepon --}}
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-phone text-sky-500 w-4"></i> Nomor Telepon
                </label>
                <input type="text" name="telepon" value="{{ old('telepon', $pegawai->telepon ?? '') }}"  x-model="currTelepon"
                    inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    minlength="12" maxlength="13"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none">
                <x-input-error :messages="$errors->get('telepon')" />
            </div>
        </div>

        {{-- Tombol Simpan Dinamis --}}
        <div class="flex items-center justify-end pt-6 border-t border-gray-50 mt-4">
            <button type="submit" 
                :disabled="!hasChanges()"
                :class="!hasChanges() ? 'bg-gray-400 cursor-not-allowed opacity-70 shadow-none' : 'bg-sky-600 hover:bg-sky-700 shadow-lg hover:shadow-sky-200'"
                class="flex items-center gap-2 px-8 py-3 text-white font-bold rounded-xl transition-all active:scale-95">
                
                <i class="fa-solid" :class="!hasChanges() ? 'fa-lock' : 'fa-floppy-disk'"></i>
                <span x-text="!hasChanges() ? 'TIDAK ADA PERUBAHAN' : 'SIMPAN PERUBAHAN'"></span>
            </button>
        </div>
    </form>
</section>