<?php

namespace Tests\Feature\Pegawai;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $pegawai;
    protected $user;
    protected $atasan;
    protected $userAtasan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Setup Atasan
        $this->atasan = Pegawai::forceCreate([
            'nama' => 'Atasan Langsung',
            'nip' => '199001012020121101',
            'jabatan' => 'Kepala Bidang',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'alamat' => 'Jl. Atasan',
            'telepon' => '08222222222',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
        ]);
        
        $this->userAtasan = User::factory()->create([
            'name' => 'Atasan Langsung',
            'role' => 'atasan',
            'id_pegawai' => $this->atasan->id,
        ]);

        // Setup Pegawai (user utama pengujian)
        $this->pegawai = Pegawai::forceCreate([
            'nama' => 'Dina Budya',
            'nip' => '199001012020121102',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'alamat' => 'Jl. Pegawai No. 123',
            'telepon' => '08333333333',
            'email' => 'dina@email.com',
            'id_atasan_langsung' => $this->atasan->id,
            'id_pejabat_pemberi_cuti' => null,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Dina Budya',
            'email' => 'dina@email.com',
            'role' => 'pegawai',
            'id_pegawai' => $this->pegawai->id,
            'telepon' => '08333333333',
        ]);

        $this->actingAs($this->user);
    }

    // =========================================================================
    // 1. TAMBAH/UBAH FOTO PROFIL (SKENARIO NORMAL)
    // =========================================================================
    public function test_tambah_ubah_foto_profil_normal()
    {
        $foto = UploadedFile::fake()->image('foto-dina.jpg', 500, 500)->size(500);

        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'foto' => $foto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $pegawaiFresh = Pegawai::find($this->pegawai->id);
        $this->assertNotNull($pegawaiFresh->foto);
        Storage::disk('public')->assertExists($pegawaiFresh->foto);
    }

    // =========================================================================
    // 2. VALIDASI EKSTENSI FILE FOTO
    // =========================================================================
    public function test_validasi_ekstensi_file_foto_pdf()
    {
        $fotoPdf = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'foto' => $fotoPdf,
        ]);

        $response->assertSessionHasErrors('foto');
    }

    // =========================================================================
    // 3. VALIDASI UKURAN MAKSIMAL FOTO PROFIL
    // =========================================================================
    public function test_validasi_ukuran_maksimal_foto_profil()
    {
        $fotoHD = UploadedFile::fake()->image('foto-HD.jpg')->size(3000); // 3MB (melebihi 2MB)

        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'foto' => $fotoHD,
        ]);

        $response->assertSessionHasErrors('foto');
    }

    // =========================================================================
    // 4. HAPUS FOTO PROFIL (RESET KE DEFAULT)
    // =========================================================================
    public function test_hapus_foto_profil_reset_ke_default()
    {
        // Setup: Upload foto dulu
        $foto = UploadedFile::fake()->image('foto-dina.jpg', 500, 500)->size(500);
        $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'foto' => $foto,
        ]);

        $pegawaiBefore = Pegawai::find($this->pegawai->id);
        $this->assertNotNull($pegawaiBefore->foto);

        // Action: Hapus foto
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'hapus_foto' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pegawaiAfter = Pegawai::find($this->pegawai->id);
        $this->assertNull($pegawaiAfter->foto);
    }

    // =========================================================================
    // 5. TAMBAH/UBAH NOMOR TELEPON (SKENARIO SUKSES)
    // =========================================================================
    public function test_tambah_ubah_nomor_telepon_sukses()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '081234567890',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $userFresh = User::find($this->user->id);
        $this->assertEquals('081234567890', $userFresh->telepon);

        $pegawaiFresh = Pegawai::find($this->pegawai->id);
        $this->assertEquals('081234567890', $pegawaiFresh->telepon);
    }

    // =========================================================================
    // 6. VALIDASI INPUT NOMOR TELEPON (KARAKTER ILEGAL)
    // =========================================================================
    public function test_validasi_input_nomor_telepon_karakter_ilegal()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '0812ABCD!@#',
        ]);

        $response->assertSessionHasErrors('telepon');
    }

    // =========================================================================
    // 7. HAPUS NOMOR TELEPON
    // =========================================================================
    public function test_hapus_nomor_telepon()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // =========================================================================
    // 8. TAMBAH/UBAH EMAIL (SKENARIO SUKSES)
    // =========================================================================
    public function test_tambah_ubah_email_sukses()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina.baru@email.com',
            'telepon' => '08333333333',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $userFresh = User::find($this->user->id);
        $this->assertEquals('dina.baru@email.com', $userFresh->email);
    }

    // =========================================================================
    // 9. VALIDASI FORMAT STRING EMAIL
    // =========================================================================
    public function test_validasi_format_string_email()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina.asal.ketik', // Tidak ada @
            'telepon' => '08333333333',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // 10. VALIDASI EMAIL (UNIQUE CHECKER)
    // =========================================================================
    public function test_validasi_email_unique_checker()
    {
        // Buat user lain dengan email tertentu
        $otherPegawai = Pegawai::forceCreate([
            'nama' => 'Other User',
            'nip' => '199001012020121103',
            'jabatan' => 'Staf Lain',
            'unit_kerja' => 'Bidang B',
            'status' => 'aktif',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
        ]);

        User::factory()->create([
            'email' => 'pengguna@gmail.com',
            'id_pegawai' => $otherPegawai->id,
        ]);

        // Coba gunakan email yang sudah ada
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'pengguna@gmail.com', // Email milik orang lain
            'telepon' => '08333333333',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // 11. HAPUS EMAIL (EDGE CASE / NEGATIF)
    // =========================================================================
    public function test_hapus_email_edge_case()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => '',
            'telepon' => '08333333333',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // =========================================================================
    // 12. TAMBAH/UBAH ALAMAT LENGKAP
    // =========================================================================
    public function test_tambah_ubah_alamat_lengkap()
    {
        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'alamat' => 'Jl. Pangeran Antasari No. 123, RT 01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pegawaiFresh = Pegawai::find($this->pegawai->id);
        $this->assertEquals('Jl. Pangeran Antasari No. 123, RT 01', $pegawaiFresh->alamat);
    }

    // =========================================================================
    // 13. VALIDASI KARAKTER KEAMANAN PADA ALAMAT (ANTI-XSS)
    // =========================================================================
    public function test_validasi_karakter_keamanan_alamat_anti_xss()
    {
        $alamatDenganScript = "<script>alert('Hacked')</script>";

        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'alamat' => $alamatDenganScript,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pegawaiFresh = Pegawai::find($this->pegawai->id);
        // Alamat disimpan apa adanya, Laravel Blade akan auto-escape saat tampil
        $this->assertEquals($alamatDenganScript, $pegawaiFresh->alamat);
    }

    // =========================================================================
    // 14. HAPUS ALAMAT
    // =========================================================================
    public function test_hapus_alamat()
    {
        // Setup: Ada alamat dulu
        $this->pegawai->update(['alamat' => 'Jl. Lama']);

        $response = $this->patch(route('pegawai.profile.update'), [
            'nama' => 'Dina Budya',
            'email' => 'dina@email.com',
            'telepon' => '08333333333',
            'alamat' => '', // Kosong
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pegawaiAfter = Pegawai::find($this->pegawai->id);
        // Cek apakah alamat kosong (empty string atau null)
        $this->assertTrue(empty($pegawaiAfter->alamat));
    }
}
