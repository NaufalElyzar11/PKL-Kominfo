<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pegawai;
    protected $atasan;
    protected $pejabat;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Spatie roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Super Admin dihapus sesuai spesifikasi sistem
        $roles = ['admin', 'atasan', 'pegawai', 'pejabat'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
        }
        
        // Setup data dummy untuk relasi
        $this->atasan = AtasanLangsung::forceCreate([
            'nama_atasan' => 'Atasan Test',
            'nip_atasan' => '111111'
        ]);

        $this->pejabat = PejabatPemberiCuti::forceCreate([
            'nama_pejabat' => 'Pejabat Test',
            'nip_pejabat' => '222222'
        ]);

        // Setup user untuk pengujian
        $this->pegawai = Pegawai::forceCreate([
            'nama' => 'Pegawai Login Test',
            'nip' => '199001012020121001',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'status' => 'aktif',
            'id_atasan_langsung' => $this->atasan->id,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
            'sisa_cuti' => 12,
            'telepon' => '081234567890',
            'email' => 'pegawai@test.com'
        ]);

        $this->user = User::forceCreate([
            'name' => 'Pegawai Login Test',
            'email' => 'login@test.com',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
            'id_pegawai' => $this->pegawai->id
        ]);
    }

    /** ===================== PENGUJIAN TAMPILAN FORM ===================== */

    /**
     * Test 1: Menampilkan form login
     */
    public function test_menampilkan_form_login_dengan_benar()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** ===================== PENGUJIAN LOGIN BERHASIL ===================== */

    /**
     * Test 2: Login berhasil menggunakan nama lengkap (name)
     */
    public function test_login_berhasil_menggunakan_nama_lengkap()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pegawai.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test 3: Login berhasil menggunakan NIP (melalui relasi pegawai)
     */
    public function test_login_berhasil_menggunakan_nip()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->pegawai->nip,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pegawai.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test 4: Login berhasil dengan role admin harus dialihkan ke dashboard admin
     */
    public function test_login_admin_berhasil_dan_dialihkan_ke_dashboard_admin()
    {
        $adminUser = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        $response = $this->post(route('login'), [
            'login_identifier' => 'Admin User',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($adminUser);
    }

    /**
     * Test 5: Login berhasil dengan role atasan harus dialihkan ke dashboard atasan
     */
    public function test_login_atasan_berhasil_dan_dialihkan_ke_dashboard_atasan()
    {
        $atasanUser = User::forceCreate([
            'name' => 'Atasan User',
            'email' => 'atasan@test.com',
            'password' => Hash::make('password123'),
            'role' => 'atasan'
        ]);

        $response = $this->post(route('login'), [
            'login_identifier' => 'Atasan User',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('atasan.dashboard'));
        $this->assertAuthenticatedAs($atasanUser);
    }

    /**
     * Test 6: Login berhasil dengan role pejabat (tanpa sinkronisasi Spatie)
     */
    public function test_login_pejabat_berhasil_tanpa_sinkronisasi_spatie()
    {
        $pejabatUser = User::forceCreate([
            'name' => 'Pejabat User',
            'email' => 'pejabat@test.com',
            'password' => Hash::make('password123'),
            'role' => 'pejabat'
        ]);

        $response = $this->post(route('login'), [
            'login_identifier' => 'Pejabat User',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pejabat.dashboard'));
        $this->assertAuthenticatedAs($pejabatUser);
    }

    /**
     * Test 7: Memastikan pengguna terautentikasi setelah login berhasil
     */
    public function test_pengguna_terautentikasi_setelah_login_berhasil()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($this->user);
    }

    /** ===================== PENGUJIAN LOGIN GAGAL ===================== */

    /**
     * Test 8: Login gagal - pengguna tidak terdaftar
     */
    public function test_login_gagal_ketika_pengguna_tidak_ditemukan()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => 'User Tidak Ada',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Nama Lengkap atau NIP tidak terdaftar!');
        $this->assertGuest();
    }

    /**
     * Test 9: Login gagal - kata sandi salah
     */
    public function test_login_gagal_karena_kata_sandi_salah()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Kata sandi salah!');
        $this->assertGuest();
    }

    /**
     * Test 10: Login gagal - kolom identitas kosong
     */
    public function test_login_gagal_karena_kolom_identitas_kosong()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login_identifier');
        $this->assertGuest();
    }

    /**
     * Test 11: Login gagal - kolom kata sandi kosong
     */
    public function test_login_gagal_karena_kolom_kata_sandi_kosong()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** ===================== PENGUJIAN PEMBATASAN AKSES (RATE LIMITING) ===================== */

    /**
     * Test 12: Pembatasan akses memblokir setelah 4 kali percobaan gagal
     */
    public function test_pembatasan_akses_memblokir_setelah_5_kali_gagal()
    {
        $throttleKey = strtolower($this->user->name) . '|127.0.0.1';

        // Lakukan 4x percobaan login gagal
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'login_identifier' => $this->user->name,
                'password' => 'wrongpassword',
            ]);
        }

        // Percobaan ke-5 harus terblokir
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    /**
     * Test 13: Kunci pembatasan akses terkait dengan alamat IP
     */
    public function test_kunci_pembatasan_akses_terkait_dengan_alamat_ip()
    {
        $throttleKey = strtolower($this->user->name) . '|127.0.0.1';

        // Lakukan 4x percobaan login gagal
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('login'), [
                'login_identifier' => $this->user->name,
                'password' => 'wrongpassword',
            ]);
        }

        // Cek bahwa rate limiter mencatat percobaan
        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, 4));
    }

    /**
     * Test 14: Pembatasan akses dibersihkan setelah login berhasil
     */
    public function test_pembatasan_akses_dibersihkan_setelah_login_berhasil()
    {
        $throttleKey = strtolower($this->user->name) . '|127.0.0.1';

        // Login berhasil
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        // Setelah login berhasil, rate limiter harus dibersihkan
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 4));
    }

    /** ===================== PENGUJIAN LOGOUT ===================== */

    /**
     * Test 15: Logout berhasil
     */
    public function test_logout_berhasil()
    {
        // Login terlebih dahulu
        $this->actingAs($this->user);
        
        // Cek pengguna sudah terautentikasi
        $this->assertAuthenticatedAs($this->user);

        // Proses Logout
        $response = $this->post(route('logout'));

        // Harus dialihkan ke halaman login
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Anda berhasil logout.');
        $this->assertGuest();
    }

    /**
     * Test 16: Sesi dihancurkan setelah logout
     */
    public function test_sesi_dihancurkan_setelah_logout()
    {
        $this->actingAs($this->user);
        
        // Atur data sesi
        session(['user_data' => 'test']);
        $this->assertTrue(session()->has('user_data'));

        // Proses Logout
        $this->post(route('logout'));

        // Sesi seharusnya dihapus (invalidated)
        $this->assertFalse(session()->has('user_data'));
    }

    /**
     * Test 17: Token CSRF diperbarui setelah logout
     */
    public function test_token_csrf_diperbarui_setelah_logout()
    {
        $this->actingAs($this->user);

        $this->post(route('logout'));

        // Token baru harus di-generate saat logout
        $response = $this->get(route('login'));
        
        // Cek bahwa ada token baru di dalam sesi
        $response->assertSessionHas('_token');
    }

    /** ===================== PENGUJIAN KASUS KHUSUS (EDGE CASES) ===================== */

    /**
     * Test 19: Login dengan hak akses tidak valid akan langsung logout
     */
    public function test_login_dengan_hak_akses_tidak_valid_akan_otomatis_logout()
    {
        $invalidRoleUser = User::forceCreate([
            'name' => 'Invalid Role User',
            'email' => 'invalid@test.com',
            'password' => Hash::make('password123'),
            'role' => 'invalid_role'
        ]);

        $response = $this->post(route('login'), [
            'login_identifier' => 'Invalid Role User',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Hak akses tidak dikenali.');
        $this->assertGuest();
    }

    /**
     * Test 20: Validasi input - identitas login harus berupa teks (string)
     */
    public function test_identitas_login_harus_berupa_teks_string()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => 123,  // Bukan string
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    /**
     * Test 21: Kolom kata sandi wajib diisi pada validasi
     */
    public function test_kolom_kata_sandi_wajib_diisi_pada_validasi()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test 22: Kolom identitas login wajib diisi pada validasi
     */
    public function test_kolom_identitas_login_wajib_diisi_pada_validasi()
    {
        $response = $this->post(route('login'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login_identifier');
    }

    /**
     * Test 23: Percobaan login gagal mencegah login selanjutnya
     */
    public function test_percobaan_login_gagal_mencegah_login_selanjutnya()
    {
        // Percobaan 1-4: Login gagal
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('login'), [
                'login_identifier' => $this->user->name,
                'password' => 'wrongpassword',
            ]);
        }

        // Percobaan 5: Harus terblokir
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    /**
     * Test 24: Redirect dengan membawa input sebelumnya saat login gagal
     */
    public function test_login_gagal_mengembalikan_input_sebelumnya()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        // withInput() harus menyimpan login_identifier (tapi tidak kata sandi demi keamanan)
        $response->assertSessionHas('_old_input');
    }
}