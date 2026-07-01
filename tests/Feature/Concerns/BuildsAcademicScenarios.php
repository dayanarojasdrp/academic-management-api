<?php

namespace Tests\Feature\Concerns;

use App\Models\Campus;
use App\Models\Career;
use App\Models\Course;
use App\Models\CurriculumPlan;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\FinancialConcept;
use App\Models\GradeComponent;
use App\Models\GradeSheet;
use App\Models\GradingScale;
use App\Models\GradingScaleLevel;
use App\Models\Group;
use App\Models\Institution;
use App\Models\Modality;
use App\Models\Permission;
use App\Models\Professor;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentCharge;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectOffering;
use App\Models\SubjectOfferingSchedule;
use App\Models\User;

trait BuildsAcademicScenarios
{
    protected function userWithPermissions(array $permissions, array $roles = ['test_admin']): User
    {
        $permissionModels = collect($permissions)->mapWithKeys(fn (string $permission) => [
            $permission => Permission::firstOrCreate(
                ['code' => $permission],
                ['name' => $permission, 'module' => str($permission)->before('.')->toString()]
            ),
        ]);

        $roleModels = collect($roles)->map(function (string $roleCode) use ($permissionModels): Role {
            $role = Role::firstOrCreate(
                ['code' => $roleCode],
                ['name' => str($roleCode)->replace('_', ' ')->title()->toString(), 'is_system' => false]
            );
            $role->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));

            return $role;
        });

        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->sync($roleModels->pluck('id'));

        return $user->load('roles.permissions');
    }

    protected function academicScenario(array $overrides = []): array
    {
        $institution = Institution::create([
            'code' => $overrides['institution_code'] ?? 'INST',
            'name' => 'Institucion Demo',
            'country' => 'Cuba',
            'timezone' => 'America/Havana',
            'status' => 'active',
        ]);

        $campus = Campus::create([
            'institution_id' => $institution->id,
            'code' => 'MAIN',
            'name' => 'Sede Central',
            'status' => 'active',
        ]);

        $faculty = Faculty::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'code' => 'FING',
            'name' => 'Facultad de Ingenieria',
            'status' => 'active',
        ]);

        $department = Department::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'code' => 'DSW',
            'name' => 'Departamento de Software',
            'status' => 'active',
        ]);

        $modality = Modality::create([
            'institution_id' => $institution->id,
            'code' => 'PRESENCIAL',
            'name' => 'Presencial',
            'requires_classroom' => true,
            'requires_online_platform' => false,
            'status' => 'active',
        ]);

        $career = Career::create([
            'institution_id' => $institution->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'modality_id' => $modality->id,
            'name' => 'Ingenieria Informatica',
            'abbreviation' => 'INF',
        ]);

        $course = Course::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'name' => 'Curso 2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'status' => 'active',
        ]);

        $group = Group::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'modality_id' => $modality->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'name' => $overrides['group_name'] ?? 'INF-1A',
            'shift' => 'diurno',
            'status' => 'active',
        ]);

        $student = Student::create([
            'group_id' => $group->id,
            'student_code' => $overrides['student_code'] ?? 'EST-0001',
            'first_name' => 'Ana',
            'last_name' => 'Perez',
            'document_type' => 'carnet',
            'document_number' => $overrides['document_number'] ?? '00010112345',
            'email' => $overrides['student_email'] ?? 'ana@example.edu',
            'admission_date' => '2026-09-01',
            'status' => 'active',
        ]);

        $subject = Subject::create([
            'code' => $overrides['subject_code'] ?? 'PRG-101',
            'name' => 'Programacion I',
            'credits' => 5,
            'weekly_hours' => 8,
        ]);

        $professor = Professor::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'subject_id' => $subject->id,
            'professor_code' => $overrides['professor_code'] ?? 'PROF-0001',
            'first_name' => 'Carlos',
            'last_name' => 'Rodriguez',
            'email' => $overrides['professor_email'] ?? 'carlos@example.edu',
            'status' => 'active',
        ]);

        $plan = CurriculumPlan::create([
            'career_id' => $career->id,
            'effective_course_id' => $course->id,
            'name' => 'Plan Regular',
            'version' => '2026',
            'duration_semesters' => 10,
            'status' => 'active',
            'is_current' => true,
        ]);

        $plan->subjects()->sync([
            $subject->id => ['semester' => 1, 'is_required' => true, 'minimum_passing_grade' => 60],
        ]);

        $offering = $this->createOffering(compact(
            'institution',
            'campus',
            'faculty',
            'department',
            'modality',
            'course',
            'career',
            'group',
            'plan',
            'subject',
            'professor'
        ), $overrides['offering'] ?? []);

        $concept = FinancialConcept::create([
            'code' => $overrides['concept_code'] ?? 'ENROLLMENT_FEE',
            'name' => 'Derecho de matricula',
            'type' => 'enrollment',
            'default_amount' => 250,
            'currency' => 'USD',
            'is_required_for_enrollment' => true,
            'is_active' => true,
        ]);

        $scale = $this->createGradingScale();

        return compact(
            'institution',
            'campus',
            'faculty',
            'department',
            'modality',
            'career',
            'course',
            'group',
            'student',
            'subject',
            'professor',
            'plan',
            'offering',
            'concept',
            'scale'
        );
    }

    protected function createPaidCharge(Student $student, Course $course, FinancialConcept $concept): StudentCharge
    {
        return StudentCharge::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'financial_concept_id' => $concept->id,
            'original_amount' => 250,
            'adjustment_amount' => 0,
            'paid_amount' => 250,
            'balance_amount' => 0,
            'currency' => 'USD',
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-30',
            'status' => 'paid',
        ]);
    }

    protected function createUnpaidCharge(Student $student, Course $course, FinancialConcept $concept): StudentCharge
    {
        return StudentCharge::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'financial_concept_id' => $concept->id,
            'original_amount' => 250,
            'adjustment_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 250,
            'currency' => 'USD',
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-30',
            'status' => 'pending',
        ]);
    }

    protected function createEnrollment(Student $student, Course $course, string $status = 'active'): Enrollment
    {
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'start_course_id' => $course->id,
            'enrollment_date' => '2026-09-01',
            'status' => $status,
        ]);

        $student->update(['current_enrollment_id' => $enrollment->id]);

        return $enrollment;
    }

    protected function createOffering(array $scenario, array $overrides = []): SubjectOffering
    {
        $offering = SubjectOffering::create([
            'institution_id' => $scenario['institution']->id,
            'campus_id' => $scenario['campus']->id,
            'faculty_id' => $scenario['faculty']->id,
            'department_id' => $scenario['department']->id,
            'modality_id' => $scenario['modality']->id,
            'course_id' => $scenario['course']->id,
            'career_id' => $scenario['career']->id,
            'group_id' => $scenario['group']->id,
            'curriculum_plan_id' => $scenario['plan']->id,
            'subject_id' => $scenario['subject']->id,
            'professor_id' => $scenario['professor']->id,
            'semester' => $overrides['semester'] ?? 1,
            'capacity' => $overrides['capacity'] ?? 30,
            'reserved_seats' => 0,
            'modality' => 'presencial',
            'status' => $overrides['status'] ?? 'open',
            'starts_at' => '2026-09-01',
            'ends_at' => '2026-12-20',
        ]);

        if (($overrides['with_schedule'] ?? true) === true) {
            SubjectOfferingSchedule::create([
                'subject_offering_id' => $offering->id,
                'weekday' => $overrides['weekday'] ?? 1,
                'starts_at' => $overrides['starts_at'] ?? '08:00',
                'ends_at' => $overrides['ends_at'] ?? '10:00',
                'classroom' => $overrides['classroom'] ?? 'Lab 1',
            ]);
        }

        return $offering;
    }

    protected function createSubjectEnrollment(Enrollment $enrollment, SubjectOffering $offering, string $status = 'enrolled'): SubjectEnrollment
    {
        return SubjectEnrollment::create([
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'subject_id' => $offering->subject_id,
            'subject_offering_id' => $offering->id,
            'curriculum_plan_id' => $offering->curriculum_plan_id,
            'course_id' => $offering->course_id,
            'career_id' => $offering->career_id,
            'group_id' => $offering->group_id,
            'semester' => $offering->semester,
            'enrolled_at' => '2026-09-01',
            'status' => $status,
        ]);
    }

    protected function createGradingScale(): GradingScale
    {
        $scale = GradingScale::create([
            'code' => 'LATAM-100',
            'name' => 'Escala 0-100',
            'min_value' => 0,
            'max_value' => 100,
            'passing_value' => 60,
            'decimal_places' => 2,
            'is_default' => true,
            'status' => 'active',
        ]);

        collect([
            ['F', 'Reprobado', 0, 59.99, false],
            ['A', 'Aprobado', 60, 100, true],
        ])->each(fn (array $level) => GradingScaleLevel::create([
            'grading_scale_id' => $scale->id,
            'code' => $level[0],
            'label' => $level[1],
            'min_value' => $level[2],
            'max_value' => $level[3],
            'is_passing' => $level[4],
        ]));

        return $scale;
    }

    protected function createGradeSheet(SubjectOffering $offering, GradingScale $scale, string $status = 'draft'): GradeSheet
    {
        return GradeSheet::create([
            'subject_offering_id' => $offering->id,
            'professor_id' => $offering->professor_id,
            'grading_scale_id' => $scale->id,
            'course_id' => $offering->course_id,
            'career_id' => $offering->career_id,
            'group_id' => $offering->group_id,
            'subject_id' => $offering->subject_id,
            'sheet_type' => 'ordinary',
            'call_number' => 1,
            'status' => $status,
            'opened_at' => '2026-12-01',
            'submitted_at' => in_array($status, ['submitted', 'signed', 'closed'], true) ? now() : null,
            'signed_at' => in_array($status, ['signed', 'closed'], true) ? now() : null,
            'closed_at' => $status === 'closed' ? now() : null,
        ]);
    }

    protected function createFinalComponent(SubjectOffering $offering): GradeComponent
    {
        return GradeComponent::create([
            'subject_offering_id' => $offering->id,
            'code' => 'FINAL',
            'name' => 'Evaluacion final',
            'type' => 'final',
            'term' => 'final',
            'weight' => 100,
            'max_score' => 100,
            'is_required' => true,
            'due_date' => '2026-12-15',
            'status' => 'active',
        ]);
    }
}
