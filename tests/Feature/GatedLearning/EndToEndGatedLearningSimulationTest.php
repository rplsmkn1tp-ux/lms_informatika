<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonCheckpoint;
use App\Models\LessonProgress;
use App\Models\Submission;
use App\Models\User;
use App\Livewire\Guru\LessonManagement;
use App\Livewire\Siswa\Belajar\DaftarTp;
use App\Livewire\Siswa\Belajar\MateriDetail;
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
        'title' => 'Integrasi Aplikasi Perkantoran (Office)',
        'description' => 'Memahami dan mempraktikkan pengolah kata, angka, dan presentasi secara bertahap.',
    ]);

    $this->teacher = User::factory()->create([
        'name' => 'Budi Guru Informatika',
        'role' => 'guru',
        'status' => 'active',
    ]);

    $this->student = User::factory()->create([
        'name' => 'Ahmad Siswa Rajin',
        'role' => 'siswa',
        'status' => 'active',
    ]);

    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->year->id,
    ]);

    Schema::enableForeignKeyConstraints();
});

test('full end-to-end simulation of gated learning workflow from teacher setup to student mastery', function () {
    // =========================================================================
    // STEP 1: GURU SETUP 3-STAGE GATED CURRICULUM
    // =========================================================================
    $this->actingAs($this->teacher);

    // 1.1 Modul 1: Literasi Pengenalan Antarmuka Word (checkpoint_only)
    $lesson1 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M1',
        'title' => 'Pengenalan Ribbons dan Menu Word',
        'content' => 'Materi literasi dasar mengenal antarmuka Microsoft Word dan fungsi ribbon.',
        'sort_order' => 1,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'checkpoint_only',
        'created_by' => $this->teacher->id,
    ]);
    $lesson1->classrooms()->attach($this->classroom->id);

    $cp1_1 = LessonCheckpoint::create([
        'lesson_id' => $lesson1->id,
        'question' => 'Di tab manakah Anda dapat menemukan opsi pengaturan paragraf dan font?',
        'options' => [
            'A' => 'Insert',
            'B' => 'Home',
            'C' => 'Layout',
            'D' => 'View',
        ],
        'correct_answer' => 'B',
        'explanation' => 'Tab Home memuat grup Font dan Paragraph dasar.',
        'sort_order' => 1,
    ]);

    $cp1_2 = LessonCheckpoint::create([
        'lesson_id' => $lesson1->id,
        'question' => 'Kombinasi tombol keyboard untuk membuat teks menjadi tebal (Bold) adalah?',
        'options' => [
            'A' => 'Ctrl + I',
            'B' => 'Ctrl + U',
            'C' => 'Ctrl + B',
            'D' => 'Ctrl + K',
        ],
        'correct_answer' => 'C',
        'explanation' => 'Ctrl + B adalah shortcut keyboard untuk Bold teks.',
        'sort_order' => 2,
    ]);

    // 1.2 Modul 2: Praktik Format Surat & Tabel (Hybrid: checkpoint_and_task)
    $lesson2 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M2',
        'title' => 'Praktik Pembuatan Surat Resmi & Tabel',
        'content' => 'Panduan pembuatan dokumen surat dinas lengkap dengan kop dan tabel lampiran.',
        'sort_order' => 2,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'checkpoint_and_task',
        'prerequisite_lesson_id' => $lesson1->id,
        'created_by' => $this->teacher->id,
    ]);
    $lesson2->classrooms()->attach($this->classroom->id);

    $cp2_1 = LessonCheckpoint::create([
        'lesson_id' => $lesson2->id,
        'question' => 'Menu untuk menyisipkan tabel ke dalam dokumen berada pada tab?',
        'options' => [
            'A' => 'Insert',
            'B' => 'Design',
            'C' => 'Home',
            'D' => 'References',
        ],
        'correct_answer' => 'A',
        'explanation' => 'Tab Insert menyediakan tombol Tables untuk menyisipkan baris dan kolom.',
        'sort_order' => 1,
    ]);

    $task2 = Assessment::create([
        'learning_objective_id' => $this->tp->id,
        'element_id' => $this->element->id,
        'lesson_id' => $lesson2->id,
        'type' => 'tugas',
        'title' => 'Praktik Dokumen: Pembuatan Surat Undangan Resmi (.docx)',
        'instructions' => 'Ketik surat resmi sesuai template contoh dan lampirkan tabel daftar hadir.',
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);
    $task2->classrooms()->attach($this->classroom->id);

    // 1.3 Modul 3: Integrasi Data Mail Merge (task_only)
    $lesson3 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M3',
        'title' => 'Integrasi Mail Merge dengan Data Excel',
        'content' => 'Menghubungkan sumber data tabel Excel ke dokumen surat Word.',
        'sort_order' => 3,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'task_only',
        'prerequisite_lesson_id' => $lesson2->id,
        'created_by' => $this->teacher->id,
    ]);
    $lesson3->classrooms()->attach($this->classroom->id);

    $task3 = Assessment::create([
        'learning_objective_id' => $this->tp->id,
        'element_id' => $this->element->id,
        'lesson_id' => $lesson3->id,
        'type' => 'tugas',
        'title' => 'Proyek Akhir Mail Merge Terintegrasi',
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);
    $task3->classrooms()->attach($this->classroom->id);

    // 1.4 Verifikasi Mode Pratinjau Guru
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson2,
    ])
    ->assertStatus(200)
    ->assertSee('Mode Pratinjau Guru')
    ->assertSee('Praktik Pembuatan Surat Resmi & Tabel');

    // =========================================================================
    // STEP 2: SISWA MASUK DAN MELIHAT DAFTAR MATERI
    // =========================================================================
    $this->actingAs($this->student);

    // 2.1 Cek status gembok pada Daftar TP
    Livewire::test(DaftarTp::class, [
        'element' => $this->element,
    ])
    ->assertStatus(200)
    ->assertSee('Pengenalan Ribbons dan Menu Word')
    ->assertSee('Syarat: Lulus Kuis Pemantik Literasi')
    ->assertSee('Praktik Pembuatan Surat Resmi &amp; Tabel', false)
    ->assertSee('🔒 Selesaikan prasyarat: "Pengenalan Ribbons dan Menu Word"', false)
    ->assertSee('Integrasi Mail Merge dengan Data Excel')
    ->assertSee('🔒 Selesaikan prasyarat: "Praktik Pembuatan Surat Resmi &amp; Tabel"', false);

    // 2.2 Uji Keamanan: Siswa mencoba melompati gerbang akses (Direct URL)
    // Siswa dialihkan otomatis ke materi prasyarat pertama (Lesson 1) dengan notifikasi
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson2,
    ])
    ->assertRedirect($lesson1->siswa_url);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson3,
    ])
    ->assertRedirect($lesson1->siswa_url);

    // =========================================================================
    // STEP 3: SISWA BELAJAR MODUL 1 & VERIFIKASI MEMBACA (CHECKPOINT QUIZ)
    // =========================================================================
    // 3.1 Buka Modul 1
    $materi1Component = Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson1,
    ])
    ->assertStatus(200)
    ->assertSee('Pengenalan Ribbons dan Menu Word')
    ->assertSee('Kuis Pemantik Literasi (Verifikasi Membaca)');

    // 3.2 Siswa mencoba kuis tapi ada jawaban salah (Soal 1 Benar 'B', Soal 2 Salah 'A')
    $materi1Component
        ->set('checkpointAnswers', [
            $cp1_1->id => 'B',
            $cp1_2->id => 'A', // Salah, harusnya C
        ])
        ->call('submitCheckpoints')
        ->assertHasNoErrors()
        ->assertSee('Skor Anda: 50%')
        ->assertSee('Silakan baca ulang modul materi dan coba lagi')
        ->assertSee('Belum Tepat');

    // Pastikan Modul 2 masih terkunci
    expect($lesson1->isCompletedBy($this->student))->toBeFalse();
    expect($lesson2->isUnlockedFor($this->student))->toBeFalse();

    // 3.3 Siswa mengulang kuis dan menjawab 100% benar
    $materi1Component
        ->call('tryAgainCheckpoints')
        ->set('checkpointAnswers', [
            $cp1_1->id => 'B',
            $cp1_2->id => 'C',
        ])
        ->call('submitCheckpoints')
        ->assertHasNoErrors()
        ->assertSee('Skor: 100% (Tuntas)')
        ->assertSee('Validasi literasi Anda tuntas');

    // Verifikasi Modul 1 tuntas dan Modul 2 terbuka!
    expect($lesson1->isCompletedBy($this->student))->toBeTrue();
    expect($lesson2->isUnlockedFor($this->student))->toBeTrue();

    // =========================================================================
    // STEP 4: SISWA MASUK KE MODUL 2 (HYBRID: CHECKPOINT + TUGAS PRAKTIK)
    // =========================================================================
    // 4.1 Buka Modul 2 (sudah terbuka)
    $materi2Component = Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson2,
    ])
    ->assertStatus(200)
    ->assertSee('Praktik Pembuatan Surat Resmi & Tabel');

    // 4.2 Siswa selesaikan kuis literasi Modul 2
    $materi2Component
        ->set('checkpointAnswers', [
            $cp2_1->id => 'A', // Benar: Insert
        ])
        ->call('submitCheckpoints')
        ->assertHasNoErrors()
        ->assertSee('Validasi literasi Anda tuntas');

    // Catatan: Kuis lulus, TETAPI Modul 2 adalah hybrid (checkpoint_and_task),
    // sehingga Modul 2 BELUM tuntas dan Modul 3 MASIH TERKUNCI!
    expect($lesson2->isCompletedBy($this->student))->toBeFalse();
    expect($lesson3->isUnlockedFor($this->student))->toBeFalse();

    // 4.3 Siswa mengumpulkan tugas praktik via KerjakanTugas
    Livewire::test(KerjakanTugas::class, [
        'assessment' => $task2,
    ])
    ->assertStatus(200);

    // Simulasikan pengumpulan file tugas
    Submission::create([
        'assessment_id' => $task2->id,
        'student_id' => $this->student->id,
        'file_path' => 'submissions/surat_resmi_ahmad.docx',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    // Verifikasi otomatisasi: Karena kuis sudah 100% dan tugas sudah dikumpulkan,
    // Modul 2 sekarang TUNTAS dan Modul 3 otomatis TERBUKA!
    expect($lesson2->isCompletedBy($this->student))->toBeTrue();
    expect($lesson3->isUnlockedFor($this->student))->toBeTrue();

    // =========================================================================
    // STEP 5: SISWA DAPAT MENGAKSES MODUL 3
    // =========================================================================
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson3,
    ])
    ->assertStatus(200)
    ->assertSee('Integrasi Mail Merge dengan Data Excel');

    // 5.1 Siswa dapat menavigasi balik ke Modul 1 dan Modul 2 dengan aman
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson1,
    ])
    ->assertStatus(200)
    ->assertSee('Skor: 100% (Tuntas)');
});

