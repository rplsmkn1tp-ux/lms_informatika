<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'lesson_id']);
            $table->index(['student_id', 'completed_at']);
        });

        Schema::create('objective_mastery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->decimal('best_score', 5, 2)->nullable();
            $table->enum('status', ['belum', 'remedial', 'tercapai', 'pengayaan'])->default('belum');
            $table->timestamps();

            $table->unique(['student_id', 'learning_objective_id', 'classroom_id'], 'objective_mastery_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objective_mastery');
        Schema::dropIfExists('lesson_progress');
    }
};
