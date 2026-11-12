<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('grade_group', 20)->nullable()->default('tugas')->after('type');
            $table->unsignedInteger('grade_seq')->nullable()->after('grade_group');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['grade_group', 'grade_seq']);
        });
    }
};
