<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_plans', function (Blueprint $table) {
            $table->foreignId('effective_course_id')->nullable()->after('career_id')->constrained('courses')->nullOnDelete();
            $table->foreignId('expires_course_id')->nullable()->after('effective_course_id')->constrained('courses')->nullOnDelete();
            $table->boolean('is_current')->default(false)->after('status');

            $table->index(['career_id', 'is_current', 'status']);
        });

        Schema::table('curriculum_plan_subject', function (Blueprint $table) {
            $table->foreignId('prerequisite_subject_id')->nullable()->after('subject_id')->constrained('subjects')->nullOnDelete();
            $table->unsignedSmallInteger('minimum_passing_grade')->default(60)->after('is_required');

            $table->index(['curriculum_plan_id', 'semester']);
            $table->index(['prerequisite_subject_id']);
        });

        Schema::create('subject_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prerequisite_subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedSmallInteger('minimum_grade')->default(60);
            $table->timestamps();

            $table->unique(['curriculum_plan_id', 'subject_id', 'prerequisite_subject_id']);
        });

        Schema::create('subject_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('career_id')->constrained()->restrictOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('curriculum_plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('professor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('semester')->nullable();
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->unsignedSmallInteger('reserved_seats')->default(0);
            $table->string('modality', 40)->default('presencial');
            $table->string('status', 30)->default('open');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'group_id', 'subject_id']);
            $table->index(['career_id', 'course_id', 'status']);
            $table->index(['group_id', 'semester', 'status']);
            $table->index(['subject_id', 'status']);
        });

        Schema::create('subject_offering_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_offering_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('classroom')->nullable();
            $table->timestamps();

            $table->index(['weekday', 'starts_at', 'ends_at']);
        });

        Schema::table('subject_enrollments', function (Blueprint $table) {
            $table->foreignId('subject_offering_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            $table->foreignId('curriculum_plan_id')->nullable()->after('subject_offering_id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('semester')->nullable()->after('group_id');

            $table->index(['subject_offering_id', 'status']);
            $table->index(['curriculum_plan_id', 'semester', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_offering_id');
            $table->dropConstrainedForeignId('curriculum_plan_id');
            $table->dropColumn('semester');
        });

        Schema::dropIfExists('subject_offering_schedules');
        Schema::dropIfExists('subject_offerings');
        Schema::dropIfExists('subject_prerequisites');

        Schema::table('curriculum_plan_subject', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prerequisite_subject_id');
            $table->dropColumn('minimum_passing_grade');
        });

        Schema::table('curriculum_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('effective_course_id');
            $table->dropConstrainedForeignId('expires_course_id');
            $table->dropColumn('is_current');
        });
    }
};
