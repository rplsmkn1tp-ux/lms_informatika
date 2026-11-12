<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('attendance_session_id')->nullable()->after('lesson_id')->constrained('attendance_sessions')->nullOnDelete();
            $table->json('teacher_attachments')->nullable()->after('instructions');
            $table->json('submission_types')->nullable()->after('teacher_attachments');
            $table->boolean('allow_late')->default(false)->after('submission_types');
            $table->boolean('allow_resubmit')->default(false)->after('allow_late');
            $table->dateTime('opens_at')->nullable()->after('allow_resubmit');
            $table->dateTime('due_at')->nullable()->after('opens_at');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->longText('text_content')->nullable()->after('file_path');
            $table->string('link_url', 500)->nullable()->after('text_content');
            $table->boolean('is_late')->default(false)->after('link_url');
        });

        DB::statement("ALTER TABLE `submissions` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'dikerjakan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `submissions` MODIFY COLUMN `status` ENUM('dikerjakan', 'dikumpulkan', 'dinilai') NOT NULL DEFAULT 'dikerjakan'");

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['text_content', 'link_url', 'is_late']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['attendance_session_id']);
            $table->dropColumn([
                'attendance_session_id',
                'teacher_attachments',
                'submission_types',
                'allow_late',
                'allow_resubmit',
                'opens_at',
                'due_at',
            ]);
        });
    }
};
