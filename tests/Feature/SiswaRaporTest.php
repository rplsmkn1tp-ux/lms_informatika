<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaRaporTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_access_rapor_page_without_error()
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $classroom = Classroom::create([
            'name' => '10 Pemasaran 1',
            'join_code' => Classroom::generateJoinCode(),
            'academic_year_id' => $year->id,
            'grade' => 10,
            'major' => 'Pemasaran',
        ]);

        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $student->classrooms()->attach($classroom->id, ['academic_year_id' => $year->id]);

        $this->actingAs($student);

        $response = $this->get('/rapor');
        $response->assertStatus(200);
        $response->assertSee('Nilai & Rapor Belajar');
    }

    public function test_student_can_see_graded_assessments_and_feedback()
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $classroom = Classroom::create([
            'name' => '10 Pemasaran 1',
            'join_code' => Classroom::generateJoinCode(),
            'academic_year_id' => $year->id,
            'grade' => 10,
            'major' => 'Pemasaran',
        ]);

        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $student->classrooms()->attach($classroom->id, ['academic_year_id' => $year->id]);

        $teacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $assessment = Assessment::create([
            'title' => 'Tugas Algoritma Dasar',
            'type' => 'tugas',
            'grade_group' => 'tugas',
            'grade_seq' => 1,
            'max_score' => 100,
            'kktp' => 75,
            'status' => 'published',
            'created_by' => $teacher->id,
        ]);
        $assessment->classrooms()->attach($classroom->id);

        Submission::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'score' => 88.5,
            'status' => 'dinilai',
            'feedback' => 'Kerja bagus sekali!',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student);

        $response = $this->get('/rapor');
        $response->assertStatus(200);
        $response->assertSee('Nilai & Rapor Belajar');
        $response->assertSee('Tugas Algoritma Dasar');
        $response->assertSee('88.5');
        $response->assertSee('Kerja bagus sekali!');
        $response->assertSee('Tercapai');
    }
}
