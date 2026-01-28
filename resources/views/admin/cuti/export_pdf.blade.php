<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Cuti Pegawai</title>
    <style>
        /* Pengaturan Kertas A4 */
        @page {
            margin: 1.5cm; /* Margin standar Word */
        }

        body { 
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            line-height: 1.4; 
            margin: 0; 
        }
        
        /* Kop Surat Tabel */
        .kop-table { 
            width: 100%; 
            border-bottom: 4px solid #000; 
            margin-bottom: 2px; 
        }
        
        .kop-bottom-line { 
            border-bottom: 1px solid #000; 
            margin-bottom: 20px; 
        }

        .logo-cell { width: 15%; vertical-align: middle; text-align: center; padding-bottom: 10px; }
        .text-cell { width: 85%; text-align: center; vertical-align: middle; padding-bottom: 10px; }

        /* Logo Lebih Besar */
        .logo { width: 100px; height: auto; } 

        .pemkot { font-size: 16pt; font-weight: bold; margin: 0; }
        .dinas { font-size: 18pt; font-weight: bold; margin: 0; }
        .alamat { font-size: 10pt; margin: 2px 0; }
        .kontak { font-size: 10pt; margin: 0; }

        .judul-laporan { 
            text-align: center; 
            font-size: 14pt; 
            font-weight: bold; 
            text-decoration: underline; 
            margin: 25px 0; 
            text-transform: uppercase; 
        }

        /* Logic Repeat Header Tabel */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11pt; /* Font lebih besar agar mudah dibaca */
        }

        /* Ini adalah kunci agar header mengulang di tiap halaman */
        table.data-table thead { 
            display: table-header-group; 
        }

        table.data-table th { 
            background-color: #f2f2f2; 
            border: 1px solid #000; 
            padding: 10px; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 10pt;
        }
        
        table.data-table td { border: 1px solid #000; padding: 8px; vertical-align: top; }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* Tanda Tangan */
        .ttd-wrapper { margin-top: 40px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; font-size: 12pt; }
        .ttd-space { height: 70px; }
    </style>
</head>
<body>

    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('image/banjarbaru_logo.png') }}" class="logo">
            </td>
            <td class="text-cell">
                <div class="pemkot">PEMERINTAH KOTA BANJARBARU</div>
                <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                <div class="alamat">Jl. Pangeran Suriansyah Nomor 5 Banjarbaru, Kalimantan Selatan</div>
                <div class="kontak">Telp./Fax. (0511) 6749126 E-mail: kominfobjb@banjarbarukota.go.id</div>
            </td>
        </tr>
    </table>
    <div class="kop-bottom-line"></div>

    <div class="judul-laporan">REKAPITULASI DATA CUTI PEGAWAI</div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama / NIP</th>
                <th width="15%">Jenis Cuti</th>
                <th width="15%">Tanggal</th>
                <th width="8%">Hari</th>
                <th width="20%">Alasan</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuti as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <span class="font-bold">{{ $item->nama }}</span><br>
                    NIP. {{ $item->nip }}
                </td>
                <td class="text-center">{{ $item->jenis_cuti }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s/d 
                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}
                </td>
                <td class="text-center font-bold">{{ $item->jumlah_hari }}</td>
                <td>{{ $item->alasan_cuti }}</td>
                <td class="text-center font-bold">
                    {{ strtoupper($item->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd-wrapper">
        <div class="ttd-box">
            <p>Banjarbaru, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Admin Kepegawaian,</p>
            <div class="ttd-space"></div>
            <p><strong>( ________________________ )</strong></p>
            <p>NIP. ........................................</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>