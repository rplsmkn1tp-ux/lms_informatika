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
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('status');
            }
            $table->string('status', 50)->default('draft')->change();
        });

        Schema::table('lesson_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_attachments', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('type');
            }
            if (!Schema::hasColumn('lesson_attachments', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('sort_order');
            }
        });

        Schema::table('lesson_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_progress', 'completed_items')) {
                $table->json('completed_items')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_progress', 'completed_items')) {
                $table->dropColumn('completed_items');
            }
        });

        Schema::table('lesson_attachments', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('lesson_attachments', 'file_size')) $cols[] = 'file_size';
            if (Schema::hasColumn('lesson_attachments', 'sort_order')) $cols[] = 'sort_order';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            $table->enum('status', ['draft', 'published'])->default('draft')->change();
        });
    }
};
