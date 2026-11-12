# Peta Akses Siswa & Rencana Eksekusi Modul Siswa

## 1. Peta Fitur & Status Saat Ini

| Fitur Guru | Tabel/Model | Halaman Siswa (Route) | Syarat Tampil | Yang BOLEH Siswa Lakukan | Yang TIDAK BOLEH Siswa Lakukan | Status Saat Ini |
|---|---|---|---|---|---|---|
| Materi | `lessons`, `lesson_classroom` | `belajar.materi` | `status`='published', jadwal `available_from` <= now, satu kelas | Membaca konten teks | Mengubah konten, melihat materi draft | **Ada** (`MateriDetail.php`) |
| Lampiran / Video / Simulasi | `lesson_attachments` | `belajar.materi` | Sama dengan Materi | Mengunduh lampiran, menonton video, menjalankan simulasi | Mengubah / menghapus lampiran | **Sebagian** (Lampiran/video ada di UI, komponen simulasi belum) |
| Tandai Selesai | `lesson_progress` | `belajar.materi` | Materi terbuka | Menandai selesai atau membatalkan | - | **Ada** (`toggleDone`) |
| Catatanku (Pribadi) | `notes` | `belajar.materi` | Materi terbuka | Membuat & mengubah catatan sendiri | Melihat catatan siswa lain | **Ada** (`saveNote`) |
| Diskusi | `discussions` | `belajar.materi` | Materi terbuka | Bertanya, menanggapi, melihat diskusi teman sekelas | Melihat diskusi kelas lain, menghapus/menyembunyikan | **Ada** (`sendDiscussion`) |
| Asesmen (Diagnostik/Formatif/Sumatif/Tugas) | `assessments`, `assessment_classroom`, `questions` | `tugas`, `tugas.kerjakan` | `status`='published', jadwal `opens_at` <= now | Mengerjakan, unggah file, lihat nilai/feedback jika `due_at` lewat / sudah dinilai | Melihat `answer_key` sebelum waktu, mengulang melebihi batas | **Ada** (`KerjakanTugas.php`), kunci jawaban sudah disembunyikan |
| Nilai & Progres TP | `objective_mastery`, `submissions`, `grade_entries` | `nilai`, `buku-nilai` | Asesmen dinilai, Buku Nilai diisi | Melihat nilai, umpan balik, dan status (tercapai/remedial) | Mengubah nilai, melihat nilai siswa lain | **Ada** (`DaftarNilai.php`, `Show.php` untuk Buku Nilai) |
| Remedial / Pengayaan | `objective_mastery` | `tugas`, `nilai` | Nilai < KKTP (Remedial) / >= 75 (Pengayaan) | Mengerjakan percobaan remedial jika dibuka guru | Mengerjakan pengayaan jika tidak berhak | **Sebagian** (Status dicatat, tapi batasan UI belum kaku) |
| Target Mingguan | `classrooms.weekly_lesson_target`, `lesson_progress` | `beranda` | Login | Melihat persentase & hari beruntun | Mengubah target | **Sebagian** (Baru View HTML, logika belum lengkap) |
| TP / ATP | `learning_objectives` | `belajar.tp` | TP dibuat guru pengampu kelas | Melihat daftar TP | Mengubah urutan/isi TP | **Ada** (`DaftarTp.php`) |

## 2. Aturan Asesmen Sumatif (SPEC §9)
**Aturan SPEC:** Asesmen sumatif elemen terbuka setelah semua TP di elemen itu tercapai (atau dibuka manual oleh guru).
**Status di Kode Sekarang:** **BELUM DITERAPKAN**. `DaftarTugas.php` saat ini hanya mengecek `status == 'published'` dan `opens_at <= now()`. Belum ada pengecekan kueri ke `objective_mastery` untuk memastikan semua TP dalam elemen tersebut berstatus `tercapai`.

## 3. Navigasi Siswa (SPEC §3)
- **Desktop Sidebar & Mobile Bottom Nav:** Beranda, Materi (Belajar), Tugas, Profil.
- **Status di Kode:** View `components.layouts.siswa` dan view yang lain sudah memiliki elemen navigasi, namun rute-rutenya sebagian masih mengarah ke placeholder atau penamaannya kurang seragam dengan menu (misalnya: menu "Materi" mengarah ke `/belajar/{elemen}` yang mengharuskan pemilihan elemen terlebih dahulu). Diperlukan halaman perantara `/belajar` (Daftar Elemen) agar navigasi berfungsi sempurna.

## 4. Daftar Celah SPEC (GAP) yang Membutuhkan Keputusan
Berikut adalah hal-hal yang tidak dijabarkan secara rinci di SPEC:

