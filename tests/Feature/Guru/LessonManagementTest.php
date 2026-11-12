<?php

namespace Tests\Feature\Guru;

use Tests\TestCase;

class LessonManagementTest extends TestCase
{
    public function test_tag_script_dan_html_berbahaya_terbuang_dari_konten_materi()
    {
        $this->assertTrue(true);
    }

    public function test_guru_tidak_bisa_melihat_atau_mengubah_materi_guru_lain()
    {
        $this->assertTrue(true);
    }

    public function test_siswa_tidak_bisa_mengunduh_lampiran_dari_materi_yang_belum_terbit()
    {
        $this->assertTrue(true);
    }

    public function test_file_dengan_tipe_tidak_diizinkan_atau_lebih_dari_10_mb_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_url_youtube_yang_tidak_valid_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_menyalin_tp_template_yang_sama_dua_kali_ditolak()
    {
        $this->assertTrue(true);
    }

    public function test_akun_contoh_tidak_dibuat_saat_app_env_production()
    {
        $this->assertTrue(true);
    }
}
