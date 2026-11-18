<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Classroom;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standarisasi kurikulum Informatika SMK/SMA Fase E/F sesuai Kepmendikbudristek No. 244/M/2024
     * dan Keputusan Kepala BSKAP No. 032/H/KR/2024 / No. 046/H/KR/2025:
     * Mapel Informatika hanya memiliki 2 elemen utama:
     * 1. Berpikir Komputasional (BK)
     * 2. Literasi Digital (LD) -> mencakup TIK, JKI, DSI, Sistem & Pemanfaatan Teknologi
     */
    public function up(): void
    {
        // Jika belum ada data elemen (misal saat fresh migration di test suite), tidak perlu konsolidasi
        if (Element::count() === 0) {
            return;
        }

        // 1. Pastikan Elemen BK & LD ada dan mutakhir
        $bk = Element::firstOrCreate(
            ['code' => 'BK'],
            [
                'name' => 'Berpikir Komputasional',
                'description' => 'Berpikir komputasional (Computational Thinking)',
                'phase' => 'E',
                'cp_reference' => 'Keputusan Kepala BSKAP No. 046/H/KR/2025',
            ]
        );

        $ld = Element::firstOrCreate(
            ['code' => 'LD'],
            [
                'name' => 'Literasi Digital',
                'description' => 'Literasi Digital (termasuk Teknologi Informasi & Komunikasi)',
                'phase' => 'E',
                'cp_reference' => 'Keputusan Kepala BSKAP No. 046/H/KR/2025',
            ]
        );

        // 2. Bersihkan data simulasi / scratch test jika ada (AP_SIM)
        $simClassroom = Classroom::where('join_code', 'SIMPPLG')->first();
        if ($simClassroom) {
            $simUserIds = User::whereIn('email', [
                'guru.simulasi@smk.test',
                'ketua.simulasi@smk.test',
                'rajin.simulasi@smk.test',
                'malas.simulasi@smk.test',
            ])->pluck('id');

            DB::table('classroom_student')->where('classroom_id', $simClassroom->id)->delete();
            DB::table('teaching_assignments')->where('classroom_id', $simClassroom->id)->delete();
            DB::table('submissions')->whereIn('student_id', $simUserIds)->delete();
            DB::table('lesson_progress')->whereIn('student_id', $simUserIds)->delete();
            DB::table('assessment_group_members')->whereIn('student_id', $simUserIds)->delete();
            DB::table('assessment_groups')->where('classroom_id', $simClassroom->id)->delete();
            DB::table('assessment_classroom')->where('classroom_id', $simClassroom->id)->delete();
            if (Schema::hasTable('lesson_classroom')) {
                DB::table('lesson_classroom')->where('classroom_id', $simClassroom->id)->delete();
            }
            
            User::whereIn('id', $simUserIds)->delete();
            $simClassroom->delete();
        }

        // Hapus elemen AP_SIM jika ada
        $apSim = Element::where('code', 'AP_SIM')->first();
        if ($apSim) {
            $apSimTps = LearningObjective::where('element_id', $apSim->id)->get();
            foreach ($apSimTps as $tp) {
                // Hapus relasi assessment & lesson simulasi
                $assIds = Assessment::where('learning_objective_id', $tp->id)->pluck('id');
                DB::table('submissions')->whereIn('assessment_id', $assIds)->delete();
                DB::table('assessment_group_members')->whereIn('assessment_id', $assIds)->delete();
                DB::table('assessment_groups')->whereIn('assessment_id', $assIds)->delete();
                DB::table('assessment_classroom')->whereIn('assessment_id', $assIds)->delete();
                Assessment::whereIn('id', $assIds)->delete();

                $lessonIds = Lesson::where('learning_objective_id', $tp->id)->pluck('id');
                DB::table('lesson_checkpoints')->whereIn('lesson_id', $lessonIds)->delete();
                DB::table('lesson_progress')->whereIn('lesson_id', $lessonIds)->delete();
                if (Schema::hasTable('lesson_classroom')) {
                    DB::table('lesson_classroom')->whereIn('lesson_id', $lessonIds)->delete();
                }
                Lesson::whereIn('id', $lessonIds)->delete();

                $tp->delete();
            }
            $apSim->delete();
        }

        // 3. Pindahkan semua TP & data dari TIK ke LD (Literasi Digital)
        $tik = Element::where('code', 'TIK')->first();
        if ($tik) {
            $tikTps = LearningObjective::where('element_id', $tik->id)->orderBy('sort_order')->get();
            foreach ($tikTps as $tp) {
                $maxLdSort = (LearningObjective::where('element_id', $ld->id)->max('sort_order') ?? 7) + 1;
                $ldCount = LearningObjective::where('element_id', $ld->id)->count();
                $newCode = $tp->code;
                if (str_starts_with($newCode, 'TP-TIK-') || str_starts_with($newCode, 'TIK-')) {
                    $newCode = 'LD-' . ($ldCount + 1);
                }

                $tp->update([
                    'element_id' => $ld->id,
                    'code' => $newCode,
                    'sort_order' => $maxLdSort,
                ]);
            }

            // Perbarui referensi element_id di tabel lain
            if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'element_id')) {
                DB::table('assessments')->where('element_id', $tik->id)->update(['element_id' => $ld->id]);
            }
            if (Schema::hasTable('teaching_modules') && Schema::hasColumn('teaching_modules', 'element_id')) {
                DB::table('teaching_modules')->where('element_id', $tik->id)->update(['element_id' => $ld->id]);
            }

            // Hapus elemen TIK yang sudah dilebur ke LD
            $tik->delete();
        }

        // 4. Pastikan elemen non-BK dan non-LD lainnya (jika ada sisa) juga dialihkan ke BK atau LD
        $otherElements = Element::whereNotIn('code', ['BK', 'LD'])->get();
        foreach ($otherElements as $other) {
            $targetElement = in_array(strtoupper($other->code), ['AP', 'SK', 'ALGORITMA']) ? $bk : $ld;
            
            $otherTps = LearningObjective::where('element_id', $other->id)->get();
            foreach ($otherTps as $tp) {
                $maxTargetSort = LearningObjective::where('element_id', $targetElement->id)->max('sort_order') ?? 0;
                $maxTargetSort++;
                $tp->update([
                    'element_id' => $targetElement->id,
                    'sort_order' => $maxTargetSort,
                ]);
            }

            if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'element_id')) {
                DB::table('assessments')->where('element_id', $other->id)->update(['element_id' => $targetElement->id]);
            }
            if (Schema::hasTable('teaching_modules') && Schema::hasColumn('teaching_modules', 'element_id')) {
                DB::table('teaching_modules')->where('element_id', $other->id)->update(['element_id' => $targetElement->id]);
            }

            $other->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Standarisasi kurikulum tidak perlu dibalikkan ke elemen usang
    }
};
