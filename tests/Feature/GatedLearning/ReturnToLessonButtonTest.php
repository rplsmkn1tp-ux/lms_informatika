<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\Submission;
use App\Models\User;
use App\Livewire\Siswa\Tugas\KerjakanTugas;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();

    $this->year = AcademicYear::create([
        'name' => '2026/2027',
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'academic_year_id' => $this->year->id,
        'name' => 'X PPLG 1',
        'join_code' => 'XPPLG1',
    ]);

    $this->element = Element::create([
        'code' => 'TIK',
        'name' => 'Teknologi Informasi dan Komunikasi',
        'phase' => 'E',
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'TP-TIK-01',
        'title' => 'Pengolahan Kata Terintegrasi',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);

    $this->student = User::factory()->create([
        'role' => 'siswa',
        'status' => 'active',
    ]);

    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->year->id,
    ]);

    $this->lesson = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M1',
        'title' => 'Praktik Pembuatan Surat Resmi',
        'sort_order' => 1,
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);
    $this->lesson->classrooms()->attach($this->classroom->id);

    $this->task = Assessment::create([
        'learning_objective_id' => $this->tp->id,
        'element_id' => $this->element->id,
        'lesson_id' => $this->lesson->id,
        'type' => 'tugas',
        'title' => 'Tugas Praktik: Format Surat Dinas (.docx)',
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);
    $this->task->classrooms()->attach($this->classroom->id);

    Schema::enableForeignKeyConstraints();
});

test('task page shows return to lesson button when task is submitted and graded', function () {
    $this->actingAs($this->student);

    // Create graded submission
    Submission::create([
        'assessment_id' => $this->task->id,
        'student_id' => $this->student->id,
        'file_path' => 'submissions/surat_dinas.docx',
        'status' => Submission::STATUS_GRADED,
        'score' => 90,
        'submitted_at' => now(),
    ]);

    Livewire::test(KerjakanTugas::class, [
        'assessment' => $this->task,
    ])
    ->assertStatus(200)
    ->assertSee('Tugas Telah Dinilai oleh Guru')
    ->assertSee('Kembali ke Materi: ' . $this->lesson->title)
    ->assertDontSee('Kembali ke Materi Pembelajaran');
});

test('task page shows return to lesson button when task is submitted and pending grade', function () {
    $this->actingAs($this->student);

    // Create submitted submission
    Submission::create([
        'assessment_id' => $this->task->id,
        'student_id' => $this->student->id,
        'file_path' => 'submissions/surat_dinas.docx',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    Livewire::test(KerjakanTugas::class, [
        'assessment' => $this->task,
    ])
    ->assertStatus(200)
    ->assertSee('Tugas sudah Anda kirim')
    ->assertSee('Kembali ke Materi: ' . $this->lesson->title)
    ->assertDontSee('Kembali ke Materi Pembelajaran');
});

test('task page displays lkpd template download and kktp rubric card for student', function () {
    $this->task->update([
        'teacher_attachments' => [
            [
                'type' => 'file',
                'name' => 'Template_Surat_Resmi.docx',
                'path' => 'teacher_attachments/template.docx',
                'size' => 10240,
            ]
        ],
        'rubric' => "1. Format Margin: 30 Poin\n2. Struktur Kop Surat: 35 Poin\n3. Tabel Lampiran: 35 Poin",
    ]);

    $this->actingAs($this->student);

    Livewire::test(KerjakanTugas::class, [
        'assessment' => $this->task,
    ])
    ->assertStatus(200)
    ->assertSee('Unduh Template LKPD: Template_Surat_Resmi.docx')
    ->assertSee('Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)')
    ->assertSee('Format Margin: 30 Poin')
    ->assertSee('Struktur Kop Surat: 35 Poin');
});

test('materi detail displays badges for lkpd template and kktp rubric', function () {
    $this->task->update([
        'teacher_attachments' => [
            [
                'type' => 'file',
                'name' => 'Template_Surat_Resmi.docx',
                'path' => 'teacher_attachments/template.docx',
                'size' => 10240,
            ]
        ],
        'rubric' => "1. Format Margin: 30 Poin",
    ]);

    $this->actingAs($this->student);

    Livewire::test(\App\Livewire\Siswa\Belajar\MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson,
    ])
    ->assertStatus(200)
    ->assertSee('Ada Template LKPD')
    ->assertSee('Ada Rubrik KKTP');
});

