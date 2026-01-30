<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Cuti Pegawai</title>
    <style>
        @page { margin: 1.2cm 1.5cm; }

        body { 
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0;
        }

        /* Tambahkan table-layout: fixed agar ukuran kolom presisi */
        table.main-report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; 
            border: none;
        }

        thead { display: table-header-group; }
        
        /* Hilangkan padding pada th container kop agar garis menempel ke pinggir */
        .header-container { padding: 0 !important; border: none !important; }

        .line-double {
            border-bottom: 3px solid #000;
            padding-bottom: 1px;
            margin-bottom: 2px;
        }
        .line-single {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }
        
        .logo { width: 80px; height: auto; }
        
        .header-text { text-align: center; }
        .pemkot { font-size: 14pt; font-weight: bold; margin: 0; }
        .dinas { font-size: 16pt; font-weight: bold; margin: 0; }
        .alamat, .kontak { font-size: 10pt; font-weight: normal; margin: 0; }

        .judul-laporan { 
            text-align: center; 
            font-size: 12pt; 
            font-weight: bold; 
            text-decoration: underline; 
            padding: 15px 0; 
            text-transform: uppercase; 
        }

        .data-table-header th { 
            background-color: #f2f2f2; 
            border: 1px solid #000; 
            padding: 8px 4px; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 9pt;
            text-align: center;
        }
        
        .data-cell { 
            border: 1px solid #000; 
            padding: 6px 4px; 
            vertical-align: top; 
            font-size: 10pt; 
            word-wrap: break-word; /* Mencegah teks meluber */
        }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .ttd-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 230px; text-align: center; font-size: 11pt; }
        .ttd-space { height: 60px; }
    </style>
</head>
<body>

    <table class="main-report-table">
        <thead>
            <tr class="no-border">
                <th colspan="8" class="header-container">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="15%" align="center" style="padding-bottom: 10px;">
                                <img src="{{ public_path('image/banjarbaru_logo.png') }}" class="logo">
                            </td>
                            <td width="85%" class="header-text" style="padding-bottom: 10px;">
                                <div class="pemkot">PEMERINTAH KOTA BANJARBARU</div>
                                <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                                <div class="alamat">Jl. Pangeran Suriansyah No. 5, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru 70711 </div>
                                <div class="kontak">Telp./Fax. (0511) 5200052 E-mail: diskominfo@banjarbarukota.go.id</div>
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
                <th width="15%">Pengganti</th><th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuti as $index => $item)
                <tr>
                    <td class="data-cell text-center">{{ $index + 1 }}</td>
                    <td class="data-cell">
                        <span class="font-bold">{{ $item->nama }}</span><br>
                        <small>NIP. {{ $item->nip }}</small>
                    </td>
                    <td class="data-cell text-center">{{ $item->jenis_cuti }}</td>
                    <td class="data-cell text-center">
                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/y') }}<br>s/d<br>
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/y') }}
                    </td>
                    <td class="data-cell text-center font-bold">{{ $item->jumlah_hari }}</td>
                    <td class="data-cell">{{ Str::limit($item->alasan_cuti, 40) }}</td>
                    <td class="data-cell">
                        <div class="font-bold" >{{ $item->delegasi->nama ?? '-' }}</div>
                        <div style="font-size: 8pt; color: #666;">{{ $item->delegasi->jabatan ?? '' }}</div>
                    </td>
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
            <p><strong>Nariyati, A.Md</strong></p>
            <p>NIP. 197202062001122001</p>
        </div>
    </div>

</body>
</html>