<?php

use App\Models\User;
use App\Models\Lesson;
use App\Models\LessonCheckpoint;
use App\Models\LessonProgress;
use App\Models\LearningObjective;
use App\Models\Element;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();

    $this->year = AcademicYear::create([
        'name' => '2026/2027',
        'is_active' => true,
    ]);

    $this->element = Element::create([
        'code' => 'JKI',
        'name' => 'Jaringan Komputer dan Internet',
        'phase' => 'E',
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'TP-JKI-01',
        'title' => 'Komponen Jaringan dan IP Address',
        'description' => 'Memahami komponen jaringan dan pengalamatan IP',
    ]);

    $this->student = User::factory()->create([
        'role' => 'siswa',
        'status' => 'active',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);

    Schema::enableForeignKeyConstraints();
});

test('lesson checkpoint can validate correct answers', function () {
    $lesson = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Konsep Dasar IP Address',
        'created_by' => $this->teacher->id,
    ]);

    $checkpoint = LessonCheckpoint::create([
        'lesson_id' => $lesson->id,
        'question' => 'Berapa jumlah bit pada IPv4?',
        'options' => [
            ['key' => 'A', 'text' => '16 bit'],
            ['key' => 'B', 'text' => '32 bit'],
            ['key' => 'C', 'text' => '64 bit'],
            ['key' => 'D', 'text' => '128 bit'],
        ],
        'correct_answer' => 'B',
        'explanation' => 'IPv4 memiliki panjang 32 bit yang dibagi menjadi 4 oktet.',
        'sort_order' => 1,
    ]);

    expect($checkpoint->isCorrect('B'))->toBeTrue();
    expect($checkpoint->isCorrect('b'))->toBeTrue();
    expect($checkpoint->isCorrect('A'))->toBeFalse();
    expect($checkpoint->isCorrect(null))->toBeFalse();
});

test('first lesson without prerequisite is always unlocked for student', function () {
    $lesson1 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Materi 1 - Pengenalan Jaringan',
        'sort_order' => 1,
        'is_gated' => true,
        'completion_rule' => 'checkpoint_and_task',
        'created_by' => $this->teacher->id,
    ]);

    expect($lesson1->isUnlockedFor($this->student))->toBeTrue();
});

test('subsequent lesson is locked until student completes prerequisite lesson', function () {
    $lesson1 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Materi 1 - Pengenalan Jaringan',
        'sort_order' => 1,
        'is_gated' => true,
        'completion_rule' => 'checkpoint_only',
        'created_by' => $this->teacher->id,
    ]);

    $checkpoint = LessonCheckpoint::create([
        'lesson_id' => $lesson1->id,
        'question' => 'Apa kepanjangan LAN?',
        'options' => [
            ['key' => 'A', 'text' => 'Local Area Network'],
            ['key' => 'B', 'text' => 'Large Area Network'],
        ],
        'correct_answer' => 'A',
    ]);

    $lesson2 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Materi 2 - Topologi Jaringan',
        'sort_order' => 2,
        'is_gated' => true,
        'created_by' => $this->teacher->id,
    ]);

    // Belum mengerjakan kuis pemantik materi 1 -> materi 2 terkunci
    expect($lesson2->isUnlockedFor($this->student))->toBeFalse();

    // Siswa lulus kuis pemantik materi 1
    LessonProgress::create([
        'student_id' => $this->student->id,
        'lesson_id' => $lesson1->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
        'checkpoint_answers' => ['1' => 'A'],
    ]);

    // Sekarang materi 2 otomatis terbuka!
    expect($lesson2->isUnlockedFor($this->student))->toBeTrue();
});

test('hybrid lesson with task requires both checkpoint and task submission to unlock next lesson', function () {
    $lesson1 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Praktik Ms. Excel - Rumus VLOOKUP',
        'sort_order' => 1,
        'is_gated' => true,
        'completion_rule' => 'checkpoint_and_task',
        'created_by' => $this->teacher->id,
    ]);

    LessonCheckpoint::create([
        'lesson_id' => $lesson1->id,
        'question' => 'Rumus untuk mencari data vertikal di Excel?',
        'options' => [
            ['key' => 'A', 'text' => 'VLOOKUP'],
            ['key' => 'B', 'text' => 'HLOOKUP'],
        ],
        'correct_answer' => 'A',
    ]);

    $assessment = Assessment::create([
        'lesson_id' => $lesson1->id,
        'learning_objective_id' => $this->tp->id,
        'title' => 'Tugas Praktik File Excel VLOOKUP',
        'type' => 'tugas',
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);

    $lesson2 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'title' => 'Praktik Ms. Excel - Pivot Table',
        'sort_order' => 2,
        'is_gated' => true,
        'created_by' => $this->teacher->id,
    ]);

    // Baru kuis pemantik saja, tugas belum -> Masih terkunci
    $progress = LessonProgress::create([
        'student_id' => $this->student->id,
        'lesson_id' => $lesson1->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);
    expect($lesson2->isUnlockedFor($this->student))->toBeFalse();

    // Siswa mengunggah file tugas praktik
    Submission::create([
        'assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'file_path' => 'submissions/tugas_excel.xlsx',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    // Kuis pemantik + tugas sudah terpenuhi -> Materi 2 terbuka!
    expect($lesson2->isUnlockedFor($this->student))->toBeTrue();
});
