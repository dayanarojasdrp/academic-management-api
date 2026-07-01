<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('certificate_code', 60)->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->string('verification_code', 100)->unique();
            $table->string('file_path')->nullable();
            $table->string('status', 30)->default('generated');
            $table->json('snapshot_data');
            $table->timestamps();

            $table->index(['student_id', 'type', 'status']);
            $table->index(['course_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
