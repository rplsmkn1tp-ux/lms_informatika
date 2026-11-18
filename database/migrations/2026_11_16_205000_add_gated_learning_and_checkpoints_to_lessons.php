<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom penguncian berjenjang pada tabel lessons
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'is_gated')) {
                $table->boolean('is_gated')->default(false)->after('sort_order');
            }
            if (!Schema::hasColumn('lessons', 'completion_rule')) {
                $table->string('completion_rule', 40)->default('standard')->after('is_gated');
            }
            if (!Schema::hasColumn('lessons', 'prerequisite_lesson_id')) {
                $table->foreignId('prerequisite_lesson_id')
                    ->nullable()
                    ->after('completion_rule')
                    ->constrained('lessons')
                    ->nullOnDelete();
            }
        });

        // 2. Buat tabel lesson_checkpoints (Kuis Pemantik Literasi per Materi)
        if (!Schema::hasTable('lesson_checkpoints')) {
            Schema::create('lesson_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
                $table->text('question');
                $table->json('options'); // Format: [{"key": "A", "text": "..."}, {"key": "B", "text": "..."}]
                $table->string('correct_answer', 10); // "A", "B", "C", atau "D"
                $table->text('explanation')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 3. Tambahkan kolom status kuis pemantik pada tabel lesson_progress
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_progress', 'checkpoint_score')) {
                $table->integer('checkpoint_score')->nullable()->after('completed_items');
            }
            if (!Schema::hasColumn('lesson_progress', 'checkpoint_passed_at')) {
                $table->dateTime('checkpoint_passed_at')->nullable()->after('checkpoint_score');
            }
            if (!Schema::hasColumn('lesson_progress', 'checkpoint_answers')) {
                $table->json('checkpoint_answers')->nullable()->after('checkpoint_passed_at');
            }
            if (!Schema::hasColumn('lesson_progress', 'is_unlocked')) {
                $table->boolean('is_unlocked')->default(false)->after('checkpoint_answers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('lesson_progress', 'is_unlocked')) $cols[] = 'is_unlocked';
            if (Schema::hasColumn('lesson_progress', 'checkpoint_answers')) $cols[] = 'checkpoint_answers';
            if (Schema::hasColumn('lesson_progress', 'checkpoint_passed_at')) $cols[] = 'checkpoint_passed_at';
            if (Schema::hasColumn('lesson_progress', 'checkpoint_score')) $cols[] = 'checkpoint_score';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::dropIfExists('lesson_checkpoints');

        Schema::table('lessons', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('lessons', 'prerequisite_lesson_id')) {
                $table->dropForeign(['prerequisite_lesson_id']);
                $cols[] = 'prerequisite_lesson_id';
            }
            if (Schema::hasColumn('lessons', 'completion_rule')) $cols[] = 'completion_rule';
            if (Schema::hasColumn('lessons', 'is_gated')) $cols[] = 'is_gated';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
