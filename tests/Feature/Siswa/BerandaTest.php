<?php

namespace Tests\Feature\Siswa;

use App\Livewire\Siswa\Beranda;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class BerandaTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_view_beranda()
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        
        $response = $this->actingAs($siswa)->get(route('siswa.beranda'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(Beranda::class);
    }

    public function test_streak_calculation_works()
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $academicYear = \App\Models\AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $classroom = Classroom::create([
            'name' => 'X TKJ 1',
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'join_code' => 'ABCDEF',
        ]);
        $siswa->classrooms()->attach($classroom->id, ['academic_year_id' => $academicYear->id]);

        Carbon::setTestNow('2026-10-07 12:00:00'); // Let's pretend today is Oct 7

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        // Progress 3 days in a row
        LessonProgress::create(['student_id' => $siswa->id, 'lesson_id' => 1, 'completed_at' => '2026-10-07 10:00:00']);
        LessonProgress::create(['student_id' => $siswa->id, 'lesson_id' => 2, 'completed_at' => '2026-10-06 10:00:00']);
        LessonProgress::create(['student_id' => $siswa->id, 'lesson_id' => 3, 'completed_at' => '2026-10-05 10:00:00']);
        // Gap day 4
        LessonProgress::create(['student_id' => $siswa->id, 'lesson_id' => 4, 'completed_at' => '2026-10-03 10:00:00']);
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        Livewire::actingAs($siswa)
            ->test(Beranda::class)
            ->assertViewHas('streak', 3)
            ->assertSee('3 hari beruntun');

        Carbon::setTestNow(); // reset
    }

    public function test_announcements_displayed()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $academicYear = \App\Models\AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $classroom = Classroom::create([
            'name' => 'X TKJ 1',
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'join_code' => 'ABCDEG',
        ]);
        $siswa->classrooms()->attach($classroom->id, ['academic_year_id' => $academicYear->id]);

        $ann1 = Announcement::create([
            'classroom_id' => null, // Global
            'user_id' => $admin->id,
            'title' => 'Pengumuman Global',
            'body' => 'Isi',
            'published_at' => now()->subDay(),
        ]);

        $ann2 = Announcement::create([
            'classroom_id' => $classroom->id, // Class specific
            'user_id' => $admin->id,
            'title' => 'Pengumuman Kelas',
            'body' => 'Isi',
            'published_at' => now()->subHour(),
        ]);

        Livewire::actingAs($siswa)
            ->test(Beranda::class)
            ->assertViewHas('announcements', function($anns) use ($ann1, $ann2) {
                return $anns->contains($ann1) && $anns->contains($ann2);
            })
            ->assertSee('Pengumuman Global')
            ->assertSee('Pengumuman Kelas');
    }
}
