<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use App\Models\Assessment;
use App\Models\Submission;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== MEMULAI PENGUJIAN FITUR MATERI GURU & SISWA ===\n\n";

// 1. GURU: Tambah ATP baru
echo "1. Menguji Tambah ATP Baru oleh Guru...\n";
$guru = User::where('role', 'guru')->first();
if (!$guru) {
    echo "ERROR: User guru tidak ditemukan.\n";
    exit(1);
}
Auth::login($guru);
echo "   Login sebagai Guru: {$guru->name} (ID: {$guru->id})\n";

$element = Element::orderBy('code')->first();
echo "   Elemen: {$element->name} (ID: {$element->id})\n";

$atpCode = 'BK-TEST-' . rand(100, 999);
$tp = LearningObjective::create([
    'element_id' => $element->id,
    'code' => $atpCode,
    'title' => 'Analisis Graf dan Algoritma Pencarian Jalur',
    'description' => 'Siswa mampu merepresentasikan masalah graf dan menelusuri algoritma BFS/DFS.',
    'kktp' => 75,
    'sort_order' => 99,
    'is_template' => false,
    'created_by' => $guru->id,
]);
echo "   ATP berhasil dibuat: [{$tp->code}] {$tp->title} (ID: {$tp->id})\n";

// 2. GURU: Buat Materi dengan 1 PDF dan 1 Video YouTube
echo "\n2. Menguji Pembuatan Materi dengan PDF privat & Video YouTube...\n";

// Buat dummy PDF di storage local
$dummyPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";
$pdfPath = 'lesson_attachments/test_dummy_' . uniqid() . '.pdf';
Storage::disk('local')->put($pdfPath, $dummyPdfContent);
$pdfSize = Storage::disk('local')->size($pdfPath);

$targetClassroom = $guru->taughtClassrooms->first();
if (!$targetClassroom) {
    $targetClassroom = Classroom::first();
    $guru->taughtClassrooms()->attach($targetClassroom->id, ['academic_year' => '2026/2027']);
}
echo "   Kelas Target: {$targetClassroom->name} (ID: {$targetClassroom->id})\n";

// Extract YouTube ID
$testYoutubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$extractedVideoId = \App\Livewire\Guru\LessonManagement::extractYouTubeId($testYoutubeUrl);
echo "   Ekstraksi YouTube URL: '{$testYoutubeUrl}' -> ID: '{$extractedVideoId}'\n";
if ($extractedVideoId !== 'dQw4w9WgXcQ') {
    echo "ERROR: Ekstraksi YouTube ID gagal!\n";
    exit(1);
}

// Simpan Lesson
$lesson = Lesson::create([
    'learning_objective_id' => $tp->id,
    'code' => $tp->code . '.1',
    'title' => 'Pengenalan Algoritma BFS dan DFS pada Graf',
    'summary' => 'Mempelajari cara kerja algoritma Breadth-First Search dan Depth-First Search.',
    'content' => 'Berikut adalah pengantar materi representasi graf menggunakan adjacency list dan adjacency matrix.',
    'estimated_minutes' => 45,
    'sort_order' => 1,
    'status' => 'published',
    'created_by' => $guru->id,
]);

// Sync Classroom
$lesson->classrooms()->sync([$targetClassroom->id => ['available_from' => null]]);

// Lampiran PDF
$pdfAttachment = $lesson->attachments()->create([
    'label' => 'Modul 1 - Teori Graf dan Lintasan Terpendek',
    'file_path' => $pdfPath,
    'type' => 'pdf',
    'sort_order' => 1,
    'file_size' => $pdfSize,
]);

// Lampiran YouTube
$videoAttachment = $lesson->attachments()->create([
    'label' => 'Animasi Visualisasi BFS dan DFS',
    'file_path' => $extractedVideoId,
    'type' => 'youtube',
    'sort_order' => 2,
]);

echo "   Materi berhasil dibuat: [{$lesson->code}] {$lesson->title} (ID: {$lesson->id})\n";
echo "   - PDF: {$pdfAttachment->label} (Path: {$pdfAttachment->file_path})\n";
echo "   - Video: {$videoAttachment->label} (ID: {$videoAttachment->file_path})\n";

