<?php

namespace Tests\Feature\Siswa;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\Submission;
use App\Models\User;
use App\Services\SiswaGradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiswaGradeSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $year;
    protected Classroom $classroom;
    protected User $teacher;
    protected User $studentAndi;
    protected User $studentBudi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'name' => '10 Pemasaran 1',
            'join_code' => Classroom::generateJoinCode(),
            'academic_year_id' => $this->year->id,
            'grade' => 10,
            'major' => 'Pemasaran',
        ]);

        $this->teacher = User::factory()->create([
            'name' => 'Guru Pengampu',
            'role' => 'guru',
            'status' => 'active',
        ]);

        $this->studentAndi = User::factory()->create([
            'name' => 'Andi Siswa',
            'email' => 'andi@example.com',
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $this->studentAndi->classrooms()->attach($this->classroom->id, ['academic_year_id' => $this->year->id]);

        $this->studentBudi = User::factory()->create([
            'name' => 'Budi Siswa',
            'email' => 'budi@example.com',
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $this->studentBudi->classrooms()->attach($this->classroom->id, ['academic_year_id' => $this->year->id]);

        // Create 3 LMS Assessments (Tugas 1, Tugas 2, Tugas 3)
        $tugas1 = Assessment::create([
            'title' => 'Tugas 1 - Algoritma',
            'type' => 'tugas',
            'grade_group' => 'tugas',
            'grade_seq' => 1,
            'max_score' => 100,
            'kktp' => 75,
            'status' => 'published',
            'created_by' => $this->teacher->id,
        ]);
        $tugas1->classrooms()->attach($this->classroom->id);

        $tugas2 = Assessment::create([
            'title' => 'Tugas 2 - Pemrograman Dasar',
            'type' => 'tugas',
            'grade_group' => 'tugas',
            'grade_seq' => 2,
            'max_score' => 100,
            'kktp' => 75,
            'status' => 'published',
            'created_by' => $this->teacher->id,
        ]);
        $tugas2->classrooms()->attach($this->classroom->id);

        $tugas3 = Assessment::create([
            'title' => 'Tugas 3 - Logika Proposisi',
            'type' => 'tugas',
            'grade_group' => 'tugas',
            'grade_seq' => 3,
            'max_score' => 100,
            'kktp' => 75,
            'status' => 'published',
            'created_by' => $this->teacher->id,
        ]);
        $tugas3->classrooms()->attach($this->classroom->id);

        // Submissions for Andi:
        // Tugas 1: 55 (Belum Tercapai)
        Submission::create([
            'assessment_id' => $tugas1->id,
            'student_id' => $this->studentAndi->id,
            'score' => 55,
            'status' => 'dinilai',
            'feedback' => 'Perbaiki logika perulangan',
            'submitted_at' => now(),
        ]);

        // Tugas 2: 80 (Tercapai)
        Submission::create([
            'assessment_id' => $tugas2->id,
            'student_id' => $this->studentAndi->id,
            'score' => 80,
            'status' => 'dinilai',
            'feedback' => 'Bagus!',
            'submitted_at' => now(),
        ]);

        // Tugas 3: Belum dinilai (score null)
        Submission::create([
            'assessment_id' => $tugas3->id,
            'student_id' => $this->studentAndi->id,
            'score' => null,
            'status' => 'dikumpulkan',
            'submitted_at' => now(),
        ]);

        // Create GradeColumns in Gradebook:
        // UH 1, UH 2, UH 3, MID, UAS, and Tugas 1-3
        $colTugas1 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'tugas', 'seq' => 1, 'label' => 'Tugas 1', 'max_score' => 100]);
        $colTugas2 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'tugas', 'seq' => 2, 'label' => 'Tugas 2', 'max_score' => 100]);
        $colTugas3 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'tugas', 'seq' => 3, 'label' => 'Tugas 3', 'max_score' => 100]);

        $colUh1 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'uh', 'seq' => 1, 'label' => 'UH 1', 'max_score' => 100]);
        $colUh2 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'uh', 'seq' => 2, 'label' => 'UH 2', 'max_score' => 100]);
        $colUh3 = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'uh', 'seq' => 3, 'label' => 'UH 3', 'max_score' => 100]);

        $colMid = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'mid', 'seq' => 1, 'label' => 'MID', 'max_score' => 100]);
        $colUas = GradeColumn::create(['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->year->id, 'group' => 'uas', 'seq' => 1, 'label' => 'UAS', 'max_score' => 100]);

        // GradeEntries for Andi:
        GradeEntry::create(['column_id' => $colTugas1->id, 'student_id' => $this->studentAndi->id, 'score' => 55]);
        GradeEntry::create(['column_id' => $colTugas2->id, 'student_id' => $this->studentAndi->id, 'score' => 80]);
        // colTugas3: null (Belum dinilai)

        GradeEntry::create(['column_id' => $colUh1->id, 'student_id' => $this->studentAndi->id, 'score' => 90]);
        GradeEntry::create(['column_id' => $colUh2->id, 'student_id' => $this->studentAndi->id, 'score' => 90]);
        // colUh3: null (Belum dinilai)

        GradeEntry::create(['column_id' => $colMid->id, 'student_id' => $this->studentAndi->id, 'score' => 80]);
        GradeEntry::create(['column_id' => $colUas->id, 'student_id' => $this->studentAndi->id, 'score' => 99]);

        // GradeEntries for Budi:
        GradeEntry::create(['column_id' => $colUh1->id, 'student_id' => $this->studentBudi->id, 'score' => 60]);
    }

    public function test_siswa_grade_service_returns_all_grades_for_student()
    {
        $service = app(SiswaGradeService::class);
        $grades = $service->getStudentGrades($this->studentAndi->id, $this->classroom->id, $this->year->id);

        $this->assertCount(8, $grades);

        $labels = $grades->pluck('nama_kolom')->toArray();
        $this->assertContains('MID', $labels);
        $this->assertContains('UAS', $labels);
        $this->assertContains('UH 1', $labels);
        $this->assertContains('UH 2', $labels);
        $this->assertContains('UH 3', $labels);
        $this->assertContains('Tugas 1', $labels);
        $this->assertContains('Tugas 2', $labels);
        $this->assertContains('Tugas 3', $labels);

        // Check scores and KKTP status
        $mid = $grades->firstWhere('nama_kolom', 'MID');
        $this->assertEquals(80, $mid->score);
        $this->assertEquals('Tercapai', $mid->status_kktp);
        $this->assertTrue($mid->is_graded);

        $uas = $grades->firstWhere('nama_kolom', 'UAS');
        $this->assertEquals(99, $uas->score);
        $this->assertEquals('Tercapai', $uas->status_kktp);

        $tugas1 = $grades->firstWhere('nama_kolom', 'Tugas 1');
        $this->assertEquals(55, $tugas1->score);
        $this->assertEquals('Belum Tercapai', $tugas1->status_kktp);

        $tugas3 = $grades->firstWhere('nama_kolom', 'Tugas 3');
        $this->assertNull($tugas3->score);
        $this->assertEquals('Belum dinilai', $tugas3->status_kktp);
        $this->assertFalse($tugas3->is_graded);

        $uh3 = $grades->firstWhere('nama_kolom', 'UH 3');
        $this->assertNull($uh3->score);
        $this->assertEquals('Belum dinilai', $uh3->status_kktp);
    }

    public function test_backend_student_isolation_prevents_viewing_other_student_grades()
    {
        $service = app(SiswaGradeService::class);

        // Andi sees his UH 1 as 90
        $andiGrades = $service->getStudentGrades($this->studentAndi->id, $this->classroom->id, $this->year->id);
        $andiUh1 = $andiGrades->firstWhere('nama_kolom', 'UH 1');
        $this->assertEquals(90, $andiUh1->score);

        // Budi sees his UH 1 as 60
        $budiGrades = $service->getStudentGrades($this->studentBudi->id, $this->classroom->id, $this->year->id);
        $budiUh1 = $budiGrades->firstWhere('nama_kolom', 'UH 1');
        $this->assertEquals(60, $budiUh1->score);

        // Unenrolled student gets empty collection
        $otherStudent = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $otherGrades = $service->getStudentGrades($otherStudent->id, $this->classroom->id, $this->year->id);
        $this->assertCount(0, $otherGrades);
    }

    public function test_rapor_livewire_component_shows_synchronized_tabs_and_counts()
    {
        $this->actingAs($this->studentAndi);

        $component = Livewire::test(\App\Livewire\Siswa\Rapor::class)
            ->set('activeTab', 'semua');

        // Tab "Semua Penilaian" displays 8 items
        $component->assertSee('Semua Penilaian (8)')
            ->assertSee('Tugas Mandiri (3)')
            ->assertSee('UH & Ujian (5)', false)
            ->assertSee('MID')
            ->assertSee('UAS')
            ->assertSee('UH 1')
            ->assertSee('UH 2')
            ->assertSee('UH 3')
            ->assertSee('Tugas 1')
            ->assertSee('Tugas 2')
            ->assertSee('Tugas 3');

        // Tab "Tugas Mandiri" displays 3 items
        $component->set('activeTab', 'tugas')
            ->assertSee('Tugas 1')
            ->assertSee('Tugas 2')
            ->assertSee('Tugas 3')
            ->assertDontSee('UH 1');

        // Tab "UH & Ujian" displays 5 items (UH 1-3, MID, UAS)
        $component->set('activeTab', 'ujian')
            ->assertSee('UH 1')
            ->assertSee('UH 2')
            ->assertSee('UH 3')
            ->assertSee('MID')
            ->assertSee('UAS')
            ->assertDontSee('Tugas 1');

        // Tab "Rapor & Buku Nilai" (Halaman A) displays "Rincian Kolom Penilaian Guru"
        $component->set('activeTab', 'buku_nilai')
            ->assertSee('Rincian Kolom Penilaian Guru')
            ->assertSee('MID')
            ->assertSee('UAS')
            ->assertSee('UH 1')
            ->assertSee('UH 2')
            ->assertSee('UH 3')
            ->assertSee('Tugas 1')
            ->assertSee('Tugas 2')
            ->assertSee('Tugas 3')
            ->assertSee('Belum dinilai');
    }

    public function test_buku_nilai_page_uses_siswa_grade_service_and_displays_status_kktp()
    {
        $this->actingAs($this->studentAndi);

        $response = $this->get('/buku-nilai');
        $response->assertStatus(200);
        $response->assertSee('Buku Nilai');
        $response->assertSee('Status KKTP');
        $response->assertSee('UH 1');
        $response->assertSee('UH 2');
        $response->assertSee('UH 3');
        $response->assertSee('MID');
        $response->assertSee('UAS');
        $response->assertSee('Tugas 1');
        $response->assertSee('Tugas 2');
        $response->assertSee('Tugas 3');
        $response->assertSee('Belum dinilai');
        $response->assertSee('Tercapai');
        $response->assertSee('Belum Tercapai');
    }
}
