<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;

class ManajemenCutiAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());
    }

    /**
     * Helper untuk membuat data pegawai
     */
    private function buatPegawai(string $nama, string $nip, string $unitKerja = 'Bidang A'): Pegawai
    {
        return Pegawai::forceCreate([
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => 'Staf',
            'unit_kerja' => $unitKerja,
            'atasan' => 'Atasan',
            'pemberi_cuti' => 'Pejabat',
            'status' => 'aktif',
        ]);
    }

    /**
     * Helper untuk membuat data cuti
     */
    private function buatCuti(array $attributes = []): Cuti
    {
        $user = $attributes['user'] ?? User::factory()->create();
        $pegawai = $attributes['pegawai'] ?? $this->buatPegawai('Test Pegawai', '999999999999999999');

        return Cuti::forceCreate([
            'user_id' => $user->id,
            'id_pegawai' => $pegawai->id,
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'jabatan' => $pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => $attributes['jenis_cuti'] ?? 'Tahunan',
            'tanggal_mulai' => $attributes['tanggal_mulai'] ?? Carbon::now()->startOfDay(),
            'tanggal_selesai' => $attributes['tanggal_selesai'] ?? Carbon::now()->addDays(2)->endOfDay(),
            'jumlah_hari' => $attributes['jumlah_hari'] ?? 2,
            'tahun' => $attributes['tahun'] ?? Carbon::now()->year,
            'keterangan' => $attributes['keterangan'] ?? 'Cuti dinas',
            'status' => $attributes['status'] ?? 'Menunggu',
            'id_atasan_langsung' => $attributes['id_atasan_langsung'] ?? null,
            'id_pejabat_pemberi_cuti' => $attributes['id_pejabat_pemberi_cuti'] ?? null,
            'atasan_nama' => $attributes['atasan_nama'] ?? 'Atasan',
            'pejabat_nama' => $attributes['pejabat_nama'] ?? 'Pejabat',
            'catatan_tolak_pejabat' => $attributes['catatan_tolak_pejabat'] ?? null,
            'status_pejabat' => $attributes['status_pejabat'] ?? 'pending',
        ]);
    }

    public function test_admin_dapat_memfilter_cuti_berdasarkan_pencarian_status_dan_rentang_tanggal()
    {
        $pegawaiAnton = $this->buatPegawai('Anton', '111111111111111111');
        $this->buatCuti([
            'pegawai' => $pegawaiAnton,
            'tanggal_mulai' => Carbon::create(2026, 6, 1),
            'tanggal_selesai' => Carbon::create(2026, 6, 3),
            'status' => 'Ditolak',
            'keterangan' => 'Cuti karena alasan penting',
            'tahun' => 2026,
        ]);

        $pegawaiBudi = $this->buatPegawai('Budi', '222222222222222222');
        $this->buatCuti([
            'pegawai' => $pegawaiBudi,
            'tanggal_mulai' => Carbon::create(2026, 7, 1),
            'tanggal_selesai' => Carbon::create(2026, 7, 4),
            'status' => 'Disetujui',
            'keterangan' => 'Cuti reguler',
            'tahun' => 2026,
        ]);

        $response = $this->get(route('admin.cuti.index', [
            'search' => 'Anton',
            'status' => 'Ditolak',
            'tanggal_dari' => '2026-06-01',
            'tanggal_sampai' => '2026-06-05',
        ]));

        $response->assertOk();
        $response->assertViewHas('cuti');
        $response->assertSee('Anton');
        $response->assertDontSee('Budi');
    }

    public function test_admin_dapat_melihat_halaman_detail_cuti()
    {
        $cuti = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Citra', '333333333333333333'),
            'jenis_cuti' => 'Penting',
            'keterangan' => 'Menghadiri acara keluarga',
            'tanggal_mulai' => Carbon::create(2026, 8, 1),
            'tanggal_selesai' => Carbon::create(2026, 8, 3),
            'status' => 'Disetujui',
        ]);

        $response = $this->get(route('admin.cuti.show', $cuti->id));

        $response->assertOk();
        $response->assertSeeText($cuti->pegawai->nama);
        $response->assertSeeText($cuti->pegawai->nip);
        $response->assertSeeText('Jenis Cuti');
        $response->assertSeeText('Menghadiri acara keluarga');
    }

    public function test_halaman_persetujuan_admin_menampilkan_permintaan_pejabat_yang_tertunda()
    {
        $pending = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Dewi', '444444444444444444'),
            'status' => 'Disetujui Atasan',
        ]);

        $this->buatCuti([
            'pegawai' => $this->buatPegawai('Erik', '555555555555555555'),
            'status' => 'Disetujui',
        ]);

        $response = $this->get(route('admin.cuti.approval'));

        $response->assertOk();
        $response->assertViewHas('stats', ['menunggu' => 1, 'disetujui' => 1, 'ditolak' => 0]);
        $response->assertViewHas('pengajuan', function ($pengajuan) use ($pending) {
            return $pengajuan->count() === 1 && $pengajuan->first()->id === $pending->id;
        });
        $response->assertSeeText('Disetujui Atasan');
    }

    public function test_admin_tidak_dapat_menyetujui_cuti_tanpa_konfirmasi_izin_pejabat()
    {
        $cuti = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Fajar', '666666666666666666'),
            'status' => 'Disetujui Atasan',
        ]);

        $response = $this->post(route('admin.cuti.approve', $cuti->id), [
            'pejabat_confirmed' => false,
        ]);

        $response->assertSessionHas('error', 'Gagal! Anda harus memberikan konfirmasi izin dari Pejabat terlebih dahulu.');
        $this->assertSame('Disetujui Atasan', $cuti->fresh()->status);
    }

    public function test_admin_dapat_menyetujui_cuti_setelah_konfirmasi_izin_pejabat()
    {
        $cuti = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Gita', '777777777777777777'),
            'status' => 'Disetujui Atasan',
            'user' => User::factory()->create(),
        ]);

        $response = $this->post(route('admin.cuti.approve', $cuti->id), [
            'pejabat_confirmed' => true,
        ]);

        $response->assertSessionHas('success', 'Pengajuan cuti telah disetujui atas izin Pejabat.');
        $this->assertSame('Disetujui', $cuti->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $cuti->user_id,
            'title' => 'Cuti Disetujui',
        ]);
    }

    public function test_penolakan_cuti_oleh_admin_memerlukan_alasan_yang_valid()
    {
        $cuti = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Hani', '888888888888888888'),
            'status' => 'Disetujui Atasan',
            'user' => User::factory()->create(),
        ]);

        $response = $this->post(route('admin.cuti.reject', $cuti->id), [
            'pejabat_confirmed' => true,
            'catatan_penolakan' => '',
        ]);

        $response->assertSessionHasErrors('catatan_penolakan');
        $this->assertSame('Disetujui Atasan', $cuti->fresh()->status);
    }

    public function test_admin_dapat_menolak_cuti_dengan_konfirmasi_dan_alasan_yang_jelas()
    {
        $cuti = $this->buatCuti([
            'pegawai' => $this->buatPegawai('Ika', '999999999999999999'),
            'status' => 'Disetujui Atasan',
            'user' => User::factory()->create(),
        ]);

        $response = $this->post(route('admin.cuti.reject', $cuti->id), [
            'pejabat_confirmed' => true,
            'catatan_penolakan' => 'Tidak memenuhi syarat',
        ]);

        $response->assertSessionHas('success', 'Pengajuan cuti berhasil ditolak berdasarkan arahan Pejabat.');
        $this->assertSame('Ditolak', $cuti->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $cuti->user_id,
            'title' => 'Cuti Ditolak Pejabat',
        ]);
    }
}