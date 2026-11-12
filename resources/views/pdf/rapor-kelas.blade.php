<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Kelas {{ $classroom->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        h1, h2 {
            text-align: center;
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
            text-align: center;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 5px;
        }
        table.data th {
            background-color: #f0f0f0;
        }
        .text-center {
            text-align: center;
        }
        .check {
            color: green;
            font-weight: bold;
        }
        .cross {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>REKAPITULASI NILAI AKHIR SISWA</h1>
    <h2>SMK NEGERI 1 TANJUNGPANDAN</h2>
    
    <div class="info">
        Kelas: <strong>{{ $classroom->name }}</strong> | Tahun Ajaran: <strong>{{ $academicYear->name }}</strong>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">NISN</th>
                <th width="25%">Nama Siswa</th>
                <th>Rata-rata UH</th>
                <th>Rata-rata Tugas</th>
                <th>UAS/MID</th>
                <th>Nilai Absensi</th>
                <th>NILAI AKHIR</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                @php
                    $res = $results[$student->id] ?? null;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $student->nisn ?? '-' }}</td>
                    <td>{{ $student->name }}</td>
                    <td class="text-center">{{ $res ? number_format($res['avg_uh'], 1) : '-' }}</td>
                    <td class="text-center">{{ $res ? number_format($res['avg_tugas'], 1) : '-' }}</td>
                    <td class="text-center">{{ $res ? number_format($res['avg_uas_mid'], 1) : '-' }}</td>
                    <td class="text-center">{{ $res ? number_format($res['attendance_score'], 1) : '-' }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $res ? number_format($res['final_score'], 1) : '-' }}</td>
                    <td class="text-center">
                        @if($res)
                            @if($res['final_score'] >= 75)
                                <span class="check">TUNTAS</span>
                            @else
                                <span class="cross">BELUM TUNTAS</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Belum ada siswa di kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
