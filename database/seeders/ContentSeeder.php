<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first();

        $bk = \App\Models\Element::updateOrCreate(
            ['code' => 'BK'],
            [
                'name' => 'Berpikir Komputasional',
                'description' => 'Berpikir komputasional (Computational Thinking)',
                'phase' => 'E',
                'cp_reference' => 'Keputusan Kepala BSKAP No. 046/H/KR/2025'
            ]
        );

        $ld = \App\Models\Element::updateOrCreate(
            ['code' => 'LD'],
            [
                'name' => 'Literasi Digital',
                'description' => 'Literasi Digital',
                'phase' => 'E',
                'cp_reference' => 'Keputusan Kepala BSKAP No. 046/H/KR/2025'
            ]
        );

        $tps = [
            ['code' => 'BK-1', 'title' => 'Struktur data & algoritma standar', 'element_id' => $bk->id],
            ['code' => 'BK-2', 'title' => 'Proses komputasi untuk data berkualitas', 'element_id' => $bk->id],
            ['code' => 'BK-3', 'title' => 'Beragam solusi dengan algoritma', 'element_id' => $bk->id],
            ['code' => 'BK-4', 'title' => 'Rancangan program dalam pseudocode', 'element_id' => $bk->id],
            ['code' => 'BK-5', 'title' => 'Sistem Komputer', 'element_id' => $bk->id],
            ['code' => 'LD-1', 'title' => 'Mesin pencari lanjutan', 'element_id' => $ld->id],
            ['code' => 'LD-2', 'title' => 'Periksa fakta & membaca lateral', 'element_id' => $ld->id],
            ['code' => 'LD-3', 'title' => 'Aplikasi perkantoran lanjutan', 'element_id' => $ld->id],
            ['code' => 'LD-4', 'title' => 'Keamanan jaringan lokal & internet', 'element_id' => $ld->id],
            ['code' => 'LD-5', 'title' => 'Produksi & kolaborasi konten digital', 'element_id' => $ld->id],
            ['code' => 'LD-6', 'title' => 'Etika & budaya digital', 'element_id' => $ld->id],
            ['code' => 'LD-7', 'title' => 'Keamanan akun pribadi', 'element_id' => $ld->id],
        ];

        $bk5 = null;
        foreach ($tps as $index => $tp) {
            $created = \App\Models\LearningObjective::updateOrCreate(
                [
                    'element_id' => $tp['element_id'],
                    'code' => $tp['code'],
                ],
                [
                    'title' => $tp['title'],
                    'sort_order' => $index + 1,
                    'is_template' => true,
                    'created_by' => $admin ? $admin->id : null,
                ]
            );
            if ($tp['code'] === 'BK-5') {
                $bk5 = $created;
            }
        }

        // Subbab BK-5
        $subbabs = [
            '5.1 Perangkat keras & perangkat lunak',
            '5.2 Model komputer Von Neumann',
            '5.3 Siklus fetch–decode–execute',
            '5.4 Peran sistem operasi',
            '5.5 Proses, memori & berkas'
        ];

        $lesson52 = null;
        foreach ($subbabs as $index => $sub) {
            $code = substr($sub, 0, 3);
            $title = substr($sub, 4);
            $lesson = \App\Models\Lesson::updateOrCreate(
                [
                    'learning_objective_id' => $bk5->id,
                    'code' => $code,
                ],
                [
                    'title' => $title,
                    'sort_order' => $index + 1,
                    'status' => 'published',
                    'created_by' => $admin ? $admin->id : null,
                    'content' => $code === '5.2' ? '<p>Tujuan pembelajaran: memahami komponen utama CPU, RAM, I/O, dan bus. <strong>Catatan: RAM bersifat volatil.</strong></p>' : ''
                ]
            );
            if ($code === '5.2') {
                $lesson52 = $lesson;
            }
        }

        // Kuis formatif untuk 5.2
        if ($lesson52) {
            $assessment = \App\Models\Assessment::updateOrCreate(
                [
                    'learning_objective_id' => $bk5->id,
                    'lesson_id' => $lesson52->id,
                    'title' => 'Kuis 5.2 Model Von Neumann',
                ],
                [
                    'type' => 'formatif',
                    'status' => 'published',
                    'created_by' => $admin ? $admin->id : null,
                ]
            );

            \App\Models\Question::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'body' => 'Pada simulasi tadi, komponen mana yang menghitung 12 + 30?',
                ],
                [
                    'type' => 'pilihan_ganda',
                    'options' => json_encode(['A' => 'Unit kendali (CU)', 'B' => 'ALU', 'C' => 'Memori utama (RAM)', 'D' => 'Perangkat output']),
                    'answer_key' => 'B',
                    'points' => 100
                ]
            );
        }
    }
}
