<section x-data="{ 
    showPassword: false, 
    newPassword: '',
    {{-- Hitung kekuatan sandi secara real-time --}}
    get strength() {
        let score = 0;
        if (this.newPassword.length >= 8) score++;
        if (/[A-Z]/.test(this.newPassword)) score++;
        if (/[0-9]/.test(this.newPassword)) score++;
        if (/[^A-Za-z0-9]/.test(this.newPassword)) score++;
        return score;
    }
}">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <i class="fa-solid fa-key text-sky-600 mr-2"></i> {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang kuat untuk menjaga keamanan.') }}
        </p>
    </header>

    <form method="post" action="{{ route('pegawai.profile.password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        {{-- 1. Current Password --}}
        <div class="relative">
            <x-input-label for="current_password" :value="__('Current Password')" />
            <div class="relative mt-1">
                {{-- PERBAIKAN: Gunakan x-bind:type agar Alpine.js yang mengontrol, bukan PHP --}}
                <x-text-input id="current_password" name="current_password" 
                    x-bind:type="showPassword ? 'text' : 'password'" 
                    class="block w-full pr-10" />
                
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-sky-600">
                    <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- 2. New Password --}}
        <div>
            <x-input-label for="password" :value="__('New Password')" />
            
            {{-- KETERANGAN KETENTUAN: Muncul di bawah Label agar pengguna tahu syaratnya --}}
            <p class="text-[11px] text-slate-500 mb-2 italic">
                *Wajib: Minimal 8 karakter, Huruf Kapital (A-Z), Angka (0-9), dan Simbol.
            </p>

            <div class="relative">
                {{-- PERBAIKAN: Gunakan :type (titik dua satu) agar tersensor otomatis sejak awal --}}
                <input id="password" name="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    x-model="newPassword"
                    class="block w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-md shadow-sm pr-10" />
                
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-sky-600">
                    <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>

            {{-- INDIKATOR KEKUATAN SANDI --}}
            <div class="mt-3 space-y-2">
                <div class="flex gap-1 h-1.5">
                    <template x-for="i in 4">
                        <div :class="strength >= i ? (strength <= 2 ? 'bg-red-500' : (strength === 3 ? 'bg-yellow-400' : 'bg-emerald-500')) : 'bg-gray-200'" 
                             class="flex-1 rounded-full transition-all duration-500"></div>
                    </template>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider" 
                   :class="strength <= 2 ? 'text-red-500' : (strength === 3 ? 'text-yellow-600' : 'text-emerald-600')"
                   x-text="strength === 0 ? '' : (strength <= 2 ? 'Sangat Lemah' : (strength === 3 ? 'Sedang' : 'Sangat Kuat'))">
                </p>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- 3. Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <x-text-input id="password_confirmation" name="password_confirmation" 
                    x-bind:type="showPassword ? 'text' : 'password'" 
                    class="block w-full pr-10" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-sky-600 hover:bg-sky-700 shadow-lg px-8 py-2.5 rounded-xl transition-all">
                {{ __('SIMPAN PERUBAHAN') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" 
                   class="text-sm text-emerald-600 font-bold">
                    <i class="fa-solid fa-check-circle mr-1"></i> {{ __('Tersimpan.') }}
                </p>
            @endif
        </div>
    </form>
</section>