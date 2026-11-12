# Rencana Tahap 4 Bagian B: Belajar & Halaman Materi Siswa

## Tujuan
Mengimplementasikan alur pembelajaran siswa, mulai dari menelusuri elemen, memilih Tujuan Pembelajaran (TP), hingga mengakses halaman materi (subbab) lengkap dengan tab interaktif dan komponen simulasi.

## Spesifikasi Berdasarkan SPEC.md
1. **Daftar TP & Subbab (`/belajar/{elemen}`)**:
   - Menampilkan TP milik guru yang diplot ke kelas siswa tersebut.
   - Hanya menampilkan materi/subbab yang sudah berstatus `published` dan `available_from` (jika ada) sudah terlewati.
   
2. **Halaman Materi (`/belajar/{elemen}/{tp}/{subbab}`)**:
   - Layout: Terdapat *sidebar* (desktop) atau *drawer* (mobile) untuk daftar subbab pada TP yang sama.
   - Tiga Tab Utama: 
     - **Materi**: Berisi `content`, sematan `video_url`, dan daftar lampiran (`lesson_attachments`). 
     - **Diskusi**: Menampilkan dan membuat diskusi/pertanyaan terkait materi ini. Menampilkan balasan dari guru atau siswa lain.
     - **Catatanku**: Catatan pribadi (`notes`) untuk siswa pada materi ini yang disimpan otomatis/secara manual.
   - Tombol **Tandai Selesai**: Menyimpan riwayat penyelesaian subbab ke `lesson_progress`. Ini akan memengaruhi perhitungan progres mingguan dan fitur hari beruntun.
   
3. **Komponen Khusus: Simulasi Von Neumann (Subbab 5.2)**:
   - Sebuah komponen Blade + Alpine.js untuk mensimulasikan siklus fetch-decode-execute.
   - Terdapat komponen visual: Input (Keyboard), Control Unit (CU), Arithmetic Logic Unit (ALU), Memori Utama (RAM), dan Output (Layar).
   - Animasi/Langkah: 6 langkah menghitung `12 + 30` dengan kontrol "Sebelumnya" dan "Langkah berikutnya".

## Langkah Implementasi
1. **Komponen Livewire Baru**:
   - `App\Livewire\Siswa\Belajar\DaftarTp`: Untuk melihat daftar TP dan subbab per elemen.
   - `App\Livewire\Siswa\Belajar\MateriDetail`: Untuk halaman materi interaktif (termasuk tab dan penanganan tombol Tandai Selesai).
   - `App\Livewire\Siswa\Belajar\Diskusi`: Komponen khusus untuk mengelola percakapan dan balasan di tab Diskusi.
   - `App\Livewire\Siswa\Belajar\Catatan`: Komponen khusus untuk menyimpan catatan pribadi (*debounced auto-save* jika memungkinkan).

2. **Pembuatan View & Routing**:
   - Membuat rute:
     - `Route::get('belajar/{element:code}', DaftarTp::class)->name('siswa.belajar.tp');`
     - `Route::get('belajar/{element:code}/{tp:code}/{lesson:code}', MateriDetail::class)->name('siswa.belajar.materi');`
   - Membuat desain responsif sesuai kanvas/`docs/desain/materi.html`. (Lebar 390px, navigasi bawah untuk mobile).

3. **Simulasi Von Neumann (Alpine.js)**:
   - Membuat file komponen `resources/views/components/simulasi-von-neumann.blade.php`.
   - Menggunakan Alpine.js (`x-data="{ step: 1 }"`) untuk memanipulasi *state* komponen visual menyala/mati sesuai 6 tahapan yang ada di SPEC.

4. **Pengujian (Pest)**:
   - `tests/Feature/Siswa/BelajarTest.php`:
     - Pastikan siswa tidak bisa mengakses materi yang belum terbit (`published`) atau belum saatnya (`available_from`).
     - Pastikan siswa tidak bisa mengakses TP guru lain (yang tidak mengajar di kelasnya).
     - Uji tombol "Tandai Selesai" berhasil menambah rekaman ke `lesson_progress` dan memengaruhi capaian.
     - Uji tab diskusi (tambah, baca) dan tab catatan (tambah, baca khusus diri sendiri).
