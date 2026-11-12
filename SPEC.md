# SPEC — Ruang Informatika

Aplikasi web pembelajaran Informatika Fase E (kelas X) untuk SMK Negeri 1 Tanjungpandan.

> Dokumen ini adalah **sumber kebenaran proyek**. Agent AI wajib membacanya sebelum menulis kode,
> mengikuti isinya, dan mencatat setiap perubahan yang disetujui di bagian **13. Keputusan**.

---

## 1. Tujuan

- **Siswa** belajar Informatika sesuai CP terbaru, mengerjakan asesmen, dan memantau ketercapaian TP.
- **Guru** menyusun TP/ATP, materi, dan asesmen, lalu menilai dan menindaklanjuti (remedial/pengayaan).
- **Admin** mengelola data sekolah, memverifikasi akun guru, dan melihat laporan.

## 2. Stack & aturan teknis

- Laravel 13 (PHP ≥ 8.3), MySQL 8 (atau MariaDB setara), Composer, Node.js LTS + Vite.
- Frontend: Blade + Livewire + Tailwind CSS + Alpine.js. Autentikasi memakai starter kit resmi Laravel berbasis Livewire
  dengan **Flux UI versi gratis** (dilarang memakai komponen Flux Pro berbayar), diberi gaya sesuai palet bagian 3.
- Paket tambahan yang disetujui (wajib dicek kompatibilitasnya dengan Laravel 13 sebelum dipasang; jika belum kompatibel, usulkan alternatif):
  `maatwebsite/excel`, `barryvdh/laravel-dompdf`, `mews/purifier`, `blade-ui-kit/blade-lucide-icons`. Editor teks kaya: **Trix**.
- Antrean: `QUEUE_CONNECTION=database`; scheduler menjalankan `queue:work --stop-when-empty` dan dipicu cron `schedule:run`.
  Bisa diganti ke `sync` lewat `.env` bila hosting tidak mendukung cron.
- Hasil build Vite (`public/build`) tidak di-commit; dibangun dengan `npm run build` sebelum deploy lalu diunggah.
- Peran: kolom `role` (enum `admin`, `guru`, `siswa`) + middleware `role:` + Policy. Tanpa paket RBAC tambahan.
- Nama tabel, model, variabel, dan nama route dalam bahasa Inggris (konvensi Laravel). Teks antarmuka dalam bahasa Indonesia.
- `.env`: `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`, `DB_DATABASE=ruang_informatika`.
- Validasi lewat Form Request, otorisasi lewat Policy, eager loading untuk relasi, DB transaction untuk operasi berantai.
- Tes otomatis memakai Pest.
- Tidak ada rahasia di repositori; semua konfigurasi lewat `.env` dan `.env.example`.
- Harus berjalan di **Laragon** (Windows) untuk pengembangan dan di **server sekolah / shared hosting** dengan PHP 8.3 untuk produksi.

## 3. Desain antarmuka

Acuan visual: desain di kanvas Claude (tangkapan layar dilampirkan terpisah ke agent). Ikuti **gaya visualnya saja**;
beranda di tangkapan layar masih menampilkan 8 elemen versi lama — isi tetap 2 elemen (BK, LD) sesuai bagian 4.

- Komponen umum memakai Flux UI gratis. Komponen khusus (kartu elemen, simulasi, kuis) dibuat sebagai komponen Blade sendiri.

- **Warna**: latar `#F4F5F2`, teks utama `#15191F`, teks sekunder `#4A515C`, garis `#E2E4DF`,
  primer `#2347C4` (lembut `#E8EDFC`), aksen amber `#F2A93B` (lembut `#FDF1DC`),
  benar `#1D6B45`, salah `#B4441A`.
- **Huruf**: Plus Jakarta Sans (antarmuka), JetBrains Mono (kode dan label kecil).
- **Bentuk**: radius kartu 16–20px, tombol 12px.
- **Aksesibilitas**: target sentuh minimal 44px, kontras teks minimal 4.5:1, status tidak hanya dibedakan dengan warna (selalu ada teks/ikon).
- **Responsif**: semua halaman bisa dipakai di lebar 390px tanpa geser horizontal. Sidebar menumpuk di HP. Siswa di HP mendapat navigasi bawah: Beranda, Materi, Tugas, Profil.
- Ikon garis (Lucide atau Heroicons outline). Tanpa emoji.

## 4. Kurikulum

