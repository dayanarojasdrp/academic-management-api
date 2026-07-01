<?php

namespace Tests\Feature\Academic;

use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuildsAcademicScenarios;
use Tests\TestCase;

class SubjectEnrollmentRulesTest extends TestCase
{
    use BuildsAcademicScenarios;
    use RefreshDatabase;

    public function test_subject_enrollment_rejects_subject_outside_current_curriculum_plan(): void
    {
        $scenario = $this->academicScenario(['offering' => ['with_schedule' => false]]);
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);

        $outsideSubject = Subject::create([
            'code' => 'HIS-404',
            'name' => 'Historia fuera del plan',
            'credits' => 3,
        ]);
        $outsideScenario = array_merge($scenario, ['subject' => $outsideSubject]);
        $outsideOffering = $this->createOffering($outsideScenario, ['with_schedule' => false]);

        Sanctum::actingAs($this->userWithPermissions(['subject_enrollments.manage']));

        $response = $this->postJson('/api/subject-enrollments', [
            'enrollment_id' => $enrollment->id,
            'subject_offering_id' => $outsideOffering->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['subject_id']);
    }

    public function test_subject_enrollment_rejects_full_subject_offering_capacity(): void
    {
        $scenario = $this->academicScenario(['offering' => ['capacity' => 1, 'with_schedule' => false]]);
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);

        $otherStudent = Student::create([
            'group_id' => $scenario['group']->id,
            'student_code' => 'EST-0002',
            'first_name' => 'Luis',
            'last_name' => 'Gomez',
            'document_type' => 'carnet',
            'document_number' => '00020254321',
            'email' => 'luis@example.edu',
            'admission_date' => '2026-09-01',
            'status' => 'active',
        ]);
        $otherEnrollment = $this->createEnrollment($otherStudent, $scenario['course']);
        $this->createSubjectEnrollment($otherEnrollment, $scenario['offering']);

        Sanctum::actingAs($this->userWithPermissions(['subject_enrollments.manage']));

        $response = $this->postJson('/api/subject-enrollments', [
            'enrollment_id' => $enrollment->id,
            'subject_offering_id' => $scenario['offering']->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['subject_offering_id']);
    }

    public function test_subject_enrollment_rejects_schedule_conflicts(): void
    {
        $scenario = $this->academicScenario();
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);
        $this->createSubjectEnrollment($enrollment, $scenario['offering']);

        $secondSubject = Subject::create([
            'code' => 'MAT-101',
            'name' => 'Matematica I',
            'credits' => 4,
        ]);
        $scenario['plan']->subjects()->attach($secondSubject->id, [
            'semester' => 1,
            'is_required' => true,
            'minimum_passing_grade' => 60,
        ]);
        $secondOffering = $this->createOffering(
            array_merge($scenario, ['subject' => $secondSubject]),
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '11:00']
        );

        Sanctum::actingAs($this->userWithPermissions(['subject_enrollments.manage']));

        $response = $this->postJson('/api/subject-enrollments', [
            'enrollment_id' => $enrollment->id,
            'subject_offering_id' => $secondOffering->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['subject_offering_id']);
    }
}
