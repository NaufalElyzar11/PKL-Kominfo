@extends('layouts.admin')

@section('title', 'Daftar Pengajuan Cuti Pegawai')

@section('content')
<div class="min-h-screen px-3 py-4 bg-[#E3F2FD]">
    {{-- Wrapper utama dgn Alpine scope --}}
    <div x-data="cutiTable()" @keydown.escape.window="closeModal()" class="space-y-3">

        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow p-4 border border-gray-200 space-y-3">
            <h1 class="text-lg font-bold text-gray-800">Daftar Pengajuan Cuti Pegawai</h1>

            {{-- Filter sederhana --}}
            <form method="GET" action="{{ route('admin.cuti.index') }}" class="flex flex-wrap items-center justify-between gap-2 text-[10px]">
                <div class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/NIP"
                    class="px-2 py-1 border border-gray-300 rounded w-40 focus:ring-1 focus:ring-blue-400 outline-none">

                <select name="status" class="px-2 py-1 border border-gray-300 rounded w-32 text-[10px] focus:ring-1 focus:ring-blue-400 outline-none">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Disetujui Atasan" {{ request('status') == 'Disetujui Atasan' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" placeholder="Tanggal Mulai"
                    class="px-2 py-1 border border-gray-300 rounded w-36 text-[10px] focus:ring-1 focus:ring-blue-400 outline-none">

                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" placeholder="Tanggal Selesai"
                    class="px-2 py-1 border border-gray-300 rounded w-36 text-[10px] focus:ring-1 focus:ring-blue-400 outline-none">

                <button type="submit" class="px-2 py-1 bg-sky-600 text-white rounded text-[10px] hover:bg-sky-700 transition shadow-sm">
                    Filter
                </button>

                @if(request('search') || request('status') || request('tanggal_dari') || request('tanggal_sampai'))
                    <a href="{{ route('admin.cuti.index') }}" class="px-2 py-1 bg-gray-500 text-white rounded text-[10px] hover:bg-gray-600 transition shadow-sm">
                        Reset
                    </a>
                @endif
                </div>

                {{-- Tombol Export PDF di ujung kanan --}}
                <a href="{{ route('admin.cuti.export-pdf', request()->query()) }}" 
                    class="flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all shadow-sm text-[10px] font-bold">
                    <i class="fa-solid fa-file-pdf"></i>
                    Export PDF
                </a>
            </form>

            {{-- Tabel cuti (compact) --}}
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                <table class="w-full border-collapse bg-white text-xs">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white">
                        <tr>
                            <th class="px-2 py-2 border font-bold text-center">No</th>
                            <th class="px-2 py-2 border font-bold text-left">Nama</th>
                            <th class="px-2 py-2 border font-bold text-center">NIP</th> {{-- KOLOM BARU --}}
                            <th class="px-2 py-2 border font-bold text-left">Jabatan</th>
                            <th class="px-2 py-2 border font-bold text-left">Jenis</th>
                            <th class="px-2 py-2 border font-bold text-center">Mulai</th>
                            <th class="px-2 py-2 border font-bold text-center">Selesai</th>
                            <th class="px-2 py-2 border font-bold text-center">Hari</th>
                            <th class="px-2 py-2 border font-bold text-center">Status</th>
                            <th class="px-2 py-2 border font-bold text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cuti as $i => $c)
                            @php
                                $cutiData = [
                                    'id' => $c->id,
                                    'nama' => optional($c->pegawai)->nama ?? '-',
                                    'nip' => optional($c->pegawai)->nip ?? '-',
                                    'jabatan' => optional($c->pegawai)->jabatan ?? '-',
                                    'jenis_cuti' => $c->jenis_cuti,
                                    'tanggal_mulai' => \Carbon\Carbon::parse($c->tanggal_mulai)->translatedFormat('d F Y'),
                                    'tanggal_selesai' => \Carbon\Carbon::parse($c->tanggal_selesai)->translatedFormat('d F Y'),
                                    'jumlah_hari' => $c->jumlah_hari,
                                    'alasan_cuti' => $c->keterangan ?? ($c->alasan_cuti ?? '-'),
                                    'status' => $c->status,
                                ];
                            @endphp

                            <tr class="border hover:bg-gray-50 transition">
                                <td class="px-2 py-1 border text-center">{{ $i + $cuti->firstItem() }}</td>
                                <td class="px-2 py-1 border font-medium text-gray-800">{{ $cutiData['nama'] }}</td>
                                <td class="px-2 py-1 border text-center font-mono text-gray-600">{{ $cutiData['nip'] }}</td> {{-- DATA NIP --}}
                                <td class="px-2 py-1 border text-gray-500">{{ $cutiData['jabatan'] }}</td>
                                <td class="px-2 py-1 border">{{ $cutiData['jenis_cuti'] }}</td>
                                <td class="px-2 py-1 border text-center font-mono">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d/m/y') }}</td>
                                <td class="px-2 py-1 border text-center font-mono">{{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d/m/y') }}</td>
                                <td class="px-2 py-1 border text-center font-bold">{{ $cutiData['jumlah_hari'] }}</td>

                                <td class="px-2 py-1 border text-center">
                                    @php
                                        $status_color = match ($c->status) {
                                            'disetujui' => 'bg-green-100 text-green-700',
                                            'ditolak'   => 'bg-red-100 text-red-700',
                                            default     => 'bg-yellow-100 text-yellow-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-[1px] text-[9px] rounded-full font-bold {{ $status_color }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>

                                <td class="px-2 py-1 border text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button @click="openDetailModal(@js($cutiData))"
                                            class="text-blue-600 hover:text-blue-800 transition" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <button type="button"
                                            onclick="confirmDelete('{{ $c->id }}', '{{ addslashes($cutiData['nama']) }}')"
                                            class="text-red-600 hover:text-red-800 transition" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>

                                        {{-- Form Delete Unik --}}
                                        <form id="delete-form-{{ $c->id }}" action="{{ route('admin.cuti.destroy', $c->id) }}" method="POST" style="display: none;">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-gray-500 italic">Belum ada data pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-600 gap-2">
                <p>Menampilkan {{ $cuti->firstItem() ?? 0 }} - {{ $cuti->lastItem() ?? 0 }} dari {{ $cuti->total() }} data</p>
                <div>{{ $cuti->links() }}</div>
            </div>
        </div>

        {{-- ===================== MODAL DETAIL (COMPACT VERSION) ===================== --}}
        <div x-show="showDetail" x-cloak 
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-[100] p-4" 
            x-transition.opacity> 
        
            <div @click.away="closeModal()" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6 text-sm max-h-[90vh] overflow-y-auto border border-gray-100"> 
                
                <div class="flex justify-between items-center mb-5 border-b pb-3">
                    <h2 class="text-lg font-bold text-sky-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-500"></i> Detail Pengajuan Cuti
                    </h2>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div x-show="selectedCuti" class="grid grid-cols-2 gap-3 text-gray-700">
                    {{-- Box Informasi --}}
                    <div class="p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">Nama Pegawai</p>
                        <p x-text="selectedCuti?.nama" class="font-bold text-gray-800"></p>
                    </div>
                    <div class="p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">NIP</p>
                        <p x-text="selectedCuti?.nip" class="font-medium text-gray-700"></p>
                    </div>
                    <div class="p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">Jenis Cuti</p>
                        <p x-text="selectedCuti?.jenis_cuti" class="font-bold text-sky-600"></p>
                    </div>
                    <div class="p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">Durasi</p>
                        <p class="font-bold text-gray-800"><span x-text="selectedCuti?.jumlah_hari"></span> Hari</p>
                    </div>
                    <div class="col-span-2 p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">Periode Tanggal</p>
                        <p class="font-medium"><span x-text="selectedCuti?.tanggal_mulai"></span> s/d <span x-text="selectedCuti?.tanggal_selesai"></span></p>
                    </div>
                    <div class="col-span-2 p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider mb-0.5">Alasan Cuti</p>
                        <p x-text="selectedCuti?.alasan_cuti" class="italic text-gray-600 leading-relaxed"></p>
                    </div>
                    <div class="col-span-2 p-2.5 border border-gray-100 rounded-xl bg-gray-50/50 shadow-sm flex items-center justify-between">
                        <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider">Status Saat Ini</p>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter"
                            :class="statusClass(selectedCuti?.status)"
                            x-text="selectedCuti?.status">
                        </span>
                    </div>
                </div>

                <div class="text-right mt-6 pt-4 border-t border-gray-50">
                    <button @click="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg text-xs font-bold transition shadow-md active:scale-95">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div> {{-- end Alpine x-data --}}
</div>

{{-- SCRIPT AREA --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Alpine JS Component Logic
    function cutiTable() {
        return {
            showDetail: false,
            selectedCuti: null,
            openDetailModal(data) {
                this.selectedCuti = data;
                this.showDetail = true;
            },
            closeModal() {
                this.showDetail = false;
                this.selectedCuti = null;
            },
            statusClass(status) {
                if (status === 'disetujui') return 'bg-green-100 text-green-700';
                if (status === 'ditolak') return 'bg-red-100 text-red-700';
                return 'bg-yellow-100 text-yellow-700';
            }
        }
    }

    // 2. Global Confirm Delete Function (SweetAlert)
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Pengajuan Cuti?',
            text: "Data pengajuan milik " + nama + " akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

{{-- Notifikasi Berhasil Hapus --}}
@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        borderRadius: '15px'
    });
</script>
@endif

{{-- 4. Notifikasi Error --}}
@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000
    });
</script>
@endif
@endsection