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
        Schema::table('learning_objectives', function (Blueprint $table) {
            $table->unique(['element_id', 'code'], 'learning_objectives_element_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_objectives', function (Blueprint $table) {
            $table->dropUnique('learning_objectives_element_code_unique');
        });
    }
};