test('end-to-end flow of all-in-one module creation with lkpd template, kktp rubric, checkpoint quiz, and return navigation', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    // 1. GURU MEMBUAT MODUL AJAR DENGAN ASESMEN TERPADU (ALL-IN-ONE)
    $this->actingAs($this->teacher);

    $templateFile = \Illuminate\Http\UploadedFile::fake()->create('Template_Surat_Dinas_SMK.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test(LessonManagement::class)
        ->call('createForTp', $this->tp->id)
        ->set('title', 'Modul 1: Tata Naskah Surat Dinas Perkantoran')
        ->set('code', 'M1.1')
        ->set('summary', 'Siswa mampu memahami standar surat dinas dan menyusun surat resmi sesuai kaidah perkantoran.')
        ->set('content', 'Uraian modul ajar: Pelajari tata naskah dinas, margin 4-4-3-3, kop surat, nomor surat, dan lampiran tabel.')
        ->set('selectedClassrooms', [(string)$this->classroom->id])
        ->set('status', 'published')
        ->set('is_gated', true)
        ->set('completion_rule', 'checkpoint_and_task')
        ->set('checkpoints', [
            [
                'question' => 'Berapa margin standar atas-kiri-bawah-kanan untuk surat resmi?',
                'options' => [
                    'A' => '4 cm - 4 cm - 3 cm - 3 cm',
                    'B' => '2 cm - 2 cm - 2 cm - 2 cm',
                    'C' => '1 cm - 1 cm - 1 cm - 1 cm',
                    'D' => 'Bebas sesuai selera',
                ],
                'correct_answer' => 'A',
                'explanation' => 'Standar tata naskah dinas resmi menggunakan margin 4-4-3-3 cm.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Unsur apa yang wajib ada pada kepala surat (kop surat) dinas resmi?',
                'options' => [
                    'A' => 'Foto pribadi penulis',
                    'B' => 'Logo instansi, nama instansi, alamat, dan kontak resmi',
                    'C' => 'Daftar harga barang',
                    'D' => 'Daftar riwayat hidup',
                ],
                'correct_answer' => 'B',
                'explanation' => 'Kop surat memuat identitas lembaga: logo, nama instansi, alamat, dan kontak.',
                'sort_order' => 2,
            ]
        ])
        ->set('isCreatingNewTask', true)
        ->set('newTaskTitle', 'Tugas Praktik: Pembuatan Surat Dinas Resmi (.docx)')
        ->set('newTaskInstructions', 'Unduh berkas template LKPD berikut, ketik surat undangan rapat dinas sesuai instruksi modul, dan unggah file hasil pekerjaan Anda.')
        ->set('newTaskSubmissionTypes', ['file'])
        ->set('newTaskMaxScore', 100)
        ->set('newTaskKktp', "1. Format Margin 4-4-3-3 cm: 25 Poin\n2. Struktur Kop Surat Lengkap: 35 Poin\n3. Tabel Lampiran Agenda Rapat: 40 Poin")
        ->set('newTaskTemplateFile', $templateFile)
        ->call('save')
        ->assertHasNoErrors();

    // Verifikasi modul tersimpan di DB
    $createdLesson = Lesson::where('code', 'M1.1')->first();
    expect($createdLesson)->not->toBeNull();
    expect($createdLesson->is_gated)->toBeTrue();
    expect($createdLesson->completion_rule)->toBe('checkpoint_and_task');
    expect($createdLesson->checkpoints)->toHaveCount(2);

    // Verifikasi asesmen terpadu tersimpan dan tertaut
    $createdTask = Assessment::where('lesson_id', $createdLesson->id)->first();
    expect($createdTask)->not->toBeNull();
    expect($createdTask->title)->toBe('Tugas Praktik: Pembuatan Surat Dinas Resmi (.docx)');
    expect($createdTask->rubric)->toContain('Format Margin 4-4-3-3 cm: 25 Poin');
    expect($createdTask->teacher_attachments)->toHaveCount(1);
    expect($createdTask->teacher_attachments[0]['name'])->toBe('Template_Surat_Dinas_SMK.docx');
    \Illuminate\Support\Facades\Storage::disk('local')->assertExists($createdTask->teacher_attachments[0]['path']);

    // Guru juga menyiapkan Modul 2 lanjutan (prasyarat: Modul 1)
    $lesson2 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M1.2',
        'title' => 'Modul 2: Kolaborasi Data Spreadsheet & Mail Merge',
        'summary' => 'Membuat surat massal dengan data penerima otomatis dari tabel Excel.',
        'sort_order' => 2,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'standard',
        'prerequisite_lesson_id' => $createdLesson->id,
        'created_by' => $this->teacher->id,
    ]);
    $lesson2->classrooms()->attach($this->classroom->id);

    // 2. SISWA MEMBUKA MODUL DAN MENGERJAKAN ALUR PEMBELAJARAN
    $this->actingAs($this->student);

    // 2.1 Cek Modul 2 masih terkunci
    expect($lesson2->isUnlockedFor($this->student))->toBeFalse();

    // 2.2 Buka Modul 1
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $createdLesson,
    ])
    ->assertStatus(200)
    ->assertSee('Modul 1: Tata Naskah Surat Dinas Perkantoran')
    ->assertSee('Ada Template LKPD')
    ->assertSee('Ada Rubrik KKTP')
    ->assertSee('Kuis Pemantik Literasi (Verifikasi Membaca)');

    // 2.3 Siswa selesaikan Kuis Pemantik Literasi 100%
    $cpList = $createdLesson->checkpoints()->orderBy('sort_order')->get();
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $createdLesson,
    ])
    ->set('checkpointAnswers', [
        $cpList[0]->id => 'A',
        $cpList[1]->id => 'B',
    ])
    ->call('submitCheckpoints')
    ->assertHasNoErrors()
    ->assertSee('Skor: 100% (Tuntas)');

    // Kuis lulus, tapi tugas belum dikumpul -> Modul 2 harus masih terkunci!
    expect($createdLesson->isCompletedBy($this->student))->toBeFalse();
    expect($lesson2->isUnlockedFor($this->student))->toBeFalse();

    // 2.4 Siswa membuka halaman tugas praktik
    Livewire::test(KerjakanTugas::class, [
        'assessment' => $createdTask,
    ])
    ->assertStatus(200)
    ->assertSee('Unduh Template LKPD: Template_Surat_Dinas_SMK.docx')
    ->assertSee('Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)')
    ->assertSee('Format Margin 4-4-3-3 cm: 25 Poin')
    ->assertSee('Struktur Kop Surat Lengkap: 35 Poin')
    ->assertSee('Tabel Lampiran Agenda Rapat: 40 Poin');

    // 2.5 Siswa mengunggah file pengerjaan
    Submission::create([
        'assessment_id' => $createdTask->id,
        'student_id' => $this->student->id,
        'file_path' => 'submissions/surat_dinas_ahmad.docx',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    // Sekarang Kuis 100% + Tugas terkumpul -> Modul 1 tuntas & Modul 2 TERBUKA!
    expect($createdLesson->isCompletedBy($this->student))->toBeTrue();
    expect($lesson2->isUnlockedFor($this->student))->toBeTrue();

    // 3. GURU MEMBERIKAN PENILAIAN DAN FEEDBACK
    $submission = Submission::where('assessment_id', $createdTask->id)->where('student_id', $this->student->id)->first();
    $submission->update([
        'status' => Submission::STATUS_GRADED,
        'score' => 95,
        'feedback' => 'Sangat bagus! Margin dan kop surat presisi sesuai standar dinas perkantoran.',
        'graded_at' => now(),
        'graded_by' => $this->teacher->id,
    ]);

    // 4. SISWA MELIHAT HASIL NILAI DAN MENGGUNAKAN TOMBOL KEMBALI TUNGGAL
    Livewire::test(KerjakanTugas::class, [
        'assessment' => $createdTask,
    ])
    ->assertStatus(200)
    ->assertSee('Tugas Telah Dinilai oleh Guru')
    ->assertSee('95')
    ->assertSee('Sangat bagus! Margin dan kop surat presisi sesuai standar dinas perkantoran.')
    ->assertSee('Kembali ke Materi: Modul 1: Tata Naskah Surat Dinas Perkantoran')
    ->assertDontSee('Kembali ke Materi Pembelajaran'); // Memastikan tombol lama tidak muncul ganda

    // 5. SISWA DAPAT MEMBUKA MODUL 2 LANJUTAN
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $lesson2,
    ])
    ->assertStatus(200)
    ->assertSee('Modul 2: Kolaborasi Data Spreadsheet & Mail Merge');
});

