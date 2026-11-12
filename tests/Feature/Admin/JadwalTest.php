<?php

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\TeachingAssignment;
use App\Models\BellPeriod;
use App\Models\ScheduleEntry;
use App\Livewire\Admin\Jadwal\BellPeriodManager;
use App\Livewire\Admin\Jadwal\ScheduleEntryManager;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    
    $this->academicYear = AcademicYear::create([
        'name' => '2026/2027 Ganjil',
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'X PPLG 1',
        'academic_year_id' => $this->academicYear->id,
        'grade' => 10,
        'major' => 'PPLG',
        'weekly_lesson_target' => 4,
        'join_code' => 'XPPLG1',
    ]);

    $this->assignment = TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'subject' => 'Informatika',
    ]);
});

it('admin can view bell period manager', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.jadwal.waktu'))
        ->assertStatus(200)
        ->assertSee('Waktu Pelajaran');
});

it('admin can create bell period', function () {
    Livewire::actingAs($this->admin)
        ->test(BellPeriodManager::class)
        ->set('academic_year_id', $this->academicYear->id)
        ->set('day_of_week', 1)
        ->set('period_no', 1)
        ->set('starts_at', '07:00')
        ->set('ends_at', '07:45')
        ->set('type', 'pelajaran')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('bell_periods', [
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 1,
        'type' => 'pelajaran',
    ]);
});

it('admin can view schedule entry manager', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.jadwal.assignment', $this->assignment->id))
        ->assertStatus(200)
        ->assertSee('Atur Jadwal Mengajar');
});

it('admin can create schedule entry', function () {
    // Create some bell periods first
    BellPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 1,
        'starts_at' => '07:00',
        'ends_at' => '07:45',
        'type' => 'pelajaran',
    ]);
    BellPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 2,
        'starts_at' => '07:45',
        'ends_at' => '08:30',
        'type' => 'pelajaran',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ScheduleEntryManager::class, ['assignment' => $this->assignment])
        ->set('day_of_week', 1)
        ->set('start_period_no', 1)
        ->set('end_period_no', 2)
        ->set('room', 'Lab 1')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('schedule_entries', [
        'teaching_assignment_id' => $this->assignment->id,
        'start_period_no' => 1,
        'end_period_no' => 2,
        'room' => 'Lab 1',
    ]);
});

it('schedule entry validation prevents crossing break periods', function () {
    BellPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 1,
        'starts_at' => '07:00',
        'ends_at' => '07:45',
        'type' => 'pelajaran',
    ]);
    BellPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 2,
        'starts_at' => '07:45',
        'ends_at' => '08:15',
        'type' => 'istirahat',
    ]);
    BellPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'day_of_week' => 1,
        'period_no' => 3,
        'starts_at' => '08:15',
        'ends_at' => '09:00',
        'type' => 'pelajaran',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ScheduleEntryManager::class, ['assignment' => $this->assignment])
        ->set('day_of_week', 1)
        ->set('start_period_no', 1)
        ->set('end_period_no', 3)
        ->call('save')
        ->assertHasErrors(['end_period_no']);

    $this->assertDatabaseMissing('schedule_entries', [
        'teaching_assignment_id' => $this->assignment->id,
        'start_period_no' => 1,
        'end_period_no' => 3,
    ]);
});
