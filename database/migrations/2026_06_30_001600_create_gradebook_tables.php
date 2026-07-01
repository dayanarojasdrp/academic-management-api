<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->decimal('min_value', 6, 2)->default(0);
            $table->decimal('max_value', 6, 2)->default(100);
            $table->decimal('passing_value', 6, 2)->default(60);
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_default')->default(false);
            $table->string('status', 30)->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_default']);
        });

        Schema::create('grading_scale_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scale_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('label');
            $table->decimal('min_value', 6, 2);
            $table->decimal('max_value', 6, 2);
            $table->decimal('grade_points', 5, 2)->nullable();
            $table->boolean('is_passing')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['grading_scale_id', 'code']);
            $table->index(['grading_scale_id', 'min_value', 'max_value']);
        });

        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_offering_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('type', 40)->default('partial');
            $table->string('term', 40)->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('max_score', 6, 2)->default(100);
            $table->boolean('is_required')->default(true);
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['subject_offering_id', 'code']);
            $table->index(['subject_offering_id', 'status']);
        });

        Schema::create('grade_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('professor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grading_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('career_id')->constrained()->restrictOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('sheet_type', 40)->default('ordinary');
            $table->unsignedSmallInteger('call_number')->default(1);
            $table->unsignedSmallInteger('partial_number')->nullable();
            $table->string('status', 30)->default('draft');
            $table->date('opened_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signature_hash', 128)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['subject_offering_id', 'sheet_type', 'call_number', 'partial_number'], 'grade_sheets_offering_type_call_partial_unique');
            $table->index(['course_id', 'career_id', 'status']);
            $table->index(['professor_id', 'status']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'subject_id', 'evaluation_type']);
            $table->foreignId('grade_sheet_id')->nullable()->after('professor_id')->constrained()->nullOnDelete();
            $table->foreignId('grade_component_id')->nullable()->after('grade_sheet_id')->constrained()->nullOnDelete();
            $table->foreignId('grading_scale_id')->nullable()->after('grade_component_id')->constrained()->nullOnDelete();
            $table->foreignId('grading_scale_level_id')->nullable()->after('grading_scale_id')->constrained()->nullOnDelete();
            $table->decimal('raw_value', 6, 2)->nullable()->after('value');
            $table->decimal('normalized_value', 6, 2)->nullable()->after('raw_value');
            $table->decimal('weight', 5, 2)->default(0)->after('normalized_value');
            $table->string('attempt_type', 40)->default('ordinary')->after('evaluation_type');
            $table->unsignedSmallInteger('call_number')->default(1)->after('attempt_type');
            $table->unsignedSmallInteger('partial_number')->nullable()->after('call_number');
            $table->boolean('is_final')->default(false)->after('partial_number');
            $table->timestamp('published_at')->nullable()->after('evaluated_at');
            $table->timestamp('signed_at')->nullable()->after('published_at');
            $table->timestamp('locked_at')->nullable()->after('signed_at');
            $table->foreignId('change_authorized_by_user_id')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
            $table->text('change_reason')->nullable()->after('change_authorized_by_user_id');

            $table->unique(['subject_enrollment_id', 'grade_component_id', 'attempt_type', 'call_number'], 'grades_enrollment_component_attempt_call_unique');
            $table->index(['grade_sheet_id', 'status']);
            $table->index(['grade_component_id', 'status']);
            $table->index(['student_id', 'attempt_type', 'call_number', 'status'], 'grades_student_attempt_call_status_index');
        });

        Schema::create('grade_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('current_value', 6, 2)->nullable();
            $table->decimal('requested_value', 6, 2)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('reason');
            $table->text('decision_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['grade_id', 'status']);
            $table->index(['requested_by_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_change_requests');

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_student_attempt_call_status_index');
            $table->dropIndex(['grade_component_id', 'status']);
            $table->dropIndex(['grade_sheet_id', 'status']);
            $table->dropUnique('grades_enrollment_component_attempt_call_unique');
            $table->dropForeign(['change_authorized_by_user_id']);
            $table->dropForeign(['grading_scale_level_id']);
            $table->dropForeign(['grading_scale_id']);
            $table->dropForeign(['grade_component_id']);
            $table->dropForeign(['grade_sheet_id']);
            $table->dropColumn([
                'change_reason',
                'change_authorized_by_user_id',
                'locked_at',
                'signed_at',
                'published_at',
                'is_final',
                'partial_number',
                'call_number',
                'attempt_type',
                'weight',
                'normalized_value',
                'raw_value',
                'grading_scale_level_id',
                'grading_scale_id',
                'grade_component_id',
                'grade_sheet_id',
            ]);
            $table->unique(['student_id', 'subject_id', 'evaluation_type']);
        });

        Schema::dropIfExists('grade_sheets');
        Schema::dropIfExists('grade_components');
        Schema::dropIfExists('grading_scale_levels');
        Schema::dropIfExists('grading_scales');
    }
};
