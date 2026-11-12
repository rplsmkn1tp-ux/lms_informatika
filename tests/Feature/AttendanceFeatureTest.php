<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Livewire\Guru\Absensi\SessionForm;
use App\Livewire\Siswa\Absensi\KehadiranSaya;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

test('guru can save attendance and all enrolled students get attendance records', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $class = Classroom::create(['name' => 'X-RPL-Attendance', 'join_code' => 'RPLAT01', 'academic_year_id' => $year->id, 'grade' => '10']);
    $class->teachingAssignments()->create(['teacher_id' => $guru->id, 'academic_year_id' => $year->id]);

    $student1 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    $student2 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    $student1->classrooms()->attach($class->id, ['academic_year_id' => $year->id]);
    $student2->classrooms()->attach($class->id, ['academic_year_id' => $year->id]);

    Livewire::actingAs($guru)
        ->test(SessionForm::class, ['classroom' => $class, 'sessionId' => 'new'])
        ->set('meeting_date', '2026-10-08')
        ->set('meeting_no', 1)
        ->set('topic', 'Pengenalan Algoritma')
        ->set('attendance.' . $student1->id, 'hadir')
        ->set('attendance.' . $student2->id, 'izin')
        ->call('save')
        ->assertHasNoErrors();

    $session = AttendanceSession::where('classroom_id', $class->id)->where('meeting_date', '2026-10-08')->first();
    expect($session)->not->toBeNull();
    expect($session->status)->toBe('open');

    $records = AttendanceRecord::where('session_id', $session->id)->get();
    expect($records)->toHaveCount(2);

    $record1 = $records->firstWhere('student_id', $student1->id);
    expect($record1->status)->toBe('hadir');

    $record2 = $records->firstWhere('student_id', $student2->id);
    expect($record2->status)->toBe('izin');

    // Siswa can view KehadiranSaya and see 08/10/2026 even when session status is open
    Livewire::actingAs($student1)
        ->test(KehadiranSaya::class)
        ->assertStatus(200)
        ->assertSee('08/10/2026')
        ->assertSee('Pengenalan Algoritma')
        ->assertSee('Hadir');

    Livewire::actingAs($student2)
        ->test(KehadiranSaya::class)
        ->assertStatus(200)
        ->assertSee('08/10/2026')
        ->assertSee('Pengenalan Algoritma')
        ->assertSee('Izin');
});

