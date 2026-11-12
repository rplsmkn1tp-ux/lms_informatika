<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor {{ $student->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1, h2, h3 {
            text-align: center;
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
            border: none;
        }
        .info td {
            padding: 3px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 6px;
        }
        table.data th {
            background-color: #f0f0f0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge-success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>LAPORAN HASIL BELAJAR INFORMATIKA</h1>
    <h2>SMK NEGERI 1 TANJUNGPANDAN</h2>
    
    <div class="info">
        <table>
            <tr>
                <td width="15%">Nama Siswa</td>
                <td width="35%">: <strong>{{ $student->name }}</strong></td>
                <td width="15%">Kelas</td>
                <td width="35%">: <strong>{{ $classroom->name }}</strong></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>: {{ $student->nisn }}</td>
                <td>Tahun Ajaran</td>
                <td>: {{ $academicYear->name }}</td>
            </tr>
        </table>
    </div>

    <h3>Rincian Nilai Akademik</h3>
    <table class="data">
        <thead>
            <tr>
                <th width="40%">Komponen Penilaian</th>
                <th width="30%">Nilai Rata-rata</th>
                <th width="30%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ulangan Harian (UH) / Tujuan Pembelajaran</td>
                <td class="text-center">{{ number_format($reportData['avg_uh'] ?? 0, 1) }}</td>
                <td class="text-center">Bobot: {{ \App\Models\GradeWeight::where('classroom_id', $classroom->id)->value('weight_uh') ?? 40 }}%</td>
            </tr>
            <tr>
                <td>Tugas Praktik / Portofolio</td>
                <td class="text-center">{{ number_format($reportData['avg_tugas'] ?? 0, 1) }}</td>
                <td class="text-center">Bobot: {{ \App\Models\GradeWeight::where('classroom_id', $classroom->id)->value('weight_tugas') ?? 30 }}%</td>
            </tr>
            <tr>
                <td>Ujian (UAS/MID)</td>
                <td class="text-center">{{ number_format($reportData['avg_uas_mid'] ?? 0, 1) }}</td>
                <td class="text-center">Bobot: {{ \App\Models\GradeWeight::where('classroom_id', $classroom->id)->value('weight_uas_mid') ?? 20 }}%</td>
            </tr>
            <tr>
                <td>Kedisiplinan & Kehadiran</td>
                <td class="text-center">{{ number_format($reportData['attendance_score'] ?? 0, 1) }}</td>
                <td class="text-center">Bobot: {{ \App\Models\GradeWeight::where('classroom_id', $classroom->id)->value('weight_absensi') ?? 10 }}%</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">NILAI AKHIR</th>
                <th class="text-center" style="font-size: 14px;">{{ number_format($reportData['final_score'] ?? 0, 1) }}</th>
                <th class="text-center">
                    @if(($reportData['final_score'] ?? 0) >= 75)
                        <span style="color: green;">TUNTAS</span>
                    @else
                        <span style="color: red;">BELUM TUNTAS</span>
                    @endif
                </th>
            </tr>
        </tfoot>
    </table>

    <h3>Rekapitulasi Kehadiran</h3>
    <table class="data" style="width: 50%; margin: 0 auto;">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah (Hari)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Hadir</td>
                <td class="text-center">{{ $reportData['attendance']['Hadir'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Sakit</td>
                <td class="text-center">{{ $reportData['attendance']['Sakit'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td class="text-center">{{ $reportData['attendance']['Izin'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Alpa (Tanpa Keterangan)</td>
                <td class="text-center">{{ $reportData['attendance']['Alpa'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 50px; width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 33%; text-align: center;">
                    Mengetahui,<br>
                    Orang Tua/Wali
                    <br><br><br><br><br>
                    (.....................................)
                </td>
                <td style="width: 34%; text-align: center;"></td>
                <td style="width: 33%; text-align: center;">
                    Tanjungpandan, {{ date('d F Y') }}<br>
                    Guru Mata Pelajaran
                    <br><br><br><br><br>
                    <strong>{{ $classroom->teacher->name ?? 'Guru Informatika' }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
