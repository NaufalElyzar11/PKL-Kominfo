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

class LoginTest extends TestCase
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
        
        $roles = ['super_admin', 'admin', 'atasan', 'pegawai', 'pejabat'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
        }
        
        // Setup dummy data untuk relasi
        $this->atasan = AtasanLangsung::forceCreate([
            'nama_atasan' => 'Atasan Test',
            'nip_atasan' => '111111'
        ]);

        $this->pejabat = PejabatPemberiCuti::forceCreate([
            'nama_pejabat' => 'Pejabat Test',
            'nip_pejabat' => '222222'
        ]);

        // Setup user untuk testing
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

    /** ===================== FORM DISPLAY TESTS ===================== */

    /**
     * Test 1: Menampilkan form login
     */
    public function test_show_login_form_displays_correctly()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** ===================== SUCCESSFUL LOGIN TESTS ===================== */

    /**
     * Test 2: Login berhasil menggunakan nama lengkap (name)
     */
    public function test_login_success_with_name()
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
    public function test_login_success_with_nip()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->pegawai->nip,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pegawai.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test 4: Login berhasil dengan role admin harus redirect ke admin dashboard
     */
    public function test_login_success_admin_redirects_to_admin_dashboard()
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
     * Test 5: Login berhasil dengan role super_admin harus redirect ke super dashboard
     */
    public function test_login_success_super_admin_redirects_to_super_dashboard()
    {
        $superAdminUser = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin'
        ]);

        $response = $this->post(route('login'), [
            'login_identifier' => 'Super Admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('super.dashboard'));
        $this->assertAuthenticatedAs($superAdminUser);
    }

    /**
     * Test 6: Login berhasil dengan role atasan harus redirect ke atasan dashboard
     */
    public function test_login_success_atasan_redirects_to_atasan_dashboard()
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
     * Test 7: Login berhasil dengan role pejabat (tanpa Spatie sync)
     */
    public function test_login_success_pejabat_without_spatie_sync()
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
     * Test 8: Login berhasil dengan authenticated
     */
    public function test_user_is_authenticated_after_login()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($this->user);
    }

    /** ===================== FAILED LOGIN TESTS ===================== */

    /**
     * Test 9: Login gagal - user tidak terdaftar
     */
    public function test_login_fails_when_user_not_found()
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
     * Test 10: Login gagal - password salah
     */
    public function test_login_fails_with_wrong_password()
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
     * Test 11: Login gagal - field login_identifier kosong
     */
    public function test_login_fails_with_empty_identifier()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login_identifier');
        $this->assertGuest();
    }

    /**
     * Test 12: Login gagal - field password kosong
     */
    public function test_login_fails_with_empty_password()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** ===================== RATE LIMITING TESTS ===================== */

    /**
     * Test 13: Rate limiting - blokir setelah 4 kali percobaan gagal
     */
    public function test_rate_limiting_blocks_after_4_failed_attempts()
    {
        $throttleKey = strtolower($this->user->name) . '|127.0.0.1';

        // Lakukan 4x percobaan login gagal
        for ($i = 0; $i < 4; $i++) {
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
     * Test 14: Rate limiting terkait dengan IP address
     */
    public function test_rate_limiting_key_includes_ip_address()
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
     * Test 15: Rate limiter di-clear setelah login berhasil
     */
    public function test_rate_limiter_cleared_after_successful_login()
    {
        $throttleKey = strtolower($this->user->name) . '|127.0.0.1';

        // Login berhasil
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'password123',
        ]);

        // Setelah login berhasil, rate limiter harus di-clear
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 4));
    }

    /** ===================== LOGOUT TESTS ===================== */

    /**
     * Test 16: Logout berhasil
     */
    public function test_logout_successfully()
    {
        // Login dulu
        $this->actingAs($this->user);
        
        // Cek user sudah authenticated
        $this->assertAuthenticatedAs($this->user);

        // Logout
        $response = $this->post(route('logout'));

        // Harus redirect ke login
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Anda berhasil logout.');
        $this->assertGuest();
    }

    /**
     * Test 17: Session invalidate setelah logout
     */
    public function test_session_invalidated_after_logout()
    {
        $this->actingAs($this->user);
        
        // Set session data
        session(['user_data' => 'test']);
        $this->assertTrue(session()->has('user_data'));

        // Logout
        $this->post(route('logout'));

        // Session seharusnya di-invalidate
        $this->assertFalse(session()->has('user_data'));
    }

    /**
     * Test 18: CSRF token di-regenerate setelah logout
     */
    public function test_csrf_token_regenerated_after_logout()
    {
        $this->actingAs($this->user);

        $this->post(route('logout'));

        // Token baru harus digenerate saat logout
        $response = $this->get(route('login'));
        
        // Cek bahwa ada token baru di session
        $response->assertSessionHas('_token');
    }

    /** ===================== EDGE CASE TESTS ===================== */

    /**
     * Test 19: Login dengan identifier case-insensitive
     */
    public function test_login_is_case_insensitive()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => strtoupper($this->user->name),
            'password' => 'password123',
        ]);

        // Seharusnya tetap berhasil jika rate limiter case-insensitive
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test 20: Login sebagai pegawai tanpa role invalid
     */
    public function test_login_with_invalid_role_logs_out()
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
     * Test 21: Input validation - login_identifier harus string
     */
    public function test_login_identifier_must_be_string()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => 123,  // Bukan string
            'password' => 'password123',
        ]);

        // Laravel harus mengkonversi atau validasi
        $this->assertGuest();
    }

    /**
     * Test 22: Password field harus ada dalam validasi
     */
    public function test_password_field_is_required()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test 23: Login identifier field harus ada dalam validasi
     */
    public function test_login_identifier_field_is_required()
    {
        $response = $this->post(route('login'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login_identifier');
    }

    /**
     * Test 24: Cek percobaan login gagal tidak bisa login lagi
     */
    public function test_failed_login_attempts_prevent_login()
    {
        // Attempt 1-4: Login gagal
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('login'), [
                'login_identifier' => $this->user->name,
                'password' => 'wrongpassword',
            ]);
        }

        // Attempt 5: Harus terblokir
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    /**
     * Test 25: Redirect dengan input sebelumnya saat login gagal
     */
    public function test_failed_login_returns_input()
    {
        $response = $this->post(route('login'), [
            'login_identifier' => $this->user->name,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        // withInput() harus menyimpan login_identifier (tapi tidak password untuk keamanan)
        $response->assertSessionHas('_old_input');
    }
}
