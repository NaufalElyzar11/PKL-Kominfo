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
                                        $statusLower = strtolower($c->status);
                                        $status_color = match (true) {
                                            str_contains($statusLower, 'disetujui') => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                            str_contains($statusLower, 'ditolak') => 'bg-rose-100 text-rose-700 border border-rose-200',
                                            str_contains($statusLower, 'proses') || str_contains($statusLower, 'pejabat') => 'bg-sky-100 text-sky-700 border border-sky-200',
                                            str_contains($statusLower, 'menunggu') => 'bg-amber-100 text-amber-700 border border-amber-200',
                                            default => 'bg-gray-100 text-gray-700 border border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[9px] rounded-full font-bold {{ $status_color }}">
                                        @if(str_contains($statusLower, 'disetujui'))
                                            <i class="fa-solid fa-circle-check text-[8px]"></i>
                                        @elseif(str_contains($statusLower, 'ditolak'))
                                            <i class="fa-solid fa-circle-xmark text-[8px]"></i>
                                        @elseif(str_contains($statusLower, 'menunggu'))
                                            <i class="fa-solid fa-clock text-[8px]"></i>
                                        @elseif(str_contains($statusLower, 'proses'))
                                            <i class="fa-solid fa-spinner text-[8px]"></i>
                                        @endif
                                        {{ ucwords($c->status) }}
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

        {{-- ===================== MODAL DETAIL (PREMIUM DESIGN) ===================== --}}
        <div x-show="showDetail" x-cloak 
             class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-2 sm:p-4" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        
            <div @click.away="closeModal()" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100"> 
                
                {{-- ========== HEADER DENGAN GRADIENT (BLUE) ========== --}}
                <div class="bg-gradient-to-r from-blue-700 to-blue-900 px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fa-solid fa-file-contract text-white text-lg sm:text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-base sm:text-lg tracking-wide">Detail Pengajuan Cuti</h3>
                                <p class="text-blue-100 text-[10px] sm:text-xs">Informasi lengkap pengajuan cuti pegawai</p>
                            </div>
                        </div>
                        <button @click="closeModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 group">
                            <i class="fa-solid fa-xmark text-white group-hover:rotate-90 transition-transform duration-200"></i>
                        </button>
                    </div>
                </div>

                {{-- ========== CONTENT ========== --}}
                <div class="p-4 sm:p-6 max-h-[85vh] overflow-y-auto">
                    
                    {{-- Status Badge Large --}}
                    <div class="flex justify-center mb-6">
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm"
                              :class="statusClass(selectedCuti?.status)">
                            <i class="fa-solid" 
                               :class="{
                                   'fa-circle-check': selectedCuti?.status.toLowerCase().includes('disetujui'),
                                   'fa-circle-xmark': selectedCuti?.status.toLowerCase().includes('ditolak'),
                                   'fa-clock': selectedCuti?.status.toLowerCase().includes('menunggu'),
                                   'fa-spinner fa-spin': selectedCuti?.status.toLowerCase().includes('proses')
                               }"></i>
                            <span x-text="selectedCuti?.status"></span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Data Pegawai Box --}}
                        <div class="bg-slate-50 rounded-xl p-4 border border-gray-100 sm:col-span-2">
                            <div class="flex items-center gap-2 mb-3 border-b border-gray-200 pb-2">
                                <i class="fa-solid fa-user text-blue-600 text-xs"></i>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Identitas Pegawai</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Nama Pegawai</p>
                                    <p class="text-sm font-bold text-gray-800" x-text="selectedCuti?.nama"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">NIP</p>
                                    <p class="text-sm font-mono text-gray-700" x-text="selectedCuti?.nip"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Jenis Cuti Box --}}
                        <div class="bg-blue-50/50 rounded-xl p-3 border border-blue-100">
                             <p class="text-[10px] text-blue-400 font-bold uppercase mb-1">Jenis Cuti</p>
                             <div class="flex items-center gap-2">
                                 <i class="fa-solid fa-layer-group text-blue-600"></i>
                                 <p class="text-sm font-bold text-blue-800" x-text="selectedCuti?.jenis_cuti"></p>
                             </div>
                        </div>

                        {{-- Durasi Box --}}
                        <div class="bg-amber-50/50 rounded-xl p-3 border border-amber-100">
                             <p class="text-[10px] text-amber-400 font-bold uppercase mb-1">Durasi Cuti</p>
                             <div class="flex items-center gap-2">
                                 <i class="fa-regular fa-clock text-amber-600"></i>
                                 <p class="text-sm font-bold text-amber-800"><span x-text="selectedCuti?.jumlah_hari"></span> Hari</p>
                             </div>
                        </div>

                        {{-- Tanggal Box --}}
                        <div class="sm:col-span-2 bg-gray-50 rounded-xl p-3 border border-gray-100 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Mulai Tanggal</p>
                                <p class="text-xs font-bold text-gray-700" x-text="selectedCuti?.tanggal_mulai"></p>
                            </div>
                            <div class="text-gray-300"><i class="fa-solid fa-arrow-right-long"></i></div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Sampai Tanggal</p>
                                <p class="text-xs font-bold text-gray-700" x-text="selectedCuti?.tanggal_selesai"></p>
                            </div>
                        </div>

                        {{-- Alasan Box --}}
                        <div class="sm:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">
                                <i class="fa-solid fa-quote-left mr-1 text-gray-300"></i> Alasan Cuti
                            </p>
                            <p class="text-sm text-gray-600 italic leading-relaxed pl-3 border-l-2 border-blue-200" x-text="selectedCuti?.alasan_cuti"></p>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="flex justify-end pt-4 mt-6 border-t border-gray-100">
                        <button @click="closeModal()" 
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[11px] sm:text-xs font-bold transition-all flex items-center gap-2">
                            <i class="fa-solid fa-xmark"></i> Tutup
                        </button>
                    </div>
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
                setTimeout(() => {
                    this.selectedCuti = null;
                }, 300); // Tunggu animasi selesai
            },
            statusClass(status) {
                if (!status) return 'bg-gray-100 text-gray-700';
                const s = status.toLowerCase();
                if (s.includes('disetujui')) return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                if (s.includes('ditolak')) return 'bg-rose-100 text-rose-700 border border-rose-200';
                if (s.includes('proses') || s.includes('pejabat')) return 'bg-sky-100 text-sky-700 border border-sky-200';
                if (s.includes('menunggu')) return 'bg-amber-100 text-amber-700 border border-amber-200';
                return 'bg-gray-100 text-gray-700 border border-gray-200';
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