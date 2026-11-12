<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Classroom;
use App\Models\LearningObjective;
use App\Models\Assessment;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Services\ObjectiveMasteryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Tahap5KetercapaianTest extends TestCase
{
    use RefreshDatabase;

    public function test_ketercapaian_tp_terupdate_otomatis()
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        
        $tahun = \App\Models\AcademicYear::create([
            'name' => '2026/2027 Ganjil',
            'is_active' => true,
        ]);

        $kelas = Classroom::create([
            'academic_year_id' => $tahun->id,
            'name' => 'X RPL 1',
            'grade' => 10,
            'weekly_lesson_target' => 1,
            'join_code' => 'TEST-1234',
            'join_code_active' => true,
        ]);
        $kelas->students()->attach($siswa->id, ['academic_year_id' => $tahun->id]);
        
        TeachingAssignment::create([
            'teacher_id' => $guru->id,
            'classroom_id' => $kelas->id,
        ]);

        $element = \App\Models\Element::create([
            'code' => 'BK',
            'name' => 'Berpikir Komputasional',
            'description' => 'Test',
            'sort_order' => 1,
        ]);

        $tp = LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'BK-1',
            'title' => 'Test TP',
            'sort_order' => 1,
            'created_by' => $guru->id,
        ]);

        // Assesment tidak punya factory? Mari buat manual
        $asesmen = Assessment::create([
            'learning_objective_id' => $tp->id,
            'element_id' => $element->id,
            'title' => 'Tugas 1',
            'type' => 'tugas',
            'kktp' => 75,
            'max_score' => 100,
            'created_by' => $guru->id,
        ]);

        // Siswa submit tugas pertama
        $submission = Submission::create([
            'assessment_id' => $asesmen->id,
            'student_id' => $siswa->id,
            'attempt' => 1,
            'status' => Submission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $service = new ObjectiveMasteryService();

        // Uji kasus Remedial
        $submission->update(['score' => 60, 'status' => Submission::STATUS_GRADED, 'graded_by' => $guru->id]);
        $mastery = $service->updateMastery($siswa->id, $tp->id, $kelas->id);
        
        $this->assertEquals(60, $mastery->best_score);
        $this->assertEquals('remedial', $mastery->status);

        // Uji kasus Tercapai
        $submission->update(['score' => 80]);
        $mastery = $service->updateMastery($siswa->id, $tp->id, $kelas->id);
        
        $this->assertEquals(80, $mastery->best_score);
        $this->assertEquals('tercapai', $mastery->status);

        // Uji kasus Pengayaan
        $submission->update(['score' => 90]);
        $mastery = $service->updateMastery($siswa->id, $tp->id, $kelas->id);
        
        $this->assertEquals(90, $mastery->best_score);
        $this->assertEquals('pengayaan', $mastery->status);
    }
}
