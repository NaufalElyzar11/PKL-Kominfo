<?php

namespace Tests\Feature\Pegawai;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\AtasanLangsung;
use App\Models\PejabatPemberiCuti;
use App\Models\Cuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PengajuanCutiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pegawai;
    protected $delegasi;
    protected $atasan;
    protected $pejabat;

    // 1. SETUP: Persiapan Data Dummy
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock API eksternal untuk ambil hari libur nasional
        Http::fake([
            'dayoffapi.vercel.app/*' => Http::response([]),
        ]);
        
        // Matikan middleware auth dan role untuk testing
        $this->withoutMiddleware();

        // GUNAKAN forceCreate() AGAR KEBAL DARI ATURAN $fillable
        $atasan = AtasanLangsung::forceCreate(['nama_atasan' => 'Atasan Dummy', 'nip_atasan' => '111111']);
        $pejabat = PejabatPemberiCuti::forceCreate(['nama_pejabat' => 'Pejabat Dummy', 'nip_pejabat' => '222222']);
        
        $this->atasan = $atasan;
        $this->pejabat = $pejabat;

        // Buat Akun User Pemohon
        $this->user = User::forceCreate([
            'name' => 'Pegawai Pemohon',
            'email' => 'pemohon@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        // Buat Data Pegawai Pemohon (Tersambung ke User)
        $this->pegawai = Pegawai::forceCreate([
            'nama' => 'Pegawai Pemohon',
            'nip' => '199001012020121001',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'status' => 'aktif',
            'id_atasan_langsung' => $atasan->id,
            'id_pejabat_pemberi_cuti' => $pejabat->id,
            'sisa_cuti' => 12,
            'telepon' => '081234567890',
            'email' => 'pegawai1@test.com'
        ]);

        // Update User dengan id_pegawai
        $this->user->update(['id_pegawai' => $this->pegawai->id]);

        // Buat Akun User Delegasi
        $userDelegasi = User::forceCreate([
            'name' => 'Rekan Delegasi',
            'email' => 'delegasi@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        // Buat Data Rekan Delegasi (Satu Atasan)
        $this->delegasi = Pegawai::forceCreate([
            'nama' => 'Rekan Delegasi',
            'nip' => '199201012020121002',
            'jabatan' => 'Staf Jaringan',
            'unit_kerja' => 'Bidang E-Government',
            'status' => 'aktif',
            'id_atasan_langsung' => $atasan->id,
            'id_pejabat_pemberi_cuti' => $pejabat->id,
            'sisa_cuti' => 12,
            'telepon' => '081234567891',
            'email' => 'pegawai2@test.com'
        ]);

        // Update User Delegasi dengan id_pegawai
        $userDelegasi->update(['id_pegawai' => $this->delegasi->id]);
    }

    /** Jalur 1 */
    public function test_gagal_jika_data_pegawai_tidak_ditemukan() 
    {
        $userTanpaPegawai = User::forceCreate([
            'name' => 'User Kosong', 'email' => 'kosong@test.com', 'password' => bcrypt('123'), 'role' => 'pegawai'
        ]);

        $this->actingAs($userTanpaPegawai);
        $response = $this->post(route('pegawai.cuti.store'), []);

        $response->assertSessionHas('error', 'Data pegawai belum ditemukan.');
    }

    /** Jalur 2 */
    public function test_gagal_jika_masih_ada_pengajuan_berstatus_menunggu() 
    {
        $this->actingAs($this->user);

        // Sengaja buatkan 1 data cuti berstatus 'Menunggu'
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Contoh No. 1',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(2),
            'tanggal_selesai' => Carbon::now()->addDays(3),
            'jumlah_hari' => 2,
            'keterangan' => 'Cuti Pertama',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Kedua',
            'tanggal_mulai' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(12)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', 'Gagal! Pengajuan sebelumnya masih dalam tahap regulasi (Tahap Atasan/Pejabat). Harap tunggu Keputusan Akhir.');
    }

    /** Jalur 3 */
    public function test_gagal_jika_delegasi_tidak_di_bawah_atasan_langsung_yang_sama()
    {
        $this->actingAs($this->user);

        // Buat atasan berbeda
        $atasanLain = AtasanLangsung::forceCreate([
            'nama_atasan' => 'Atasan Lain',
            'nip_atasan' => '333333'
        ]);

        // Buat delegasi dengan atasan berbeda
        $delegasiAtasanBerbeda = Pegawai::forceCreate([
            'nama' => 'Delegasi Atasan Berbeda',
            'nip' => '199301012020121003',
            'jabatan' => 'Staf Admin',
            'unit_kerja' => 'Bidang Lain',
            'status' => 'aktif',
            'id_atasan_langsung' => $atasanLain->id,
            'id_pejabat_pemberi_cuti' => $this->pejabat->id,
            'sisa_cuti' => 12,
            'telepon' => '081234567892',
            'email' => 'pegawai3@test.com'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $delegasiAtasanBerbeda->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', 'Pegawai pengganti harus berada di bawah naungan Atasan Langsung yang sama.');
    }

    /** Jalur 4 */
    public function test_gagal_jika_kuota_cuti_tahunan_unit_kerja_sudah_penuh()
    {
        $this->actingAs($this->user);

        // Buat 2 pegawai lain di unit kerja yang sama yang sudah mengajukan cuti
        for ($i = 1; $i <= 2; $i++) {
            $userLain = User::forceCreate([
                'name' => "Pegawai Lain {$i}",
                'email' => "lain{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'pegawai'
            ]);

            $pegawaiLain = Pegawai::forceCreate([
                'nama' => "Pegawai Lain {$i}",
                'nip' => "199{$i}010120201210{$i}0",
                'jabatan' => 'Staf',
                'unit_kerja' => 'Bidang E-Government', // Unit kerja sama
                'status' => 'aktif',
                'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
                'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
                'sisa_cuti' => 12,
                'telepon' => "08123456789{$i}",
                'email' => "pegawai{$i}@test.com"
            ]);

            $userLain->update(['id_pegawai' => $pegawaiLain->id]);

            // Buat cuti yang sedang menunggu
            Cuti::forceCreate([
                'user_id' => $userLain->id,
                'id_pegawai' => $pegawaiLain->id,
                'nama' => $pegawaiLain->nama,
                'nip' => $pegawaiLain->nip,
                'jabatan' => $pegawaiLain->jabatan,
                'alamat' => 'Jl. Contoh',
                'jenis_cuti' => 'Tahunan',
                'tanggal_mulai' => Carbon::now()->addDays(5),
                'tanggal_selesai' => Carbon::now()->addDays(7),
                'jumlah_hari' => 3,
                'keterangan' => 'Cuti Test',
                'status' => 'Menunggu',
                'tahun' => date('Y'),
                'id_atasan_langsung' => $pegawaiLain->id_atasan_langsung,
                'id_pejabat_pemberi_cuti' => $pegawaiLain->id_pejabat_pemberi_cuti,
                'atasan_nama' => '-',
                'pejabat_nama' => '-'
            ]);
        }

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', "Gagal! Kuota Cuti Tahunan di Bidang E-Government sudah penuh (Maks. 2 orang).");
    }

    /** Jalur 5 */
    public function test_gagal_jika_delegasi_sedang_memiliki_jadwal_cuti()
    {
        $this->actingAs($this->user);

        // Buat delegasi yang sedang cuti
        Cuti::forceCreate([
            'user_id' => User::where('id_pegawai', $this->delegasi->id)->first()->id,
            'id_pegawai' => $this->delegasi->id,
            'nama' => $this->delegasi->nama,
            'nip' => $this->delegasi->nip,
            'jabatan' => $this->delegasi->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(3),
            'tanggal_selesai' => Carbon::now()->addDays(10),
            'jumlah_hari' => 6,
            'keterangan' => 'Cuti Delegasi',
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->delegasi->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->delegasi->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', "Gagal! Pegawai pengganti ({$this->delegasi->nama}) sudah memiliki jadwal cuti.");
    }

    /** Jalur 6 */
    public function test_gagal_jika_pemohon_sedang_bertugas_sebagai_delegasi()
    {
        $this->actingAs($this->user);

        // Buat pegawai lain yang menggunakan pemohon sebagai delegasi
        $userLain = User::forceCreate([
            'name' => 'Pegawai Lain',
            'email' => 'lain@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        $pegawaiLain = Pegawai::forceCreate([
            'nama' => 'Pegawai Lain',
            'nip' => '199401012020121004',
            'jabatan' => 'Staf',
            'unit_kerja' => 'Bidang Lain',
            'status' => 'aktif',
            'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
            'sisa_cuti' => 12,
            'telepon' => '081234567893',
            'email' => 'pegawai4@test.com'
        ]);

        $userLain->update(['id_pegawai' => $pegawaiLain->id]);

        // Buat cuti dimana pemohon adalah delegasi
        Cuti::forceCreate([
            'user_id' => $userLain->id,
            'id_pegawai' => $pegawaiLain->id,
            'id_delegasi' => $this->pegawai->id, // Pemohon sebagai delegasi
            'nama' => $pegawaiLain->nama,
            'nip' => $pegawaiLain->nip,
            'jabatan' => $pegawaiLain->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(3),
            'tanggal_selesai' => Carbon::now()->addDays(10),
            'jumlah_hari' => 6,
            'keterangan' => 'Cuti dengan Delegasi',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $pegawaiLain->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $pegawaiLain->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', "Gagal! Anda terdaftar sebagai Petugas Pengganti untuk {$pegawaiLain->nama} di tanggal tersebut.");
    }

    /** Jalur 7 */
    public function test_gagal_jika_rentang_tanggal_bentrok_dengan_jadwal_cuti_lain()
    {
        $this->actingAs($this->user);

        // Buat cuti lain untuk pemohon yang bentrok tanggal
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(3),
            'tanggal_selesai' => Carbon::now()->addDays(10),
            'jumlah_hari' => 6,
            'keterangan' => 'Cuti Bentrok',
            'status' => 'Disetujui', // Ubah status agar tidak terdeteksi sebagai pending
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'), // Bentrok dengan cuti sebelumnya
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', "Gagal! Anda sudah memiliki jadwal cuti lain di tanggal tersebut.");
    }

    /** Jalur 8 */
    public function test_gagal_jika_cuti_tahunan_lebih_dari_1x_dalam_sebulan()
    {
        $this->actingAs($this->user);

        // Buat cuti tahunan lain di bulan yang sama
        $nextMonth = Carbon::now()->addMonth()->month;
        $nextMonthYear = Carbon::now()->addMonth()->year;
        
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addMonth()->startOfMonth()->addDays(4), // Hari 5 bulan depan
            'tanggal_selesai' => Carbon::now()->addMonth()->startOfMonth()->addDays(5), // Hari 6 bulan depan
            'jumlah_hari' => 2,
            'keterangan' => 'Cuti Tahunan Bulan Depan',
            'status' => 'Disetujui',
            'tahun' => $nextMonthYear,
            'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Tahunan Kedua',
            'tanggal_mulai' => Carbon::now()->addMonth()->startOfMonth()->addDays(14)->format('Y-m-d'), // Hari 15 bulan depan
            'tanggal_selesai' => Carbon::now()->addMonth()->startOfMonth()->addDays(15)->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', "Gagal! Cuti Tahunan hanya boleh diajukan 1x dalam sebulan.");
    }

    public function test_gagal_jika_sisa_kuota_tidak_mencukupi()
    {
        $this->actingAs($this->user);

        // Pastikan tidak ada cuti pending
        Cuti::where('user_id', $this->user->id)->delete();

        // Buat cuti yang sudah terpakai 11 hari agar sisa hanya 1 hari
        Cuti::forceCreate([
            'user_id' => $this->user->id,
            'id_pegawai' => $this->pegawai->id,
            'nama' => $this->pegawai->nama,
            'nip' => $this->pegawai->nip,
            'jabatan' => $this->pegawai->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->subDays(30), // 30 hari yang lalu
            'tanggal_selesai' => Carbon::now()->subDays(20), // 20 hari yang lalu
            'jumlah_hari' => 11, // Sudah pakai 11 hari
            'keterangan' => 'Cuti Sudah Terpakai',
            'status' => 'Disetujui',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $this->pegawai->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $this->pegawai->id_pejabat_pemberi_cuti,
            'atasan_nama' => '-',
            'pejabat_nama' => '-'
        ]);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(10)->format('Y-m-d'), // Durasi 6 hari, sisa hanya 1
        ]);

        $response->assertSessionHas('error', 'Gagal! Sisa kuota cuti Anda tidak mencukupi untuk durasi ini.');
    }

    /** Jalur 10 */
    public function test_gagal_validasi_jika_tanggal_mulai_di_masa_lalu()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->subDays(1)->format('Y-m-d'), // Tanggal kemarin
            'tanggal_selesai' => Carbon::now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('tanggal_mulai');
    }

    /** Jalur 11 */
    public function test_gagal_validasi_jika_tanggal_selesai_mendahului_tanggal_mulai()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(5)->format('Y-m-d'), // Tanggal selesai sebelum tanggal mulai
        ]);

        $response->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_gagal_jika_id_delegasi_tidak_ditemukan()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => 9999,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('id_delegasi');
    }

    public function test_gagal_jika_jenis_cuti_tidak_valid()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Sakit',
            'keterangan' => 'Cuti Test',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('jenis_cuti');
    }

    public function test_gagal_jika_keterangan_tidak_diisi()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pegawai.cuti.store'), [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('keterangan');
    }

    /** Jalur 12 (Sukses) */
    public function test_sukses_simpan_pengajuan_jika_seluruh_syarat_terpenuhi() 
    {
        $this->actingAs($this->user);

        $formData = [
            'id_delegasi' => $this->delegasi->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Pulang kampung',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'alamat' => 'Jl. Contoh No. 123, Kota Test',
        ];

        $response = $this->post(route('pegawai.cuti.store'), $formData);

        // Cek status response
        $this->assertEquals(302, $response->getStatusCode(), 'Response harus redirect (302)');

        $response->assertRedirect(route('pegawai.cuti.index'));
        $response->assertSessionHas('success', 'Pengajuan cuti berhasil dikirim.');

        // Cek tabel 'cuti' di database
        $this->assertDatabaseHas('cuti', [ 
            'user_id' => $this->user->id,
            'jenis_cuti' => 'Tahunan',
            'status' => 'Menunggu',
            'id_delegasi' => $this->delegasi->id
        ]);
    }
}