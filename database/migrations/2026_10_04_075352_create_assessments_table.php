<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_objective_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('element_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['diagnostik', 'formatif', 'sumatif', 'tugas']);
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->integer('kktp')->nullable();
            $table->integer('max_score')->default(100);
            $table->integer('max_attempts')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
