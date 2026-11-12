<?php

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Note;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Services\WeeklyProgress;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;

function studentFixture(): array
{
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $classroom = Classroom::create(['name' => 'X RPL 1', 'join_code' => 'XRPL1-7K2Q', 'academic_year_id' => $year->id]);
    $other = Classroom::create(['name' => 'X RPL 2', 'join_code' => 'XRPL2-8M3R', 'academic_year_id' => $year->id]);

    $teacher = User::factory()->create(['role' => 'guru']);
    $student = User::factory()->create(['role' => 'siswa']);
    $student->classrooms()->attach($classroom->id, ['academic_year_id' => $classroom->academic_year_id]);

    $element = Element::firstOrCreate(['code' => 'BK'], ['name' => 'Berpikir Komputasional']);
    $tp = LearningObjective::create([
        'element_id' => $element->id, 'code' => 'BK-5', 'title' => 'Sistem Komputer',
        'sort_order' => 5, 'is_template' => false, 'created_by' => $teacher->id,
    ]);

    return compact('year', 'classroom', 'other', 'teacher', 'student', 'element', 'tp');
}

function makeLesson(array $f, array $attrs = []): Lesson
{
    return Lesson::create(array_merge([
        'learning_objective_id' => $f['tp']->id, 'code' => '5.2',
        'title' => 'Model komputer Von Neumann', 'status' => 'published',
        'created_by' => $f['teacher']->id,
    ], $attrs));
}

it('menemukan kelas aktif siswa', function () {
    $f = studentFixture();

    expect($f['student']->activeClassroom()->id)->toBe($f['classroom']->id);
});

it('hanya menampilkan subbab terbit yang sudah dibuka untuk kelas siswa', function () {
    $f = studentFixture();

    $open = makeLesson($f, ['code' => '5.1']);
    $open->classrooms()->attach($f['classroom']->id, ['available_from' => now()->subDay()]);

    $future = makeLesson($f, ['code' => '5.2']);
    $future->classrooms()->attach($f['classroom']->id, ['available_from' => now()->addDay()]);

    $draft = makeLesson($f, ['code' => '5.3', 'status' => 'draft']);
    $draft->classrooms()->attach($f['classroom']->id);

    $otherClass = makeLesson($f, ['code' => '5.4']);
    $otherClass->classrooms()->attach($f['other']->id);

    $visible = Lesson::availableFor($f['classroom']->id)->pluck('code')->all();

    expect($visible)->toBe(['5.1']);
});

it('menilai pilihan ganda otomatis dan menyerahkan esai ke guru', function () {
    $f = studentFixture();
    $assessment = Assessment::create([
        'learning_objective_id' => $f['tp']->id, 'type' => 'formatif',
        'title' => 'Kuis 5.2', 'status' => 'published', 'created_by' => $f['teacher']->id,
    ]);

    $pg = Question::create([
        'assessment_id' => $assessment->id, 'type' => 'pilihan_ganda',
        'body' => 'Komponen mana yang menghitung 12 + 30?',
        'options' => ['A' => 'CU', 'B' => 'ALU', 'C' => 'RAM', 'D' => 'Output'],
        'answer_key' => 'B', 'points' => 10,
    ]);
    $essay = Question::create([
        'assessment_id' => $assessment->id, 'type' => 'esai', 'body' => 'Jelaskan.', 'points' => 10,
    ]);

    expect($pg->check('B'))->toBeTrue()
        ->and($pg->check(' b '))->toBeTrue()
        ->and($pg->check('A'))->toBeFalse()
        ->and($pg->check(null))->toBeFalse()
        ->and($essay->check('apa saja'))->toBeNull();
});

it('tidak membocorkan kunci jawaban saat soal diserialisasi', function () {
    $f = studentFixture();
    $assessment = Assessment::create([
        'learning_objective_id' => $f['tp']->id, 'type' => 'formatif',
        'title' => 'Kuis', 'created_by' => $f['teacher']->id,
    ]);
    $q = Question::create([
        'assessment_id' => $assessment->id, 'type' => 'pilihan_ganda',
        'body' => 'Soal', 'options' => ['A' => 'x', 'B' => 'y'], 'answer_key' => 'B',
    ]);

    expect($q->toArray())->not->toHaveKey('answer_key');
});

it('memakai default_kktp bila KKTP asesmen kosong', function () {
    $f = studentFixture();
    \App\Models\Setting::setValue('default_kktp', 80);

    $assessment = Assessment::create([
        'learning_objective_id' => $f['tp']->id, 'type' => 'formatif',
        'title' => 'Kuis', 'created_by' => $f['teacher']->id,
    ]);

    expect($assessment->effectiveKktp())->toBe(80);

    $assessment->update(['kktp' => 70]);
    expect($assessment->fresh()->effectiveKktp())->toBe(70);
});

it('menolak percobaan ganda dengan nomor yang sama', function () {
    $f = studentFixture();
    $assessment = Assessment::create([
        'learning_objective_id' => $f['tp']->id, 'type' => 'formatif',
        'title' => 'Kuis', 'created_by' => $f['teacher']->id,
    ]);

    Submission::create(['assessment_id' => $assessment->id, 'student_id' => $f['student']->id, 'attempt' => 1]);

    expect(fn () => Submission::create([
        'assessment_id' => $assessment->id, 'student_id' => $f['student']->id, 'attempt' => 1,
    ]))->toThrow(QueryException::class);
});

