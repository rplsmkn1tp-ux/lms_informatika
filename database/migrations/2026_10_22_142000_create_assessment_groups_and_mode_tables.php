<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah assignment_mode dan max_group_members di tabel assessments
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'assignment_mode')) {
                $table->enum('assignment_mode', ['individual', 'group'])->default('individual')->after('type');
            }
            if (!Schema::hasColumn('assessments', 'max_group_members')) {
                $table->unsignedInteger('max_group_members')->default(4)->after('assignment_mode');
            }
        });

        // 2. Buat tabel assessment_groups
        if (!Schema::hasTable('assessment_groups')) {
            Schema::create('assessment_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
                $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
                $table->string('name');
                $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
                $table->text('contribution_notes')->nullable();
                $table->timestamps();

                $table->index(['assessment_id', 'classroom_id']);
            });
        }

        // 3. Buat tabel pivot assessment_group_members
        if (!Schema::hasTable('assessment_group_members')) {
            Schema::create('assessment_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_group_id')->constrained('assessment_groups')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['assessment_group_id', 'student_id']);
            });
        }

        // 4. Tambah assessment_group_id di tabel submissions
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'assessment_group_id')) {
                $table->foreignId('assessment_group_id')
                    ->nullable()
                    ->after('assessment_id')
                    ->constrained('assessment_groups')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'assessment_group_id')) {
                $table->dropForeign(['assessment_group_id']);
                $table->dropColumn('assessment_group_id');
            }
        });

        Schema::dropIfExists('assessment_group_members');
        Schema::dropIfExists('assessment_groups');

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'max_group_members')) {
                $table->dropColumn('max_group_members');
            }
            if (Schema::hasColumn('assessments', 'assignment_mode')) {
                $table->dropColumn('assignment_mode');
            }
        });
    }
};
