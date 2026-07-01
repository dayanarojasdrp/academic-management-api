<?php

namespace Tests\Feature\Academic;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuildsAcademicScenarios;
use Tests\TestCase;

class EnrollmentPaymentTest extends TestCase
{
    use BuildsAcademicScenarios;
    use RefreshDatabase;

    public function test_enrollment_is_blocked_when_student_has_required_debt(): void
    {
        $scenario = $this->academicScenario();
        $this->createUnpaidCharge($scenario['student'], $scenario['course'], $scenario['concept']);

        Sanctum::actingAs($this->userWithPermissions(['enrollments.create']));

        $response = $this->postJson('/api/enrollments', [
            'student_id' => $scenario['student']->id,
            'start_course_id' => $scenario['course']->id,
            'enrollment_date' => '2026-09-01',
            'status' => 'active',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['student_id']);

        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $scenario['student']->id,
            'start_course_id' => $scenario['course']->id,
        ]);
    }

    public function test_enrollment_is_allowed_when_student_has_financial_clearance(): void
    {
        $scenario = $this->academicScenario();
        $this->createPaidCharge($scenario['student'], $scenario['course'], $scenario['concept']);

        Sanctum::actingAs($this->userWithPermissions(['enrollments.create']));

        $response = $this->postJson('/api/enrollments', [
            'student_id' => $scenario['student']->id,
            'start_course_id' => $scenario['course']->id,
            'enrollment_date' => '2026-09-01',
            'status' => 'active',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.student_id', $scenario['student']->id)
            ->assertJsonPath('data.start_course_id', $scenario['course']->id);

        $this->assertDatabaseHas('students', [
            'id' => $scenario['student']->id,
            'current_enrollment_id' => $response->json('data.id'),
        ]);
    }
}
