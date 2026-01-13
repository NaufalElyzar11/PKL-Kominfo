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
        // Query disesuaikan dengan kolom 'pegawai_id' dan 'tahun' di DB Anda
        return Cuti::where('pegawai_id', $this->pegawaiId)
                    ->where('tahun', $this->tahun)
                    ->orderBy('tanggal_mulai', 'asc')
                    ->get();
    }

    public function headings(): array
    {
        return ['NO', 'JENIS CUTI', 'MULAI', 'SELESAI', 'HARI', 'ALAMAT', 'ALASAN', 'STATUS'];
    }

    public function map($cuti): array
    {
        return [
            ++$this->rowNumber,
            $cuti->jenis_cuti, // Sesuai kolom DB
            \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y'),
            \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y'),
            $cuti->jumlah_hari . ' hari',
            $cuti->alamat, // Sesuai kolom DB
            $cuti->keterangan, // Sesuai kolom DB (Anda menggunakan nama 'keterangan' di DB)
            ucfirst($cuti->status), // Sesuai kolom DB
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $pegawai = Pegawai::find($this->pegawaiId);
                
                // Judul Laporan
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN RIWAYAT CUTI PEGAWAI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Informasi Pegawai (A3 - B6)
                $sheet->setCellValue('A3', 'NAMA');   $sheet->setCellValue('B3', ': ' . ($pegawai->nama ?? '-'));
                $sheet->setCellValue('A4', 'NIP');    $sheet->setCellValue('B4', ': ' . ($pegawai->nip ?? '-'));
                $sheet->setCellValue('A5', 'JABATAN');$sheet->setCellValue('B5', ': ' . ($pegawai->jabatan ?? '-'));
                $sheet->setCellValue('A6', 'TAHUN');  $sheet->setCellValue('B6', ': ' . $this->tahun);
                $sheet->getStyle('A3:A6')->getFont()->setBold(true);

                // Styling Tabel (Mulai A8)
                $lastRow = 8 + $this->rowNumber;
                $maxRow = max(15, $lastRow); 
                $tableRange = 'A8:H' . $maxRow;

                // Garis/Border
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Header Biru
                $sheet->getStyle('A8:H8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0070C0');
                $sheet->getStyle('A8:H8')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
                
                $sheet->getStyle($tableRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F9:G' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                foreach (range('A', 'H') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
            },
        ];
    }
}