<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Cuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;

class ManajemenPegawaiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag());
    }

    public function test_index_menampilkan_daftar_pegawai_dan_menerima_filter_pencarian()
    {
        Pegawai::forceCreate(['nama' => 'Anton', 'unit_kerja' => 'Seksi A', 'status' => 'aktif', 'jabatan' => 'Staf A', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        Pegawai::forceCreate(['nama' => 'Budi', 'unit_kerja' => 'Seksi B', 'status' => 'aktif', 'jabatan' => 'Staf B', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);

        $response = $this->get(route('admin.pegawai.index', [
            'search' => 'Anton',
            'unit_kerja' => 'Seksi A',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('pegawai');
        $response->assertSee('Anton');
        $response->assertDontSee('Budi');
    }

    public function test_store_berhasil_membuat_pegawai_dan_user_baru()
    {
        $response = $this->post(route('admin.pegawai.store'), [
            'nama' => 'Joko',
            'nip' => '199001012020121005',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'role' => 'pegawai',
            'status' => 'aktif',
            'atasan' => 'Atasan Dummy',
            'pejabat' => 'Pejabat Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pegawai', ['nama' => 'Joko', 'nip' => '199001012020121005']);
        $this->assertDatabaseHas('users', ['nip' => '199001012020121005', 'role' => 'pegawai']);

        $pegawai = Pegawai::where('nip', '199001012020121005')->first();
        $this->assertNotNull($pegawai);
        $this->assertDatabaseHas('users', ['id_pegawai' => $pegawai->id]);
    }

    public function test_store_gagal_jika_jabatan_unik_sudah_terisi()
    {
        Pegawai::forceCreate(['jabatan' => 'Kepala Dinas', 'nama' => 'Existing', 'status' => 'aktif', 'unit_kerja' => 'Bidang A', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);

        $response = $this->post(route('admin.pegawai.store'), [
            'nama' => 'Andi',
            'nip' => '199001012020121006',
            'jabatan' => 'Kepala Dinas',
            'unit_kerja' => 'Bidang E-Government',
            'role' => 'pegawai',
            'status' => 'aktif',
            'atasan' => 'Atasan Dummy',
            'pejabat' => 'Pejabat Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', 'Jabatan "Kepala Dinas" sudah terisi. Hanya boleh ada 1 orang untuk jabatan ini.');
    }

    public function test_store_gagal_jika_nip_sudah_terdaftar()
    {
        Pegawai::forceCreate(['nip' => '199001012020121007', 'nama' => 'Existing', 'status' => 'aktif', 'jabatan' => 'Staf IT', 'unit_kerja' => 'Bidang A', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);

        $response = $this->post(route('admin.pegawai.store'), [
            'nama' => 'Rina',
            'nip' => '199001012020121007',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'role' => 'pegawai',
            'status' => 'aktif',
            'atasan' => 'Atasan Dummy',
            'pejabat' => 'Pejabat Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_store_gagal_jika_password_sudah_digunakan()
    {
        User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->post(route('admin.pegawai.store'), [
            'nama' => 'Tulip',
            'nip' => '199001012020121008',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Bidang E-Government',
            'role' => 'pegawai',
            'status' => 'aktif',
            'atasan' => 'Atasan Dummy',
            'pejabat' => 'Pejabat Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
            'password' => 'secret123',
        ]);

        $response->assertSessionHas('error', 'Password sudah digunakan. Silakan gunakan kombinasi password lain.');
    }

    public function test_update_berhasil_mengubah_data_pegawai_dan_user()
    {
        $pegawai = Pegawai::forceCreate(['nama' => 'Rudi', 'nip' => '199001012020121009', 'jabatan' => 'Staf IT', 'unit_kerja' => 'Seksi X', 'status' => 'aktif', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $user = User::factory()->create(['name' => 'Rudi', 'role' => 'pegawai', 'id_pegawai' => $pegawai->id]);

        $response = $this->patch(route('admin.pegawai.update', $pegawai->id), [
            'nama' => 'Rudi Updated',
            'nip' => '199001012020121009',
            'jabatan' => 'Staf IT',
            'unit_kerja' => 'Seksi Y',
            'role' => 'pegawai',
            'status' => 'nonaktif',
            'atasan' => 'Atasan Baru',
            'id_atasan_langsung' => 0,
            'id_pejabat_pemberi_cuti' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pegawai', ['id' => $pegawai->id, 'nama' => 'Rudi Updated', 'unit_kerja' => 'Seksi Y', 'status' => 'nonaktif']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Rudi Updated']);
    }

    public function test_update_gagal_jika_nip_duplikat()
    {
        $pegawai1 = Pegawai::forceCreate(['nip' => '199001012020121010', 'nama' => 'P1', 'status' => 'aktif', 'jabatan' => 'Staf', 'unit_kerja' => 'Seksi A', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $user1 = User::factory()->create(['id_pegawai' => $pegawai1->id]);
        $pegawai2 = Pegawai::forceCreate(['nip' => '199001012020121011', 'nama' => 'P2', 'status' => 'aktif', 'jabatan' => 'Staf', 'unit_kerja' => 'Seksi B', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $user2 = User::factory()->create(['id_pegawai' => $pegawai2->id]);

        $response = $this->patch(route('admin.pegawai.update', $pegawai2->id), [
            'nama' => $pegawai2->nama,
            'nip' => '199001012020121010',
            'jabatan' => $pegawai2->jabatan,
            'unit_kerja' => $pegawai2->unit_kerja,
            'role' => 'pegawai',
            'status' => $pegawai2->status,
            'atasan' => 'Atasan Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
        ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_update_gagal_jika_jabatan_unik_sudah_terisi()
    {
        $existing = Pegawai::forceCreate(['jabatan' => 'Kepala Dinas', 'nama' => 'Existing', 'nip' => '199001012020121020', 'unit_kerja' => 'Bidang X', 'status' => 'aktif', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $userExisting = User::factory()->create(['id_pegawai' => $existing->id]);
        $pegawai = Pegawai::forceCreate(['jabatan' => 'Staf IT', 'nama' => 'P2', 'nip' => '199001012020121021', 'unit_kerja' => 'Bidang Y', 'status' => 'aktif', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $user = User::factory()->create(['id_pegawai' => $pegawai->id]);

        $response = $this->patch(route('admin.pegawai.update', $pegawai->id), [
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'jabatan' => 'Kepala Dinas',
            'unit_kerja' => $pegawai->unit_kerja,
            'role' => 'pegawai',
            'status' => $pegawai->status,
            'atasan' => 'Atasan Dummy',
            'id_atasan_langsung' => null,
            'id_pejabat_pemberi_cuti' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jabatan "Kepala Dinas" sudah terisi.');
    }

    public function test_destroy_berhasil_menghapus_pegawai_dan_user()
    {
        $pegawai = Pegawai::forceCreate(['nama' => 'Anton', 'nip' => '199001012020121013', 'jabatan' => 'Staf IT', 'unit_kerja' => 'Bidang A', 'status' => 'aktif', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $user = User::factory()->create(['name' => 'Anton', 'id_pegawai' => $pegawai->id]);

        $response = $this->delete(route('admin.pegawai.destroy', $pegawai->id));

        $response->assertRedirect(route('admin.pegawai.index'));
        $this->assertDatabaseMissing('pegawai', ['id' => $pegawai->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_destroy_gagal_jika_atasan_memiliki_antrian_cuti_menunggu()
    {
        $atasan = Pegawai::forceCreate(['nama' => 'Atasan A', 'nip' => '199001012020121014', 'jabatan' => 'Atasan', 'unit_kerja' => 'Bidang A', 'status' => 'aktif', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);
        $userAtasan = User::factory()->create(['role' => 'atasan', 'id_pegawai' => $atasan->id]);
        $bawahan = Pegawai::forceCreate(['id_atasan_langsung' => $atasan->id, 'nama' => 'Bawahan A', 'nip' => '199001012020121015', 'jabatan' => 'Staf', 'unit_kerja' => 'Bidang A', 'status' => 'aktif', 'atasan' => $atasan->nama, 'pemberi_cuti' => 'Pejabat']);
        $userBawahan = User::factory()->create(['id_pegawai' => $bawahan->id]);

        Cuti::forceCreate([
            'user_id' => $userBawahan->id,
            'id_pegawai' => $bawahan->id,
            'nama' => $bawahan->nama,
            'nip' => $bawahan->nip,
            'jabatan' => $bawahan->jabatan,
            'alamat' => 'Jl. Contoh',
            'jenis_cuti' => 'Tahunan',
            'tanggal_mulai' => Carbon::now()->addDays(5),
            'tanggal_selesai' => Carbon::now()->addDays(7),
            'jumlah_hari' => 3,
            'keterangan' => 'Cuti Bawahan',
            'status' => 'Menunggu',
            'tahun' => date('Y'),
            'id_atasan_langsung' => $bawahan->id_atasan_langsung,
            'id_pejabat_pemberi_cuti' => $bawahan->id_pejabat_pemberi_cuti,
            'atasan_nama' => $atasan->nama,
            'pejabat_nama' => '-',
        ]);

        $response = $this->delete(route('admin.pegawai.destroy', $atasan->id));

        $response->assertRedirect(route('admin.pegawai.index'));
        $response->assertSessionHas('error', 'Atasan tidak bisa dihapus, karena atasan ini masih memiliki antrian persetujuan cuti pegawainya.');
        $this->assertDatabaseHas('pegawai', ['id' => $atasan->id]);
    }

    public function test_check_unique_mengembalikan_false_jika_nilai_kosong()
    {
        $response = $this->post(route('admin.pegawai.checkUnique'), [
            'field' => 'nip',
            'value' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function test_check_unique_mengembalikan_true_jika_nip_sudah_ada()
    {
        Pegawai::forceCreate(['nip' => '199001012020121012', 'nama' => 'Existing', 'status' => 'aktif', 'jabatan' => 'Staf', 'unit_kerja' => 'Bidang A', 'atasan' => 'Atasan', 'pemberi_cuti' => 'Pejabat']);

        $response = $this->post(route('admin.pegawai.checkUnique'), [
            'field' => 'nip',
            'value' => '199001012020121012',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    public function test_check_password_mendeteksi_password_yang_sudah_ada()
    {
        User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->post(route('admin.pegawai.checkPassword'), [
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    public function test_check_password_mengembalikan_false_untuk_password_baru()
    {
        User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->post(route('admin.pegawai.checkPassword'), [
            'password' => 'unique456',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }
}