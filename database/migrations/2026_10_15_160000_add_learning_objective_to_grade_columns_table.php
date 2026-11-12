<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom learning_objective_id pada grade_columns (nullable untuk backward compatibility)
        Schema::table('grade_columns', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_columns', 'learning_objective_id')) {
                $table->foreignId('learning_objective_id')->nullable()->after('label')->constrained('learning_objectives')->nullOnDelete();
            }
        });

        // 2. Pivot table untuk mendukung kaitan lebih dari satu TP per kolom nilai
        if (!Schema::hasTable('grade_column_learning_objective')) {
            Schema::create('grade_column_learning_objective', function (Blueprint $table) {
                $table->id();
                $table->foreignId('grade_column_id')->constrained('grade_columns')->cascadeOnDelete();
                $table->foreignId('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['grade_column_id', 'learning_objective_id'], 'gcol_lo_unique');
            });
        }

        // 3. Pivot table untuk mendukung kaitan TP pada asesmen/tugas (multiple TP jika diperlukan)
        if (!Schema::hasTable('assessment_learning_objective')) {
            Schema::create('assessment_learning_objective', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->foreignId('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['assessment_id', 'learning_objective_id'], 'ass_lo_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_learning_objective');
        Schema::dropIfExists('grade_column_learning_objective');

        Schema::table('grade_columns', function (Blueprint $table) {
            if (Schema::hasColumn('grade_columns', 'learning_objective_id')) {
                $table->dropForeign(['learning_objective_id']);
                $table->dropColumn('learning_objective_id');
            }
        });
    }
};