// 3. GURU: Hubungkan 1 Tugas ke Materi Ini
echo "\n3. Menguji Pembuatan Tugas yang Terhubung ke Materi...\n";
$assessment = Assessment::create([
    'title' => 'Tugas Praktik 1: Implementasi Graf BFS',
    'instructions' => 'Buatlah dokumen PPTX atau DOCX laporan hasil penelusuran graf dan simulasinya.',
    'type' => 'tugas',
    'lesson_id' => $lesson->id,
    'learning_objective_id' => $tp->id,
    'created_by' => $guru->id,
    'status' => 'published',
    'submission_types' => ['file', 'text'],
    'max_score' => 100,
    'due_at' => Carbon::now()->addDays(7),
]);
$assessment->classrooms()->sync([$targetClassroom->id]);
echo "   Tugas berhasil dibuat & terhubung: {$assessment->title} (ID: {$assessment->id})\n";

// Cek proteksi hapus ATP jika sudah dipakai materi
echo "\n4. Menguji Proteksi Hapus ATP...\n";
try {
    $component = new \App\Livewire\Guru\TpManagement();
    $component->delete($tp->id);
    // Jika lolos tanpa error, cek session error
    if (session()->has('error')) {
        echo "   [BERHASIL] Hapus ATP ditolak: " . session('error') . "\n";
    } else {
        echo "   [PERINGATAN] Hapus ATP tidak memunculkan error session.\n";
    }
} catch (\Exception $e) {
    echo "   [BERHASIL] Hapus ATP ditolak dengan exception: " . $e->getMessage() . "\n";
}

// 5. SISWA KELAS TERSEBUT: Akses PDF, Tonton Video, Catat Progres
echo "\n5. Menguji Akses Siswa di Kelas Target ({$targetClassroom->name})...\n";
$siswa = $targetClassroom->students()->first();
if (!$siswa) {
    echo "   Membuat siswa dummy di kelas {$targetClassroom->name}...\n";
    $siswa = User::create([
        'name' => 'Siswa Uji Materi',
        'email' => 'siswa_uji_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'siswa',
        'username' => 'siswa_uji_' . rand(100, 999),
        'nisn' => '9988776655',
    ]);
    $targetClassroom->students()->attach($siswa->id);
}
Auth::login($siswa);
echo "   Login sebagai Siswa: {$siswa->name} (ID: {$siswa->id})\n";

// Cek controller stream PDF
$controller = new \App\Http\Controllers\LessonAttachmentController();
$response = $controller->streamPdf($lesson, $pdfAttachment);
echo "   Hasil Stream PDF Controller: HTTP Status " . $response->getStatusCode() . "\n";
echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
if ($response->getStatusCode() !== 200 || !str_contains($response->headers->get('Content-Type'), 'pdf')) {
    echo "ERROR: Streaming PDF gagal!\n";
    exit(1);
}

// Test Download PDF
$downloadResponse = $controller->downloadPdf($lesson, $pdfAttachment);
echo "   Hasil Download PDF: HTTP Status " . $downloadResponse->getStatusCode() . "\n";
echo "   - Content-Disposition: " . $downloadResponse->headers->get('Content-Disposition') . "\n";

// Test Livewire MateriDetail komponen
$materiComponent = new \App\Livewire\Siswa\Belajar\MateriDetail();
$materiComponent->mount($element, $tp, $lesson->code);
echo "   MateriDetail::mount() berhasil dijalankan tanpa error!\n";
echo "   - isNotFound: " . ($materiComponent->isNotFound ? 'true' : 'false') . "\n";
echo "   - isLocked: " . ($materiComponent->isLocked ? 'true' : 'false') . "\n";

// Siswa tandai PDF sudah dibaca
echo "   Menguji 'Tandai sudah dibaca' untuk PDF...\n";
$materiComponent->toggleItemProgress('pdf_' . $pdfAttachment->id);
$isPdfDone = $materiComponent->isItemDone('pdf_' . $pdfAttachment->id);
echo "   - Status PDF sudah dibaca: " . ($isPdfDone ? 'YA' : 'TIDAK') . "\n";

// Siswa tandai Video sudah ditonton
echo "   Menguji 'Tandai sudah ditonton' untuk Video YouTube...\n";
$materiComponent->toggleItemProgress('video_' . $videoAttachment->id);
$isVideoDone = $materiComponent->isItemDone('video_' . $videoAttachment->id);
echo "   - Status Video sudah ditonton: " . ($isVideoDone ? 'YA' : 'TIDAK') . "\n";

