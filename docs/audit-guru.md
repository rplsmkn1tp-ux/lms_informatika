# Audit Keamanan Guru

Dokumen ini melacak keamanan akses Guru terhadap data dan tindakan yang ada di dalam aplikasi.

Status **Jalan** berarti sudah diverifikasi secara otomatis melalui `Feature Test` (biasanya memanggil komponen Livewire secara langsung dan memastikan menerima `403 Forbidden` atau respons yang sesuai).

## Hak Akses & Kepemilikan Data

| Modul / Komponen | Aksi | Status | Catatan / Lokasi Tes |
|---|---|---|---|
| Dasbor | Guru hanya bisa mengakses rute `/guru`, admin ditolak | Jalan | `GuruAccessTest::test_guru_hanya_bisa_mengakses_halaman_guru` |
| Kelas | Mencegah akses ke kelas yang tidak diampu | Jalan | `GuruModuleTest::test('guru receives 403 when accessing unassigned classroom')` |
| Kelas | Mencegah pembuatan/pembaruan kode gabung (join code) untuk kelas yang tidak diampu | Jalan | `GuruModuleTest::test('guru receives 403 when trying to regenerate code for unassigned classroom')` |
| TP | Mencegah edit TP milik guru lain | Jalan | `GuruModuleTest::test('guru cannot edit another gurus tp')` |
| TP | Mencegah hapus TP milik guru lain | Jalan | `GuruModuleTest::test('guru cannot delete another gurus tp')` |
| Materi (Lesson) | Mencegah edit materi milik guru lain | Jalan | `GuruModuleTest::test('guru cannot edit another gurus lesson')` & `GuruAccessTest::test_guru_a_tidak_bisa_mengubah_data_materi_bukan_miliknya` |
| Asesmen | Mencegah edit asesmen milik guru lain | Jalan | `GuruModuleTest::test('guru cannot edit another gurus assessment')` |
| Penilaian | Mencegah menilai tugas siswa dari kelas yang tidak diampu | Jalan | `GuruModuleTest::test('guru cannot grade submission of student from other classroom')` |
| Buku Nilai | Mencegah melihat/mengedit buku nilai kelas yang tidak diampu | Ditunda | Fitur ditunda ke Tahap 2 |
| Absensi | Mencegah membuka sesi / mencatat absensi untuk kelas yang tidak diampu | Ditunda | Fitur ditunda ke Tahap 2 |
