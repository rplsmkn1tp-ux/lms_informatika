# Rencana Tahap 3 Bagian B (Modul Guru: Materi & Subbab)

## 1. Persiapan & Routing
- Menambahkan *route* `GET /guru/materi` yang diarahkan ke komponen `App\Livewire\Guru\LessonManagement`.
- Membuat Model & Migration untuk `lessons`, `lesson_attachments`, dan `lesson_classroom` (jika belum sesuai spesifikasi).
- Menginstal dan mengonfigurasi *library* `mews/purifier` untuk membersihkan konten HTML dari Trix.

## 2. Manajemen Materi (`LessonManagement`)
- **Tampilan Daftar Materi:** Menampilkan subbab milik guru (`created_by`), terkait TP miliknya.
- **Form Editor (Trix):**
  - Mengintegrasikan editor Trix dengan Alpine.js.
  - **Trix File Upload Dimatikan:** Menonaktifkan fitur *drag-and-drop* atau *upload inline* di dalam Trix.
  - **Keamanan (XSS):** Konten dibersihkan memakai `mews/purifier` saat disimpan ke database dan saat di-*render* (ditampilkan).
- **Blok Interaktif:**
  - Karena Trix tidak dapat memuat komponen Blade, tambahkan kolom `interactive_component` (nullable) di tabel `lessons`.
  - Guru dapat memilih blok interaktif dari daftar (saat ini hanya tersedia: `"von-neumann"`). Saat siswa melihat materi, komponen *simulasi* akan di-render di bagian tersebut.
- **Tautan YouTube:** 
  - Form validasi URL YouTube.
  - Sistem akan mengekstrak ID video dan menyimpannya. Saat ditampilkan, video di-*embed* lewat `youtube-nocookie.com`.
- **Manajemen Lampiran (`lesson_attachments`):**
  - Diunggah melalui *form* terpisah (bukan di dalam Trix).
  - Format diizinkan: PDF, DOCX, PPTX, XLSX, PNG, JPG (Maks. 10 MB).
  - Berkas disimpan di *storage privat* (`storage/app/private/attachments`).
  - *Route* pengunduhan yang memeriksa *policy/hak akses*: Hanya guru pemilik yang bisa mengunduh, ATAU siswa yang berada di kelas yang materinya sudah diterbitkan.

## 3. Jadwal & Terbit ke Kelas
- **Pivot `lesson_classroom`:** Form untuk menerbitkan materi hanya ke kelas-kelas yang **diampu oleh guru tersebut** (*teaching assignments*).
- Input tanggal rilis (`available_from`) bersifat opsional (jika kosong, langsung tersedia).

## 4. Mekanisme Salin Materi (Template TP → TP Guru)
- Menyesuaikan logika fitur "Salin Template TP":
  - Saat guru menyalin TP template (milik Admin), semua subbab/materi (draft/publish) yang menempel pada TP template tersebut ikut tersalin menjadi draf milik guru tersebut.

## 5. Seeder BK-5 Lengkap
- Membuat `Bk5ContentSeeder` untuk menyuntikkan 5 subbab ke TP template BK-5:
  - 5.1 Perangkat keras & perangkat lunak.
  - 5.2 Model komputer Von Neumann (Lengkap: TP, komponen CPU/RAM/I/O, volatilitas RAM, dan *interactive_component* = `"von-neumann"`).
  - 5.3 Siklus fetch–decode–execute.
  - 5.4 Peran sistem operasi.
  - 5.5 Proses, memori & berkas.
- **Update Seeder Lokal:** Guru contoh otomatis sudah memiliki salinan TP BK-5 ini, dan subbabnya sudah berstatus terbit ke kelas contoh (X RPL 1).

## 6. Pengujian (Pest)
Menambahkan pengujian untuk memastikan:
- Guru tidak bisa mengubah atau menghapus materi milik guru lain.
- Siswa tidak bisa mengunduh lampiran dari materi yang belum terbit untuk kelasnya (403 Forbidden).
- Tag `<script>` dan elemen HTML berbahaya lainnya otomatis terbuang dari input Trix oleh Purifier.
