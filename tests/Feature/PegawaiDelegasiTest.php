<?php

use App\Models\Cuti;
use App\Models\Notification;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function createPegawaiWithUser(array $pegawaiAttrs = [], array $userAttrs = [])
{
    $pegawai = Pegawai::create(array_merge([
        'nama' => 'Pegawai ' . Str::random(5),
        'nip' => 'NIP' . now()->timestamp . rand(100, 999),
        'jabatan' => 'Staf',
        'unit_kerja' => 'Teknologi Informasi',
        'status' => 'aktif',
        'id_atasan_langsung' => 1,
        'id_pejabat_pemberi_cuti' => 1,
        'alamat' => 'Jl. Test No. 1',
        'email' => null,
        'telepon' => '08123456789',
    ], $pegawaiAttrs));

    $user = User::create(array_merge([
        'name' => $pegawai->nama,
        'email' => 'test+' . Str::random(8) . '@example.test',
        'password' => 'password',
        'role' => 'pegawai',
        'id_pegawai' => $pegawai->id,
    ], $userAttrs));

    return compact('pegawai', 'user');
}

function createCuti(array $attributes = [])
{
    return Cuti::create(array_merge([
        'user_id' => null,
        'id_pegawai' => null,
        'id_delegasi' => null,
        'nama' => 'Pengaju',
        'nip' => 'NIP000',
        'jabatan' => 'Staf',
        'alamat' => 'Jl. Uji Coba',
        'jenis_cuti' => 'Tahunan',
        'tanggal_mulai' => '2026-05-11',
        'tanggal_selesai' => '2026-05-13',
        'jumlah_hari' => 3,
        'tahun' => 2026,
        'keterangan' => 'Pengujian cuti',
        'status' => 'Disetujui',
        'status_delegasi' => 'pending',
        'status_atasan' => 'pending',
        'status_pejabat' => 'pending',
        'catatan_tolak_delegasi' => null,
        'catatan_tolak_atasan' => null,
        'catatan_tolak_pejabat' => null,
        'id_atasan_langsung' => 1,
        'id_pejabat_pemberi_cuti' => 1,
    ], $attributes));
}

/*
|--------------------------------------------------------------------------
| Skenario Pengujian (Bahasa Indonesia)
|--------------------------------------------------------------------------
*/

// 1. Skenario untuk filter dropdown delegasi
it('tidak menampilkan diri sendiri sebagai petugas pengganti yang tersedia', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 99]);
    $colleague = createPegawaiWithUser(['id_atasan_langsung' => 99]);

    $response = $this->actingAs($applicant['user'])
        ->get('/pegawai/cuti/available-delegates?tanggal_mulai=2026-05-11&tanggal_selesai=2026-05-13');

    $response->assertOk();
    $response->assertJsonMissing(['id' => $applicant['pegawai']->id]);
    $response->assertJsonFragment(['id' => $colleague['pegawai']->id]);
});

// 2. Skenario mengecek rekan yang sedang cuti
it('mengecualikan rekan yang sedang cuti dari daftar petugas pengganti pada tanggal yang sama', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 100]);
    $busyDelegate = createPegawaiWithUser(['id_atasan_langsung' => 100], ['name' => 'Nor Amalia']);
    $freeDelegate = createPegawaiWithUser(['id_atasan_langsung' => 100]);

    createCuti([
        'user_id' => $busyDelegate['user']->id,
        'id_pegawai' => $busyDelegate['pegawai']->id,
        'nama' => $busyDelegate['pegawai']->nama,
        'nip' => $busyDelegate['pegawai']->nip,
        'jabatan' => $busyDelegate['pegawai']->jabatan,
        'status' => 'Disetujui',
        'tanggal_mulai' => '2026-05-11',
        'tanggal_selesai' => '2026-05-13',
    ]);

    $response = $this->actingAs($applicant['user'])
        ->get('/pegawai/cuti/available-delegates?tanggal_mulai=2026-05-11&tanggal_selesai=2026-05-13');

    $response->assertOk();
    $response->assertJsonMissing(['id' => $busyDelegate['pegawai']->id]);
    $response->assertJsonFragment(['id' => $freeDelegate['pegawai']->id]);
});

