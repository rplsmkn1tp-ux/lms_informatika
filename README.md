# LMS Informatika

LMS (Learning Management System) khusus untuk mata pelajaran Informatika kelas X.

## Fitur Utama
- **Admin**: Verifikasi guru, kelola pengguna, tahun ajaran, kelas, plot mengajar, dan kurikulum master.
- **Guru**: Kelola TP/ATP (Tujuan Pembelajaran), materi pelajaran, asesmen, penilaian, dan rapor.
- **Siswa**: Belajar melalui materi interaktif, simulasi Von Neumann, kuis, dan diskusi.

## Panduan Instalasi di Laragon (Local Development)
1. Buka **Laragon** dan pastikan **Apache** dan **MySQL** sudah berjalan.
2. Clone repositori ini di dalam folder `C:\laragon\www\` (atau sesuai konfigurasi Laragon Anda).
3. Buka Terminal di Laragon (Tombol **Terminal** di aplikasi Laragon).
4. Jalankan perintah instalasi dependency:
   ```bash
   composer install
   npm install
   ```
5. Salin konfigurasi `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```
6. Buat database baru di MySQL (misalnya dengan HeidiSQL yang bawaan Laragon) dengan nama `lms_informatika` atau sesuai keinginan.
7. Sesuaikan file `.env` dengan kredensial database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lms_informatika
   DB_USERNAME=root
   DB_PASSWORD=
   ```
8. Generate *application key*:
   ```bash
   php artisan key:generate
   ```
9. Jalankan migrasi dan seeder database:
   ```bash
   php artisan migrate:fresh --seed
   ```
10. Buat *symbolic link* untuk storage (penting agar file unggahan/gambar bisa diakses):
    ```bash
    php artisan storage:link
    ```
11. Build aset frontend (Pilih salah satu):
    ```bash
    npm run dev   # (Untuk mode development)
    npm run build # (Untuk produksi)
    ```
12. Anda bisa mengakses aplikasi melalui `http://lms-informatika.test` di browser Anda (tergantung konfigurasi virtual host Laragon).

## Akun Contoh (Dari Seeder)
Jika Anda menjalankan perintah `php artisan migrate:fresh --seed` (pada environment `local`), berikut adalah akun yang bisa digunakan:
- **Admin Utama**:
  - Email: `admin@sekolah.id` (Sesuai `ADMIN_EMAIL` di `.env`, defaultnya `admin@sekolah.id` jika tidak diatur)
  - Password: `password` (Sesuai `ADMIN_PASSWORD` di `.env`, defaultnya `password`)
- **Guru**:
  - Email: `guru@example.com`
  - Password: `password`
- **Siswa**:
  - Email: `siswa@example.com`
  - Password: `password`

## Panduan Deployment (Server Sekolah / Shared Hosting)
Untuk mengunggah aplikasi ke server sekolah atau shared hosting:

### Prasyarat:
- **PHP**: Minimal versi 8.3
- **Database**: MySQL 8.0+ atau MariaDB
- **Web Server**: Apache / Nginx
- Composer dan Node.js/NPM (jika tidak ada akses SSH untuk npm, Anda bisa build aset `npm run build` di lokal dan unggah folder `public/build/`).

### Langkah-langkah Deployment:
1. **Unggah Kode**: Upload seluruh file proyek ke folder web root server Anda (misal `public_html/` atau `/var/www/html/`). Pastikan **hanya** folder `public/` yang diekspos secara langsung (jika memungkinkan atur DocumentRoot).
2. **Setup .env**: Sesuaikan file `.env` dengan kredensial server produksi:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://lms.sekolahanda.sch.id
   ```
3. **Optimasi Laravel**:
   Jika Anda memiliki akses SSH, jalankan:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Storage Link**:
   Jalankan `php artisan storage:link` di server untuk menghubungkan `storage/app/public` ke `public/storage`.
   *Jika di cPanel tanpa terminal, Anda bisa membuat route sementara untuk memanggil `Artisan::call('storage:link')`.*
5. **Keamanan (HTTPS)**:
   Sangat disarankan (WAJIB) untuk menginstal sertifikat SSL (misal dari Let's Encrypt) agar semua transaksi data (termasuk login) terlindungi enkripsi HTTPS.
6. **Background Jobs / Queue (Opsional)**:
   Untuk notifikasi email berjalan di latar belakang tanpa memberatkan user:
   - Atur `.env`: `QUEUE_CONNECTION=database`
   - Setup Cron Job di cPanel/server:
     ```bash
     * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
     ```

## Tech Stack
- Laravel 11
- Livewire 3
- Flux UI
- TailwindCSS
- Alpine.js
