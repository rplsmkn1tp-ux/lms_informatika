# Status Pemenuhan Spesifikasi (SPEC.md Bagian 5–9 & 13)

Berikut adalah hasil audit kelengkapan fitur berdasarkan `SPEC.md` dan permintaan tambahan terakhir.

| Fitur | Status | Route atau File | Nama Tes yang Membuktikannya |
|---|---|---|---|
| **Bagian 5 & 6: Akun & Pengguna** | | | |
| Admin: Verifikasi akun guru | Selesai | `admin/verifikasi`, `App\Livewire\Admin\Verification` | `Admin/VerificationTest.php` |
| Admin: Kelola akun (nonaktifkan, ubah data, dll) | Sebagian | `admin/users`, `App\Livewire\Admin\UserManagement` | `Admin/UserManagementTest.php` |
| Admin: Kelola akun lengkap (Tab Guru/Siswa, sandi sementara) | Belum | - | Belum |
| Admin: Pindah kelas siswa | Belum | - | Belum |
| Siswa: Pendaftaran dengan kode kelas | Selesai | `register`, `App\Models\User` | `Auth/RegistrationTest.php` |
| **Bagian 5: Master Data** | | | |
| Admin: Tahun ajaran & semester | Selesai | `admin/tahun-ajaran`, `App\Livewire\Admin\AcademicYearManagement` | `Admin/AcademicYearManagementTest.php` |
| Admin: Kelas & rombel | Selesai | `admin/classrooms`, `App\Livewire\Admin\ClassroomManagement` | `Admin/ClassroomManagementTest.php` |
| Admin: Plot guru mengajar | Selesai | `admin/teaching-assignments`, `App\Livewire\Admin\TeachingAssignment` | `Admin/TeachingAssignmentTest.php` |
| Admin: Kurikulum CP Fase E | Selesai | `admin/kurikulum`, `App\Livewire\Admin\CurriculumManagement` | `Admin/CurriculumManagementTest.php` |
| Admin: Pengaturan sekolah | Selesai | `admin/pengaturan`, `App\Livewire\Admin\Settings` | `Settings/SettingsTest.php` |
| **Bagian 5: Pembelajaran Guru** | | | |
| Guru: Susun TP, ATP, KKTP | Selesai | `guru/tp`, `App\Livewire\Guru\TpManagement` | `Guru/TpManagementTest.php` |
| Guru: Buat materi, subbab, video, lampiran | Selesai | `guru/materi`, `App\Livewire\Guru\LessonManagement` | `Guru/LessonManagementTest.php` |
| Guru: Buat asesmen & bank soal | Selesai | `guru/asesmen`, `App\Livewire\Guru\AssessmentManagement` | `Guru/AssessmentManagementTest.php` |
| **Bagian 5 & 8: Pembelajaran Siswa (Tahap 4)** | | | |
| Siswa: Beranda (target mingguan, dll) | Selesai | `beranda`, `resources/views/siswa/beranda.blade.php` | `Student/BerandaTest.php` |
| Siswa: Halaman materi (Desain, simulasi) | Selesai | `belajar/...`, `App\Livewire\Siswa\Belajar\MateriDetail` | `Student/MateriDetailTest.php` |
| Siswa: Kuis (Tanpa kunci di browser, pembahasan, auto-save) | Belum | - | Belum |
| Siswa: Kuis (Batas percobaan, timer) | Belum | - | Belum |
| Siswa: Kumpulkan tugas | Selesai | `tugas/{assessment}`, `KerjakanTugas.php` | `SiswaTugasTest.php` |
| Siswa: Diskusi materi | Sebagian | `App\Livewire\Siswa\Belajar\MateriDetail` | `Student/MateriDetailTest.php` |
| Siswa: Catatanku | Selesai | `App\Livewire\Siswa\Belajar\MateriDetail` | `Student/MateriDetailTest.php` |
| **Bagian 5 & 9: Penilaian & Rapor (Tahap 5)** | | | |
| Guru: Antrean penilaian dengan rubrik | Selesai | `guru/penilaian` | `SmokeTest.php` |
| Guru/Siswa: Umpan balik & remedial grants | Selesai | `AssessmentAttemptGrant` | `SmokeTest.php` |
| Sistem: Ketercapaian TP otomatis (Tercapai/Remedial) | Selesai | `ObjectiveMasteryService` | `SmokeTest.php` |
| Guru: Peta ketercapaian TP | Selesai | `guru/peta-ketercapaian` | `SmokeTest.php` |
| Sistem: Sumatif terbuka otomatis | Selesai | `ObjectiveMasteryService` | `SmokeTest.php` |
| Guru: Rapor per elemen (Ekspor PDF) | Selesai | `guru/laporan-rapor`, `RaporController` | `SmokeTest.php` |
| Admin: Laporan admin | Selesai | `admin/laporan-kelulusan` | `SmokeTest.php` |
| **Bagian 8 & 13: Fitur Tambahan** | | | |
| Guru/Admin: CRUD Pengumuman | Belum | - | Belum |
| Keamanan: Purifier HTML saat DITAMPILKAN | Belum | - | Belum |
