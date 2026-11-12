<?php

use App\Models\Assessment;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Guru\Tugas\Form;
use App\Livewire\Guru\Tugas\Index;

beforeEach(function () {
    $this->guru = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);
});

test('assessment effectiveMeetingNo returns meeting_no or attendanceSession meeting_no', function () {
    $year = App\Models\AcademicYear::first() ?? App\Models\AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $classroom = Classroom::first() ?? Classroom::create([
        'name' => 'X-RPL-Test3',
        'join_code' => 'XRPL997',
        'grade' => '10',
        'academic_year_id' => $year->id,
    ]);

    $a1 = Assessment::create([
        'title' => 'Tugas 1',
        'type' => 'tugas',
        'created_by' => $this->guru->id,
        'meeting_no' => 3,
        'attendance_session_id' => null,
        'max_score' => 100,
        'status' => 'published',
    ]);
    expect($a1->effectiveMeetingNo())->toBe(3);

    $session = AttendanceSession::create([
        'academic_year_id' => $year->id,
        'classroom_id' => $classroom->id,
        'teacher_id' => $this->guru->id,
        'title' => 'Pertemuan 5',
        'meeting_no' => 5,
        'meeting_date' => now()->toDateString(),
        'start_time' => '07:00:00',
        'end_time' => '09:00:00',
    ]);
    $a2 = Assessment::create([
        'title' => 'Tugas 2',
        'type' => 'tugas',
        'created_by' => $this->guru->id,
        'meeting_no' => null,
        'attendance_session_id' => $session->id,
        'max_score' => 100,
        'status' => 'published',
    ]);
    expect($a2->effectiveMeetingNo())->toBe(5);

    // If both set, meeting_no takes priority
    $a3 = Assessment::create([
        'title' => 'Tugas 3',
        'type' => 'tugas',
        'created_by' => $this->guru->id,
        'meeting_no' => 2,
        'attendance_session_id' => $session->id,
        'max_score' => 100,
        'status' => 'published',
    ]);
    expect($a3->effectiveMeetingNo())->toBe(2);
});

test('guru can create assessment with custom meeting_no and lesson', function () {
    $year = App\Models\AcademicYear::first() ?? App\Models\AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $classroom = Classroom::first() ?? Classroom::create([
        'name' => 'X-RPL-Test',
        'join_code' => 'XRPL999',
        'grade' => '10',
        'academic_year_id' => $year->id,
    ]);
    $lesson = Lesson::first();
    $tp = App\Models\LearningObjective::first() ?? App\Models\LearningObjective::create([
        'element_id' => App\Models\Element::first()?->id ?? App\Models\Element::create(['name' => 'BK', 'code' => 'BK'])->id,
        'code' => 'BK-1',
        'title' => 'TP Test',
        'sort_order' => 1,
        'created_by' => $this->guru->id,
    ]);

    $this->actingAs($this->guru);

    Livewire::test(Form::class)
        ->set('title', 'Tugas Uji Coba Pertemuan')
        ->set('selectedClassrooms', [$classroom->id])
        ->set('learning_objective_id', $tp->id)
        ->set('lesson_id', $lesson ? $lesson->id : null)
        ->set('meeting_no', 4)
        ->set('max_score', 100)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('guru.tugas.index'));

    $created = Assessment::where('title', 'Tugas Uji Coba Pertemuan')->first();
    expect($created)->not->toBeNull();
    expect($created->meeting_no)->toBe(4);
    expect($created->effectiveMeetingNo())->toBe(4);
});

test('guru tugas index renders meeting number and lesson title', function () {
    $year = App\Models\AcademicYear::first() ?? App\Models\AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $classroom = Classroom::first() ?? Classroom::create([
        'name' => 'X-RPL-Test2',
        'join_code' => 'XRPL998',
        'grade' => '10',
        'academic_year_id' => $year->id,
    ]);
    $lesson = Lesson::first();
    $tugas = Assessment::create([
        'created_by' => $this->guru->id,
        'title' => 'Latihan Pseudocode Unik',
        'type' => 'tugas',
        'lesson_id' => $lesson ? $lesson->id : null,
        'meeting_no' => 2,
        'max_score' => 100,
        'status' => 'published',
    ]);
    $tugas->classrooms()->attach($classroom->id);

    $this->actingAs($this->guru);

    $test = Livewire::test(Index::class)
        ->assertSee('Latihan Pseudocode Unik')
        ->assertSee('Pertemuan ke-2');
        
    if ($lesson) {
        $test->assertSee($lesson->title);
    }
});
