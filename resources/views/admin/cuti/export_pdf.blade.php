<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Cuti Pegawai</title>
    <style>
        /* Pengaturan Kertas A4 Portrait */
        @page { margin: 1.2cm 1.5cm; }

        body { 
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0;
        }

        /* Struktur Tabel Utama untuk Repeat Header */
        table.main-report-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        /* Memaksa thead mengulang di tiap halaman */
        thead { display: table-header-group; }
        
        /* Reset border untuk elemen di dalam thead */
        .no-border td, .no-border th { border: none !important; }

        /* Styling Garis Ganda Kop Surat */
        .line-double {
            border-bottom: 4px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .line-single {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }
        
        .logo { width: 110px; height: auto; vertical-align: middle; }
        
        .header-text { text-align: center; vertical-align: middle; }
        .pemkot { font-size: 16pt; font-weight: bold; margin: 0; }
        .dinas { font-size: 18pt; font-weight: bold; margin: 0; }
        
        /* Alamat & Kontak: Tidak Bold */
        .alamat { font-size: 11pt; font-weight: normal; margin: 5px 0 2px 0; }
        .kontak { font-size: 11pt; font-weight: normal; margin: 0; }

        .judul-laporan { 
            text-align: center; 
            font-size: 14pt; 
            font-weight: bold; 
            text-decoration: underline; 
            padding: 20px 0; 
            text-transform: uppercase; 
        }

        /* Styling Tabel Data */
        .data-table-header th { 
            background-color: #f2f2f2; 
            border: 1px solid #000; 
            padding: 10px; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 10pt;
            text-align: center;
        }
        
        .data-cell { border: 1px solid #000; padding: 8px; vertical-align: top; font-size: 11pt; }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* Area Tanda Tangan */
        .ttd-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; font-size: 12pt; }
        .ttd-space { height: 75px; }
    </style>
</head>
<body>

    <table class="main-report-table">
        <thead>
            <tr class="no-border">
                <th colspan="7">
                    <table width="100%" border="0">
                        <tr>
                            <td width="15%" align="center">
                                <img src="{{ public_path('image/banjarbaru_logo.png') }}" class="logo">
                            </td>
                            <td width="85%" class="header-text">
                                <div class="pemkot">PEMERINTAH KOTA BANJARBARU</div>
                                <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                                <div class="alamat">Jl. Pangeran Suriansyah No. 5, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru 70711</div>
                                <div class="kontak">Telp./Fax. (0511)5200052 E-mail: diskominfo@banjarbarukota.go.id</div>
                            </td>
                        </tr>
                    </table>
                    <div class="line-double"></div>
                    <div class="line-single"></div>

                    <div class="judul-laporan">REKAPITULASI DATA CUTI PEGAWAI</div>
                </th>
            </tr>
            <tr class="data-table-header">
                <th width="5%">No</th>
                <th width="25%">Nama / NIP</th>
                <th width="12%">Jenis</th>
                <th width="18%">Tanggal</th>
                <th width="8%">Hari</th>
                <th width="20%">Alasan</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuti as $index => $item)
            <tr>
                <td class="data-cell text-center">{{ $index + 1 }}</td>
                <td class="data-cell">
                    <span class="font-bold">{{ $item->nama }}</span><br>
                    NIP. {{ $item->nip }}
                </td>
                <td class="data-cell text-center">{{ $item->jenis_cuti }}</td>
                <td class="data-cell text-center">
                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }}<br>s/d<br>
                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}
                </td>
                <td class="data-cell text-center font-bold">{{ $item->jumlah_hari }}</td>
                <td class="data-cell">{{ $item->alasan_cuti }}</td>
                <td class="data-cell text-center font-bold">
                    {{ strtoupper($item->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd-section">
        <div class="ttd-box">
            <p>Banjarbaru, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Administrasi Kepegawaian,</p>
            <div class="ttd-space"></div>
            <p><strong>( ________________________ )</strong></p>
            <p>NIP. ........................................</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>