<?php

namespace Tests\Feature\Atasan;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AtasanApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $pejabat;
    protected $userPejabat;
    protected $atasan;
    protected $userAtasan;
    protected $pegawaiBawahan;
    protected $userPegawai;
    protected $rekanDelegasi;
    protected $userDelegasi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());

        // 1. Setup Pejabat
        $this->pejabat = Pegawai::forceCreate([
            'nama' => 'Pejabat Tertinggi',
            'nip' => '199001012020121100',
            'jabatan' => 'Kepala Dinas',
            'unit_kerja' => 'Diskominfo',
            'status' => 'aktif',
            'alamat' => 'Jl. Pejabat',
            'telepon' => '08111111111',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
        ]);
        $this->userPejabat = User::factory()->create([
            'name' => 'Pejabat Tertinggi',
            'role' => 'pejabat',
            'id_pegawai' => $this->pejabat->id,
        ]);

        // 2. Setup Atasan Langsung (Aktor Utama Pengujian Ini)
        $this->atasan = Pegawai::forceCreate([
            'nama' => 'Atasan Langsung',
            'nip' => '199001012020121101',
            'jabatan' => 'Kepala Bidang',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'alamat' => 'Jl. Atasan',
            'telepon' => '08222222222',
            'sisa_cuti' => 12,
            'id_atasan_langsung' => $this->pejabat->id,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
        ]);
        $this->userAtasan = User::factory()->create([
            'name' => 'Atasan Langsung',
            'role' => 'atasan',
            'id_pegawai' => $this->atasan->id,
        ]);

        // 3. Setup Pegawai Bawahan
        $this->pegawaiBawahan = Pegawai::forceCreate([
            'nama' => 'Pegawai Bawahan',
            'nip' => '199001012020121102',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'alamat' => 'Jl. Pegawai',
            'telepon' => '08333333333',
            'id_atasan_langsung' => $this->atasan->id,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
        ]);
        $this->userPegawai = User::factory()->create([
            'name' => 'Pegawai Bawahan',
            'role' => 'pegawai',
            'id_pegawai' => $this->pegawaiBawahan->id,
        ]);

        // 4. Setup Rekan Delegasi (Pengganti)
        $this->rekanDelegasi = Pegawai::forceCreate([
            'nama' => 'Rekan Pengganti',
            'nip' => '199001012020121103',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'alamat' => 'Jl. Rekan',
            'telepon' => '08444444444',
            'id_atasan_langsung' => $this->atasan->id,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
        ]);
        $this->userDelegasi = User::factory()->create([
            'name' => 'Rekan Pengganti',
            'role' => 'pegawai',
            'id_pegawai' => $this->rekanDelegasi->id,
        ]);

        // Login default sebagai Atasan
        $this->actingAs($this->userAtasan);
    }

    /**
     * Helper untuk membuat data pengajuan cuti bawahan
     */
    private function createCutiBawahan($overrides = [])
    {
        return Cuti::forceCreate(array_merge([
            'user_id' => $this->userPegawai->id,
            'id_pegawai' => $this->pegawaiBawahan->id,
            'nama' => $this->pegawaiBawahan->nama,
            'nip' => $this->pegawaiBawahan->nip,
            'jabatan' => $this->pegawaiBawahan->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'tahun' => date('Y'),
            'status' => 'Menunggu',
            'status_delegasi' => 'pending',
            'status_atasan' => 'pending',
            'status_pejabat' => 'pending',
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
            'pejabat_nama' => $this->pejabat->nama,
            'id_delegasi' => $this->rekanDelegasi->id,
        ], $overrides));
    }

    // =========================================================================
    // SKENARIO 1: VALIDASI KETERGANTUNGAN TAHAP PERSETUJUAN
    // =========================================================================
    public function test_atasan_tidak_dapat_menyetujui_cuti_tahap_dua_sebelum_menyetujui_delegasi_tahap_satu()
    {
        // Kondisi: Delegasi masih berstatus 'menunggu'
        $cuti = $this->createCutiBawahan(['status_delegasi' => 'pending']);

        // Aksi: Atasan mencoba langsung klik 'Setujui Cuti'
        $response = $this->post(route('atasan.approval.approve', $cuti->id));

        // Ekspektasi: Sistem memblokir dan mengembalikan error
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Silakan setujui delegasi terlebih dahulu.');
        
        $cutiFresh = Cuti::find($cuti->id);
        $this->assertEquals('pending', $cutiFresh->status_atasan);
    }

    // =========================================================================
    // SKENARIO 2 & 3: TOLAK DELEGASI
    // =========================================================================
    public function test_atasan_gagal_menolak_delegasi_jika_kolom_alasan_kosong()
    {
        $cuti = $this->createCutiBawahan();

        // Aksi: Menolak tanpa menyertakan alasan
        $response = $this->post(route('atasan.approval.tolakDelegasi', $cuti->id), [
            'catatan_tolak_delegasi' => '',
        ]);

        // Ekspektasi: Gagal validasi
        $response->assertSessionHasErrors(['catatan_tolak_delegasi']);
    }

    public function test_atasan_berhasil_menolak_delegasi_sistem_mengubah_status_dan_mengirim_notifikasi()
    {
        $cuti = $this->createCutiBawahan();

        // Aksi: Menolak dengan alasan yang valid (Hanya huruf dan spasi)
        $response = $this->post(route('atasan.approval.tolakDelegasi', $cuti->id), [
            'catatan_tolak_delegasi' => 'Delegasi sedang banyak tugas',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);
        
        // Ekspektasi: Status delegasi ditolak, status utama menjadi Revisi
        $this->assertEquals('ditolak', $cutiFresh->status_delegasi);
        $this->assertEquals('Revisi Delegasi', $cutiFresh->status);
        $this->assertEquals('Delegasi sedang banyak tugas', $cutiFresh->catatan_tolak_delegasi);

        // Ekspektasi: Notifikasi terkirim ke pemohon (bawahan)
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Revisi Delegasi Diperlukan',
        ]);
    }

    // =========================================================================
    // SKENARIO 4: SETUJUI DELEGASI
    // =========================================================================
    public function test_atasan_berhasil_menyetujui_delegasi_dan_membuka_akses_tahap_dua()
    {
        $cuti = $this->createCutiBawahan();

        // Aksi: Menyetujui delegasi
        $response = $this->post(route('atasan.approval.approveDelegasi', $cuti->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);
        $this->assertEquals('disetujui', $cutiFresh->status_delegasi);
    }

    // =========================================================================
    // SKENARIO 5 & 6: TOLAK PENGAJUAN CUTI TAHAP 2
    // =========================================================================
    public function test_atasan_gagal_menolak_cuti_utama_jika_kolom_alasan_kosong()
    {
        // Kondisi: Delegasi sudah disetujui (Tahap 2 terbuka)
        $cuti = $this->createCutiBawahan(['status_delegasi' => 'disetujui']);

        $response = $this->post(route('atasan.approval.reject', $cuti->id), [
            'catatan_tolak_atasan' => '',
        ]);

        $response->assertSessionHasErrors(['catatan_tolak_atasan']);
    }

    public function test_atasan_berhasil_menolak_cuti_utama_secara_final_beserta_alasan()
    {
        $cuti = $this->createCutiBawahan(['status_delegasi' => 'disetujui']);

        $response = $this->post(route('atasan.approval.reject', $cuti->id), [
            'catatan_tolak_atasan' => 'Ada audit internal minggu depan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);
        
        // Ekspektasi: Status berhenti di Ditolak
        $this->assertEquals('Ditolak', $cutiFresh->status);
        $this->assertEquals('ditolak', $cutiFresh->status_atasan);
        $this->assertEquals('Ada audit internal minggu depan', $cutiFresh->catatan_tolak_atasan);

        // Ekspektasi: Notifikasi penolakan dikirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Cuti Ditolak Atasan',
        ]);
    }

    // =========================================================================
    // SKENARIO 7: SETUJUI PENGAJUAN CUTI TAHAP 2 (SUKSES)
    // =========================================================================
    public function test_atasan_berhasil_menyetujui_cuti_utama_dan_sistem_meneruskan_ke_pejabat()
    {
        // Kondisi: Delegasi sudah disetujui
        $cuti = $this->createCutiBawahan(['status_delegasi' => 'disetujui']);

        $response = $this->post(route('atasan.approval.approve', $cuti->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);

        // Ekspektasi: Status berubah menjadi Disetujui Atasan dan siap dibaca Pejabat
        $this->assertEquals('Disetujui Atasan', $cutiFresh->status);
        $this->assertEquals('disetujui', $cutiFresh->status_atasan);

        // Cek Notifikasi ke Bawahan
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Cuti Disetujui Atasan',
        ]);

        // Cek Notifikasi diteruskan ke Pejabat
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPejabat->id,
            'title'   => 'Approval Cuti Diperlukan',
        ]);
    }

    // =========================================================================
    // SKENARIO 8: PENGAJUAN CUTI PRIBADI ATASAN (BYPASS DELEGASI & ATASAN)
    // =========================================================================
    public function test_atasan_dapat_mengajukan_cuti_pribadi_langsung_ke_tahap_pejabat_tanpa_delegasi()
    {
        // Aksi: Atasan mengisi form cuti miliknya sendiri
        $response = $this->post(route('atasan.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Liburan Keluarga',
            'tanggal_mulai' => Carbon::now()->addDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(12)->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiPribadi = Cuti::where('user_id', $this->userAtasan->id)->first();
        $this->assertNotNull($cutiPribadi);

        // Ekspektasi Bypass: 
        // id_delegasi harus NULL, status "Menunggu", status_atasan "pending", status_delegasi otomatis "disetujui"
        $this->assertNull($cutiPribadi->id_delegasi);
        $this->assertEquals('Menunggu', $cutiPribadi->status);
        $this->assertEquals('disetujui', $cutiPribadi->status_delegasi);
        $this->assertEquals('pending', $cutiPribadi->status_atasan);
    }

    // =========================================================================
    // SKENARIO 9: VALIDASI KUOTA CUTI PRIBADI ATASAN
    // =========================================================================
    public function test_sistem_menolak_pengajuan_cuti_pribadi_atasan_jika_melebihi_sisa_kuota()
    {
        // Buat Atasan seolah-olah sudah menghabiskan 10 hari cuti tahun ini
        Cuti::forceCreate([
            'user_id' => $this->userAtasan->id,
            'id_pegawai' => $this->atasan->id,
            'nama' => $this->atasan->nama,
            'nip' => $this->atasan->nip,
            'jabatan' => $this->atasan->jabatan,
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->subDays(20)->toDateString(),
            'tanggal_selesai' => Carbon::now()->subDays(10)->toDateString(),
            'jumlah_hari' => 10,
            'tahun' => date('Y'),
            'status' => 'Disetujui',
        ]);

        // Aksi: Atasan mencoba cuti lagi selama 5 hari (Sisa aslinya tinggal 2 hari)
        $response = $this->post(route('atasan.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Acara Besar',
            'tanggal_mulai' => Carbon::now()->addDays(10)->toDateString(),
            // +7 hari kalender = minimal 5 hari kerja
            'tanggal_selesai' => Carbon::now()->addDays(17)->toDateString(), 
        ]);

        // Ekspektasi: Sistem memblokir karena sisa kuota (2) < yang diajukan (5)
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Sisa jatah Anda', session('error'));
    }

    // =========================================================================
    // SKENARIO 10: VALIDASI DOUBLE BOOKING PRIBADI ATASAN
    // =========================================================================
    public function test_sistem_menolak_pengajuan_cuti_pribadi_atasan_jika_tanggal_bentrok()
    {
        $mulai = Carbon::now()->addDays(20)->toDateString();
        $selesai = Carbon::now()->addDays(23)->toDateString();

        // Kondisi: Atasan sudah punya pengajuan cuti di tanggal tersebut
        Cuti::forceCreate([
            'user_id' => $this->userAtasan->id,
            'id_pegawai' => $this->atasan->id,
            'nama' => $this->atasan->nama,
            'nip' => $this->atasan->nip,
            'jabatan' => $this->atasan->jabatan,
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'jumlah_hari' => 3,
            'tahun' => date('Y'),
            'status' => 'Disetujui', // Jadwal sudah aktif
        ]);

        // Aksi: Atasan mencoba mengajukan lagi di tanggal yang sama/beririsan
        $response = $this->post(route('atasan.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Keperluan Mendadak',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
        ]);

        // Ekspektasi: Sistem mendeteksi double booking
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah memiliki pengajuan cuti pada rentang tanggal tersebut', session('error'));
    }
}

/*
# Test Results: Atasan Approval (AtasanApprovalTest.php)

## File Location
`tests/Feature/Atasan/AtasanApprovalTest.php`

## Total Tests: 10 ✅ ALL PASSED

### Skenario 1: Validasi Keterkaitan Tahap Persetujuan (1 test)
✅ **test_atasan_tidak_dapat_menyetujui_cuti_tahap_dua_sebelum_menyetujui_delegasi_tahap_satu**
- Atasan tidak bisa approve cuti utama jika delegasi masih 'pending'
- Error message: "Silakan setujui delegasi terlebih dahulu."
- Route: `POST /atasan/approval/{id}/approve`

### Skenario 2 & 3: Penolakan Delegasi (2 tests)
✅ **test_atasan_gagal_menolak_delegasi_jika_kolom_alasan_kosong**
- Validasi gagal jika `catatan_tolak_delegasi` kosong
- Route: `POST /atasan/approval/{id}/tolakDelegasi`

✅ **test_atasan_berhasil_menolak_delegasi_sistem_mengubah_status_dan_mengirim_notifikasi**
- Status delegasi: 'ditolak', Status utama: 'Revisi Delegasi'
- Catatan penolakan disimpan
- Notifikasi dikirim ke pegawai bawahan
- Route: `POST /atasan/approval/{id}/tolakDelegasi`

### Skenario 4: Persetujuan Delegasi (1 test)
✅ **test_atasan_berhasil_menyetujui_delegasi_dan_membuka_akses_tahap_dua**
- Status delegasi berubah ke 'disetujui'
- Membuka akses untuk approve cuti utama
- Route: `POST /atasan/approval/{id}/approveDelegasi`

### Skenario 5 & 6: Penolakan Cuti Utama (2 tests)
✅ **test_atasan_gagal_menolak_cuti_utama_jika_kolom_alasan_kosong**
- Validasi gagal jika `catatan_tolak_atasan` kosong
- Route: `POST /atasan/approval/{id}/reject`

✅ **test_atasan_berhasil_menolak_cuti_utama_secara_final_beserta_alasan**
- Status final: 'Ditolak', status_atasan: 'ditolak'
- Catatan penolakan disimpan
- Notifikasi dikirim ke pegawai bawahan
- Route: `POST /atasan/approval/{id}/reject`

### Skenario 7: Persetujuan Cuti Utama (1 test)
✅ **test_atasan_berhasil_menyetujui_cuti_utama_dan_sistem_meneruskan_ke_pejabat**
- Status: 'Disetujui Atasan', status_atasan: 'disetujui'
- Notifikasi ke pegawai bawahan dan pejabat
- Route: `POST /atasan/approval/{id}/approve`

### Skenario 8: Pengajuan Cuti Pribadi Atasan (1 test)
✅ **test_atasan_dapat_mengajukan_cuti_pribadi_langsung_ke_tahap_pejabat_tanpa_delegasi**
- Bypass delegasi dan approval atasan
- Status: 'Menunggu', status_delegasi: 'disetujui', status_atasan: 'pending'
- Route: `POST /atasan/cuti/store`

### Skenario 9: Validasi Kuota Cuti Pribadi (1 test)
✅ **test_sistem_menolak_pengajuan_cuti_pribadi_atasan_jika_melebihi_sisa_kuota**
- Validasi sisa kuota (12 hari default - 10 sudah digunakan = 2 hari)
- Error message: "Sisa jatah Anda"
- Route: `POST /atasan/cuti/store`

### Skenario 10: Validasi Double Booking Pribadi (1 test)
✅ **test_sistem_menolak_pengajuan_cuti_pribadi_atasan_jika_tanggal_bentrok**
- Deteksi overlap tanggal dengan cuti yang sudah disetujui
- Error message: "sudah memiliki pengajuan cuti pada rentang tanggal tersebut"
- Route: `POST /atasan/cuti/store`

## Test Status Summary
- **Total:** 10
- **Passed:** 10 ✅
- **Failed:** 0

## Key Technical Details
- **Database Schema Issues Fixed:**
  - Column `status_delegasi`: enum ['pending', 'disetujui', 'ditolak'] (bukan 'menunggu')
  - Added required fields: `nip`, `jabatan`, `tanggal_mulai`, `tanggal_selesai` to all Cuti::forceCreate() calls
  - Proper date handling for historical cuti records

- **Workflow Logic Validated:**
  - Two-phase approval: Delegasi → Cuti Utama
  - Atasan bypass for personal leave
  - Proper status transitions and notifications
  - Validation for required fields and business rules

- **Authentication:** `actingAs($userAtasan)` for role-based testing
- **Middleware:** `withoutMiddleware()` for testing
- **Relationships:** Proper foreign key constraints between pegawai, cuti, and notifications

## Business Logic Validated
- Sequential approval process (delegasi first, then cuti)
- Atasan can reject delegasi with reasons
- Final rejection stops the process
- Atasan personal leave bypasses delegation
- Kuota validation for personal leave
- Double booking prevention
- Comprehensive notification system
*/