@extends('layouts.admin')

@section('title', 'Daftar Pengajuan Cuti Pegawai')

@section('content')
<div class="min-h-screen px-3 py-4 bg-[#E3F2FD]">
    {{-- Wrapper utama dgn Alpine scope --}}
    <div x-data="cutiTable()" x-init="" @keydown.escape.window="closeModal()" class="space-y-3">

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="mb-2 px-3 py-2 bg-green-100 text-green-700 border border-green-300 rounded text-xs">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-2 px-3 py-2 bg-red-100 text-red-700 border border-red-300 rounded text-xs">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow p-4 border border-gray-200 space-y-3">
            <h1 class="text-lg font-bold text-gray-800">Daftar Pengajuan Cuti Pegawai</h1>

            {{-- Filter sederhana --}}
            <form method="GET" action="{{ route('admin.cuti.index') }}" class="flex flex-wrap items-center gap-2 text-[10px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/NIP"
                    class="px-2 py-1 border border-gray-300 rounded w-40 focus:ring-1 focus:ring-blue-400">

                <select name="status" class="px-2 py-1 border border-gray-300 rounded w-32 text-[10px] focus:ring-1 focus:ring-blue-400">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status')=='pending'? 'selected':'' }}>⏳ Menunggu</option>
                    <option value="disetujui" {{ request('status')=='disetujui'? 'selected':'' }}>✅ Disetujui</option>
                    <option value="ditolak" {{ request('status')=='ditolak'? 'selected':'' }}>❌ Ditolak</option>
                </select>

                <button type="submit" class="px-2 py-1 bg-sky-600 text-white rounded text-[10px] hover:bg-sky-700">
                    Filter
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('admin.cuti.index') }}" class="px-2 py-1 bg-gray-500 text-white rounded text-[10px] hover:bg-gray-600">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Tabel cuti (compact) --}}
            <div class="overflow-x-auto overflow-y-auto max-h-[430px] text-xs mt-2 border border-gray-200 rounded-lg shadow-sm">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gradient-to-r from-[#0288D1] to-[#03A9F4] text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-2 py-1 border">No</th>
                            <th class="px-2 py-1 border">Nama</th>
                            <th class="px-2 py-1 border">Jabatan</th>
                            <th class="px-2 py-1 border">Jenis</th>
                            <th class="px-2 py-1 border">Mulai</th>
                            <th class="px-2 py-1 border">Selesai</th>
                            <th class="px-2 py-1 border">Hari</th>
                            <th class="px-2 py-1 border">Alasan</th>
                            <th class="px-2 py-1 border">Status</th>
                            <th class="px-2 py-1 border text-center">Aksi</th>
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

                            <tr class="border hover:bg-gray-50" data-cuti='@json($cutiData)'>
                                <td class="px-2 py-1 border text-center">{{ $i + $cuti->firstItem() }}</td>
                                <td class="px-2 py-1 border">{{ $cutiData['nama'] }}</td>
                                <td class="px-2 py-1 border">{{ $cutiData['jabatan'] }}</td>
                                <td class="px-2 py-1 border">{{ $cutiData['jenis_cuti'] }}</td>
                                <td class="px-2 py-1 border font-mono">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d/m/y') }}</td>
                                <td class="px-2 py-1 border font-mono">{{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d/m/y') }}</td>
                                <td class="px-2 py-1 border text-center">{{ $cutiData['jumlah_hari'] }}</td>

                                <td class="px-2 py-1 border truncate max-w-[120px]" title="{{ $cutiData['alasan_cuti'] }}">
                                    {{ \Illuminate\Support\Str::limit($cutiData['alasan_cuti'], 35) }}
                                </td>


                                <td class="px-2 py-1 border text-center">
                                    @php
                                        $status_color = match ($c->status) {
                                            'disetujui' => 'bg-green-200 text-green-700 border border-green-300',
                                            'ditolak' => 'bg-red-200 text-red-700 border border-red-300',
                                            default => 'bg-yellow-200 text-yellow-700 border border-yellow-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-[2px] text-[9px] rounded {{ $status_color }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>

                                <td class="px-2 py-1 border text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button
                                            @click="openDetailModal($el.closest('tr').dataset.cuti)"
                                            class="text-blue-600 hover:text-blue-800"
                                            title="Detail">
                                            <i class="fa-solid fa-eye text-[11px]"></i>
                                        </button>

                                        {{-- BUTTON DELETE YANG SUDAH DIUBAH --}}
                                        <button
                                            type="button"
                                            @click="confirmDelete({{ $c->id }}, '{{ $cutiData['nama'] }}')"
                                            class="text-red-600 hover:text-red-800"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-3 text-gray-500">Belum ada data pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3 flex items-center justify-between">
                <div class="text-[12px] text-gray-600">
                    Menampilkan {{ $cuti->firstItem() ?? 0 }} - {{ $cuti->lastItem() ?? 0 }} dari {{ $cuti->total() }} data
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        @click="goToPage({{ max($cuti->currentPage() - 1, 1) }})"
                        :disabled="{{ $cuti->currentPage() }} <= 1"
                        class="px-2 py-1 border rounded hover:bg-gray-100 text-[12px] {{ $cuti->currentPage() <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                        &lt;
                    </button>

                    @for ($p = max(1, $cuti->currentPage()-2); $p <= min($cuti->lastPage(), $cuti->currentPage()+2); $p++)
                        <button
                            @click="goToPage({{ $p }})"
                            class="px-3 py-1 rounded text-[12px] {{ $p == $cuti->currentPage() ? 'bg-blue-600 text-white' : 'border hover:bg-gray-100' }}">
                            {{ $p }}
                        </button>
                    @endfor

                    <button
                        @click="goToPage({{ min($cuti->currentPage() + 1, $cuti->lastPage()) }})"
                        :disabled="{{ $cuti->currentPage() }} >= {{ $cuti->lastPage() }}"
                        class="px-2 py-1 border rounded hover:bg-gray-100 text-[12px] {{ $cuti->currentPage() >= $cuti->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                        &gt;
                    </button>

                    <div class="hidden sm:block">
                        {{ $cuti->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>

        </div> {{-- end div bg-white/80 --}}

        {{-- ===================== MODAL DETAIL ===================== --}}
        <div x-show="showDetail" x-cloak 
            class="fixed inset-0 bg-gray-900 bg-opacity-70 grid place-items-center z-50 p-4" 
            x-transition.opacity> 
        
            <div @click.outside="closeModal()" x-transition.scale.duration.200ms
                class="bg-white w-full max-w-lg rounded-xl shadow-2xl p-5 text-sm 
                        max-h-[90vh] overflow-y-auto"> 
                
                <h2 class="text-lg font-bold text-sky-700 mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fa-solid fa-circle-info"></i> Detail Pengajuan Cuti
                </h2>

                <template x-if="selectedCuti">
                    <div class="grid grid-cols-2 gap-4 text-gray-700">

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">Nama Pegawai</p>
                            <p x-text="selectedCuti.nama" class="text-sm font-medium"></p>
                        </div>

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">NIP</p>
                            <p x-text="selectedCuti.nipMasked" class="text-sm font-medium"></p>
                        </div>

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">Jenis Cuti</p>
                            <p x-text="selectedCuti.jenis_cuti" class="text-sm font-medium"></p>
                        </div>

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">Tanggal Cuti</p>
                            <p x-text="selectedCuti.tanggal_mulai + ' - ' + selectedCuti.tanggal_selesai" class="text-sm font-medium"></p>
                        </div>

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">Jumlah Hari</p>
                            <p x-text="selectedCuti.jumlah_hari + ' Hari'" class="text-sm font-medium"></p>
                        </div>

                        <div class="col-span-2 space-y-1 p-2 border border-gray-200 rounded-lg">
                            <p class="font-semibold text-gray-500 text-xs">Alasan Cuti</p>
                            <p x-text="selectedCuti.alasan_cuti" class="text-sm font-medium"></p>
                        </div>

                        <div class="space-y-1 p-2 border border-gray-200 rounded-lg flex flex-col justify-center">
                            <p class="font-semibold text-gray-500 text-xs">Status Pengajuan</p>
                            <span class="px-3 py-[2px] rounded text-white text-xs font-semibold self-start"
                                :class="statusClass(selectedCuti.status)"
                                x-text="statusText(selectedCuti.status)"></span>
                        </div>

                    </div>
                </template>

                <div class="text-right mt-5 pt-3 border-t">
                    <button @click="closeModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ===================== MODAL KONFIRMASI HAPUS ===================== --}}
        <div x-show="showDelete" x-cloak 
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-[60]"
            x-transition.opacity>
            
            <div @click.outside="closeDelete()"
                x-transition.scale.duration.200ms
                class="bg-white w-full max-w-sm rounded-xl shadow-xl p-5 text-sm">

                <div class="text-center">
                    <div class="text-red-600 text-4xl mb-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Yakin ingin menghapus?</h2>
                    <p class="text-gray-600 mt-1 text-sm">
                        Data pengajuan cuti <b x-text="deleteName"></b> akan dihapus permanen.
                    </p>
                </div>

                <form :action="'/admin/cuti/' + deleteId" method="POST" class="mt-4">
                    @csrf
                    @method('DELETE')

                    <div class="flex flex-col items-center gap-2 mt-5">

    <button type="button"
            @click="closeDelete()"
            class="w-full px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg text-sm">
        Batal
    </button>

    <button type="submit"
            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm">
        Hapus
    </button>

</div>

                </form>
            </div>
        </div>

    </div> {{-- end Alpine scope --}}
</div>

{{-- Alpine component script --}}
<script>
    function cutiTable() {
        return {
            showDetail: false,
            selectedCuti: null,

            showDelete: false,
            deleteId: null,
            deleteName: '',

            openDetailModal(cutiJson) {
                try {
                    const parsed = typeof cutiJson === 'string' ? JSON.parse(cutiJson) : cutiJson;
                    const nip = parsed.nip ?? '';
                    let nipMasked = '-';
                    if (nip && nip.length > 4) {
                        const start = nip.slice(0, 2);
                        const end = nip.slice(-2);
                        nipMasked = start + '*'.repeat(Math.max(nip.length - 4, 2)) + end;
                    } else {
                        nipMasked = nip || '-';
                    }
                    parsed.nipMasked = nipMasked;

                    this.selectedCuti = parsed;
                    this.showDetail = true;
                } catch (e) {
                    console.error('Gagal membuka modal detail cuti:', e);
                    this.selectedCuti = null;
                }
            },

            closeModal() {
                this.showDetail = false;
                this.selectedCuti = null;
            },

            confirmDelete(id, name) {
                this.deleteId = id;
                this.deleteName = name;
                this.showDelete = true;
            },

            closeDelete() {
                this.showDelete = false;
                this.deleteId = null;
                this.deleteName = '';
            },

            goToPage(page) {
                page = Math.max(1, page);
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            },

            statusClass(status) {
                if (!status) return 'bg-yellow-500';
                if (status === 'disetujui') return 'bg-green-600';
                if (status === 'ditolak') return 'bg-red-600';
                return 'bg-yellow-500';
            },

            statusText(status) {
                if (!status) return 'Menunggu';
                if (status === 'disetujui') return 'Disetujui';
                if (status === 'ditolak') return 'Ditolak';
                if (status === 'pending') return 'Menunggu';
                return status;
            }
        }
    }
</script>
@endsection
