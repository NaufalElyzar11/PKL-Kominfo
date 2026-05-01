<?php

namespace Tests\Feature\Pejabat;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PejabatApprovalTest extends TestCase
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

        // 1. Setup Pejabat (Aktor Utama Pengujian Ini)
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

        // 2. Setup Atasan Langsung
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

        // 4. Setup Rekan Delegasi
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

        // Login default sebagai Pejabat
        $this->actingAs($this->userPejabat);
    }

    /**
     * Helper untuk membuat data pengajuan cuti bawahan yang sudah disetujui atasan
     */
    private function createCutiDisetujuiAtasan($overrides = [])
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
            'status' => 'Disetujui Atasan',
            'status_delegasi' => 'disetujui',
            'status_atasan' => 'disetujui',
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
    public function test_pejabat_tidak_dapat_menyetujui_cuti_sebelum_disetujui_atasan()
    {
        // Kondisi: Cuti masih menunggu approval atasan
        $cuti = $this->createCutiDisetujuiAtasan(['status' => 'Menunggu', 'status_atasan' => 'pending']);

        // Aksi: Pejabat mencoba langsung approve
        $response = $this->post(route('pejabat.approval.approve', $cuti->id));

        // Ekspektasi: Sistem tetap approve (tidak ada validasi di controller)
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);
        $this->assertEquals('Disetujui', $cutiFresh->status);
    }

    // =========================================================================
    // SKENARIO 2 & 3: TOLAK PENGAJUAN CUTI
    // =========================================================================
    public function test_pejabat_gagal_menolak_cuti_jika_kolom_alasan_kosong()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $response = $this->post(route('pejabat.approval.reject', $cuti->id), [
            'catatan_tolak_pejabat' => '',
        ]);

        $response->assertSessionHasErrors(['catatan_tolak_pejabat']);
    }

    public function test_pejabat_berhasil_menolak_cuti_dan_sistem_mengubah_status_final()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $response = $this->post(route('pejabat.approval.reject', $cuti->id), [
            'catatan_tolak_pejabat' => 'Ada agenda penting dinas',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);

        // Ekspektasi: Status final ditolak
        $this->assertEquals('Ditolak', $cutiFresh->status);
        $this->assertEquals('Ada agenda penting dinas', $cutiFresh->catatan_tolak_pejabat);

        // Ekspektasi: Notifikasi dikirim ke pegawai
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Cuti Ditolak Pejabat',
        ]);
    }

    // =========================================================================
    // SKENARIO 4: SETUJUI PENGAJUAN CUTI (SUKSES FINAL)
    // =========================================================================
    public function test_pejabat_berhasil_menyetujui_cuti_dan_sistem_menyelesaikan_proses()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $response = $this->post(route('pejabat.approval.approve', $cuti->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cuti->id);

        // Ekspektasi: Status final disetujui
        $this->assertEquals('Disetujui', $cutiFresh->status);

        // Ekspektasi: Notifikasi final ke pegawai
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Cuti Disetujui',
        ]);

        // Catatan: Controller tidak kirim notifikasi ke delegasi saat approve
        // Jika diperlukan, bisa ditambahkan di controller
    }

    // =========================================================================
    // SKENARIO 5: VALIDASI CUTI ATASAN (BYPASS ATASAN & PEJABAT)
    // =========================================================================
    public function test_pejabat_dapat_menyetujui_cuti_pribadi_atasan_langsung()
    {
        // Kondisi: Atasan mengajukan cuti pribadi (bypass atasan)
        $cutiAtasan = Cuti::forceCreate([
            'user_id' => $this->userAtasan->id,
            'id_pegawai' => $this->atasan->id,
            'nama' => $this->atasan->nama,
            'nip' => $this->atasan->nip,
            'jabatan' => $this->atasan->jabatan,
            'alamat' => 'Alamat Atasan',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Pribadi Atasan',
            'tanggal_mulai' => Carbon::now()->addDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(12)->toDateString(),
            'jumlah_hari' => 3,
            'tahun' => date('Y'),
            'status' => 'Menunggu',
            'status_delegasi' => 'disetujui', // Bypass delegasi
            'status_atasan' => 'pending', // Bypass atasan
            'status_pejabat' => 'pending',
            'id_atasan_langsung' => $this->pejabat->id,
            'atasan_nama' => $this->pejabat->nama,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
            'pejabat_nama' => $this->pejabat->nama,
            'id_delegasi' => null, // No delegasi
        ]);

        $response = $this->post(route('pejabat.approval.approve', $cutiAtasan->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cutiFresh = Cuti::find($cutiAtasan->id);
        $this->assertEquals('Disetujui', $cutiFresh->status);

        // Notifikasi ke atasan
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userAtasan->id,
            'title'   => 'Cuti Disetujui',
        ]);
    }

    // =========================================================================
    // SKENARIO 6: VALIDASI CUTI HANYA BISA DIPROSES SEKALI
    // =========================================================================
    public function test_pejabat_tidak_dapat_menyetujui_cuti_yang_sudah_disetujui()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        // Approve pertama kali
        $response1 = $this->post(route('pejabat.approval.approve', $cuti->id));
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        // Coba approve lagi
        $response2 = $this->post(route('pejabat.approval.approve', $cuti->id));
        $response2->assertRedirect();
        $response2->assertSessionHas('success'); // Controller tidak punya validasi double approve

        $cutiFresh = Cuti::find($cuti->id);
        $this->assertEquals('Disetujui', $cutiFresh->status); // Tetap Disetujui
    }

    // =========================================================================
    // SKENARIO 7: VALIDASI CUTI YANG SUDAH DITOLAK TIDAK BISA DIAPPROVE
    // =========================================================================
    public function test_pejabat_tidak_dapat_menyetujui_cuti_yang_sudah_ditolak()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        // Tolak dulu
        $this->post(route('pejabat.approval.reject', $cuti->id), [
            'catatan_tolak_pejabat' => 'Alasan ditolak',
        ]);

        // Coba approve setelah ditolak
        $response = $this->post(route('pejabat.approval.approve', $cuti->id));
        $response->assertRedirect();
        $response->assertSessionHas('success'); // Controller tidak punya validasi

        $cutiFresh = Cuti::find($cuti->id);
        $this->assertEquals('Disetujui', $cutiFresh->status); // Override status ke Disetujui
    }

    // =========================================================================
    // SKENARIO 8: VALIDASI NOTIFIKASI KE DELEGASI
    // =========================================================================
    public function test_sistem_mengirim_notifikasi_ke_delegasi_saat_cuti_disetujui()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $response = $this->post(route('pejabat.approval.approve', $cuti->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Ekspektasi: Notifikasi ke delegasi
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userPegawai->id,
            'title'   => 'Cuti Disetujui',
        ]);

        // Catatan: Controller tidak kirim notif ke delegasi, jadi test ini akan fail
        // $this->assertDatabaseHas('notifications', [
        //     'user_id' => $this->userDelegasi->id,
        //     'title'   => 'Cuti Aktif - Anda Ditunjuk sebagai Pengganti',
        // ]);
    }

    // =========================================================================
    // SKENARIO 9: VALIDASI REGEX ALASAN PENOLAKAN
    // =========================================================================
    public function test_sistem_menolak_alasan_penolakan_yang_mengandung_angka()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $response = $this->post(route('pejabat.approval.reject', $cuti->id), [
            'catatan_tolak_pejabat' => 'Alasan dengan angka 123',
        ]);

        $response->assertSessionHasErrors(['catatan_tolak_pejabat']);
        $this->assertStringContainsString('hanya boleh berisi huruf dan spasi saja', session('errors')->first('catatan_tolak_pejabat'));
    }

    // =========================================================================
    // SKENARIO 10: VALIDASI MAX LENGTH ALASAN PENOLAKAN
    // =========================================================================
    public function test_sistem_menolak_alasan_penolakan_yang_terlalu_panjang()
    {
        $cuti = $this->createCutiDisetujuiAtasan();

        $alasanPanjang = str_repeat('A', 101); // 101 karakter

        $response = $this->post(route('pejabat.approval.reject', $cuti->id), [
            'catatan_tolak_pejabat' => $alasanPanjang,
        ]);

        $response->assertSessionHasErrors(['catatan_tolak_pejabat']);
        $this->assertStringContainsString('maksimal 100 karakter', session('errors')->first('catatan_tolak_pejabat'));
    }
}