Acuan: **CP Informatika Fase E, Keputusan Kepala BSKAP No. 046/H/KR/2025** — dua elemen.

| Kode | Elemen |
|---|---|
| BK | Berpikir Komputasional |
| LD | Literasi Digital |

**Draf TP** (diisi seeder sebagai template; guru boleh mengubah):

| Kode | Judul | Cakupan |
|---|---|---|
| BK-1 | Struktur data & algoritma standar | Pencarian, pengurutan, list, stack, queue |
| BK-2 | Proses komputasi untuk data berkualitas | Mengumpulkan dan merapikan data, mandiri atau kelompok |
| BK-3 | Beragam solusi dengan algoritma | Membandingkan beberapa solusi untuk satu persoalan |
| BK-4 | Rancangan program dalam pseudocode | Menulis solusi dalam format dekat bahasa komputer |
| BK-5 | Sistem Komputer | Komponen, model Von Neumann, Input–Proses–Output, sistem operasi |
| LD-1 | Mesin pencari lanjutan | Kata kunci dan operator pencarian |
| LD-2 | Periksa fakta & membaca lateral | Memilah fakta dan hoaks |
| LD-3 | Aplikasi perkantoran lanjutan | Dokumen, lembar kerja, presentasi |
| LD-4 | Keamanan jaringan lokal & internet | Konfigurasi dasar kabel dan nirkabel |
| LD-5 | Produksi & kolaborasi konten digital | Membuat, menyebarkan, berpartisipasi |
| LD-6 | Etika & budaya digital | HAKI, profesi Informatika, digitalisasi budaya, saring konten negatif |
| LD-7 | Keamanan akun pribadi | Manajer kata sandi, autentikasi dua langkah, privasi akun |

**Subbab BK-5 Sistem Komputer**:
5.1 Perangkat keras & perangkat lunak · 5.2 Model komputer Von Neumann · 5.3 Siklus fetch–decode–execute ·
5.4 Peran sistem operasi · 5.5 Proses, memori & berkas.

**Hierarki konten**: Elemen → TP (urutannya = ATP) → Subbab → Aktivitas & Asesmen.

## 5. Peran & hak akses

| Modul | Admin | Guru | Siswa |
|---|---|---|---|
| Akun & pengguna | Kelola (verifikasi, nonaktifkan, reset sandi) | Lihat siswa di kelasnya | Profil & sandi sendiri |
| Pendaftaran mandiri | Kelola (buka/tutup, setujui guru) | Daftar sendiri, bagikan kode kelas | Daftar sendiri dengan kode kelas |
| Tahun ajaran, kelas & rombel | Kelola | Lihat kelas yang diampu | Lihat kelasnya |
| Plot guru mengajar | Kelola | Lihat jadwalnya | — |
| CP Fase E (master) | Kelola (kunci versi) | Lihat | Lihat ringkas |
| TP, ATP & KKTP | Lihat semua | Kelola untuk kelasnya | Lihat TP aktif |
| Materi & aktivitas | Lihat | Buat, ubah, terbitkan miliknya | Lihat yang terbit |
| Asesmen | Lihat | Buat & nilai untuk kelasnya | Kerjakan & kumpulkan |
| Nilai & umpan balik | Lihat rekap | Input & ubah untuk kelasnya | Lihat nilai sendiri |
| Absensi | Lihat rekap | Mencatat kehadiran kelasnya | Melihat riwayat sendiri |
| Buku Nilai | Lihat rekap | Mengatur bobot, kolom & isi nilai | Melihat nilai sendiri |
| Jadwal Informatika | Mengisi jadwal | Melihat semua jadwal | Melihat jadwal kelasnya |
| Diskusi kelas | Moderasi semua | Jawab & moderasi kelasnya | Bertanya & menanggapi |
| Laporan & ekspor rapor | Semua kelas | Kelas yang diampu | — |
| Pengaturan sekolah | Kelola | — | — |

## 6. Pendaftaran & akun

**Siswa**
- Form: nama lengkap, NISN (10 digit, unik), email (unik), kode kelas, kata sandi (min. 8 karakter + konfirmasi).
- Kode kelas ada dan aktif → akun `status=active` dan otomatis masuk kelas (`classroom_student`).
- Data tidak valid → pesan error per kolom, isian lain tetap tersimpan di form.
- Satu siswa hanya di **satu kelas per tahun ajaran**. Ganti semester tetap di kelas yang sama.
- Di tahun ajaran baru, siswa memasukkan kode kelas baru dari halaman profil.
- Pindah kelas dalam tahun ajaran yang sama hanya oleh admin.

