<?php
namespace App\Exports;

use App\Models\Cuti;
use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CutiExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell
{
    protected $pegawaiId;
    protected $tahun;
    private $rowNumber = 0;

    public function __construct($pegawaiId, $tahun)
    {
        $this->pegawaiId = $pegawaiId;
        $this->tahun = $tahun;
    }

    public function startCell(): string { return 'A8'; }

    public function collection()
    {
        // PERBAIKAN 1: Gunakan 'id_pegawai' (karena yang dikirim dari controller adalah ID Pegawai)
        $query = Cuti::where('id_pegawai', $this->pegawaiId);

        // PERBAIKAN 2: Logika jika tahun dipilih 'semua'
        if ($this->tahun !== 'semua') {
            $query->where('tahun', $this->tahun);
        }

        return $query->orderBy('tanggal_mulai', 'asc')->get();
    }

    public function headings(): array
    {
        // PERBAIKAN 3: Hapus 'ALAMAT'
        return ['NO', 'JENIS CUTI', 'MULAI', 'SELESAI', 'HARI', 'ALASAN', 'STATUS', 'PENGGANTI'];
    }

    public function map($cuti): array
    {
        return [
            ++$this->rowNumber,
            $cuti->jenis_cuti,
            \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y'),
            \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y'),
            $cuti->jumlah_hari . ' hari',
            // PERBAIKAN 4: Hapus kolom alamat di sini
            $cuti->keterangan, 
            ucfirst($cuti->status),
            $cuti->delegasi->nama ?? '-'
        ];
    }

public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $pegawai = Pegawai::find($this->pegawaiId);
            
            // 1. Perbaikan Judul: Merge dari A sampai H (8 kolom)
            $sheet->mergeCells('A1:H1'); 
            $sheet->setCellValue('A1', 'LAPORAN RIWAYAT CUTI PEGAWAI');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Informasi Pegawai
            $sheet->setCellValue('A3', 'NAMA');   $sheet->setCellValue('B3', ': ' . ($pegawai->nama ?? '-'));
            $sheet->setCellValue('A4', 'NIP');    $sheet->setCellValue('B4', ': ' . ($pegawai->nip ?? '-'));
            $sheet->setCellValue('A5', 'JABATAN');$sheet->setCellValue('B5', ': ' . ($pegawai->jabatan ?? '-'));
            $sheet->setCellValue('A6', 'TAHUN');  $sheet->setCellValue('B6', ': ' . ($this->tahun == 'semua' ? 'Semua Tahun' : $this->tahun));
            $sheet->getStyle('A3:A6')->getFont()->setBold(true);

            // 2. Perbaikan Jangkauan Tabel: Ubah G menjadi H
            $lastRow = 8 + $this->rowNumber;
            $maxRow = max(10, $lastRow); 
            $tableRange = 'A8:H' . $maxRow; 

            // 3. Garis/Border: Sekarang mencakup kolom H
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            // 4. Header Biru: Sekarang mewarnai A sampai H
            $sheet->getStyle('A8:H8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0070C0');
            $sheet->getStyle('A8:H8')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
            
            // 5. Perataan (Alignment)
            $sheet->getStyle($tableRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // Kolom F (Alasan) dan H (Pengganti) dibuat Rata Kiri agar rapi jika teks panjang
            $sheet->getStyle('F9:F' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H9:H' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // 6. Auto-size kolom: Sekarang memproses kolom A sampai H
            foreach (range('A', 'H') as $col) { 
                $sheet->getColumnDimension($col)->setAutoSize(true); 
            }
        },
    ];
}
}