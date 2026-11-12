# Rencana Tahap 4 Bagian C & D

## Bagian C: Kuis dan Tugas

1. **Persiapan Model & Relasi**
   - Pastikan model `Assessment`, `Question`, `Submission`, dan `SubmissionAnswer` sudah memiliki relasi yang benar.
   - Tambahkan *scope* pada `Assessment` agar siswa hanya bisa melihat asesmen yang sudah `published` dan di-_assign_ ke kelasnya via `assessment_classroom` dengan kondisi waktu yang tepat (opens_at <= now, due_at belum terlewat atau tidak bisa diakses).

2. **Daftar Tugas (`/tugas`)**
   - Buat komponen `Siswa\Tugas\DaftarTugas` untuk menampilkan daftar tugas/kuis yang di-plot ke kelas siswa (aktif/mendatang).

3. **Mengerjakan Kuis/Tugas (`/tugas/{assessment}`)**
   - Buat komponen `Siswa\Tugas\KerjakanTugas` (Livewire).
   - Validasi:
     - Apakah sudah melewati `opens_at`?
     - Apakah masih dalam `due_at`?
     - Apakah sisa *attempt* masih ada? (Default percobaan: formatif tanpa batas, sumatif 1 kali).
   - Simpan jawaban siswa di properti Livewire, lalu otomatis disimpan (`wire:model.live.debounce`) ke `submission_answers`.
   - **PENTING (Keamanan Jawaban Kuis)**:
     - Tidak boleh ada `answer_key` pada `Question` yang dikirim ke browser (properti publik Livewire). Uji via Model/JSON/Level komponen.
     - Tambahkan tes (Pest) yang memverifikasi bahwa jawaban benar tidak pernah ter-_render_ di HTML/JSON respon Livewire saat siswa mengerjakan soal.
   - **Evaluasi & Pembahasan**:
     - Jika jenis `formatif`: setelah dijawab langsung dinilai benar/salah. Pembahasan muncul.
     - Jika jenis `sumatif`: skor tidak dimunculkan per soal. Skor dihitung saat dikumpulkan. Pembahasan hanya muncul jika `due_at` sudah lewat.
   - **Tugas Unggah File**:
     - Fitur unggah dokumen siswa (pdf/docx/jpg/png/zip, maks 10 MB).
     - File disimpan secara *private* (tidak bisa diakses publik secara bebas).

## Bagian D: Halaman Nilai & Progres

1. **Halaman Nilai (`/nilai`)**
   - Komponen `Siswa\Nilai\DaftarNilai`.
   - Menampilkan daftar TP beserta nilai terbaik (`best_score`), mengacu pada KKTP TP (jika kosong, gunakan default_kktp).
   - Tampilkan status pencapaian (Belum, Remedial, Tercapai) berdasarkan KKTP tersebut.

2. **Integrasi ke Tahap 5**
   - Halaman dashboard guru & admin (Tahap 5) akan dibuat setelah Bagian C dan D selesai, karena membutuhkan data submission dan progress dari siswa.

## Langkah Pengerjaan
1. Rencana ini (C dan D).
2. Tulis implementasi komponen Livewire untuk Bagian C.
3. Tulis komponen Livewire untuk Bagian D.
4. Buat tes Pest terkait keamanan kuis dan kelengkapan otorisasi.
5. Jalankan tes dan `migrate:fresh --seed`.
