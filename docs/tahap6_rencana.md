# TAHAP 6 — Pemeriksaan Akhir dan Panduan Deploy

## 1. Audit Keamanan
- **Otorisasi Setiap Route:** Memastikan *middleware* `role` terpasang di semua rute (`admin`, `guru`, `siswa`) dan mengonfirmasi bahwa komponen Livewire menggunakan pengecekan peran atau hanya digunakan di rute yang tepat.
- **Mass Assignment:** Memeriksa seluruh Model untuk menghindari kerentanan *mass assignment*. Hanya kolom yang aman yang dimasukkan ke `$fillable`.
- **Validasi Unggahan File:** Memastikan bahwa semua rute dan komponen Livewire yang menangani unggahan file (seperti lampiran, jawaban tugas) memvalidasi tipe *mime* dan ukuran file, serta disimpan secara privat (atau *public* hanya untuk file yang tidak sensitif).
- **Rate Limit:** Menerapkan *rate limiting* yang lebih ketat pada *endpoint* pendaftaran, login, atau pengiriman unggahan untuk menghindari serangan brute-force atau spam.
- **XSS di Konten & Diskusi:** Mengamankan *render* tampilan. Konten materi dari editor *rich-text* harus di-*sanitize* dengan HTML Purifier. Komentar diskusi dan catatan wajib ditampilkan dengan fungsi bawaan Blade (`{{ }}`) untuk mencegah serangan *Cross-Site Scripting* (XSS).

## 2. Optimasi
- **Eager Loading:** Memeriksa seluruh *queries* pada Dashboard, halaman daftar pelajaran, tugas, dan nilai agar menggunakan `with()` (mencegah masalah N+1 query).
- **Index Database:** Meninjau seluruh *migrations* untuk menjamin indeks (seperti foreign keys atau kolom pencarian) sudah ditambahkan di tabel besar seperti `lesson_progress`, `discussions`, `submissions`, dll.
- **Cache Pengaturan:** Jika ada pengaturan sistem (contoh `Setting` model), maka datanya akan di-cache selama setidaknya 24 jam untuk mengurangi beban database.

## 3. Evaluasi Testing
- Menjalankan kembali seluruh skenario pengujian dengan `php artisan test` (memastikan semuanya berstatus lulus/passed).
- Meninjau batas perlindungan *test coverage* pada fitur inti seperti pendaftaran, penyelesaian materi, hingga pengiriman tugas/kuis.
- Menambahkan *test* yang tertinggal jika *coverage* dinilai kurang.

## 4. Pembaruan Dokumentasi (README)
- **Cara Instal di Laragon:** Memberikan panduan cepat instalasi dari *git clone* sampai menjalankan *server* di aplikasi *local*.
- **Akun Default:** Menyertakan informasi *login* bawaan hasil `db:seed` untuk Admin, Guru, dan Siswa sehingga pengguna awal tidak kebingungan.
- **Perintah Penting:** Merangkum daftar perintah artisan yang sering dipakai dalam *maintenance* aplikasi (`migrate:fresh --seed`, `storage:link`, dll).

## 5. Panduan Deploy
- **Infrastruktur Pendukung:**
  - Kebutuhan Server (PHP >= 8.3/8.4, MySQL, Composer).
  - Konfigurasi `storage:link`.
  - Rekomendasi penggunaan HTTPS via Let's Encrypt (SSL).
  - Pembuatan script *cron job* untuk antrean pengiriman notifikasi/email jika diterapkan.
- Akan dirangkum ke dalam satu file dokumentasi terpisah (`docs/DEPLOYMENT.md`).
