<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Akun Guru Ditolak</title>
</head>
<body>
    <p>Halo, {{ $user->name }}!</p>
    <p>Mohon maaf, pendaftaran Anda sebagai Guru telah <strong>ditolak</strong> oleh Admin.</p>
    <p><strong>Alasan Penolakan:</strong></p>
    <blockquote>{{ $rejectionReason }}</blockquote>
    <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator sekolah.</p>
    <p>Terima kasih.</p>
</body>
</html>
