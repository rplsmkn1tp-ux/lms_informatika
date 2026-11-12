# Acuan Desain — Ruang Informatika

Folder ini berisi desain halaman dari kanvas desain Claude, dalam bentuk file HTML.
Simpan di proyek sebagai `docs/desain/`.

## Halaman siswa dan pendaftaran

| File | Halaman | Route di aplikasi |
|---|---|---|
| `beranda.html` | Beranda siswa (desktop) | `/beranda` |
| `beranda-mobile.html` | Beranda siswa (HP, 390px) | `/beranda` |
| `materi.html` | Halaman materi, contoh subbab BK-5.2 (desktop) | `/belajar/{elemen}/{tp}/{subbab}` |
| `materi-mobile.html` | Halaman materi (HP, 390px) | `/belajar/{elemen}/{tp}/{subbab}` |
| `daftar.html` | Daftar akun siswa/guru (desktop) | `/daftar` |
| `daftar-mobile.html` | Daftar akun (HP, 390px) | `/daftar` |

## Panel admin

| File | Halaman | Route di aplikasi |
|---|---|---|
| `admin-dashboard.html` | Dashboard admin | `/admin` |
| `admin-verifikasi.html` | Verifikasi guru | `/admin/verifikasi` |
| `admin-pengguna.html` | Kelola pengguna (tab Siswa/Guru) | `/admin/pengguna` |
| `admin-kelas.html` | Kelas dan kode gabung | `/admin/kelas` |
| `admin-tahun-ajaran.html` | Tahun ajaran & semester | `/admin/tahun-ajaran` |

Halaman admin lain (Plot mengajar, Kurikulum, Pengumuman, Pengaturan) memakai pola yang sama:
sidebar yang sama, judul halaman, kartu putih, tabel, tombol, dan label status dari file-file di atas.

## Cara membaca file ini

- File bisa dibuka di browser untuk melihat gambaran tampilannya. Agent sebaiknya membaca
  kode HTML-nya untuk mengambil struktur, warna, ukuran huruf, jarak, dan radius.
- Bagian bertanda `{{...}}`, `<sc-for>`, dan `<sc-if>` adalah sintaks template dari alat desain
  untuk nilai dinamis. Di browser, bagian ini bisa tampak kosong atau menampilkan `{{...}}`. Itu normal.
- Skrip `<script type="text/x-dc">` di akhir setiap file menjelaskan perilaku interaktifnya
  (tab, simulasi, umpan balik kuis, dialog sandi sementara, konfirmasi hapus, form penolakan).
  Bangun ulang perilaku ini dengan Livewire/Alpine.js; jangan menyalin skripnya mentah-mentah.
- Ikon berupa SVG garis. Pakai ikon Lucide yang setara (`blade-lucide-icons`).

## Ketentuan umum

- Jika desain berbeda dengan `SPEC.md`, ikuti `SPEC.md`.
- Nama, angka, tanggal, dan tugas di desain hanya contoh. Di aplikasi, semuanya diambil dari database.
- Warna dan huruf mengikuti SPEC bagian 3. Tombol utama berwarna biru `#2347C4`, bukan hitam
  bawaan Flux. Tombol hapus/tolak berwarna merah bata `#B4441A`.
- Status selalu ditulis dengan teks (Aktif, Nonaktif, Menunggu, Tercapai, Remedial, dst.), tidak hanya warna.
- Semua halaman harus nyaman dipakai di lebar 390px dan tidak bisa digeser ke samping. Tabel lebar
  dibungkus kotak yang bisa digeser sendiri.

## Ketentuan halaman siswa

- Beranda menampilkan **2 elemen** sesuai CP Fase E terbaru: Berpikir Komputasional (BK) dan
  Literasi Digital (LD), masing-masing dengan daftar TP sesuai urutan ATP guru.
- Simulasi di `materi.html` sudah dibuat sebagai komponen `<x-sim.von-neumann />`. File ini
  menunjukkan bagaimana komponen itu ditempatkan di halaman materi.
- Kuis di `materi.html` hanya contoh tampilan. Kunci jawaban tidak boleh ikut terkirim ke browser
  (lihat SPEC bagian 13).

## Ketentuan panel admin

- **Sidebar sama di semua halaman admin**: logo Ruang Informatika, menu berkelompok (Utama, Akun,
  Akademik, Sekolah), angka jumlah guru yang menunggu verifikasi, lalu nama admin dan tombol Keluar
  di bawah. Hapus logo dan tautan bawaan starter kit (Repository, Documentation). Di HP, sidebar
  menjadi menu yang dibuka lewat tombol.
- Buat komponen Blade bersama supaya semua halaman konsisten: layout admin, judul halaman, kartu,
  tombol (utama, sekunder, bahaya, nonaktif), label status, dan tabel.
- **Pengguna**: tab Siswa dan Guru dengan jumlahnya; cari dan saring; tombol Ubah, Atur sandi,
  Pindah kelas (siswa), dan Nonaktifkan (tombol ikon dengan `aria-label`). "Atur sandi" membuka
  dialog yang menampilkan sandi sementara; pengguna wajib menggantinya saat masuk berikutnya.
  Label "Sandi sementara" muncul sampai pengguna menggantinya.
- **Kelas**: kartu per kelas berisi jumlah siswa, guru Informatika (atau peringatan "Belum ada guru"),
  target mingguan, kode gabung dengan tombol Salin, Buat ulang, dan Aktifkan/Nonaktifkan kode.
  Tombol Hapus nonaktif selama kelas masih punya siswa, disertai keterangan alasannya.
- **Tahun ajaran**: kolom "Tahun ajaran" hanya berisi tahunnya (contoh `2026/2027`); semester di kolom
  sendiri. Tombol: Jadikan aktif, Ubah, Hapus. Hapus nonaktif untuk semester yang aktif atau yang
  sudah punya kelas. Sebelum menghapus, tampilkan konfirmasi. Tabel tidak boleh terpotong di desktop.
- **Verifikasi**: kartu per pendaftar dengan tombol Setujui dan Tolak. Tolak membuka kolom alasan
  yang dikirim ke email pendaftar. Di bawahnya ada riwayat verifikasi. Kalau tidak ada pendaftar,
  tampilkan ikon centang dengan teks "Semua pendaftaran guru sudah ditinjau."
