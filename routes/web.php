<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::match(['get', 'post'], 'logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::guard('web')->logout();

    if ($request->hasSession()) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect('/');
})->name('logout');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('dashboard', function() {
        $role = auth()->user()->role;
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        if ($role === 'guru') return redirect()->route('guru.dashboard');
        return redirect()->route('siswa.beranda');
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('users', \App\Livewire\Admin\UserManagement::class)->name('users');
        Route::get('tahun-ajaran', \App\Livewire\Admin\AcademicYearManagement::class)->name('academic-years');
        Route::get('classrooms', \App\Livewire\Admin\ClassroomManagement::class)->name('classrooms');
        Route::get('teaching-assignments', \App\Livewire\Admin\TeachingAssignment::class)->name('teaching-assignments');
        Route::get('jadwal/waktu', \App\Livewire\Admin\Jadwal\BellPeriodManager::class)->name('jadwal.waktu');
        Route::get('jadwal/assignment/{assignment}', \App\Livewire\Admin\Jadwal\ScheduleEntryManager::class)->name('jadwal.assignment');
        Route::get('kurikulum', \App\Livewire\Admin\CurriculumManagement::class)->name('curriculum');
        Route::get('pengaturan', \App\Livewire\Admin\Settings::class)->name('settings');
        Route::get('pengaturan/logo', function () {
            return redirect()->route('admin.settings');
        })->name('settings.logo');
        Route::get('verifikasi', \App\Livewire\Admin\Verification::class)->name('verifikasi');
        Route::get('laporan-absensi', \App\Livewire\Admin\Absensi\LaporanAbsensi::class)->name('laporan-absensi');
        Route::get('laporan-kelulusan', \App\Livewire\Admin\LaporanKelulusan::class)->name('laporan-kelulusan');
    });

    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/', \App\Livewire\Guru\Dashboard::class)->name('dashboard');
        Route::get('kelas/{classroom}', \App\Livewire\Guru\ClassroomDetail::class)->name('classroom');
        Route::get('tp', \App\Livewire\Guru\TpManagement::class)->name('tp');
        Route::get('materi', \App\Livewire\Guru\LessonManagement::class)->name('lessons');
        Route::get('materi/{lesson}/terbitkan', \App\Livewire\Guru\LessonPublish::class)->name('lessons.publish');
        Route::get('materi/{lesson}/pratinjau', \App\Livewire\Siswa\Belajar\MateriDetail::class)->name('lessons.preview');
        Route::get('materi/{lesson}', \App\Livewire\Siswa\Belajar\MateriDetail::class)->name('lessons.show');

        Route::get('absensi', \App\Livewire\Guru\Absensi\Index::class)->name('absensi.index');
        Route::get('absensi/{classroom}/sesi', \App\Livewire\Guru\Absensi\SessionManager::class)->name('absensi.session');
        Route::get('absensi/{classroom}/form/{sessionId}', \App\Livewire\Guru\Absensi\SessionForm::class)->name('absensi.form');
        Route::get('agenda', \App\Livewire\Guru\Agenda\Index::class)->name('agenda.index');
        Route::get('peta-ketercapaian', \App\Livewire\Guru\PetaKetercapaian::class)->name('peta-ketercapaian');
        Route::get('laporan-rapor', \App\Livewire\Guru\LaporanRapor::class)->name('laporan-rapor');
        Route::get('buku-nilai', \App\Livewire\Guru\BukuNilai\Index::class)->name('buku-nilai.index');
        Route::get('buku-nilai/{classroom}', \App\Livewire\Guru\BukuNilai\Spreadsheet::class)->name('buku-nilai.spreadsheet');
        Route::get('buku-nilai/{classroom}/{academicYear}/pdf', [\App\Http\Controllers\Guru\BukuNilaiExportController::class, 'pdf'])->name('buku-nilai.export.pdf');
        Route::get('buku-nilai/{classroom}/{academicYear}/excel', [\App\Http\Controllers\Guru\BukuNilaiExportController::class, 'excel'])->name('buku-nilai.export.excel');
        Route::get('rapor/cetak-siswa/{student}/{classroom}/{academicYear}', [\App\Http\Controllers\RaporController::class, 'cetakSiswa'])->name('rapor.cetak-siswa');
        Route::get('jadwal', \App\Livewire\Guru\Jadwal\Index::class)->name('jadwal');

        Route::get('tugas', \App\Livewire\Guru\Tugas\Index::class)->name('tugas.index');
        Route::get('tugas/buat', \App\Livewire\Guru\Tugas\Form::class)->name('tugas.create');
        Route::get('tugas/{assessment}/edit', \App\Livewire\Guru\Tugas\Form::class)->name('tugas.edit');
        Route::get('tugas/{assessment}/detail', \App\Livewire\Guru\Tugas\Detail::class)->name('tugas.detail');
        Route::get('tugas/{assessment}/download-zip', [\App\Http\Controllers\Guru\TugasExportController::class, 'downloadZip'])->name('tugas.download-zip');
    });

    // Rute akses materi PDF privat (aman dengan otorisasi hak akses controller)
    Route::get('materi/{lesson}/attachment/{attachment}/stream', [\App\Http\Controllers\LessonAttachmentController::class, 'streamPdf'])->name('materi.attachment.stream');
    Route::get('materi/{lesson}/attachment/{attachment}/download', [\App\Http\Controllers\LessonAttachmentController::class, 'downloadPdf'])->name('materi.attachment.download');

    // Rute unduh lampiran dan file jawaban (aman dengan validasi hak akses di controller)
    Route::get('tugas/{assessment}/attachment/{index}', [\App\Http\Controllers\Guru\TugasExportController::class, 'downloadTeacherAttachment'])->name('guru.tugas.download-attachment');
    Route::get('tugas/submission/{submission}/file', [\App\Http\Controllers\Guru\TugasExportController::class, 'serveSubmissionFile'])->name('guru.tugas.submission.file');
    Route::get('tugas/submission/{submission}/download', [\App\Http\Controllers\Guru\TugasExportController::class, 'downloadSubmissionFile'])->name('guru.tugas.submission.download');
    Route::get('tugas/submission/{submission}/preview', [\App\Http\Controllers\Guru\TugasExportController::class, 'previewSubmissionFile'])->name('guru.tugas.submission.preview');

    Route::middleware('role:siswa')->name('siswa.')->group(function () {
        Route::get('beranda', \App\Livewire\Siswa\Beranda::class)->name('beranda');
        Route::get('belajar', \App\Livewire\Siswa\Belajar\DaftarElemen::class)->name('belajar');
        Route::get('belajar/{element:code}', \App\Livewire\Siswa\Belajar\DaftarTp::class)->name('belajar.tp');
        Route::get('belajar/{element:code}/{tp:code}/{lesson}', \App\Livewire\Siswa\Belajar\MateriDetail::class)->name('belajar.materi');
        Route::get('tugas', \App\Livewire\Siswa\Tugas\DaftarTugas::class)->name('tugas');
        Route::get('tugas/{assessment}', \App\Livewire\Siswa\Tugas\KerjakanTugas::class)->name('tugas.kerjakan');
        Route::get('nilai', \App\Livewire\Siswa\Nilai\DaftarNilai::class)->name('nilai');
        Route::get('buku-nilai', \App\Livewire\Siswa\BukuNilai\Show::class)->name('buku-nilai');
        Route::get('absensi', \App\Livewire\Siswa\Absensi\KehadiranSaya::class)->name('absensi');
        Route::get('rapor', \App\Livewire\Siswa\Rapor::class)->name('rapor');
        Route::get('rapor/cetak/{classroom}/{year}', [\App\Http\Controllers\RaporController::class, 'cetakSiswaSendiri'])->name('rapor.cetak');
        Route::view('coming-soon', 'siswa.coming-soon')->name('coming-soon');
    });
});

Route::middleware('auth')->group(function () {
    Route::view('pending-verification', 'pages.pending')->name('pending.notice');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('profil', \App\Livewire\Profil::class)->name('profil');
});
