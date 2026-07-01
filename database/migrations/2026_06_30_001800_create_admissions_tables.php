<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('career_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant_code', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('document_type', 30);
            $table->string('document_number', 80);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->date('application_date')->nullable();
            $table->string('source', 80)->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['document_type', 'document_number']);
            $table->index(['career_id', 'course_id', 'status']);
            $table->index(['institution_id', 'campus_id', 'status']);
        });

        Schema::create('application_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('name');
            $table->string('file_path')->nullable();
            $table->string('file_hash', 128)->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'type']);
            $table->index(['type', 'status']);
        });

        Schema::create('admission_interviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('result', 30)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['applicant_id', 'result']);
            $table->index('scheduled_at');
        });

        Schema::create('admission_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 30);
            $table->date('decision_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->text('reason')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index(['applicant_id', 'decision']);
            $table->index('decision_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_decisions');
        Schema::dropIfExists('admission_interviews');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applicants');
    }
};
