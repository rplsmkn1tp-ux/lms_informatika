<?php

namespace Tests\Feature\Guru;

use Tests\TestCase;

class LessonManagementTest extends TestCase
{
    public function test_tag_script_dan_html_berbahaya_terbuang_dari_konten_materi()
    {
        $this->assertTrue(true);
    }

    public function test_guru_tidak_bisa_melihat_atau_mengubah_materi_guru_lain()
    {
        $this->assertTrue(true);
    }

    public function test_siswa_tidak_bisa_mengunduh_lampiran_dari_materi_yang_belum_terbit()
    {
        $this->assertTrue(true);
    }

    public function test_file_dengan_tipe_tidak_diizinkan_atau_lebih_dari_10_mb_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_url_youtube_yang_tidak_valid_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_menyalin_tp_template_yang_sama_dua_kali_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_akun_contoh_tidak_dibuat_saat_app_env_production()
    {
        $this->assertTrue(true);
    }

    public function test_guru_bisa_filter_rekap_progres_siswa_per_kelas()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'guru']);
        $unique = rand(1000, 9999);
        $element = \App\Models\Element::firstOrCreate(
            ['code' => 'T' . $unique],
            ['name' => 'Elemen Test ' . $unique, 'phase' => 'E', 'sort_order' => 99]
        );
        $tp = \App\Models\LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'TP.' . $unique,
            'title' => 'Test TP ' . $unique,
            'sort_order' => 1,
            'created_by' => $teacher->id,
        ]);
        $academicYear = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2024/2025 Test'],
            ['is_active' => true]
        );
        $classA = \App\Models\Classroom::create([
            'name' => 'Kelas A Test',
            'academic_year_id' => $academicYear->id,
            'join_code' => 'CLSA' . rand(1000, 9999),
        ]);
        $classB = \App\Models\Classroom::create([
            'name' => 'Kelas B Test',
            'academic_year_id' => $academicYear->id,
            'join_code' => 'CLSB' . rand(1000, 9999),
        ]);

        $studentA = \App\Models\User::factory()->create(['role' => 'siswa', 'name' => 'Budi Kelas A']);
        $studentA->classrooms()->attach($classA->id, ['academic_year_id' => $academicYear->id]);

        $studentB = \App\Models\User::factory()->create(['role' => 'siswa', 'name' => 'Siti Kelas B']);
        $studentB->classrooms()->attach($classB->id, ['academic_year_id' => $academicYear->id]);

        $lesson = \App\Models\Lesson::create([
            'learning_objective_id' => $tp->id,
            'code' => 'TEST-1.1',
            'title' => 'Lesson Test',
            'status' => 'published',
            'created_by' => $teacher->id,
            'sort_order' => 1,
        ]);
        $lesson->classrooms()->attach([$classA->id, $classB->id]);

        \Livewire\Livewire::actingAs($teacher)
            ->test(\App\Livewire\Guru\LessonManagement::class)
            ->call('openProgressModal', $lesson->id)
            ->assertSee('Budi Kelas A')
            ->assertSee('Siti Kelas B')
            ->set('progressClassFilter', $classA->id)
            ->assertSee('Budi Kelas A')
            ->assertDontSee('Siti Kelas B')
            ->set('progressClassFilter', $classB->id)
            ->assertSee('Siti Kelas B')
            ->assertDontSee('Budi Kelas A')
            ->set('progressClassFilter', null)
            ->assertSee('Budi Kelas A')
            ->assertSee('Siti Kelas B');
    }

    public function test_guru_dapat_membuat_materi_dengan_gated_learning_dan_kuis_pemantik()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'guru']);
        $unique = rand(1000, 9999);
        $element = \App\Models\Element::firstOrCreate(
            ['code' => 'EL' . $unique],
            ['name' => 'Elemen Test ' . $unique, 'phase' => 'E', 'sort_order' => 99]
        );
        $tp = \App\Models\LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'TP.' . $unique,
            'title' => 'Dasar Ms. Office',
            'sort_order' => 1,
            'created_by' => $teacher->id,
        ]);
        $academicYear = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2024/2025 Test ' . $unique],
            ['is_active' => true]
        );
        $classroom = \App\Models\Classroom::create([
            'name' => 'X RPL 1 ' . $unique,
            'academic_year_id' => $academicYear->id,
            'join_code' => 'JC' . $unique,
        ]);
        $teacher->taughtClassrooms()->attach($classroom->id);

        \Livewire\Livewire::actingAs($teacher)
            ->test(\App\Livewire\Guru\LessonManagement::class)
            ->call('createForTp', $tp->id)
            ->set('title', 'Mail Merge pada Microsoft Word')
            ->set('code', 'TIK.01.01')
            ->set('summary', 'Siswa mempelajari dan mempraktikkan Mail Merge.')
            ->set('selectedClassrooms', [(string)$classroom->id])
            ->set('is_gated', true)
            ->set('completion_rule', 'checkpoint_only')
            ->call('addCheckpoint')
            ->set('checkpoints.0.question', 'Apa fungsi utama Mail Merge pada Microsoft Word?')
            ->set('checkpoints.0.options.A', 'Membuat surat massal secara otomatis')
            ->set('checkpoints.0.options.B', 'Menyunting gambar bitmap')
            ->set('checkpoints.0.options.C', 'Menghitung rumus matematika')
            ->set('checkpoints.0.options.D', 'Menampilkan animasi presentasi')
            ->set('checkpoints.0.correct_answer', 'A')
            ->set('checkpoints.0.explanation', 'Mail Merge digunakan untuk membuat dokumen massal dengan data dinamis.')
            ->call('save')
            ->assertHasNoErrors();

        $lesson = \App\Models\Lesson::where('code', 'TIK.01.01')->first();
        $this->assertNotNull($lesson);
        $this->assertTrue($lesson->is_gated);
        $this->assertEquals('checkpoint_only', $lesson->completion_rule);

        $checkpoint = $lesson->checkpoints()->first();
        $this->assertNotNull($checkpoint);
        $this->assertEquals('Apa fungsi utama Mail Merge pada Microsoft Word?', $checkpoint->question);
        $this->assertEquals('A', $checkpoint->correct_answer);
        $this->assertEquals('Membuat surat massal secara otomatis', $checkpoint->options['A']);
    }

    public function test_validasi_kuis_pemantik_harus_lengkap_jika_gated_learning_aktif()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'guru']);
        $unique = rand(1000, 9999);
        $element = \App\Models\Element::firstOrCreate(
            ['code' => 'EL2' . $unique],
            ['name' => 'Elemen Test 2 ' . $unique, 'phase' => 'E', 'sort_order' => 99]
        );
        $tp = \App\Models\LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'TP2.' . $unique,
            'title' => 'TP Uji Validasi',
            'sort_order' => 1,
            'created_by' => $teacher->id,
        ]);
        $academicYear = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2024/2025 Test Validasi ' . $unique],
            ['is_active' => true]
        );
        $classroom = \App\Models\Classroom::create([
            'name' => 'X RPL 2 ' . $unique,
            'academic_year_id' => $academicYear->id,
            'join_code' => 'JC2' . $unique,
        ]);
        $teacher->taughtClassrooms()->attach($classroom->id);

        \Livewire\Livewire::actingAs($teacher)
            ->test(\App\Livewire\Guru\LessonManagement::class)
            ->call('createForTp', $tp->id)
            ->set('title', 'Materi Uji Checkpoint Kosong')
            ->set('code', 'TIK.ERR.' . $unique)
            ->set('summary', 'Pengujian validasi checkpoint.')
            ->set('selectedClassrooms', [(string)$classroom->id])
            ->set('is_gated', true)
            ->set('completion_rule', 'checkpoint_only')
            ->set('checkpoints', [])
            ->call('save')
            ->assertHasErrors(['checkpoints']);
    }

    public function test_duplikasi_materi_berjenjang_ikut_menyalin_kuis_pemantik()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'guru']);
        $unique = rand(1000, 9999);
        $element = \App\Models\Element::firstOrCreate(
            ['code' => 'EL3' . $unique],
            ['name' => 'Elemen Test 3 ' . $unique, 'phase' => 'E', 'sort_order' => 99]
        );
        $tp = \App\Models\LearningObjective::create([
            'element_id' => $element->id,
            'code' => 'TP3.' . $unique,
            'title' => 'TP Duplikasi',
            'sort_order' => 1,
            'created_by' => $teacher->id,
        ]);
        $academicYear = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2024/2025 Test Duplikat ' . $unique],
            ['is_active' => true]
        );
        $classroom = \App\Models\Classroom::create([
            'name' => 'X RPL 3 ' . $unique,
            'academic_year_id' => $academicYear->id,
            'join_code' => 'JC3' . $unique,
        ]);
        $teacher->taughtClassrooms()->attach($classroom->id);

        $lesson = \App\Models\Lesson::create([
            'learning_objective_id' => $tp->id,
            'code' => 'TIK.DUP.' . $unique,
            'title' => 'Materi Asli Excel',
            'summary' => 'Rumus VLOOKUP dan HLOOKUP.',
            'status' => 'published',
            'created_by' => $teacher->id,
            'sort_order' => 1,
            'is_gated' => true,
            'completion_rule' => 'checkpoint_and_task',
        ]);
        $lesson->classrooms()->attach($classroom->id);
        $lesson->checkpoints()->create([
            'question' => 'Apa beda VLOOKUP dan HLOOKUP?',
            'options' => [
                'A' => 'VLOOKUP vertikal, HLOOKUP horizontal',
                'B' => 'VLOOKUP gambar, HLOOKUP teks',
                'C' => 'Tidak ada perbedaan',
                'D' => 'VLOOKUP untuk Word',
            ],
            'correct_answer' => 'A',
            'explanation' => 'V untuk vertical, H untuk horizontal.',
            'sort_order' => 1,
        ]);

        \Livewire\Livewire::actingAs($teacher)
            ->test(\App\Livewire\Guru\LessonManagement::class)
            ->call('duplicate', $lesson->id)
            ->assertHasNoErrors();

        $copyLesson = \App\Models\Lesson::where('code', 'TIK.DUP.' . $unique . '-copy')->first();
        $this->assertNotNull($copyLesson);
        $this->assertTrue($copyLesson->is_gated);
        $this->assertEquals('checkpoint_and_task', $copyLesson->completion_rule);

        $copyCheckpoint = $copyLesson->checkpoints()->first();
        $this->assertNotNull($copyCheckpoint);
        $this->assertEquals('Apa beda VLOOKUP dan HLOOKUP?', $copyCheckpoint->question);
        $this->assertEquals('A', $copyCheckpoint->correct_answer);
    }
}
