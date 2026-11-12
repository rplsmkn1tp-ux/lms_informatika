# Audit Keamanan Siswa

Dokumen ini melacak keamanan akses Siswa terhadap data dan tindakan yang ada di dalam aplikasi.

Status **Jalan** berarti sudah diverifikasi secara otomatis melalui `Feature Test` (biasanya memanggil komponen Livewire secara langsung dan memastikan menerima `403 Forbidden` / `404 Not Found` atau respons yang sesuai).

## Hak Akses & Kepemilikan Data

| Modul / Komponen | Aksi | Status | Catatan / Lokasi Tes |
|---|---|---|---|
| Materi Belajar | Siswa hanya bisa mengakses materi yang sudah ditugaskan ke kelasnya. Akses ke materi kelas lain ditolak | Jalan | `MateriDetailTest::test('siswa cannot access lesson not assigned to their class')` (404) |
| Materi Belajar | Siswa tidak bisa mengakses materi berstatus `draft` | Jalan | `MateriDetailTest::test('siswa cannot access draft lesson')` (404) |
| Materi Belajar | Siswa ditolak jika mencoba mengakses materi yang tanggal terbitnya belum sampai (future date) | Jalan | `MateriDetailTest::test('siswa cannot access locked lesson (future date)')` (403) |
| Materi Belajar | Membersihkan tag berbahaya seperti `<script>` agar siswa tidak terkena serangan XSS saat membaca materi | Jalan | `MateriDetailTest::test('purifier cleans malicious scripts from lesson content')` |
| Asesmen / Tugas | Mencegah siswa mengumpulkan tugas untuk tugas dari kelas lain | Jalan | `KerjakanTugasTest::test('siswa cannot access assessment assigned to other class')` (403) |
| Asesmen / Tugas | Mencegah siswa mengubah kumpulannya jika waktu sudah ditutup atau sudah dinilai | Jalan | `KerjakanTugasTest::test('siswa cannot edit submission after it is graded')` |
| Asesmen / Tugas | Mencegah siswa melihat jawaban/submission siswa lain | Jalan | `KerjakanTugasTest::test('siswa cannot see other students submissions')` |
| Absensi / Buku Nilai | Mencegah siswa memodifikasi status kehadiran atau nilainya | Ditunda | Fitur ditunda ke Tahap 2 |
