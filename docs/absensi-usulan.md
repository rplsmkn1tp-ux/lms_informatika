# KETENTUAN ABSENSI

Tujuan: guru mencatat kehadiran siswa tiap pertemuan Informatika; siswa melihat riwayat sendiri; rekap per kelas/semester.

## Data
- `attendance_sessions`: classroom_id, teacher_id, academic_year_id (semester), meeting_date, meeting_no, topic (opsional; boleh tautan ke subbab), status (terbuka/ditutup), opened_at, closed_at. Unik (classroom_id, meeting_date, meeting_no).
- `attendance_records`: session_id, student_id, status (hadir/izin/sakit/alpa; selalu ditampilkan sebagai teks), note, marked_by, marked_at. Unik (session_id, student_id).
- Siswa pindah kelas: riwayat lama tetap; daftar siswa sesi diambil dari anggota kelas saat sesi dibuat.

## Alur
- **Guru**: Absensi → pilih kelas → Buat pertemuan (tanggal bawaan hari ini, no. pertemuan otomatis, topik opsional) → daftar siswa, bawaan semua "Hadir", tombol segmen H/I/S/A per siswa (target sentuh ≥44px), tombol "Tandai semua hadir", kolom catatan → Simpan → Tutup pertemuan. Ubah setelah ditutup hanya oleh guru pemilik kelas dengan alasan (dicatat). Rekap: siswa × pertemuan, persentase kehadiran, jumlah H/I/S/A, filter semester/rentang tanggal, ekspor Excel dan PDF. Label teks "Perlu perhatian" bila kehadiran < 75%.
- **Siswa**: menu "Kehadiran Saya", hanya lihat (persentase, jumlah H/I/S/A, riwayat).
- **Admin**: laporan kehadiran per kelas/semester, lihat saja dan ekspor.
- **Hak akses**: guru hanya kelas yang ia ajar (plot mengajar); siswa hanya datanya sendiri; admin lihat semua. Policy + tes tiap batas.
- **Keputusan sementara**: siswa TIDAK absen mandiri; tanpa notifikasi; tanpa lampiran surat (hanya catatan teks). Perhitungan persen: hanya "Hadir" dihitung hadir.
- Data ini nanti dipakai Buku Nilai (kolom Total Absensi), jadi sediakan layanan PHP murni yang mudah dites: persentase kehadiran per siswa per kelas per semester.
