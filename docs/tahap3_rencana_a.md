# Rencana Tahap 3 Bagian A (Modul Guru: TP/ATP & Halaman Kelas)

## 1. Routing & Middleware
- Menambahkan route group `/guru` dengan middleware `auth` dan kustom middleware/Role check (`role:guru`).
- Mendefinisikan route:
  - `GET /guru/tp` → `App\Livewire\Guru\TpManagement`
  - `GET /guru/kelas/{kelas}` → `App\Livewire\Guru\ClassroomDetail`

## 2. Halaman Kelola TP & ATP (`TpManagement`)
- **Tampilan**: Tabel/Daftar TP yang dimiliki oleh guru yang login (`created_by = auth()->id()`).
- **Fitur Tambah Sendiri**: Guru dapat membuat TP baru secara manual.
- **Fitur Salin Template**: Modal/Pilihan untuk melihat daftar TP Template (milik admin, `is_template = true`) dan menyalinnya ke milik guru (`is_template = false`, `created_by = auth()->id()`).
- **Fitur Ubah & Hapus**: Guru bisa mengubah judul, deskripsi, KKTP, dan urutan (ATP) dari TP miliknya.
- **Validasi Akses**: Memastikan *query* selalu difilter berdasarkan `created_by = auth()->id()`, dan operasi *update/delete* akan mengecek otorisasi (hanya pemilik TP yang boleh).

## 3. Halaman Detail/Kode Kelas Guru (`ClassroomDetail`)
- **Tampilan**: Menampilkan nama kelas dengan **Kode Kelas** yang dicetak tebal/besar.
- **Fitur Salin**: Tombol *copy to clipboard* untuk memudahkan guru membagikan kode.
- **Fitur Kelola Kode**:
  - Tombol **Regenerate** (buat ulang kode kelas).
  - Tombol **Aktif/Nonaktifkan** pendaftaran kelas tersebut.
- **Validasi Akses**: Di-mount (inisialisasi komponen), kita akan mengecek apakah `auth()->user()` terdaftar mengajar di `$kelas->id` melalui tabel `teaching_assignments`. Jika tidak, `abort(403)`.

## 4. Pengujian (Pest)
Membuat berkas tes `tests/Feature/Guru/GuruModuleTest.php` untuk memastikan:
- **Hak Akses**: Admin dan siswa menerima `403 Forbidden` jika mengakses route `/guru/*`.
- **Privasi TP**: Guru tidak bisa melihat, mengedit, atau menghapus TP milik guru lain.
- **Otorisasi Kelas**: Guru menerima `403 Forbidden` jika mencoba membuka `/guru/kelas/{kelas}` untuk kelas yang tidak dia ampu.
- **Fungsi Berjalan**: Menyalin template berhasil menduplikasi baris di tabel `learning_objectives`.

> **Catatan**: *Pre-tasks* (menghapus VerificationController, perbarui README dan SPEC.md) sudah saya kerjakan. Seeder sudah memiliki akun Guru dan Siswa contoh. Pesan peringatan (risky test) berasal dari fitur bawaan pest/phpunit yang disembunyikan/dilewati (karena fitur Fortify tidak aktif), dan tidak membahayakan.

Apakah rencana ini sudah sesuai dan bisa saya mulai implementasikan sekarang?
