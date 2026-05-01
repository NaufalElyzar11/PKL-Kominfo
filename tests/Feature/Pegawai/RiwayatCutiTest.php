<?php

namespace Tests\Feature\Pegawai;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RiwayatCutiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());

        // Setup pegawai baru untuk test
        $this->pegawai = Pegawai::forceCreate([
            'nama' => 'John Doe',
            'nip' => '199001012020121001',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'status' => 'aktif',
            'atasan' => 'Atasan',
            'pemberi_cuti' => 'Pejabat',
        ]);

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'role' => 'pegawai',
            'id_pegawai' => $this->pegawai->id,
        ]);

        $this->actingAs($this->user);
    }

    // ==================== SKENARIO 1: PENANGANAN DATA KOSONG ====================

    public function test_penanganan_data_kosong_pegawai_baru_tidak_ada_riwayat_cuti()
    {
        // Arrange: Pegawai baru tanpa data riwayat cuti apapun
        // Cuti dimulai kosong (sudah terjamin dari setUp)

        // Act: Akses halaman riwayat cuti
        $response = $this->get(route('pegawai.cuti.index'));

        // Assert: Sistem harus menampilkan halaman tanpa error
        $response->assertStatus(200);
        $response->assertViewHas('riwayat'); // View harus memiliki riwayat
        
        // Riwayat seharusnya kosong/paginated kosong
        $riwayat = $response->viewData('riwayat');
        $this->assertEmpty($riwayat->items(), 'Riwayat harus kosong untuk pegawai baru');
    }

    public function test_penanganan_data_kosong_tidak_ada_layar_putih_atau_error()
    {
        // Arrange: Pastikan tidak ada data cuti
        Cuti::where('user_id', $this->user->id)->delete();

        // Act
        $response = $this->get(route('pegawai.cuti.index'));

        // Assert: Halaman harus responsif dan tidak error
        $response->assertStatus(200);
        $response->assertViewHas('pegawai');
        $response->assertViewHas('riwayat');
        $response->assertViewHas('warningMessage');
    }

    public function test_penanganan_data_kosong_tab_riwayat_tampil_dengan_benar()
    {
        // Act: Buka halaman dengan tahun yang belum memiliki data
        $response = $this->get(route('pegawai.cuti.index', ['tahun' => 2025]));

        // Assert
        $response->assertStatus(200);
        $riwayat = $response->viewData('riwayat');
        // Pastikan riwayat merupakan paginator dan kosong
        $this->assertInstanceOf(
            \Illuminate\Pagination\LengthAwarePaginator::class,
            $riwayat
        );
        $this->assertEquals(0, $riwayat->total());
    }

    // ==================== SKENARIO 2: AKURASI TAMPILAN CATATAN PENOLAKAN ====================
    public function test_sistem_memuat_multiple_catatan_penolakan_secara_akurat()
    {
        // Arrange: Buat beberapa cuti ditolak dengan catatan berbeda
        $cuti1 = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 1',
            'status' => 'Ditolak',
            'catatan_penolakan' => 'Alasan pertama',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        $cuti2 = Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(20),
            'tanggal_selesai' => Carbon::now()->addDays(22),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 2',
            'status' => 'Ditolak',
            'catatan_penolakan' => 'Alasan kedua berbeda',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act
        $response = $this->get(route('pegawai.cuti.index'));

        // Assert: Kedua catatan harus berbeda dan tepat
        $response->assertStatus(200);
        $riwayat = $response->viewData('riwayat');
        
        $catatan1Found = false;
        $catatan2Found = false;
        
        foreach ($riwayat->items() as $item) {
            if ($item->id === $cuti1->id) {
                $catatan1Found = true;
                $this->assertEquals('Alasan pertama', $item->catatan_penolakan);
            }
            if ($item->id === $cuti2->id) {
                $catatan2Found = true;
                $this->assertEquals('Alasan kedua berbeda', $item->catatan_penolakan);
            }
        }
        
        $this->assertTrue($catatan1Found && $catatan2Found, 'Kedua catatan harus ditemukan dengan benar');
    }

    // ==================== SKENARIO 3: EXPORT LAPORAN RIWAYAT ====================

    public function test_export_laporan_riwayat_berhasil_download_file_excel()
    {
        // Arrange: Buat 5 data riwayat cuti
        for ($i = 1; $i <= 5; $i++) {
            Cuti::forceCreate([
                'user_id' => $this->user->id,
                'nama' => $this->pegawai->nama,
                'nip' => $this->pegawai->nip,
                'jabatan' => $this->pegawai->jabatan,
                'alamat' => 'Jl. Test',
                'jenis_cuti' => 'Tahunan',
                'tanggal_mulai' => Carbon::now()->addDays($i * 5),
                'tanggal_selesai' => Carbon::now()->addDays($i * 5 + 2),
                'jumlah_hari' => 3,
                'keterangan' => "Liburan ke-{$i}",
                'status' => 'Disetujui',
                'tahun' => date('Y'),
                'id_atasan_langsung' => null,
                'id_pejabat_pemberi_cuti' => null,
                'atasan_nama' => 'Atasan',
                'pejabat_nama' => '-',
            ]);
        }

        // Act: Export laporan
        $response = $this->get(route('pegawai.cuti.export-excel', ['tahun' => date('Y')]));

        // Assert: Response harus berupa file download
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');
    }

    public function test_export_laporan_file_name_sesuai_pegawai_dan_tahun()
    {
        // Arrange: Buat satu data cuti
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan',
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act
        $response = $this->get(route('pegawai.cuti.export-excel', ['tahun' => date('Y')]));

        // Assert: Nama file harus sesuai format
        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Laporan_Cuti_', $contentDisposition);
        $this->assertStringContainsString('John_Doe', $contentDisposition);
        $this->assertStringContainsString(date('Y'), $contentDisposition);
    }

    public function test_export_laporan_mengikuti_tahun_yang_dipilih()
    {
        // Arrange: Buat data cuti untuk tahun berbeda
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::create(2025, 1, 1),
            'tanggal_selesai' => Carbon::create(2025, 1, 3),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 2025',
            'status' => 'Disetujui',
            'tahun' => 2025,
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::create(2024, 6, 1),
            'tanggal_selesai' => Carbon::create(2024, 6, 3),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 2024',
            'status' => 'Disetujui',
            'tahun' => 2024,
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act: Export untuk tahun 2025
        $response = $this->get(route('pegawai.cuti.export-excel', ['tahun' => 2025]));

        // Assert
        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('2025', $contentDisposition);
    }

    public function test_export_laporan_tidak_error_jika_pegawai_tidak_ada_data()
    {
        // Arrange: Pegawai tanpa data cuti (sudah terjamin dari setUp)

        // Act: Coba export
        $response = $this->get(route('pegawai.cuti.export-excel', ['tahun' => date('Y')]));

        // Assert: Harus tetap generate file kosong (tidak error)
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    // ==================== SKENARIO TAMBAHAN: RIWAYAT CUTI STATUS BERBEDA ====================

    public function test_riwayat_cuti_menampilkan_status_disetujui_dengan_benar()
    {
        // Arrange
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan Disetujui',
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act
        $response = $this->get(route('pegawai.cuti.index'));

        // Assert
        $response->assertStatus(200);
        $riwayat = $response->viewData('riwayat');
        $found = false;
        foreach ($riwayat->items() as $item) {
            if ($item->status === 'Disetujui') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Status Disetujui harus muncul di riwayat');
    }

    public function test_riwayat_cuti_filter_berdasarkan_tahun()
    {
        // Arrange
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::create(2024, 1, 1),
            'tanggal_selesai' => Carbon::create(2024, 1, 3),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 2024',
            'status' => 'Disetujui',
            'tahun' => 2024,
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act: Filter tahun 2024
        $response = $this->get(route('pegawai.cuti.index', ['tahun' => 2024]));

        // Assert
        $response->assertStatus(200);
        $riwayat = $response->viewData('riwayat');
        $this->assertEquals(1, $riwayat->total(), 'Hanya 1 data untuk tahun 2024');
    }

    public function test_riwayat_cuti_statistik_tampil_dengan_benar()
    {
        // Arrange: Buat beberapa cuti dengan status berbeda
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan',
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Test',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(20),
            'tanggal_selesai' => Carbon::now()->addDays(22),
            'jumlah_hari' => 3,
            'keterangan' => 'Liburan 2',
            'status' => 'Ditolak',
            'tahun' => date('Y'),
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'atasan_nama' => 'Atasan',
            'pejabat_nama' => '-',
        ]);

        // Act
        $response = $this->get(route('pegawai.cuti.index'));

        // Assert: Statistik harus tersedia
        $response->assertStatus(200);
        $response->assertViewHas('cutiDisetujui');
        $response->assertViewHas('cutiDitolak');
        $response->assertViewHas('totalCuti');
        
        $this->assertEquals(2, $response->viewData('totalCuti'));
        $this->assertEquals(1, $response->viewData('cutiDisetujui'));
        $this->assertEquals(1, $response->viewData('cutiDitolak'));
    }
}
