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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nisn', 10)->nullable()->unique()->after('email');
            $table->string('nip_nuptk')->nullable()->after('nisn');
            $table->string('subject')->nullable()->after('nip_nuptk');
            $table->enum('role', ['admin', 'guru', 'siswa'])->default('siswa')->after('password');
            $table->enum('status', ['pending', 'active', 'rejected', 'inactive'])->default('pending')->after('role');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('approved_by');
            $table->timestamp('last_login_at')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['nisn', 'nip_nuptk', 'subject', 'role', 'status', 'approved_at', 'approved_by', 'rejection_reason', 'last_login_at']);
        });
    }
};
