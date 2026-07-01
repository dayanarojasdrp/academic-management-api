<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            $table->index(['student_id', 'course_id', 'semester', 'status'], 'subject_enrollments_student_course_semester_status_index');
            $table->index(['student_id', 'career_id', 'status'], 'subject_enrollments_student_career_status_index');
            $table->index(['student_id', 'curriculum_plan_id', 'status'], 'subject_enrollments_student_plan_status_index');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->index(['student_id', 'evaluated_at', 'status'], 'grades_student_evaluated_status_index');
            $table->index(['student_id', 'subject_id', 'status'], 'grades_student_subject_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_student_subject_status_index');
            $table->dropIndex('grades_student_evaluated_status_index');
        });

        Schema::table('subject_enrollments', function (Blueprint $table) {
            $table->dropIndex('subject_enrollments_student_plan_status_index');
            $table->dropIndex('subject_enrollments_student_career_status_index');
            $table->dropIndex('subject_enrollments_student_course_semester_status_index');
        });
    }
};
