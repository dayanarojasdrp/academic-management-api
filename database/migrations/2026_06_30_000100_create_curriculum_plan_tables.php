<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('version', 30)->nullable();
            $table->unsignedSmallInteger('duration_semesters')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['career_id', 'name', 'version']);
            $table->index(['career_id', 'status']);
        });

        Schema::create('curriculum_plan_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('semester')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['curriculum_plan_id', 'subject_id']);
            $table->index(['subject_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_plan_subject');
        Schema::dropIfExists('curriculum_plans');
    }
};
