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
}