it('hanya mengizinkan satu catatan per siswa per subbab', function () {
    $f = studentFixture();
    $lesson = makeLesson($f);

    Note::create(['student_id' => $f['student']->id, 'lesson_id' => $lesson->id, 'body' => 'a']);

    expect(fn () => Note::create(['student_id' => $f['student']->id, 'lesson_id' => $lesson->id, 'body' => 'b']))
        ->toThrow(QueryException::class);
});

it('menghitung subbab selesai Senin–Minggu zona Asia/Jakarta', function () {
    $f = studentFixture();
    $f['classroom']->update(['weekly_lesson_target' => 3]);

    // Rabu 7 Oktober 2026, 10.00 WIB
    $now = CarbonImmutable::parse('2026-10-07 10:00', 'Asia/Jakarta');

    $inWeek = makeLesson($f, ['code' => '5.1']);
    $mondayEarly = makeLesson($f, ['code' => '5.2']);
    $lastSunday = makeLesson($f, ['code' => '5.3']);

    LessonProgress::create(['student_id' => $f['student']->id, 'lesson_id' => $inWeek->id,
        'completed_at' => $now->subDay()->setTimezone(config('app.timezone'))]);
    // Senin 00.30 WIB = Minggu 17.30 UTC → tetap minggu ini
    LessonProgress::create(['student_id' => $f['student']->id, 'lesson_id' => $mondayEarly->id,
        'completed_at' => CarbonImmutable::parse('2026-10-05 00:30', 'Asia/Jakarta')->setTimezone(config('app.timezone'))]);
    // Minggu 23.30 WIB → minggu lalu
    LessonProgress::create(['student_id' => $f['student']->id, 'lesson_id' => $lastSunday->id,
        'completed_at' => CarbonImmutable::parse('2026-10-04 23:30', 'Asia/Jakarta')->setTimezone(config('app.timezone'))]);

    $progress = new WeeklyProgress($f['student']);

    expect($progress->completedThisWeek($now))->toBe(2)
        ->and($progress->target())->toBe(3);
});

it('menghitung hari beruntun dari aktivitas belajar', function () {
    $f = studentFixture();
    $now = CarbonImmutable::parse('2026-10-07 10:00', 'Asia/Jakarta');
    $tz = config('app.timezone');

    // Aktivitas: kemarin (subbab), 2 hari lalu (asesmen), 4 hari lalu (putus di hari ke-3)
    $l1 = makeLesson($f, ['code' => '5.1']);
    $l2 = makeLesson($f, ['code' => '5.2']);
    LessonProgress::create(['student_id' => $f['student']->id, 'lesson_id' => $l1->id, 'completed_at' => $now->subDay()->setTimezone($tz)]);
    LessonProgress::create(['student_id' => $f['student']->id, 'lesson_id' => $l2->id, 'completed_at' => $now->subDays(4)->setTimezone($tz)]);

    $assessment = Assessment::create([
        'learning_objective_id' => $f['tp']->id, 'type' => 'formatif',
        'title' => 'Kuis', 'created_by' => $f['teacher']->id,
    ]);
    Submission::create([
        'assessment_id' => $assessment->id, 'student_id' => $f['student']->id, 'attempt' => 1,
        'status' => 'dikumpulkan', 'submitted_at' => $now->subDays(2)->setTimezone($tz),
    ]);

    // Hari ini belum ada aktivitas → dihitung sampai kemarin: kemarin + 2 hari lalu = 2
    expect((new WeeklyProgress($f['student']))->streak($now))->toBe(2);
});

it('menggabungkan pengumuman kelas dan sekolah yang sudah terbit', function () {
    $f = studentFixture();
    $admin = User::factory()->create(['role' => 'admin']);

    Announcement::create(['classroom_id' => null, 'user_id' => $admin->id, 'title' => 'Sekolah', 'body' => '-', 'published_at' => now()->subHour()]);
    Announcement::create(['classroom_id' => $f['classroom']->id, 'user_id' => $f['teacher']->id, 'title' => 'Kelasku', 'body' => '-', 'published_at' => now()->subHour()]);
    Announcement::create(['classroom_id' => $f['other']->id, 'user_id' => $f['teacher']->id, 'title' => 'Kelas lain', 'body' => '-', 'published_at' => now()->subHour()]);
    Announcement::create(['classroom_id' => $f['classroom']->id, 'user_id' => $f['teacher']->id, 'title' => 'Terjadwal', 'body' => '-', 'published_at' => now()->addDay()]);

    $titles = Announcement::published()->forClassroom($f['classroom']->id)->orderBy('title')->pluck('title')->all();

    expect($titles)->toBe(['Kelasku', 'Sekolah']);
});

it('menolak siswa berada di dua kelas pada tahun ajaran yang sama', function () {
    $f = studentFixture();
    
    // The student is already attached to $f['classroom'] in studentFixture()
    // Attempting to attach to $f['other'] which has the same academic_year_id should fail
    
    expect(fn () => $f['student']->classrooms()->attach($f['other']->id, ['academic_year_id' => $f['year']->id]))
        ->toThrow(QueryException::class);
});
