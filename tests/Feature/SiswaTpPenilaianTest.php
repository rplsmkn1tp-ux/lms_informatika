<?php

namespace Tests\Feature;

use App\Livewire\Siswa\Rapor;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\LearningObjective;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\SiswaGradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiswaTpPenilaianTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_tp_tab_calculates_score_and_status_correctly_from_tasks_and_columns()
    {
        // 1. Setup Tahun Ajaran & Kelas
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

        // 2. Setup Guru & Siswa
        $teacher = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'guru',
            'status' => 'active',
        ]);

        $student = User::factory()->create([
            'name' => 'Andi Siswa',
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $student->classrooms()->attach($classroom->id, ['academic_year_id' => $year->id]);

        TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject' => 'Informatika',
        ]);

        // 3. Setup Elemen & TP (BK-1 dan BK-5)
        $element = Element::create([
            'name' => 'Berpikir Komputasional',
            'code' => 'BK',
            'description' => 'Elemen Berpikir Komputasional',
        ]);

        $tpBk1 = LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'BK-1',
            'title' => 'Memahami Struktur Data',
            'description' => 'Memahami struktur data dasar dan algoritma sederhana',
            'kktp' => 75,
            'sort_order' => 1,
            'created_by' => $teacher->id,
        ]);

        $tpBk5 = LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'BK-5',
            'title' => 'Menganalisis Algoritma',
            'description' => 'Menganalisis efisiensi algoritma dan pemecahan masalah',
            'kktp' => 75,
            'sort_order' => 5,
            'created_by' => $teacher->id,
        ]);

        // A. Cek awal: Belum ada tugas dinilai, semua TP harus Belum Diuji
        $this->actingAs($student);

        Livewire::test(Rapor::class)
            ->set('activeTab', 'tp')
            ->assertSee('BK-1')
            ->assertSee('BK-5')
            ->assertSee('Belum ada penilaian')
            ->assertSee('Belum Diuji');

        // 4. Guru membuat tugas yang dikaitkan ke BK-5
        $task = Assessment::create([
            'title' => 'Tugas Analisis Algoritma',
            'type' => 'tugas',
            'grade_group' => 'tugas',
            'grade_seq' => 1,
            'max_score' => 100,
            'kktp' => 75,
            'learning_objective_id' => $tpBk5->id,
            'status' => 'published',
            'created_by' => $teacher->id,
        ]);
        $task->classrooms()->attach($classroom->id);
        $task->syncLearningObjectives([$tpBk5->id]);

        // 5. Siswa mengumpulkan tugas dan guru menilai dengan skor 85
        Submission::create([
            'assessment_id' => $task->id,
            'student_id' => $student->id,
            'score' => 85,
            'status' => 'dinilai',
            'feedback' => 'Analisis sangat baik!',
            'submitted_at' => now(),
        ]);

        // Cek SiswaGradeService langsung
        $gradeService = app(SiswaGradeService::class);
        $tpData = $gradeService->getStudentTpMasteries($student->id, $classroom->id, $year->id);
        $masteryBk5 = $tpData['masteries']->get($tpBk5->id);

        $this->assertNotNull($masteryBk5);
        $this->assertEquals(85.0, $masteryBk5->best_score);
        $this->assertEquals('Tercapai', $masteryBk5->status_label);
        $this->assertEquals(1, $masteryBk5->graded_count);
        $this->assertEquals('1 penilaian', $masteryBk5->count_label);

        // BK-1 tetap belum dinilai
        $masteryBk1 = $tpData['masteries']->get($tpBk1->id);
        $this->assertNull($masteryBk1->best_score);
        $this->assertEquals('Belum Diuji', $masteryBk1->status_label);
        $this->assertEquals(0, $masteryBk1->graded_count);

        // 6. Cek halaman Rapor Siswa tab TP
        Livewire::test(Rapor::class)
            ->set('activeTab', 'tp')
            ->assertSee('BK-5')
            ->assertSee('85')
            ->assertSee('Tercapai')
            ->assertSee('1 penilaian')
            ->assertSee('Tugas Analisis Algoritma')
            // BK-1 tetap Belum Diuji
            ->assertSee('BK-1')
            ->assertSee('Belum Diuji');

        // 7. Guru menambahkan nilai kolom UH di Buku Nilai yang dikaitkan ke BK-5 dengan skor 65
        $column = GradeColumn::create([
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'group' => 'uh',
            'seq' => 1,
            'label' => 'UH 1',
            'max_score' => 100,
            'learning_objective_id' => $tpBk5->id,
        ]);
        $column->syncLearningObjectives([$tpBk5->id]);

        GradeEntry::create([
            'column_id' => $column->id,
            'student_id' => $student->id,
            'score' => 65,
        ]);

        // Rata-rata BK-5 sekarang adalah (85 + 65) / 2 = 75.0 (Tercapai karena 75 >= 75)
        $tpData2 = $gradeService->getStudentTpMasteries($student->id, $classroom->id, $year->id);
        $masteryBk5After = $tpData2['masteries']->get($tpBk5->id);

        $this->assertEquals(75.0, $masteryBk5After->best_score);
        $this->assertEquals('Tercapai', $masteryBk5After->status_label);
        $this->assertEquals(2, $masteryBk5After->graded_count);
        $this->assertEquals('2 penilaian', $masteryBk5After->count_label);

        // 8. Cek jika rata-rata di bawah KKTP
        // Ubah skor tugas menjadi 60 dan UH menjadi 60 -> rata-rata 60.0 (Belum Tercapai)
        GradeEntry::where('column_id', $column->id)->where('student_id', $student->id)->update(['score' => 60]);
        Submission::where('assessment_id', $task->id)->where('student_id', $student->id)->update(['score' => 60]);

        $tpData3 = $gradeService->getStudentTpMasteries($student->id, $classroom->id, $year->id);
        $masteryBk5Under = $tpData3['masteries']->get($tpBk5->id);

        $this->assertEquals(60.0, $masteryBk5Under->best_score);
        $this->assertEquals('Belum Tercapai', $masteryBk5Under->status_label);
        $this->assertEquals(2, $masteryBk5Under->graded_count);

        Livewire::test(Rapor::class)
            ->set('activeTab', 'tp')
            ->assertSee('BK-5')
            ->assertSee('60')
            ->assertSee('Belum Tercapai')
            ->assertSee('2 penilaian');
    }
}