// 3. Skenario mengecek status revisi
it('mengecualikan rekan dengan status revisi delegasi dari daftar petugas pengganti tersedia', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 101]);
    $busyDelegate = createPegawaiWithUser(['id_atasan_langsung' => 101]);

    createCuti([
        'user_id' => $busyDelegate['user']->id,
        'id_pegawai' => $busyDelegate['pegawai']->id,
        'nama' => $busyDelegate['pegawai']->nama,
        'nip' => $busyDelegate['pegawai']->nip,
        'jabatan' => $busyDelegate['pegawai']->jabatan,
        'status' => 'Revisi Delegasi',
        'tanggal_mulai' => '2026-05-11',
        'tanggal_selesai' => '2026-05-13',
    ]);

    $response = $this->actingAs($applicant['user'])
        ->get('/pegawai/cuti/available-delegates?tanggal_mulai=2026-05-11&tanggal_selesai=2026-05-13');

    $response->assertOk();
    $response->assertJsonMissing(['id' => $busyDelegate['pegawai']->id]);
});

// 4. Skenario notifikasi
it('mengirimkan notifikasi ke petugas pengganti setelah pengajuan cuti berhasil dikirim', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 104]);
    $delegate = createPegawaiWithUser(['id_atasan_langsung' => 104], ['name' => 'Husnul Khatimah']);

    $response = $this->actingAs($applicant['user'])
        ->post('/pegawai/cuti', [
            'id_delegasi' => $delegate['pegawai']->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Test notifikasi delegasi',
            'tanggal_mulai' => '2026-05-11',
            'tanggal_selesai' => '2026-05-13',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success', 'Pengajuan cuti berhasil dikirim.');

    $this->assertDatabaseHas('cuti', [
        'user_id' => $applicant['user']->id,
        'id_delegasi' => $delegate['pegawai']->id,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $delegate['user']->id,
        'title' => 'Permintaan Delegasi Tugas',
    ]);
});

// 5. Skenario validasi petugas pengganti sibuk
it('menolak pengajuan jika petugas pengganti yang dipilih ternyata sedang cuti', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 102]);
    $delegate = createPegawaiWithUser(['id_atasan_langsung' => 102], ['name' => 'Nor Amalia']);

    createCuti([
        'user_id' => $delegate['user']->id,
        'id_pegawai' => $delegate['pegawai']->id,
        'nama' => $delegate['pegawai']->nama,
        'nip' => $delegate['pegawai']->nip,
        'jabatan' => $delegate['pegawai']->jabatan,
        'status' => 'Disetujui',
        'tanggal_mulai' => '2026-05-11',
        'tanggal_selesai' => '2026-05-13',
    ]);

    $response = $this->actingAs($applicant['user'])
        ->post('/pegawai/cuti', [
            'id_delegasi' => $delegate['pegawai']->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Test delegasi bentrok',
            'tanggal_mulai' => '2026-05-11',
            'tanggal_selesai' => '2026-05-13',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error', 'Gagal! Pegawai pengganti (' . $delegate['pegawai']->nama . ') sudah memiliki jadwal cuti.');
});

// 6. Skenario validasi pengaju sedang jadi delegasi orang lain
it('menolak pengajuan jika pengaju sudah terdaftar sebagai petugas pengganti orang lain', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 103]);
    $otherApplicant = createPegawaiWithUser(['id_atasan_langsung' => 103]);
    $delegate = createPegawaiWithUser(['id_atasan_langsung' => 103]);

    createCuti([
        'user_id' => $otherApplicant['user']->id,
        'id_pegawai' => $otherApplicant['pegawai']->id,
        'id_delegasi' => $applicant['pegawai']->id,
        'nama' => $otherApplicant['pegawai']->nama,
        'nip' => $otherApplicant['pegawai']->nip,
        'jabatan' => $otherApplicant['pegawai']->jabatan,
        'status' => 'Disetujui',
        'tanggal_mulai' => '2026-05-11',
        'tanggal_selesai' => '2026-05-13',
    ]);

    $response = $this->actingAs($applicant['user'])
        ->post('/pegawai/cuti', [
            'id_delegasi' => $delegate['pegawai']->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Test applicant as delegate',
            'tanggal_mulai' => '2026-05-11',
            'tanggal_selesai' => '2026-05-13',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error', 'Gagal! Anda terdaftar sebagai Petugas Pengganti untuk ' . $otherApplicant['pegawai']->nama . ' di tanggal tersebut.');
});

// 7. Skenario pilih diri sendiri
it('menolak pengajuan jika memilih diri sendiri menjadi petugas pengganti', function () {
    $applicant = createPegawaiWithUser(['id_atasan_langsung' => 105]);

    $response = $this->actingAs($applicant['user'])
        ->post('/pegawai/cuti', [
            'id_delegasi' => $applicant['pegawai']->id,
            'jenis_cuti' => 'Tahunan',
            'keterangan' => 'Test self delegate',
            'tanggal_mulai' => '2026-05-11',
            'tanggal_selesai' => '2026-05-13',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error', 'Pegawai pengganti harus berada di bawah naungan Atasan Langsung yang sama.');
});