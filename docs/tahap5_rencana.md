# Rencana Pengerjaan Tahap 5 (Penilaian, Ketercapaian TP, Rapor & Laporan)

## 1. Persiapan & Routing
- Menambahkan *route* khusus untuk guru (Penilaian, Peta Ketercapaian, Rapor) dan admin (Laporan).
- Memastikan akses hanya dapat dilakukan oleh *role* yang tepat melalui *middleware*.

## 2. Halaman Penilaian Guru (`Livewire\Guru\Penilaian`)
- **Daftar Antrean:** Menampilkan antrean *submission* siswa yang berstatus `dikumpulkan` (butuh penilaian manual seperti isian, esai, atau unggah tugas).
- **Form Penilaian:** Antarmuka bagi guru untuk membaca/mendownload file jawaban siswa, memasukkan skor manual (rubrik), dan menuliskan umpan balik (*feedback*).
- Status jawaban otomatis berubah menjadi `dinilai` setelah guru menyimpan nilai.

## 3. Ketercapaian TP & Otomatisasi (Logic & Services)
- Membuat `ObjectiveMasteryService` untuk menghitung nilai terbaik (*best score*) dari riwayat percobaan siswa pada suatu TP.
- Menentukan status di `objective_mastery` secara otomatis:
  - `belum`: Belum ada nilai/belum dikerjakan.
  - `remedial`: Nilai terbaik < KKTP. Siswa diberi umpan balik dan dapat mengulang.
  - `tercapai`: Nilai terbaik ≥ KKTP.
- Memberikan notifikasi/indikator visual bahwa siswa dengan skor > 75 direkomendasikan sebagai kandidat `pengayaan` (status `pengayaan` itu sendiri bisa di-*override* manual oleh guru).
- **Pengujian (Pest):** Membuat `Tahap5KetercapaianTest.php` khusus untuk memastikan kalkulasi skor tertinggi, perubahan status ketercapaian, dan kesempatan remedial beroperasi dengan benar.

## 4. Peta Ketercapaian Per Kelas (`Livewire\Guru\Progres`)
- Membuat tabel matriks dua dimensi (Baris = Siswa, Kolom = TP).
- Isi tabel adalah status ketercapaian yang ditulis dengan teks eksplisit ("Belum", "Remedial", "Tercapai", "Pengayaan") sesuai prinsip aksesibilitas (bukan hanya label warna).
- Tombol/menu konteks bagi guru untuk menetapkan status "Pengayaan" secara manual.

## 5. Asesmen Sumatif Elemen Terbuka Otomatis
- Saat siswa mencapai status `tercapai` atau `pengayaan` pada *seluruh* TP di suatu elemen, sistem mengizinkan siswa mengakses soal Sumatif Elemen tersebut.
- Menyediakan *toggle* "Buka Sumatif Manual" di modul Peta Ketercapaian, agar guru dapat meng-*override* kunci ini jika dirasa perlu.

## 6. Rapor Per Elemen & Laporan Admin (Eksport PDF & Excel)
- Memasang dan mengonfigurasi *library* `barryvdh/laravel-dompdf` dan `maatwebsite/excel`.
- **Rapor Guru:** Menampilkan rata-rata TP dan nilai sumatif siswa per elemen. Sistem merangkai kalimat otomatis untuk "Deskripsi Capaian" (misal: "Sangat baik pada algoritma, perlu bimbingan pada struktur data"), dan guru dapat mengeditnya sebelum dicetak (PDF) atau diunduh (Excel).
- **Laporan Admin:** Halaman `Admin/Laporan` dengan filter berdasarkan Tahun Ajaran dan Kelas, untuk melihat rekapitulasi nilai sekolah secara keseluruhan, beserta kemampuan *Export to Excel/PDF*.
