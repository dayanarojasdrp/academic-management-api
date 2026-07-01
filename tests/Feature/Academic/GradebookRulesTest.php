<?php

namespace Tests\Feature\Academic;

use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuildsAcademicScenarios;
use Tests\TestCase;

class GradebookRulesTest extends TestCase
{
    use BuildsAcademicScenarios;
    use RefreshDatabase;

    public function test_published_final_grade_marks_subject_enrollment_as_passed(): void
    {
        $scenario = $this->academicScenario();
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);
        $subjectEnrollment = $this->createSubjectEnrollment($enrollment, $scenario['offering']);
        $gradeSheet = $this->createGradeSheet($scenario['offering'], $scenario['scale']);
        $component = $this->createFinalComponent($scenario['offering']);

        Sanctum::actingAs($this->userWithPermissions(['grades.manage']));

        $response = $this->postJson('/api/grades', [
            'subject_enrollment_id' => $subjectEnrollment->id,
            'grade_sheet_id' => $gradeSheet->id,
            'grade_component_id' => $component->id,
            'raw_value' => 95,
            'is_final' => true,
            'evaluated_at' => '2026-12-15',
            'status' => 'published',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('subject_enrollments', [
            'id' => $subjectEnrollment->id,
            'status' => 'passed',
        ]);
    }

    public function test_published_final_grade_marks_subject_enrollment_as_failed(): void
    {
        $scenario = $this->academicScenario();
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);
        $subjectEnrollment = $this->createSubjectEnrollment($enrollment, $scenario['offering']);
        $gradeSheet = $this->createGradeSheet($scenario['offering'], $scenario['scale']);
        $component = $this->createFinalComponent($scenario['offering']);

        Sanctum::actingAs($this->userWithPermissions(['grades.manage']));

        $response = $this->postJson('/api/grades', [
            'subject_enrollment_id' => $subjectEnrollment->id,
            'grade_sheet_id' => $gradeSheet->id,
            'grade_component_id' => $component->id,
            'raw_value' => 45,
            'is_final' => true,
            'evaluated_at' => '2026-12-15',
            'status' => 'published',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('subject_enrollments', [
            'id' => $subjectEnrollment->id,
            'status' => 'failed',
        ]);
    }

    public function test_closed_grade_sheet_locks_published_grades_from_normal_changes(): void
    {
        $scenario = $this->academicScenario();
        $enrollment = $this->createEnrollment($scenario['student'], $scenario['course']);
        $subjectEnrollment = $this->createSubjectEnrollment($enrollment, $scenario['offering']);
        $gradeSheet = $this->createGradeSheet($scenario['offering'], $scenario['scale'], 'closed');
        $component = $this->createFinalComponent($scenario['offering']);

        $grade = Grade::create([
            'student_id' => $scenario['student']->id,
            'subject_enrollment_id' => $subjectEnrollment->id,
            'subject_id' => $scenario['subject']->id,
            'professor_id' => $scenario['professor']->id,
            'grade_sheet_id' => $gradeSheet->id,
            'grade_component_id' => $component->id,
            'grading_scale_id' => $scenario['scale']->id,
            'value' => 90,
            'raw_value' => 90,
            'normalized_value' => 90,
            'weight' => 100,
            'evaluation_type' => 'final',
            'attempt_type' => 'ordinary',
            'call_number' => 1,
            'is_final' => true,
            'evaluated_at' => '2026-12-15',
            'published_at' => now(),
            'signed_at' => now(),
            'locked_at' => now(),
            'status' => 'published',
        ]);

        Sanctum::actingAs($this->userWithPermissions(['grades.manage']));

        $response = $this->patchJson('/api/grades/'.$grade->id, [
            'subject_enrollment_id' => $subjectEnrollment->id,
            'grade_sheet_id' => $gradeSheet->id,
            'grade_component_id' => $component->id,
            'raw_value' => 70,
            'is_final' => true,
            'evaluated_at' => '2026-12-15',
            'status' => 'published',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'value' => 90,
        ]);
    }
}
