# Rencana Tahap 4 Bagian A (Modul Siswa: Navigasi, Beranda, & Profil)

## 1. Routing & Otorisasi
- Membuat rute `GET /beranda` dan `GET /profil` yang dilindungi *middleware* `auth` dan `role:siswa`.
- Mencegah Admin dan Guru mengakses rute-rute siswa ini (akan diarahkan atau dikembalikan status `403 Forbidden`).

## 2. Komponen Beranda (`App\Livewire\Siswa\Beranda`)
Tampilan disesuaikan dengan `docs/desain/beranda.html`, yang memuat:
- **Lanjutkan Belajar:** Mengambil subbab terakhir yang dibuka namun belum selesai dari `lesson_progress`. Jika belum ada, tampilkan saran subbab pertama di ATP.
- **Target Mingguan & Hari Beruntun:**
  - Mengkalkulasi jumlah subbab diselesaikan mulai hari Senin s.d. Minggu minggu ini berdasarkan zona waktu Asia/Jakarta. Membandingkannya dengan `classrooms.weekly_lesson_target`.
  - Menghitung hari aktif belajar secara beruntun.
- **Daftar Elemen & TP:**
  - Menampilkan dua elemen utama (BK dan LD).
  - Melakukan *query* TP yang menjadi milik guru pengampu di kelas siswa tersebut.
  - Untuk saat ini, status dirender sebagai "Belum mulai" atau "Berlangsung" dari `lesson_progress` (ketercapaian utuh dari `objective_mastery` akan ditambahkan pada Tahap 5).
- **Tugas Mendatang:**
  - Mengambil asesmen yang sudah terbit ke kelas siswa, dengan `due_at` terdekat di masa depan, dan belum diselesaikan (`submissions`).
- **Pengumuman:** Menampilkan `announcements` khusus kelas tersebut maupun pengumuman tingkat sekolah (`classroom_id` = null).

## 3. Komponen Profil (`App\Livewire\Siswa\Profil`)
- Form pengaturan profil dasar: Ubah nama, email, dan kata sandi.
- **Ganti Kelas (Tahun Ajaran Baru):** Form untuk memasukkan kode kelas baru. Validasi mengecek ketersediaan kode dan menambahkannya ke tabel `classroom_student`. Aturan unik `student_id` + `academic_year_id` akan diberlakukan sehingga siswa hanya terdaftar di satu kelas per tahun ajaran.

## 4. Pengujian (Pest)
Menambahkan kelas uji baru yang memastikan:
- Siswa tidak melihat materi/asesmen milik kelas lain atau yang jadwal rilisnya (`available_from`/`opens_at`) belum tiba.
- Admin dan Guru mendapatkan `403 Forbidden` saat mencoba membuka `/beranda` siswa.
- Profil dapat diubah, dan fitur "gabung kelas baru" mematuhi batasan tahun ajaran.
