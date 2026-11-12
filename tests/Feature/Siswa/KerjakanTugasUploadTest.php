<?php

use App\Models\User;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Livewire\Siswa\Tugas\KerjakanTugas;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('siswa can see styled upload button and select file', function () {
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $class = Classroom::create(['name' => 'X-RPL-Test', 'join_code' => 'RPLTEST1', 'academic_year_id' => $year->id, 'grade' => '10']);
    $student = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    $student->classrooms()->attach($class->id, ['academic_year_id' => $year->id]);

    $assessment = Assessment::create([
        'title' => 'Tugas Praktik Jaringan',
        'type' => 'tugas',
        'status' => 'published',
        'submission_types' => ['file'],
        'created_by' => 1,
    ]);
    $assessment->classrooms()->attach($class->id);

    $test = Livewire::actingAs($student)
        ->test(KerjakanTugas::class, ['assessment' => $assessment]);

    $test->assertStatus(200);
    $test->assertSee('Pilih Berkas Jawaban');
    $test->assertSee('atau seret dan lepas berkas ke sini');

    // Simulate file upload
    $file = UploadedFile::fake()->create('laporan_tugas.pdf', 1024, 'application/pdf');
    $test->set('uploadedFile', $file);

    $test->assertSee('laporan_tugas.pdf');
    $test->assertSee('Siap Dikumpulkan');
    $test->assertSee('Hapus');

    // Test remove file
    $test->call('removeUploadedFile');
    $test->assertDontSee('laporan_tugas.pdf');
});

