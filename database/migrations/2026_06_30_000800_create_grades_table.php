<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('professor_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('value', 5, 2)->nullable();
            $table->string('evaluation_type', 50)->nullable();
            $table->date('evaluated_at')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'evaluation_type']);
            $table->index(['subject_enrollment_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['professor_id', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
