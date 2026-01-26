@extends('layouts.pegawai')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ showRejectModal: false, rejectId: null }">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Persetujuan Cuti Pegawai</h2>
        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Tahap: Atasan Langsung</span>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-3">Pegawai</th>
                    <th class="px-4 py-3">Jenis Cuti</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $c)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium">{{ $c->pegawai->nama ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $c->jenis_cuti }}</td>
                    <td class="px-4 py-3">{{ $c->tanggal_mulai }} s/d {{ $c->tanggal_selesai }}</td>
                    <td class="px-4 py-3 flex gap-2">
                        <form action="{{ route('atasan.approval.approve', $c->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Setuju</button>
                        </form>
                        
                        <button type="button" 
                            @click="rejectId = {{ $c->id }}; showRejectModal = true"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                            Tolak
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-10 text-gray-500 italic">Tidak ada pengajuan cuti yang menunggu persetujuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative z-10">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Alasan Penolakan</h3>
            
            <form :action="'{{ url('atasan/approval') }}/' + rejectId + '/tolak'" method="POST">
                @csrf
                <textarea name="catatan" rows="4" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 p-2 border"
                    placeholder="Contoh: Pekerjaan sedang menumpuk, mohon tunda cuti..."></textarea>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showRejectModal = false" class="text-gray-600 px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-red-700">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Menangkap session 'success' dari Controller
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000
        });
    @endif
</script>
@endsection