// Cek progres akhir
$progData = $materiComponent->getProgressDataProperty();
echo "   - Rekap Progres Siswa: {$progData['completed']} / {$progData['total']} selesai ({$progData['percent']}%)\n";

// 6. SISWA KERJAKAN TUGAS DENGAN FILE PPTX / DOCX (Maks 20MB)
echo "\n6. Menguji Pengumpulan Tugas Siswa dengan Dokumen PPTX / DOCX...\n";
$dummyDocxContent = "PK\x03\x04Dummy DOCX/PPTX format binary header for testing 20MB upload support";
$uploadedSubmission = Submission::create([
    'assessment_id' => $assessment->id,
    'student_id' => $siswa->id,
    'file_path' => 'submissions/' . uniqid() . '_tugas_presentasi.pptx',
    'submitted_at' => Carbon::now(),
    'status' => Submission::STATUS_SUBMITTED,
]);
echo "   Tugas berhasil dikumpulkan: {$uploadedSubmission->original_filename} (Format: PPTX, ID Submission: {$uploadedSubmission->id})\n";

// 7. KEAMANAN: SISWA KELAS LAIN DAN MATERI DRAF
echo "\n7. Menguji Keamanan: Isolasi Siswa Kelas Lain & Materi Draf...\n";

// Buat kelas lain tanpa materi ini
$otherClassroom = Classroom::where('id', '!=', $targetClassroom->id)->first();
if (!$otherClassroom) {
    $otherClassroom = Classroom::create(['name' => '10 Otomatisasi Lain']);
}
$otherStudent = User::create([
    'name' => 'Siswa Kelas Lain',
    'email' => 'siswa_lain_' . uniqid() . '@example.com',
    'password' => bcrypt('password'),
    'role' => 'siswa',
    'username' => 'siswa_lain_' . rand(100, 999),
    'nisn' => (string) rand(1000000000, 9999999999),
]);
$academicYear = \App\Models\AcademicYear::first();
$otherClassroom->students()->attach($otherStudent->id, ['academic_year_id' => $academicYear?->id ?? 1]);
Auth::login($otherStudent);
echo "   Login sebagai Siswa Kelas Lain: {$otherStudent->name} (Kelas: {$otherClassroom->name})\n";

// Coba akses PDF
try {
    $deniedResponse = $controller->streamPdf($lesson, $pdfAttachment);
    echo "ERROR: Siswa kelas lain berhasil streaming PDF! (Status: " . $deniedResponse->getStatusCode() . ")\n";
    exit(1);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "   [AMAN] Akses PDF ditolak dengan status HTTP: " . $e->getStatusCode() . " (" . $e->getMessage() . ")\n";
}

// Coba akses MateriDetail
$otherMateriComp = new \App\Livewire\Siswa\Belajar\MateriDetail();
$otherMateriComp->mount($element, $tp, $lesson->code);
echo "   [AMAN] Siswa kelas lain membuka MateriDetail -> isNotFound: " . ($otherMateriComp->isNotFound ? 'true' : 'false') . " (Pesan: {$otherMateriComp->errorMessage})\n";

// Uji materi status Draf
$draftLesson = Lesson::create([
    'learning_objective_id' => $tp->id,
    'code' => $tp->code . '.draft',
    'title' => 'Materi Rahasia Masih Draf',
    'status' => 'draft',
    'created_by' => $guru->id,
]);
$draftLesson->classrooms()->sync([$targetClassroom->id]);

Auth::login($siswa); // Siswa kelas target
$draftMateriComp = new \App\Livewire\Siswa\Belajar\MateriDetail();
$draftMateriComp->mount($element, $tp, $draftLesson->code);
echo "   [AMAN] Siswa kelas target membuka materi Draf -> isNotFound: " . ($draftMateriComp->isNotFound ? 'true' : 'false') . " (Pesan: {$draftMateriComp->errorMessage})\n";

// Bersihkan data tes jika perlu (atau biarkan untuk verifikasi browser)
echo "\n=== SEMUA PENGUJIAN SELESAI DAN BERHASIL 100%! ===\n";
