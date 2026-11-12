<?php

use App\Services\GradeCalculationService;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\GradeWeight;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('calculate average ignores null but not zero', function () {
    $service = new class extends GradeCalculationService {
        public function getAvg(array $scores) { return $this->calculateAverage($scores); }
    };

    // 3 entries, 1 null. Avg of 80 and 90 is 85
    expect($service->getAvg([80, null, 90]))->toBe(85.0);

    // 3 entries, one is 0. Avg of 100, 0, 50 is 50
    expect($service->getAvg([100, 0, 50]))->toBe(50.0);

    // all null
    expect($service->getAvg([null, null]))->toBeNull();

    // empty array
    expect($service->getAvg([]))->toBeNull();
});

test('calculate final grade rounds properly', function () {
    $academicYear = AcademicYear::create([
        'name' => '2026/2027',
        'semester' => 'ganjil',
        'is_active' => true,
    ]);
    $classroom = Classroom::create([
        'academic_year_id' => $academicYear->id,
        'name' => 'Test Class',
        'grade' => 'X',
        'major' => 'RPL',
        'join_code' => 'TEST-1234',
        'join_code_active' => true,
        'weekly_lesson_target' => 5,
    ]);
    
    $mockAttendance = Mockery::mock(AttendanceService::class);
    $mockAttendance->shouldReceive('getStudentAttendanceSummary')->andReturn(['H' => 1]);
    $mockAttendance->shouldReceive('getStudentAttendancePercentage')->andReturn(100);
    $this->app->instance(AttendanceService::class, $mockAttendance);

    GradeWeight::create([
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'weight_uh' => 30,
        'weight_tugas' => 30,
        'weight_uas_mid' => 30,
        'weight_absensi' => 10,
    ]);

    $service = new GradeCalculationService();

    // Data:
    // UH: avg(80, 85) = 82.5
    // Tugas: avg(90, null) = 90
    // MID: 88, UAS: 85 => avg(88, 85) = 86.5
    // Absensi: 100

    $result = $service->calculateFinalGrade(1, $classroom, $academicYear, [
        'uh' => [80, 85],
        'tugas' => [90, null],
        'mid' => [88],
        'uas' => [85]
    ]);

    expect($result['avg_uh'])->toBe(82.5);
    expect($result['avg_tugas'])->toBe(90.0);
    expect($result['avg_mid_uas'])->toBe(86.5);
    expect($result['attendance_percentage'])->toBe(100.0);

    // Final score: (82.5 * 30 + 90 * 30 + 86.5 * 30 + 100 * 10) / 100
    // (2475 + 2700 + 2595 + 1000) / 100 = 8770 / 100 = 87.7 -> round to 88
    expect($result['final_score'])->toBe(88.0);
});

test('calculate final grade when some groups are null', function () {
    $academicYear = AcademicYear::create([
        'name' => '2026/2027',
        'semester' => 'ganjil',
        'is_active' => true,
    ]);
    $classroom = Classroom::create([
        'academic_year_id' => $academicYear->id,
        'name' => 'Test Class 2',
        'grade' => 'X',
        'major' => 'RPL',
        'join_code' => 'TEST-5678',
        'join_code_active' => true,
        'weekly_lesson_target' => 5,
    ]);
    
    $mockAttendance = Mockery::mock(AttendanceService::class);
    $mockAttendance->shouldReceive('getStudentAttendanceSummary')->andReturn([]);
    $mockAttendance->shouldReceive('getStudentAttendancePercentage')->andReturn(0);
    $this->app->instance(AttendanceService::class, $mockAttendance);

    GradeWeight::create([
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'weight_uh' => 40,
        'weight_tugas' => 60,
        'weight_uas_mid' => 0,
        'weight_absensi' => 0,
    ]);

    $service = new GradeCalculationService();

    $result = $service->calculateFinalGrade(1, $classroom, $academicYear, [
        'uh' => [80],
        'tugas' => [null],
        'mid' => [],
        'uas' => []
    ]);

    // only UH has value. total weight used = 40.
    // score: (80 * 40) / 40 = 80.
    expect($result['final_score'])->toBe(80.0);
});
