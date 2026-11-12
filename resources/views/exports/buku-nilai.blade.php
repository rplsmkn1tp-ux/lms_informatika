<table>
    <thead>
        <tr>
            <th colspan="2">Kelas: {{ $classroom->name }}</th>
            <th colspan="3">Semester: {{ $academicYear->semester }} ({{ $academicYear->year }})</th>
        </tr>
        <tr>
            <th>NIS</th>
            <th>Nama Siswa</th>
            @foreach($columns as $col)
                <th>{{ $col->label }}</th>
            @endforeach
            <th>Avg UH ({{ $weights->weight_uh ?? 30 }}%)</th>
            <th>Avg Tugas ({{ $weights->weight_tugas ?? 30 }}%)</th>
            <th>Avg UAS/MID ({{ $weights->weight_uas_mid ?? 30 }}%)</th>
            <th>Nilai Akhir</th>
            <th>Kehadiran ({{ $weights->weight_absensi ?? 10 }}%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
            <tr>
                <td>{{ $student->nis ?? '-' }}</td>
                <td>{{ $student->name }}</td>
                @foreach($columns as $col)
                    <td>{{ $gradeData[$student->id][$col->id] ?? '' }}</td>
                @endforeach
                <td>{{ $calculations[$student->id]['avg_uh'] ?? '' }}</td>
                <td>{{ $calculations[$student->id]['avg_tugas'] ?? '' }}</td>
                <td>{{ $calculations[$student->id]['avg_mid_uas'] ?? '' }}</td>
                <td>{{ $calculations[$student->id]['final_score'] ?? '' }}</td>
                <td>{{ $calculations[$student->id]['attendance_percentage'] ?? 0 }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