**Guru**
- Form: nama & gelar, NIP/NUPTK (opsional), email, mata pelajaran, kata sandi.
- Akun `status=pending`; semua admin mendapat notifikasi (database + email).
- Admin **menyetujui** → `active` + email ke guru. Admin **menolak** → `rejected` + alasan + email.
- Pengaturan `teacher_approval_required` (default `true`). Jika `false`, guru langsung aktif.

**Umum**
- Pengaturan `registration_open` untuk membuka/menutup pendaftaran.
- Login: siswa memakai **email atau NISN**; guru dan admin memakai email. Reset kata sandi selalu lewat email.
- URL utama `/masuk`, `/daftar`, `/lupa-sandi`; URL bawaan starter kit (`/login`, `/register`, `/forgot-password`) di-redirect ke sana.
- Akun `pending`, `rejected`, atau `inactive` tidak bisa login; tampilkan pesan yang menjelaskan statusnya.
- Rate limit pada form daftar dan login. Fitur lupa kata sandi lewat email.
- Admin pertama dibuat seeder dari `.env`: `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
- **Kode kelas**: format `XRPL1-7K2Q` (singkatan kelas + 4 karakter acak tanpa karakter mirip seperti O/0 dan I/1). Bisa dibuat ulang atau dinonaktifkan oleh guru pengampu atau admin.

## 7. Model data (minimal)

| Tabel | Kolom penting |
|---|---|
| users | name, email, password, role, status (pending/active/rejected/inactive), nisn, nip_nuptk, subject, approved_at, approved_by, rejection_reason |
| settings | key, value |
| academic_years | name (2026/2027), semester (ganjil/genap), is_active |
| classrooms | academic_year_id, name (X RPL 1), grade, major, join_code (unik), join_code_active, weekly_lesson_target (default 5, diatur guru) |
| classroom_student | classroom_id, student_id, academic_year_id, joined_at — unik (student_id, academic_year_id) |
| teaching_assignments | teacher_id, classroom_id, subject |
| elements | code (BK/LD), name, description, phase (E), cp_reference |
| learning_objectives | element_id, code (BK-5), title, description, sort_order (ATP), kktp, is_template, created_by — template: `is_template=true`; guru menyalin template menjadi TP miliknya (`is_template=false`, `created_by`=guru) yang berlaku untuk semua kelas yang ia ampu |
| lessons | learning_objective_id, code (5.2), title, summary, content, video_url, estimated_minutes, sort_order, status (draft/published), created_by |
| lesson_attachments | lesson_id, label, file_path, type |
| lesson_classroom | lesson_id, classroom_id, available_from |
| assessments | learning_objective_id (nullable), element_id (nullable), lesson_id (nullable), type (diagnostik/formatif/sumatif/tugas), title, instructions, kktp, max_score, max_attempts (nullable = tanpa batas), time_limit_minutes (nullable = tanpa batas), status, created_by — tepat satu dari `element_id` (sumatif) atau `learning_objective_id` (jenis lain) terisi |
| assessment_classroom | assessment_id, classroom_id, opens_at, due_at |
| questions | assessment_id, type (pilihan_ganda/isian/esai), body, options (json), answer_key, explanation, points, sort_order |
| submissions | assessment_id, student_id, attempt, started_at, submitted_at, file_path, score, status (dikerjakan/dikumpulkan/dinilai), feedback, graded_by |
| submission_answers | submission_id, question_id, answer, is_correct, score |
| attendance_sessions | (usulan, belum dibangun) classroom_id, teacher_id, academic_year_id, meeting_date, meeting_no, topic (nullable), status (open/closed), opened_at, closed_at — unik (classroom_id, meeting_date, meeting_no) |
| attendance_records | (usulan, belum dibangun) session_id, student_id, status (Hadir/Izin/Sakit/Alpa), note (nullable), marked_by, marked_at — unik (session_id, student_id) |
| grade_columns | (usulan, belum dibangun) classroom_id, academic_year_id, group (uh/tugas/mid/uas), seq, label, max_score |
| grade_entries | (usulan, belum dibangun) column_id, student_id, score (nullable = belum ada nilai, bukan 0) |
| grade_weights | (usulan, belum dibangun) classroom_id, academic_year_id, weight_uh, weight_tugas, weight_uas_mid, weight_absensi (total 100) |
| bell_periods | (usulan, belum dibangun) academic_year_id, day_of_week, period_no, starts_at, ends_at, type (pelajaran/istirahat/kegiatan), label — jam berbeda per hari |
| schedule_entries | (usulan, belum dibangun) teaching_assignment_id, day_of_week, start_period_no, end_period_no, room (nullable), note (nullable) — tidak boleh melintasi istirahat |
| lesson_progress | student_id, lesson_id, completed_at |
| objective_mastery | student_id, learning_objective_id, classroom_id, best_score, status (belum/remedial/tercapai/pengayaan) |
| discussions | lesson_id, classroom_id, user_id, parent_id, body, is_hidden |
| notes | student_id, lesson_id, body |
| announcements | classroom_id (nullable = seluruh sekolah), user_id, title, body, published_at |
| notifications | tabel notifikasi bawaan Laravel |

## 8. Fitur per peran

**Admin**: dashboard ringkas · verifikasi akun guru · kelola pengguna · tahun ajaran & semester · kelas & kode kelas · plot mengajar · kurikulum master (elemen & TP template) · laporan & ekspor · pengaturan sekolah (nama, logo, `registration_open`, `teacher_approval_required`, `default_kktp`).

**Guru**: dashboard (kelas diampu, tugas perlu dinilai, progres) · kode kelas · susun TP & ATP · materi/subbab (teks kaya, video, lampiran, terbitkan ke kelas) · asesmen & bank soal · penilaian & umpan balik · peta ketercapaian TP · remedial/pengayaan · rapor.

**Pengumuman**: guru membuat untuk kelasnya, admin untuk seluruh sekolah (`classroom_id` null).

**Target mingguan & hari beruntun**: progres = jumlah subbab selesai Senin–Minggu (Asia/Jakarta) dibanding
`classrooms.weekly_lesson_target`. Hari beruntun dihitung dari hari yang memiliki aktivitas belajar (tanpa tabel baru).

**Siswa**: beranda (lanjutkan belajar, target mingguan, hari beruntun, progres 2 elemen, tugas mendatang, pengumuman) · belajar per elemen → TP → subbab · halaman materi (sidebar subbab, tab Materi/Diskusi/Catatanku, daftar isi, materi pendukung, tandai selesai) · kerjakan kuis & unggah tugas · nilai & progres TP · diskusi · catatan pribadi.

**Simulasi Von Neumann** (subbab 5.2), komponen Blade + Alpine.js yang bisa dipakai ulang:
komponen IN, CU, ALU, RAM, OUT; 6 langkah menghitung 12 + 30 —
(1) Input dari keyboard → (2) data & instruksi disimpan di memori → (3) CU mengambil & menerjemahkan instruksi →
(4) ALU mengeksekusi 12 + 30 = 42 → (5) hasil disimpan ke memori → (6) Output: layar menampilkan 42.
Komponen aktif menyala; tombol Sebelumnya / Langkah berikutnya; bisa diulang.

## 9. Aturan penilaian & ketercapaian

- Asesmen diagnostik, formatif, dan tugas terhubung ke satu TP; asesmen sumatif terhubung ke satu elemen. Setiap asesmen punya KKTP (default dari pengaturan: 75, ubah sesuai keputusan sekolah).
- Pilihan ganda dinilai otomatis; isian, esai, dan tugas dinilai guru dengan rubrik.
- Nilai ≥ KKTP → TP `tercapai`. Nilai < KKTP → `remedial`: siswa mendapat umpan balik dan boleh mencoba lagi (dalam batas percobaan); nilai terbaik yang dipakai.
- Percobaan & waktu diatur per asesmen (`max_attempts`, `time_limit_minutes`).
  Default formatif: tanpa batas percobaan dan waktu, sampai `due_at`.
  Default sumatif: 1 percobaan; percobaan remedial dibuka guru; batas waktu diisi guru.
- Asesmen sumatif elemen terbuka setelah semua TP di elemen itu tercapai (guru bisa membukanya manual).
- Status `pengayaan` diberikan **manual oleh guru**. Sistem hanya memberi saran: siswa dengan nilai ≥ 75 tampil sebagai kandidat pengayaan.
- Rapor: nilai per elemen + deskripsi capaian otomatis (TP paling dikuasai dan TP yang perlu bimbingan), bisa diedit guru, ekspor Excel dan PDF.

## 10. Halaman kunci

| Area | URL |
|---|---|
| Publik | `/`, `/masuk`, `/daftar`, `/lupa-sandi` |
| Admin | `/admin`, `/admin/verifikasi`, `/admin/pengguna`, `/admin/tahun-ajaran`, `/admin/kelas`, `/admin/plot-mengajar` (termasuk jadwal), `/admin/kurikulum`, `/admin/laporan`, `/admin/pengaturan` |
| Guru | `/guru`, `/guru/kelas/{kelas}`, `/guru/tp`, `/guru/materi`, `/guru/asesmen`, `/guru/penilaian`, `/guru/buku-nilai`, `/guru/absensi`, `/guru/jadwal`, `/guru/progres/{kelas}`, `/guru/rapor/{kelas}` |
| Siswa | `/beranda` (termasuk jadwal), `/belajar/{elemen}`, `/belajar/{elemen}/{tp}/{subbab}`, `/tugas`, `/nilai` (tab: Nilai, Buku Nilai, Kehadiran), `/profil` |

## 11. Data contoh (seeder)

- Admin dari `.env`; tahun ajaran 2026/2027 semester ganjil; kelas contoh X RPL 1 dengan kode kelas.
- Elemen BK & LD, 12 draf TP, 5 subbab BK-5. Subbab 5.2 berisi lengkap: tujuan pembelajaran, komponen utama (CPU, RAM, I/O, bus), catatan "RAM bersifat volatil", simulasi, dan 1 kuis formatif:
  "Pada simulasi tadi, komponen mana yang menghitung 12 + 30?" — A. Unit kendali (CU) · **B. ALU** · C. Memori utama (RAM) · D. Perangkat output.
- Akun guru & siswa contoh **hanya** saat `APP_ENV=local`.

## 12. Definisi selesai (setiap tahap)

- `php artisan migrate:fresh --seed` berjalan tanpa error.
- Semua tes Pest lulus.
- Halaman baru bisa dipakai di lebar 390px.
- README dan bagian 13 dokumen ini diperbarui.

## 13. Keputusan

- 2026-10-03 — Stack Laravel + MySQL.
- 2026-10-03 — Kurikulum mengikuti CP Fase E dengan 2 elemen (BK, LD); Sistem Komputer menjadi TP BK-5.
- 2026-10-03 — Guru dan siswa mendaftar sendiri. Siswa aktif otomatis dengan kode kelas; guru aktif setelah disetujui admin (bisa dimatikan lewat pengaturan).
- 2026-10-04 — Proyek dikerjakan dalam 6 tahap (bagian 14), satu tahap per prompt.
- 2026-10-04 — Tangkapan layar kanvas hanya acuan gaya visual; isi beranda tetap 2 elemen.
- 2026-10-04 — Starter kit Livewire + Flux UI gratis (tanpa Flux Pro); komponen khusus (kartu elemen, simulasi, kuis) sebagai komponen Blade sendiri.
- 2026-10-04 — URL auth `/masuk`, `/daftar`, `/lupa-sandi`; URL bawaan starter kit di-redirect.
- 2026-10-04 — Paket: maatwebsite/excel, barryvdh/laravel-dompdf, mews/purifier, blade-ui-kit/blade-lucide-icons, editor Trix — dicek kompatibilitas Laravel 13 sebelum dipasang.
- 2026-10-04 — Antrean database + cron `schedule:run` (scheduler menjalankan `queue:work --stop-when-empty`); bisa `sync` lewat `.env`.
- 2026-10-04 — `public/build` tidak di-commit; dibangun sebelum deploy dan diunggah.
- 2026-10-04 — Siswa login dengan email atau NISN; guru/admin dengan email atau NIP/NUPTK; login menggunakan validasi Fortify kustom pada field `login_id`. Akun yang tidak berstatus `active` tidak bisa login.
- 2026-10-04 — Verifikasi pendaftaran hanya untuk Guru (status awal `pending` lalu diapprove/ditolak oleh admin). Siswa langsung berstatus `active` saat mendaftar dengan kode kelas yang valid.
- 2026-10-04 — TP milik guru = salinan template (`is_template=false`, `created_by`), berlaku untuk semua kelas yang diampu; belum ada pivot TP–kelas.
- 2026-10-04 — `assessments.element_id` nullable: sumatif → elemen, jenis lain → TP; tepat satu terisi.
- 2026-10-04 — Satu siswa satu kelas per tahun ajaran (`classroom_student.academic_year_id`, unik bersama `student_id`); kode kelas baru lewat profil di tahun ajaran baru; pindah kelas dalam tahun yang sama hanya oleh admin.
- 2026-10-04 — `pengayaan` ditetapkan manual oleh guru; sistem menyarankan kandidat dengan nilai ≥ 75.
- 2026-10-04 — `assessments.max_attempts` dan `time_limit_minutes` (nullable = tanpa batas); default formatif tanpa batas, sumatif 1 percobaan + remedial dibuka guru.
- 2026-10-04 — Tabel `announcements` (classroom_id nullable = seluruh sekolah).
- 2026-10-04 — `classrooms.weekly_lesson_target` (default 5); progres mingguan Senin–Minggu Asia/Jakarta; hari beruntun dihitung dari aktivitas belajar tanpa tabel baru.
- 2026-10-05 — Livewire komponen kuis siswa tidak boleh menyimpan `answer_key` di properti publik untuk menghindari kebocoran melalui HTML atau payload Livewire. Kunci diuji pada level Model/JSON dan harus ada pengujian khusus saat komponen siswa (Tahap 4) dibangun.
- 2026-10-07 — Rilis dibagi: VERSI 1 = stabilisasi (header, menu akun/profil, tombol guru), materi-kuis-tugas-nilai ujung ke ujung, Absensi, Buku Nilai, Jadwal, Pengumuman, hardening dan deploy. VERSI 1.1 = rapor PDF/Excel, laporan admin lanjutan, peta ketercapaian lanjutan, diskusi, catatan pribadi, tautan otomatis nilai kuis ke Buku Nilai.
- 2026-10-07 — Absensi: guru mencatat (siswa tidak absen mandiri); status Hadir/Izin/Sakit/Alpa; persentase kehadiran = hanya Hadir yang dihitung hadir; tanpa notifikasi, tanpa lampiran; label teks "Perlu perhatian" di bawah 75%; siswa hanya melihat riwayat sendiri (pertemuan yang sudah ditutup).
- 2026-10-07 — Buku Nilai guru: kolom bawaan 3 UH, 5 Tugas, UAS, MID (jumlah UH dan Tugas dapat diubah guru 1-10); rata-rata UH, rata-rata Tugas, rata-rata UAS&MID, Total Absensi (persen, dari modul Absensi), Rata-rata Seluruh = rata-rata biasa empat komponen dengan bobot dapat diatur guru per kelas/semester (total 100%); sel kosong = belum ada nilai (bukan 0); nilai tampil di akun siswa hanya setelah guru memilih "Tampilkan ke siswa"; nilai angka terpisah dari status ketercapaian TP; di bawah KKTP diberi tulisan "Di bawah KKTP".
- 2026-10-07 — Materi: siswa hanya melihat materi terbit di kelasnya; materi yang belum dibuka tampil terkunci dengan tanggal buka; draf dan materi kelas lain tidak tampil dan URL langsungnya ditolak.
- 2026-10-07 — Jadwal Informatika: hanya mapel Informatika; ADMIN mengisi (/admin/plot-mengajar); GURU melihat semua jadwal; SISWA hanya jadwal kelasnya sendiri, tampil di dashboard (/beranda); kode guru tidak disimpan; jam pelajaran dapat berbeda per hari (Jumat beda); kelas: 10 PPLG, 10 AKL 1, 10 AKL 2, 10 PM 1.
- 2026-10-07 — Menu akun: lingkaran inisial di kanan atas menjadi menu akun (profil, ubah sandi, keluar) untuk semua peran; halaman /profil; NISN, kelas, peran, status hanya baca; di luar SPEC dan tidak dibuat: foto profil, tema gelap, notifikasi email.
- 2026-10-07 — Aturan kerja: rencana dulu lalu "setuju" pemilik; satu bagian per giliran; dilarang membuat kode untuk fitur yang belum disetujui; dilarang melemahkan tes; laporan wajib memuat keluaran mentah "php artisan test" dan git status.

## 14. Tahapan pengerjaan

Setiap tahap dimulai hanya setelah prompt tahap itu dikirim. Jangan mengerjakan tahap berikutnya lebih dulu.

1. Fondasi, akun, dan pendaftaran mandiri.
2. Modul admin.
3. Modul guru: TP/ATP, materi, asesmen, konten BK-5.
4. Modul siswa: beranda, materi, simulasi Von Neumann, kuis, tugas, diskusi, catatan.
5. Penilaian, ketercapaian TP, rapor, laporan.
6. Audit keamanan, optimasi, README, panduan deploy.
