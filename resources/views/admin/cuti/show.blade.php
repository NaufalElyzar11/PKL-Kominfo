<x-layouts.admin>
    <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl mx-auto mt-8 border border-gray-300" style="font-family: 'Times New Roman', Times, serif;">
        <!-- Header Surat -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-wide text-black">SURAT PERMOHONAN CUTI</h1>
            <p class="text-gray-800 mt-1">Dinas / Instansi: {{ $cuti->pegawai->unit_kerja ?? '-' }}</p>
        </div>

        <!-- Informasi Pegawai -->
        <div class="mb-6 space-y-2 text-gray-900">
            <p><strong>Nama Pegawai:</strong> {{ $cuti->pegawai->nama ?? '-' }}</p>
            <p><strong>NIP:</strong> {{ $cuti->pegawai->nip ?? '-' }}</p>
            <p><strong>Jabatan:</strong> {{ $cuti->pegawai->jabatan ?? '-' }}</p>
            <p><strong>Jenis Cuti:</strong> {{ ucfirst($cuti->jenis_cuti ?? '-') }}</p>

            {{-- ✅ Gunakan nama kolom yang sesuai dengan model --}}
            <p><strong>Tanggal Mulai:</strong> {{ optional($cuti->tanggal_mulai)->format('d-m-Y') }}</p>
            <p><strong>Tanggal Selesai:</strong> {{ optional($cuti->tanggal_selesai)->format('d-m-Y') }}</p>
            
            <p><strong>Alasan:</strong> {{ $cuti->alasan_cuti ?? '-' }}</p>
        </div>

        <!-- Catatan Cuti -->
        <div class="mb-6 text-gray-900">
            <h2 class="font-semibold text-lg mb-2 text-black">Catatan Cuti</h2>
            <table class="w-full border border-gray-600 text-sm">
                <thead class="bg-gray-100 text-black">
                    <tr>
                        <th class="border border-gray-600 px-2 py-1">Tahun</th>
                        <th class="border border-gray-600 px-2 py-1">Cuti Terpakai</th>
                        <th class="border border-gray-600 px-2 py-1">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="text-gray-900">
                    @forelse(($rekapCuti ?? []) as $tahun => $data)
                        <tr>
                            <td class="border border-gray-600 px-2 py-1 text-center">{{ $tahun }}</td>
                            <td class="border border-gray-600 px-2 py-1 text-center">{{ $data['terpakai'] ?? 0 }} Hari</td>
                            <td class="border border-gray-600 px-2 py-1 text-center">
                                @php
                                    $sisa = $data['sisa'] ?? 0;
                                @endphp
                                @if($sisa > 0)
                                    Sisa {{ $sisa }} Hari
                                @elseif($sisa == 0)
                                    Habis
                                @else
                                    Melebihi kuota ({{ abs($sisa) }} Hari)
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-2 text-gray-600">Belum ada data cuti</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Status Cuti -->
        <div class="mb-6 text-gray-900">
            @php
                $status = $cuti->status ?? 'pending';
                $statusColor = [
                    'disetujui' => 'text-green-700',
                    'ditolak' => 'text-red-700',
                    'pending' => 'text-yellow-700',
                ][$status] ?? 'text-gray-700';
            @endphp

            <p><strong>Status:</strong>
                <span class="{{ $statusColor }} font-semibold">{{ ucfirst($status) }}</span>
            </p>
        </div>

        <!-- Tanda Tangan -->
        <div class="mt-10 flex justify-between flex-wrap gap-6 text-gray-900">
            <div class="text-center flex-1 min-w-[120px]">
                <p>Pegawai,</p>
                <p class="mt-16 underline font-medium">{{ $cuti->pegawai->nama ?? '-' }}</p>
            </div>
            <div class="text-center flex-1 min-w-[120px]">
                <p>Kepala Dinas</p>
                <p class="mt-16 underline font-medium">______________________</p>
            </div>
        </div>

        <!-- Tombol Kembali & Cetak -->
        <div class="mt-8 flex justify-center gap-3 flex-wrap no-print">
            <a href="{{ route('admin.cuti.index') }}"
               class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-800 transition">
                 Kembali
            </a>
            <button onclick="window.print()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Cetak
            </button>
        </div>
    </div>
</x-layouts.admin>

<!-- Print Styling -->
<style>
    @media print {
        body * {
            visibility: hidden !important;
        }
        .bg-white {
            visibility: visible !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
            font-family: 'Times New Roman', Times, serif !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
