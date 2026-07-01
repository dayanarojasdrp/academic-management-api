<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_identifier', 80)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('timezone', 80)->default('America/Havana');
            $table->string('status', 30)->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['status', 'country']);
        });

        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('address')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'status']);
        });

        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'campus_id', 'status']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
            $table->index(['faculty_id', 'status']);
            $table->index(['campus_id', 'status']);
        });

        Schema::create('modalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('requires_classroom')->default(true);
            $table->boolean('requires_online_platform')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'status']);
        });

        Schema::table('careers', function (Blueprint $table) {
            $table->dropUnique('careers_name_unique');
            $table->dropUnique('careers_abbreviation_unique');
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
            $table->foreignId('modality_id')->nullable()->after('department_id')->constrained()->nullOnDelete();

            $table->unique(['institution_id', 'name'], 'careers_institution_name_unique');
            $table->unique(['institution_id', 'abbreviation'], 'careers_institution_abbreviation_unique');
            $table->index(['institution_id', 'faculty_id']);
            $table->index(['department_id', 'modality_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();

            $table->index(['institution_id', 'status']);
            $table->index(['campus_id', 'status']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique('groups_course_id_career_id_name_unique');
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->after('campus_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
            $table->foreignId('modality_id')->nullable()->after('department_id')->constrained()->nullOnDelete();

            $table->unique(['institution_id', 'campus_id', 'course_id', 'career_id', 'name'], 'groups_institution_campus_course_career_name_unique');
            $table->index(['institution_id', 'campus_id', 'status']);
            $table->index(['faculty_id', 'department_id', 'status']);
        });

        Schema::table('professors', function (Blueprint $table) {
            $table->dropUnique('professors_professor_code_unique');
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->after('campus_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();

            $table->unique(['institution_id', 'professor_code'], 'professors_institution_code_unique');
            $table->index(['institution_id', 'status']);
            $table->index(['department_id', 'status']);
        });

        Schema::table('subject_offerings', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->after('campus_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
            $table->foreignId('modality_id')->nullable()->after('department_id')->constrained()->nullOnDelete();

            $table->index(['institution_id', 'campus_id', 'status']);
            $table->index(['faculty_id', 'department_id', 'status']);
            $table->index(['modality_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();

            $table->index(['institution_id', 'status']);
            $table->index(['campus_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campus_id');
            $table->dropConstrainedForeignId('institution_id');
        });

        Schema::table('subject_offerings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('modality_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('campus_id');
            $table->dropConstrainedForeignId('institution_id');
        });

        Schema::table('professors', function (Blueprint $table) {
            $table->dropUnique('professors_institution_code_unique');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('campus_id');
            $table->dropConstrainedForeignId('institution_id');
            $table->unique('professor_code');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique('groups_institution_campus_course_career_name_unique');
            $table->dropConstrainedForeignId('modality_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('campus_id');
            $table->dropConstrainedForeignId('institution_id');
            $table->unique(['course_id', 'career_id', 'name']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campus_id');
            $table->dropConstrainedForeignId('institution_id');
        });

        Schema::table('careers', function (Blueprint $table) {
            $table->dropUnique('careers_institution_abbreviation_unique');
            $table->dropUnique('careers_institution_name_unique');
            $table->dropConstrainedForeignId('modality_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('institution_id');
            $table->unique('name');
            $table->unique('abbreviation');
        });

        Schema::dropIfExists('modalities');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('campuses');
        Schema::dropIfExists('institutions');
    }
};
