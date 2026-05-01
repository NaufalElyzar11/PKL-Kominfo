<?php

namespace Tests\Feature\Pegawai;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DelegasiPegawaiTest extends TestCase
{
    use RefreshDatabase;

    protected $pegawai;
    protected $user;
    protected $atasan;
    protected $userAtasan;
    protected $rekanSebidang1;
    protected $userRekan1;
    protected $rekanSebidang2;
    protected $userRekan2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());

        // Setup Atasan
        $this->atasan = Pegawai::forceCreate([
            'nama' => 'Atasan Langsung',
            'nip' => '199001012020121101',
            'jabatan' => 'Atasan',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'atasan' => 'Kepala',
            'pemberi_cuti' => 'Pejabat',
        ]);

        $this->userAtasan = User::factory()->create([
            'name' => 'Atasan Langsung',
            'role' => 'atasan',
            'id_pegawai' => $this->atasan->id,
        ]);

        // Setup Pegawai Utama (Pengaju)
        $this->pegawai = Pegawai::forceCreate([
            'nama' => 'Pegawai Utama',
            'nip' => '199001012020121102',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'id_atasan_langsung' => $this->atasan->id,
            'atasan' => $this->atasan->nama,
            'pemberi_cuti' => 'Pejabat',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Pegawai Utama',
            'role' => 'pegawai',
            'id_pegawai' => $this->pegawai->id,
        ]);

        // Setup Rekan Sebidang 1 (Bebas / Tersedia)
        $this->rekanSebidang1 = Pegawai::forceCreate([
            'nama' => 'Rekan Sebidang Satu',
            'nip' => '199001012020121103',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'id_atasan_langsung' => $this->atasan->id,
            'atasan' => $this->atasan->nama,
            'pemberi_cuti' => 'Pejabat',
        ]);

        $this->userRekan1 = User::factory()->create([
            'name' => 'Rekan Sebidang Satu',
            'role' => 'pegawai',
            'id_pegawai' => $this->rekanSebidang1->id,
        ]);

        // Setup Rekan Sebidang 2 (Akan dimanipulasi statusnya)
        $this->rekanSebidang2 = Pegawai::forceCreate([
            'nama' => 'Rekan Sebidang Dua',
            'nip' => '199001012020121104',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang A',
            'status' => 'aktif',
            'id_atasan_langsung' => $this->atasan->id,
            'atasan' => $this->atasan->nama,
            'pemberi_cuti' => 'Pejabat',
        ]);

        $this->userRekan2 = User::factory()->create([
            'name' => 'Rekan Sebidang Dua',
            'role' => 'pegawai',
            'id_pegawai' => $this->rekanSebidang2->id,
        ]);

        // Secara default login sebagai Pegawai Utama
        $this->actingAs($this->user);
    }

    // ==================== SKENARIO: PENGECEKAN DELEGASI YANG TERSEDIA (GET API) ====================

    public function test_menampilkan_rekan_sebidang_tersedia_dan_mengecualikan_diri_sendiri()
    {
        $response = $this->get(route('pegawai.cuti.available-delegates', [
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
        ]));

        $response->assertStatus(200);
        $delegates = $response->json();
        
        $this->assertGreaterThanOrEqual(2, count($delegates), 'Minimal ada 2 rekan yang tersedia');
        
        $namaRekan = array_column($delegates, 'nama');
        $this->assertContains('Rekan Sebidang Satu', $namaRekan);
        $this->assertContains('Rekan Sebidang Dua', $namaRekan);
        $this->assertNotContains('Pegawai Utama', $namaRekan, 'Diri sendiri tidak boleh muncul di pilihan');
    }

    public function test_memiliki_informasi_lengkap_pada_daftar_delegasi()
    {
        $response = $this->get(route('pegawai.cuti.available-delegates', [
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
        ]));

        $response->assertStatus(200);
        $delegates = $response->json();
        
        foreach ($delegates as $delegate) {
            $this->assertArrayHasKey('id', $delegate);
            $this->assertArrayHasKey('nama', $delegate);
            $this->assertArrayHasKey('jabatan', $delegate);
        }
    }

    public function test_mengecualikan_rekan_yang_sedang_cuti_pada_pencarian_delegasi()
    {
        Cuti::forceCreate([
            'user_id' => $this->userRekan2->id,
            'id_pegawai' => $this->rekanSebidang2->id,
            'nama' => $this->rekanSebidang2->nama,
            'nip' => $this->rekanSebidang2->nip,
            'jabatan' => $this->rekanSebidang2->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Sedang Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->get(route('pegawai.cuti.available-delegates', [
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
        ]));

        $response->assertStatus(200);
        $namaRekan = array_column($response->json(), 'nama');
        $this->assertNotContains('Rekan Sebidang Dua', $namaRekan, 'Rekan yang sedang cuti harus dikecualikan');
    }

    public function test_mengecualikan_rekan_yang_menjadi_delegasi_orang_lain_pada_pencarian()
    {
        Cuti::forceCreate([
            'user_id' => $this->userAtasan->id,
            'id_pegawai' => $this->atasan->id,
            'nama' => $this->atasan->nama,
            'nip' => $this->atasan->nip,
            'jabatan' => $this->atasan->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Atasan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'status' => 'Disetujui',
            'id_delegasi' => $this->rekanSebidang2->id,
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->get(route('pegawai.cuti.available-delegates', [
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
        ]));

        $response->assertStatus(200);
        $namaRekan = array_column($response->json(), 'nama');
        $this->assertNotContains('Rekan Sebidang Dua', $namaRekan, 'Rekan yang sudah jadi delegasi harus dikecualikan');
    }

    public function test_mengecualikan_rekan_yang_berstatus_revisi_delegasi_pada_pencarian()
    {
        Cuti::forceCreate([
            'user_id' => $this->userRekan2->id,
            'id_pegawai' => $this->rekanSebidang2->id,
            'nama' => $this->rekanSebidang2->nama,
            'nip' => $this->rekanSebidang2->nip,
            'jabatan' => $this->rekanSebidang2->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Revisi Delegasi',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'status' => 'Revisi Delegasi',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->get(route('pegawai.cuti.available-delegates', [
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
        ]));

        $response->assertStatus(200);
        $namaRekan = array_column($response->json(), 'nama');
        $this->assertNotContains('Rekan Sebidang Dua', $namaRekan, 'Rekan yang sedang antre Revisi Delegasi harus dikecualikan');
    }

    // ==================== SKENARIO: PENGAJUAN CUTI & VALIDASI BACKEND (POST SUBMIT) ====================

    public function test_pengajuan_cuti_berhasil_dan_mengirimkan_notifikasi_ke_delegasi()
    {
        $response = $this->post(route('pegawai.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan keluarga',
            'id_delegasi' => $this->rekanSebidang1->id,
        ]);

        $response->assertRedirect();
        
        $cuti = Cuti::where('user_id', $this->user->id)->first();
        $this->assertNotNull($cuti);
        $this->assertEquals($this->rekanSebidang1->id, $cuti->id_delegasi);
        $this->assertEquals('Menunggu', $cuti->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->userRekan1->id,
            'title' => 'Permintaan Delegasi Tugas',
        ]);
    }

    public function test_menolak_pengajuan_jika_petugas_pengganti_ternyata_sedang_cuti()
    {
        Cuti::forceCreate([
            'user_id' => $this->userRekan1->id,
            'id_pegawai' => $this->rekanSebidang1->id,
            'nama' => $this->rekanSebidang1->nama,
            'nip' => $this->rekanSebidang1->nip,
            'jabatan' => $this->rekanSebidang1->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Sedang Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Test delegasi bentrok',
            'id_delegasi' => $this->rekanSebidang1->id,
        ]);

        $response->assertSessionHas('error'); 
    }

    public function test_menolak_pengajuan_jika_pengaju_sudah_terdaftar_sebagai_pengganti_orang_lain()
    {
        Cuti::forceCreate([
            'user_id' => $this->userRekan1->id,
            'id_pegawai' => $this->rekanSebidang1->id,
            'nama' => $this->rekanSebidang1->nama,
            'nip' => $this->rekanSebidang1->nip,
            'jabatan' => $this->rekanSebidang1->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Pengaju Sibuk',
            'id_delegasi' => $this->pegawai->id, // Pengaju jadi delegasi
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Pengaju sibuk',
            'id_delegasi' => $this->rekanSebidang2->id,
        ]);

        $response->assertSessionHas('error'); 
    }

    public function test_menolak_pengajuan_jika_memilih_diri_sendiri_sebagai_pengganti()
    {
        $response = $this->post(route('pegawai.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Pilih diri sendiri',
            'id_delegasi' => $this->pegawai->id, 
        ]);

        $response->assertSessionHas('error'); 
    }

    public function test_menolak_pengajuan_jika_delegasi_berasal_dari_bidang_lain()
    {
        $bidangLain = Pegawai::forceCreate([
            'nama' => 'Pegawai Bidang Lain',
            'nip' => '199001012020121105',
            'jabatan' => 'Staf Admin',
            'unit_kerja' => 'Bidang B',
            'status' => 'aktif',
            'id_atasan_langsung' => 999, 
            'atasan' => 'Atasan Lain',
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Delegasi beda bidang',
            'id_delegasi' => $bidangLain->id,
        ]);

        $response->assertSessionHas('error'); 
    }

    // ==================== SKENARIO: PERSETUJUAN & PENOLAKAN DELEGASI OLEH ATASAN ====================

    public function test_atasan_berhasil_menyetujui_delegasi_pegawai()
    {
        $cuti = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'id_delegasi' => $this->rekanSebidang1->id,
            'status_delegasi' => 'pending',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $this->actingAs($this->userAtasan);
        $response = $this->post(route('atasan.approval.approveDelegasi', $cuti->id));

        $response->assertRedirect();
        $this->assertEquals('disetujui', Cuti::find($cuti->id)->status_delegasi);
    }

    public function test_atasan_berhasil_menolak_delegasi_dengan_memberikan_alasan()
    {
        $cuti = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'id_delegasi' => $this->rekanSebidang1->id,
            'status_delegasi' => 'pending',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $this->actingAs($this->userAtasan);
        $response = $this->post(route('atasan.approval.tolakDelegasi', $cuti->id), [
            'catatan_tolak_delegasi' => 'Tidak bisa karena ada rapat penting',
        ]);

        $response->assertRedirect();
        $cutiUpdated = Cuti::find($cuti->id);
        $this->assertEquals('ditolak', $cutiUpdated->status_delegasi);
        $this->assertEquals('Revisi Delegasi', $cutiUpdated->status);
        $this->assertEquals('Tidak bisa karena ada rapat penting', $cutiUpdated->catatan_tolak_delegasi);
    }

    public function test_validasi_alasan_penolakan_delegasi_tidak_boleh_mengandung_angka()
    {
        $cuti = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'id_delegasi' => $this->rekanSebidang1->id,
            'status_delegasi' => 'pending',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $this->actingAs($this->userAtasan);
        $response = $this->post(route('atasan.approval.tolakDelegasi', $cuti->id), [
            'catatan_tolak_delegasi' => 'Tidak bisa 123', 
        ]);

        $response->assertSessionHasErrors('catatan_tolak_delegasi');
    }

    // ==================== SKENARIO: PENANGANAN REVISI OLEH PEGAWAI ====================

    public function test_sistem_menampilkan_pengajuan_dengan_status_revisi_delegasi()
    {
        $cuti = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'id_delegasi' => $this->rekanSebidang1->id,
            'status' => 'Revisi Delegasi',
            'status_delegasi' => 'ditolak',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->get(route('pegawai.cuti.index'));

        $response->assertStatus(200);
        $pengajuan = $response->viewData('cuti');
        
        $found = collect($pengajuan->items())->contains(function ($item) use ($cuti) {
            return $item->id === $cuti->id && $item->status === 'Revisi Delegasi';
        });
        
        $this->assertTrue($found, 'Status Revisi Delegasi harus muncul di tabel pengajuan Pegawai');
    }

    public function test_pegawai_dapat_mengubah_delegasi_saat_status_pengajuan_direvisi()
    {
        $cuti = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Alamat Default',
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'id_delegasi' => $this->rekanSebidang1->id,
            'status' => 'Revisi Delegasi',
            'status_delegasi' => 'ditolak',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->atasan->id,
            'atasan_nama' => $this->atasan->nama,
            'id_pejabat_pemberi_cuti' => null,
            'pejabat_nama' => '-',
        ]);

        $response = $this->patch(route('pegawai.cuti.update', $cuti->id), [
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(7)->toDateString(),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan',
            'id_delegasi' => $this->rekanSebidang2->id,
        ]);

        $response->assertRedirect();
        
        $cutiUpdated = Cuti::find($cuti->id);
        $this->assertEquals($this->rekanSebidang2->id, $cutiUpdated->id_delegasi);
        $this->assertEquals('Menunggu', $cutiUpdated->status); 
    }
}