# Rencana Tahap 3 Bagian C (Modul Guru: Asesmen & Bank Soal)

## 1. Pembaruan Skema & Model
- **Tabel `assessments`:**
  - Tambah tipe: `diagnostic`, `formative`, `summative`, `assignment`.
  - Relasi: `summative` terhubung ke `element_id`. Tipe lain terhubung ke `learning_objective_id` (TP). Tepat satu relasi wajib terisi (berdasarkan validasi/constraint). Kolom `lesson_id` opsional.
  - Tambah kolom `rubric` (JSON) untuk kriteria dan skor maksimal esai/tugas.
  - Kolom `max_attempts` (default: formatif tak terbatas, sumatif 1 kali) dan `time_limit_minutes`.
  - Logika KKTP: Memakai nilai dari asesmen, jika kosong turun ke TP, jika kosong turun ke default sistem.
- **Tabel `questions`:**
  - Tipe soal: Pilihan ganda, isian singkat, esai.
  - Menyimpan opsi jawaban, kunci jawaban, pembahasan, poin (skor), dan urutan (`sort_order`).
  - (Untuk tugas, tidak ada pertanyaan khusus, melainkan instruksi pada level asesmen dan siswa mengunggah berkas).
- **Tabel Baru `assessment_attempt_grants`:**
  - Untuk memfasilitasi pemberian percobaan ulang (remedial). Antarmuka akan dikerjakan pada Tahap 5, namun tabelnya disiapkan sekarang.
- **Tabel Pivot `assessment_classroom`:**
  - Menyimpan jadwal terbit `opens_at` dan `due_at`. Hanya dapat di-set untuk kelas yang diampu oleh guru tersebut.

## 2. Manajemen Asesmen & Soal
- **Pembuatan Asesmen:**
  - Form Livewire untuk membuat/mengubah asesmen. Validasi memastikan `element_id` hanya digunakan untuk sumatif.
- **Manajemen Soal (Pengganti Bank Soal Terpisah):**
  - Mengelola daftar soal secara langsung di dalam setiap asesmen.
  - **Fitur Duplikasi:** Guru dapat menggandakan keseluruhan asesmen.
  - **Fitur Salin Soal:** Guru dapat menyalin pertanyaan dari asesmen lain **yang merupakan miliknya**.
- **Kunci & Edit Mode:**
  - Apabila sudah ada jawaban dari siswa yang masuk (*submissions*), sistem mengunci pengeditan soal dan kunci jawaban. Guru hanya dapat menduplikat asesmen jika ingin memperbaiki struktur.

## 3. Keamanan Data (Kunci Jawaban)
- Di level Eloquent atau API resource, memastikan `answer_key` dan `explanation` disembunyikan (di-*hidden*) secara ketat ketika data asesmen dirender atau dikirim ke *frontend* modul siswa.

## 4. Seeder Konten (Seeder Lokal)
- Menambahkan kuis formatif untuk Subbab 5.2 milik guru contoh.
- Sesuai dengan spesifikasi (SPEC bagian 11), soal akan berbunyi: "Komponen mana yang bertugas menghitung 12 + 30?" dengan kunci jawaban "ALU".
- Asesmen ini akan diterbitkan langsung ke kelas contoh (X RPL 1).

## 5. Pengujian (Pest)
Menambahkan dan memastikan tes berikut:
- Guru hanya bisa mengelola dan menerbitkan asesmen miliknya ke kelas yang diampu.
- Aturan validasi `element_id` vs `learning_objective_id` berdasarkan tipe asesmen ditaati.
- Kunci jawaban tidak pernah bocor (tidak ter-*expose*) di respons untuk profil non-guru.
- Soal terkunci (tidak bisa di-update/delete) setelah ada setidaknya satu *submission*.
- Kuis contoh (subbab 5.2) berhasil dibuat dan dipetakan dengan benar melalui seeder.
