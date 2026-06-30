<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('current_enrollment_id')->nullable()->index();
            $table->string('student_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('document_type', 30)->default('carnet');
            $table->string('document_number')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('exit_reason')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