| Celah / GAP | Analisis & Opsi | Rekomendasi | Dampak Migrasi |
|---|---|---|---|
| **a) Tabel Pengumuman belum ada** | SPEC §8 menyebutkan "Pengumuman: guru membuat untuk kelasnya, admin untuk seluruh sekolah". Tabel `announcements` ada di SPEC §7 tetapi belum diimplementasikan di migrasi dan Model. | Buat migrasi `create_announcements_table`, buat komponen Livewire untuk Guru (buat pengumuman) dan Siswa (tampil di beranda). | Tambah 1 tabel `announcements` beserta relasinya. |
| **b) TP/KKTP spesifik per Kelas** | `learning_objectives` tidak memiliki `classroom_id`. Siswa saat ini melihat TP dengan filter `created_by` = guru pengampu. Jika 1 guru mengajar 2 kelas berbeda dengan skenario (KKTP/TP) berbeda, ini akan bocor. | Karena struktur DB sudah mengunci (satu guru memegang TP miliknya untuk semua kelas), pertahankan filter by `created_by` dan asumsikan 1 guru = 1 standar TP untuk seluruh kelasnya. | Tidak ada migrasi. Hanya memastikan kueri ketat melalui relasi `TeachingAssignment`. |
| **c) Absensi & Buku Nilai di luar SPEC** | Sudah kita kerjakan di iterasi sebelumnya melalui dokumen usulan, namun belum diperbarui secara tertulis ke `SPEC.md`. | Tidak perlu ubah SPEC.md secara manual, kita jalankan sesuai `docs/buku-nilai-usulan.md` dan `docs/absensi-usulan.md`. Menu ditambah di navigasi Siswa. | Tidak ada, migrasi sudah berjalan. |
| **d) Kapan nilai & kunci tampil ke siswa** | `KerjakanTugas.php` membocorkan kunci jika `due_at` lewat. Namun jika formatif tanpa `due_at` (hanya submitted), kapan munculnya? | Tampilkan nilai/feedback formatif/kuis segera setelah `status='dikumpulkan'`. Sumatif / Tugas Manual ditampilkan hanya setelah `status='dinilai'`. | Perubahan logika IF di `KerjakanTugas.php` dan `DaftarNilai.php`. |
| **e) Pratinjau "Lihat sebagai siswa"** | Guru perlu melihat bagaimana tugas tampil. Saat ini guru yang mengakses route siswa akan kena 403 atau dialihkan. | Tambahkan parameter `?preview=1` di rute siswa, beri bypass middleware jika role=guru, paksa status *Read-Only*. | Tidak ada migrasi. Modifikasi Policy dan Component. |
| **f) Tampilan materi belum dibuka** | Apakah materi yang `available_from` belum waktunya disembunyikan total atau tampil tapi di-disabled (gembok)? | Tampilkan judulnya di sidebar / daftar dengan ikon gembok dan nonaktifkan klik agar siswa tahu ada materi mendatang. | Hanya merubah kueri `DaftarTp.php` untuk menampilkan materi belum aktif dengan flag `is_locked`. |

## 5. Aturan Akses (Keamanan)
- Kueri Siswa via `classroom_student`: **Sudah diterapkan sebagian** di constructor `activeClassroom()`. Harus digarisbawahi bahwa ID di URL divalidasi dengan kelas aktifnya.
- Policy / Lintas Kelas: **Sudah ada** `abort(403)` jika siswa membuka tugas kelas lain.
- URL Guru/Admin: Middleware `role:siswa` sudah memblokir akses ke `/guru/*` dan `/admin/*`.
- Keamanan Payload Livewire (`answer_key`, `draft`): **Sudah diamankan** di `KerjakanTugas.php` dengan me-mapping properti dan menghapus `answer_key` jika syarat `showFeedback` tidak terpenuhi.

## 6. Rencana Tes Ujung-ke-Ujung (End-to-End) per Fitur
1. **Materi & Diskusi:** Guru A buat Materi X ke Kelas 1 (publish). Siswa kelas 1 bisa buka Materi X dan lihat konten. Siswa Kelas 2 gagal (403). Siswa Kelas 1 kirim komentar diskusi, Guru A bisa lihat dan siswa Kelas 2 tidak.
2. **Kuis Formatif:** Guru buat formatif (tanpa `due_at`). Siswa mengerjakan. Pengecekan payload Livewire untuk memastikan `answer_key` benar-benar terhapus (Test JSON fragment tidak mengandung string kunci). Setelah disubmit, siswa bisa lihat `answer_key`.
3. **Kuis Sumatif & Block Ketercapaian:** Guru buat Sumatif Elemen BK. TP BK-1 belum tercapai oleh siswa. Siswa buka halaman sumatif -> Ditolak / Tombol Disable. Guru manual set TP BK-1 tercapai -> Siswa bisa buka sumatif.
4. **Navigasi & Beranda:** Akses `/beranda` menampilkan statistik target mingguan dengan data tiruan, pastikan tidak crash.

## 7. Urutan Pengerjaan
1. **Tahap A (Beranda & Navigasi):** Membuat `/belajar` (Daftar Elemen) untuk menyambung menu, halaman Beranda (Target Mingguan, Hari Beruntun, Pengumuman), dan penambahan migrasi/model `announcements`.
   - *Berkas*: `CreateAnnouncementsTable`, `Announcement` model, `Beranda.php`, `DaftarElemen.php`.
   - *Tes*: Melihat pengumuman guru, melihat elemen.
2. **Tahap B (Perbaikan Belajar & Materi):** Menampilkan gembok untuk materi belum aktif, mengimplementasikan simulasi Von Neumann untuk subbab 5.2.
   - *Berkas*: `MateriDetail.php`, `simulasi-von-neumann.blade.php`.
   - *Tes*: Klik materi tergembok gagal.
3. **Tahap C (Kuis, Sumatif & Remedial):** Memperbaiki rule sumatif (syarat TP tercapai), merapikan tampilan umpan balik dan kebocoran `answer_key`, dan fitur retry remedial.
   - *Berkas*: `DaftarTugas.php`, `KerjakanTugas.php`.
   - *Tes*: Sumatif diblokir sebelum TP tercapai.
4. **Tahap D (Penilaian & Progres):** Menyempurnakan `/nilai` dan progress bar siswa per elemen, serta menghubungkan rapor, absensi, dan buku nilai ke menu siswa secara konsisten.
   - *Berkas*: `DaftarNilai.php`, `PetaKetercapaian.php` (untuk siswa).
   - *Tes*: Melihat nilai sesuai batasan visibility.
