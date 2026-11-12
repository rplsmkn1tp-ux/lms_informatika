<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\Classroom;

class AttendanceService
{
    /**
     * Hitung persentase kehadiran seorang siswa di kelas tertentu pada semester tertentu.
     * Hanya status 'hadir' yang dianggap hadir.
     */
    public function getStudentAttendancePercentage(int $studentId, int $classroomId, ?int $academicYearId = null): float
    {
        $query = AttendanceSession::where('classroom_id', $classroomId);
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        $totalSessions = $query->count();

        if ($totalSessions === 0) {
            return 100.0;
        }

        // Jumlah 'hadir' untuk siswa tersebut di sesi kelas
        $recordQuery = AttendanceRecord::where('student_id', $studentId)
            ->where('status', 'hadir')
            ->whereHas('session', function ($q) use ($classroomId, $academicYearId) {
                $q->where('classroom_id', $classroomId);
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
            });

        $attendedSessions = $recordQuery->count();

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }
}
