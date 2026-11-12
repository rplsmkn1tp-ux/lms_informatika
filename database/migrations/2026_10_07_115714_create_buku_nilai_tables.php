<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('group'); // uh, tugas, mid, uas
            $table->integer('seq');
            $table->string('label');
            $table->integer('max_score')->default(100);
            $table->timestamps();

            $table->unique(['classroom_id', 'academic_year_id', 'group', 'seq'], 'grade_col_unique');
        });

        Schema::create('grade_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained('grade_columns')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['column_id', 'student_id']);
        });

        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->integer('weight_uh')->default(30);
            $table->integer('weight_tugas')->default(30);
            $table->integer('weight_uas_mid')->default(30);
            $table->integer('weight_absensi')->default(10);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['classroom_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_weights');
        Schema::dropIfExists('grade_entries');
        Schema::dropIfExists('grade_columns');
    }
};
