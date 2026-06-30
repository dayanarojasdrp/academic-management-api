<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('professor_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            $table->string('status', 30)->default('active')->after('password');
            $table->timestamp('last_login_at')->nullable()->after('status');

            $table->index(['status', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
            $table->dropConstrainedForeignId('professor_id');
            $table->dropColumn(['status', 'last_login_at']);
        });
    }
};
