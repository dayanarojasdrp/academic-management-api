<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Campus;
use App\Models\AdmissionDecision;
use App\Models\AdmissionInterview;
use App\Models\Applicant;
use App\Models\ApplicationDocument;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CurriculumPlan;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Finance;
use App\Models\FinancialConcept;
use App\Models\Grade;
use App\Models\GradeAuditLog;
use App\Models\GradeComponent;
use App\Models\GradeSheet;
use App\Models\Group;
use App\Models\GradingScale;
use App\Models\GradingScaleLevel;
use App\Models\Institution;
use App\Models\Modality;
use App\Models\PaymentAllocation;
use App\Models\PaymentReceipt;
use App\Models\Professor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentCharge;
use App\Models\StudentPayment;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\SubjectOffering;
use App\Models\SubjectOfferingSchedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->resetDemoData();

        $permissions = $this->seedPermissions();
        $roles = $this->seedRoles($permissions);

        $institution = Institution::create([
            'code' => 'CME',
            'name' => 'Centro Misionero Escambray',
            'legal_name' => 'Centro Misionero Escambray',
            'country' => 'Cuba',
            'timezone' => 'America/Havana',
            'status' => 'active',
        ]);

        $campus = Campus::create([
            'institution_id' => $institution->id,
            'code' => 'MAIN',
            'name' => 'Sede Central',
            'city' => 'La Habana',
            'country' => 'Cuba',
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
            'faculty_id' => $faculty->id,
            'campus_id' => $campus->id,
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
            'description' => 'Carrera orientada al desarrollo de software y sistemas de informacion.',
        ]);

        $course = Course::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'name' => 'Curso 2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'status' => 'active',
        ]);

        $subjects = collect([
            ['code' => 'MAT-101', 'name' => 'Matematica I', 'credits' => 4, 'weekly_hours' => 6],
            ['code' => 'PRG-101', 'name' => 'Programacion I', 'credits' => 5, 'weekly_hours' => 8],
        ])->map(fn (array $data) => Subject::create($data));

        $plan = CurriculumPlan::create([
            'career_id' => $career->id,
            'effective_course_id' => $course->id,
            'name' => 'Plan Regular',
            'version' => '2026',
            'duration_semesters' => 10,
            'status' => 'active',
            'is_current' => true,
        ]);

        $plan->subjects()->sync($subjects->mapWithKeys(fn (Subject $subject, int $index) => [
            $subject->id => ['semester' => 1, 'is_required' => true],
        ]));

        $group = Group::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'modality_id' => $modality->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'name' => 'INF-1A',
            'shift' => 'diurno',
            'status' => 'active',
        ]);

        $professor = Professor::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'subject_id' => $subjects->last()->id,
            'professor_code' => 'PROF-0001',
            'first_name' => 'Carlos',
            'last_name' => 'Rodriguez',
            'email' => 'carlos.rodriguez@example.edu',
            'status' => 'active',
        ]);

        $offering = SubjectOffering::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'modality_id' => $modality->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'group_id' => $group->id,
            'curriculum_plan_id' => $plan->id,
            'subject_id' => $subjects->last()->id,
            'professor_id' => $professor->id,
            'semester' => 1,
            'capacity' => 30,
            'reserved_seats' => 0,
            'modality' => 'presencial',
            'status' => 'open',
            'starts_at' => '2026-09-01',
            'ends_at' => '2026-12-20',
        ]);

        SubjectOfferingSchedule::create([
            'subject_offering_id' => $offering->id,
            'weekday' => 1,
            'starts_at' => '08:00',
            'ends_at' => '10:00',
            'classroom' => 'Lab 1',
        ]);

        $scale = GradingScale::create([
            'code' => 'LATAM-100',
            'name' => 'Escala institucional 0-100',
            'min_value' => 0,
            'max_value' => 100,
            'passing_value' => 60,
            'decimal_places' => 2,
            'is_default' => true,
            'status' => 'active',
            'description' => 'Escala numerica general para instituciones de educacion superior latinoamericanas.',
        ]);

        collect([
            ['F', 'Reprobado', 0, 59.99, 0, false, 1],
            ['D', 'Suficiente', 60, 69.99, 1, true, 2],
            ['C', 'Bueno', 70, 79.99, 2, true, 3],
            ['B', 'Muy bueno', 80, 89.99, 3, true, 4],
            ['A', 'Excelente', 90, 100, 4, true, 5],
        ])->each(fn (array $level) => GradingScaleLevel::create([
            'grading_scale_id' => $scale->id,
            'code' => $level[0],
            'label' => $level[1],
            'min_value' => $level[2],
            'max_value' => $level[3],
            'grade_points' => $level[4],
            'is_passing' => $level[5],
            'sort_order' => $level[6],
        ]));

        $finalComponent = GradeComponent::create([
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
            'sort_order' => 1,
        ]);

        $student = Student::create([
            'group_id' => $group->id,
            'student_code' => 'EST-0001',
            'first_name' => 'Ana',
            'last_name' => 'Perez Gomez',
            'document_type' => 'carnet',
            'document_number' => '00010112345',
            'email' => 'ana.perez@example.edu',
            'admission_date' => '2026-09-01',
            'status' => 'active',
        ]);

        $gradeSheet = GradeSheet::create([
            'subject_offering_id' => $offering->id,
            'professor_id' => $professor->id,
            'grading_scale_id' => $scale->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'group_id' => $group->id,
            'subject_id' => $subjects->last()->id,
            'sheet_type' => 'ordinary',
            'call_number' => 1,
            'status' => 'draft',
            'opened_at' => '2026-12-01',
        ]);

        $enrollmentConcept = FinancialConcept::create([
            'code' => 'ENROLLMENT_FEE',
            'name' => 'Derecho de matricula',
            'type' => 'enrollment',
            'default_amount' => 250.00,
            'currency' => 'USD',
            'is_required_for_enrollment' => true,
            'is_active' => true,
            'description' => 'Cargo obligatorio para habilitar la matricula academica.',
        ]);

        $charge = StudentCharge::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'financial_concept_id' => $enrollmentConcept->id,
            'original_amount' => 250.00,
            'adjustment_amount' => 0,
            'paid_amount' => 250.00,
            'balance_amount' => 0,
            'currency' => 'USD',
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-30',
            'status' => 'paid',
        ]);

        $payment = StudentPayment::create([
            'student_id' => $student->id,
            'amount' => 250.00,
            'unallocated_amount' => 0,
            'currency' => 'USD',
            'payment_method' => 'manual',
            'payment_reference' => 'PAY-LEDGER-DEMO-0001',
            'paid_at' => '2026-08-25',
            'status' => 'confirmed',
        ]);

        PaymentAllocation::create([
            'student_payment_id' => $payment->id,
            'student_charge_id' => $charge->id,
            'amount' => 250.00,
        ]);

        PaymentReceipt::create([
            'student_payment_id' => $payment->id,
            'receipt_number' => 'RCPT-DEMO-0001',
            'issued_at' => '2026-08-25',
            'status' => 'issued',
        ]);

        $finance = Finance::create([
            'student_id' => $student->id,
            'amount' => 250.00,
            'currency' => 'USD',
            'concept' => 'enrollment',
            'payment_method' => 'manual',
            'payment_reference' => 'PAY-DEMO-0001',
            'paid_at' => '2026-08-25',
            'status' => 'paid',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'start_course_id' => $course->id,
            'enrollment_date' => '2026-09-01',
            'status' => 'active',
        ]);

        $finance->update(['enrollment_id' => $enrollment->id]);
        $charge->update(['enrollment_id' => $enrollment->id]);
        $payment->update(['enrollment_id' => $enrollment->id]);
        $student->update(['current_enrollment_id' => $enrollment->id]);

        $subjectEnrollment = SubjectEnrollment::create([
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'subject_id' => $subjects->last()->id,
            'subject_offering_id' => $offering->id,
            'curriculum_plan_id' => $plan->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'group_id' => $group->id,
            'semester' => 1,
            'enrolled_at' => '2026-09-01',
            'status' => 'enrolled',
        ]);

        $grade = Grade::create([
            'subject_enrollment_id' => $subjectEnrollment->id,
            'student_id' => $student->id,
            'subject_id' => $subjects->last()->id,
            'professor_id' => $professor->id,
            'grade_sheet_id' => $gradeSheet->id,
            'grade_component_id' => $finalComponent->id,
            'grading_scale_id' => $scale->id,
            'grading_scale_level_id' => $scale->levels()->where('code', 'A')->value('id'),
            'value' => 95,
            'raw_value' => 95,
            'normalized_value' => 95,
            'weight' => 100,
            'evaluation_type' => 'final',
            'attempt_type' => 'ordinary',
            'call_number' => 1,
            'is_final' => true,
            'evaluated_at' => '2026-12-15',
            'published_at' => '2026-12-15 10:00:00',
            'status' => 'published',
        ]);

        GradeAuditLog::create([
            'grade_id' => $grade->id,
            'old_grade' => null,
            'new_grade' => 95,
            'old_status' => null,
            'new_status' => 'published',
            'reason' => 'Carga inicial de nota demo.',
            'changed_at' => '2026-12-15 10:00:00',
        ]);

        Certificate::create([
            'certificate_code' => 'CERT-20261215-00001',
            'student_id' => $student->id,
            'type' => 'active_student_certificate',
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'generated_at' => '2026-12-15 11:00:00',
            'verification_code' => 'CERTVERIFYDEMO000000000001',
            'status' => 'generated',
            'snapshot_data' => [
                'student' => [
                    'student_code' => $student->student_code,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'status' => $student->status,
                ],
                'career' => ['name' => $career->name, 'abbreviation' => $career->abbreviation],
                'course' => ['name' => $course->name],
                'issued_at' => '2026-12-15T11:00:00Z',
            ],
        ]);

        $applicant = Applicant::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'career_id' => $career->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'applicant_code' => 'ASP-2026-0001',
            'first_name' => 'Luis',
            'last_name' => 'Martinez Lopez',
            'document_type' => 'pasaporte',
            'document_number' => 'P20260001',
            'email' => 'luis.martinez@example.edu',
            'phone' => '+5355500001',
            'application_date' => '2026-06-15',
            'source' => 'web',
            'status' => 'approved',
            'notes' => 'Aspirante de referencia para el flujo de admisiones.',
        ]);

        ApplicationDocument::create([
            'applicant_id' => $applicant->id,
            'type' => 'identity_document',
            'name' => 'Documento de identidad',
            'status' => 'verified',
            'verified_at' => '2026-06-16 09:00:00',
        ]);

        ApplicationDocument::create([
            'applicant_id' => $applicant->id,
            'type' => 'high_school_transcript',
            'name' => 'Certificacion de estudios previos',
            'status' => 'verified',
            'verified_at' => '2026-06-16 09:15:00',
        ]);

        AdmissionInterview::create([
            'applicant_id' => $applicant->id,
            'scheduled_at' => '2026-06-18 10:00:00',
            'completed_at' => '2026-06-18 10:30:00',
            'score' => 88,
            'result' => 'recommended',
            'notes' => 'Buen perfil academico y motivacion clara.',
        ]);

        AdmissionDecision::create([
            'applicant_id' => $applicant->id,
            'decision' => 'approved',
            'decision_date' => '2026-06-20',
            'valid_until' => '2026-08-31',
            'score' => 88,
            'reason' => 'Cumple requisitos documentales y entrevista satisfactoria.',
            'conditions' => ['financial_clearance_required' => true],
        ]);

        $classSession = ClassSession::create([
            'subject_offering_id' => $offering->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'group_id' => $group->id,
            'subject_id' => $subjects->last()->id,
            'professor_id' => $professor->id,
            'session_date' => '2026-09-07',
            'starts_at' => '08:00',
            'ends_at' => '10:00',
            'classroom' => 'Lab 1',
            'topic' => 'Introduccion a algoritmos',
            'delivery_mode' => 'presencial',
            'status' => 'completed',
        ]);

        AttendanceRecord::create([
            'class_session_id' => $classSession->id,
            'student_id' => $student->id,
            'subject_enrollment_id' => $subjectEnrollment->id,
            'status' => 'present',
            'minutes_late' => 0,
            'justified' => false,
            'recorded_at' => '2026-09-07 08:05:00',
        ]);

        $this->seedExpandedDemoData(
            $institution,
            $campus,
            $faculty,
            $department,
            $modality,
            $career,
            $course,
            $plan,
            $group,
            $subjects,
            $scale,
            $enrollmentConcept
        );

        $this->seedUsers($roles, $student, $professor, $institution, $campus);
    }

    private function resetDemoData(): void
    {
        $tables = [
            'personal_access_tokens',
            'status_histories',
            'grade_audit_logs',
            'certificates',
            'attendance_records',
            'class_sessions',
            'admission_decisions',
            'admission_interviews',
            'application_documents',
            'applicants',
            'grade_change_requests',
            'grades',
            'grade_sheets',
            'grade_components',
            'grading_scale_levels',
            'grading_scales',
            'subject_offering_schedules',
            'subject_enrollments',
            'subject_offerings',
            'subject_prerequisites',
            'payment_receipts',
            'payment_allocations',
            'financial_adjustments',
            'financial_holds',
            'financial_concepts',
            'student_payments',
            'student_charges',
            'finances',
            'enrollments',
            'students',
            'professors',
            'users',
            'role_user',
            'permission_role',
            'roles',
            'permissions',
            'groups',
            'curriculum_plan_subject',
            'curriculum_plans',
            'subjects',
            'courses',
            'careers',
            'modalities',
            'departments',
            'faculties',
            'campuses',
            'institutions',
        ];

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    private function seedExpandedDemoData(
        Institution $institution,
        Campus $campus,
        Faculty $faculty,
        Department $department,
        Modality $modality,
        Career $informaticsCareer,
        Course $activeCourse,
        CurriculumPlan $informaticsPlan,
        Group $informaticsGroup,
        $baseSubjects,
        GradingScale $scale,
        FinancialConcept $enrollmentConcept
    ): void {
        $virtualModality = Modality::create([
            'institution_id' => $institution->id,
            'code' => 'VIRTUAL',
            'name' => 'Virtual',
            'requires_classroom' => false,
            'requires_online_platform' => true,
            'status' => 'active',
        ]);

        $businessDepartment = Department::create([
            'institution_id' => $institution->id,
            'faculty_id' => $faculty->id,
            'campus_id' => $campus->id,
            'code' => 'DAD',
            'name' => 'Departamento de Administracion',
            'status' => 'active',
        ]);

        $businessCareer = Career::create([
            'institution_id' => $institution->id,
            'faculty_id' => $faculty->id,
            'department_id' => $businessDepartment->id,
            'modality_id' => $virtualModality->id,
            'name' => 'Administracion de Empresas',
            'abbreviation' => 'ADE',
            'description' => 'Carrera orientada a gestion organizacional, finanzas y emprendimiento.',
        ]);

        $previousCourse = Course::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'name' => 'Curso 2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'status' => 'closed',
        ]);

        $nextCourse = Course::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'name' => 'Curso 2027-2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-07-31',
            'status' => 'draft',
        ]);

        $extraSubjects = collect([
            ['code' => 'BD-201', 'name' => 'Bases de Datos', 'credits' => 4, 'weekly_hours' => 6],
            ['code' => 'WEB-202', 'name' => 'Desarrollo Web', 'credits' => 5, 'weekly_hours' => 8],
            ['code' => 'ADM-101', 'name' => 'Administracion General', 'credits' => 4, 'weekly_hours' => 5],
            ['code' => 'CON-101', 'name' => 'Contabilidad I', 'credits' => 4, 'weekly_hours' => 5],
            ['code' => 'FIN-201', 'name' => 'Finanzas Empresariales', 'credits' => 4, 'weekly_hours' => 5],
        ])->map(fn (array $data) => Subject::create($data));

        $allInformaticsSubjects = $baseSubjects->merge($extraSubjects->take(2));
        $informaticsPlan->subjects()->sync($allInformaticsSubjects->mapWithKeys(fn (Subject $subject, int $index) => [
            $subject->id => [
                'semester' => $index < 2 ? 1 : 2,
                'is_required' => true,
                'minimum_passing_grade' => 60,
            ],
        ]));

        $businessPlan = CurriculumPlan::create([
            'career_id' => $businessCareer->id,
            'effective_course_id' => $activeCourse->id,
            'name' => 'Plan Administracion Regular',
            'version' => '2026',
            'duration_semesters' => 8,
            'status' => 'active',
            'is_current' => true,
        ]);

        $businessSubjects = $extraSubjects->slice(2)->values();
        $businessPlan->subjects()->sync($businessSubjects->mapWithKeys(fn (Subject $subject, int $index) => [
            $subject->id => [
                'semester' => $index + 1,
                'is_required' => true,
                'minimum_passing_grade' => 60,
            ],
        ]));

        $informaticsGroupB = Group::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'modality_id' => $modality->id,
            'course_id' => $activeCourse->id,
            'career_id' => $informaticsCareer->id,
            'name' => 'INF-1B',
            'shift' => 'vespertino',
            'status' => 'active',
        ]);

        $businessGroup = Group::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $businessDepartment->id,
            'modality_id' => $virtualModality->id,
            'course_id' => $activeCourse->id,
            'career_id' => $businessCareer->id,
            'name' => 'ADE-1A',
            'shift' => 'virtual',
            'status' => 'active',
        ]);

        $professors = collect([
            ['PROF-0002', 'Mariela', 'Sanchez', 'mariela.sanchez@example.edu', $extraSubjects[0]->id, $department->id],
            ['PROF-0003', 'Jorge', 'Diaz', 'jorge.diaz@example.edu', $extraSubjects[1]->id, $department->id],
            ['PROF-0004', 'Elena', 'Torres', 'elena.torres@example.edu', $businessSubjects[0]->id, $businessDepartment->id],
        ])->map(fn (array $professor) => Professor::create([
            'institution_id' => $institution->id,
            'campus_id' => $campus->id,
            'faculty_id' => $faculty->id,
            'department_id' => $professor[5],
            'subject_id' => $professor[4],
            'professor_code' => $professor[0],
            'first_name' => $professor[1],
            'last_name' => $professor[2],
            'email' => $professor[3],
            'status' => 'active',
        ]));

        $offerings = collect([
            [$informaticsGroup, $allInformaticsSubjects[0], $professors[0], 1, 35, 'open'],
            [$informaticsGroup, $allInformaticsSubjects[2], $professors[0], 2, 30, 'open'],
            [$informaticsGroupB, $allInformaticsSubjects[3], $professors[1], 2, 1, 'open'],
            [$businessGroup, $businessSubjects[0], $professors[2], 1, 25, 'open'],
            [$businessGroup, $businessSubjects[1], $professors[2], 1, 25, 'open'],
        ])->map(function (array $row, int $index) use ($institution, $campus, $faculty, $department, $modality, $informaticsCareer, $businessCareer, $informaticsPlan, $businessPlan, $activeCourse) {
            [$group, $subject, $professor, $semester, $capacity, $status] = $row;
            $isBusiness = str_starts_with($group->name, 'ADE');

            $offering = SubjectOffering::create([
                'institution_id' => $institution->id,
                'campus_id' => $campus->id,
                'faculty_id' => $faculty->id,
                'department_id' => $isBusiness ? $professor->department_id : $department->id,
                'modality_id' => $isBusiness ? $group->modality_id : $modality->id,
                'course_id' => $activeCourse->id,
                'career_id' => $isBusiness ? $businessCareer->id : $informaticsCareer->id,
                'group_id' => $group->id,
                'curriculum_plan_id' => $isBusiness ? $businessPlan->id : $informaticsPlan->id,
                'subject_id' => $subject->id,
                'professor_id' => $professor->id,
                'semester' => $semester,
                'capacity' => $capacity,
                'reserved_seats' => $index === 2 ? 1 : 0,
                'modality' => $isBusiness ? 'virtual' : 'presencial',
                'status' => $status,
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-12-20',
            ]);

            SubjectOfferingSchedule::create([
                'subject_offering_id' => $offering->id,
                'weekday' => ($index % 5) + 1,
                'starts_at' => str_pad((string) (8 + $index), 2, '0', STR_PAD_LEFT).':00',
                'ends_at' => str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT).':00',
                'classroom' => $isBusiness ? 'Aula Virtual' : 'Lab '.($index + 2),
            ]);

            return $offering;
        });

        $students = collect([
            ['EST-0002', 'Beatriz', 'Lopez Ruiz', '00010212345', 'beatriz.lopez@example.edu', $informaticsGroup->id, 'active', 'paid', 92],
            ['EST-0003', 'Daniel', 'Morales Vega', '00010312345', 'daniel.morales@example.edu', $informaticsGroup->id, 'active', 'debt', 55],
            ['EST-0004', 'Camila', 'Herrera Cruz', '00010412345', 'camila.herrera@example.edu', $informaticsGroupB->id, 'active', 'partial', 76],
            ['EST-0005', 'Roberto', 'Nunez Leon', '00010512345', 'roberto.nunez@example.edu', $businessGroup->id, 'active', 'paid', 81],
            ['EST-0006', 'Paola', 'Castillo Mena', '00010612345', 'paola.castillo@example.edu', $businessGroup->id, 'withdrawn', 'paid', 68],
            ['EST-0007', 'Miguel', 'Alvarez Soto', '00010712345', 'miguel.alvarez@example.edu', $informaticsGroup->id, 'graduated', 'paid', 97],
        ])->map(function (array $row) {
            return Student::create([
                'group_id' => $row[5],
                'student_code' => $row[0],
                'first_name' => $row[1],
                'last_name' => $row[2],
                'document_type' => 'carnet',
                'document_number' => $row[3],
                'email' => $row[4],
                'phone' => '+53555'.substr($row[3], -4),
                'birth_date' => '2000-01-15',
                'admission_date' => '2026-09-01',
                'exit_date' => $row[6] === 'withdrawn' ? '2026-11-15' : ($row[6] === 'graduated' ? '2027-07-20' : null),
                'exit_reason' => $row[6] === 'withdrawn' ? 'personal_reasons' : ($row[6] === 'graduated' ? 'graduation' : null),
                'status' => $row[6],
            ]);
        });

        $tuitionConcept = FinancialConcept::create([
            'code' => 'MONTHLY_TUITION',
            'name' => 'Mensualidad academica',
            'type' => 'tuition',
            'default_amount' => 120.00,
            'currency' => 'USD',
            'is_required_for_enrollment' => false,
            'is_active' => true,
            'description' => 'Cargo mensual recurrente para reportes financieros.',
        ]);

        $students->each(function (Student $student, int $index) use ($activeCourse, $previousCourse, $enrollmentConcept, $tuitionConcept, $offerings, $scale): void {
            $statusMode = ['paid', 'debt', 'partial', 'paid', 'paid', 'paid'][$index];
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'start_course_id' => $activeCourse->id,
                'end_course_id' => $student->status === 'withdrawn' ? $activeCourse->id : null,
                'enrollment_date' => '2026-09-01',
                'status' => $student->status === 'withdrawn' ? 'cancelled' : 'active',
                'notes' => 'Matricula demo '.$student->student_code,
            ]);

            $student->update(['current_enrollment_id' => $enrollment->id]);

            $paid = $statusMode === 'debt' ? 0 : ($statusMode === 'partial' ? 100 : 250);
            $charge = StudentCharge::create([
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $activeCourse->id,
                'financial_concept_id' => $enrollmentConcept->id,
                'original_amount' => 250,
                'adjustment_amount' => 0,
                'paid_amount' => $paid,
                'balance_amount' => 250 - $paid,
                'currency' => 'USD',
                'issue_date' => '2026-08-01',
                'due_date' => '2026-08-30',
                'status' => $paid >= 250 ? 'paid' : ($paid > 0 ? 'partial' : 'overdue'),
            ]);

            StudentCharge::create([
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $activeCourse->id,
                'financial_concept_id' => $tuitionConcept->id,
                'original_amount' => 120,
                'adjustment_amount' => $index === 3 ? -20 : 0,
                'paid_amount' => $index === 1 ? 0 : 120,
                'balance_amount' => $index === 1 ? 120 : 0,
                'currency' => 'USD',
                'issue_date' => '2026-09-01',
                'due_date' => '2026-09-30',
                'status' => $index === 1 ? 'overdue' : 'paid',
                'notes' => $index === 3 ? 'Descuento demo aplicado.' : null,
            ]);

            if ($paid > 0) {
                $payment = StudentPayment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'amount' => $paid,
                    'unallocated_amount' => 0,
                    'currency' => 'USD',
                    'payment_method' => $index % 2 === 0 ? 'transfer' : 'cash',
                    'payment_reference' => 'PAY-DEMO-'.$student->student_code,
                    'paid_at' => '2026-08-25',
                    'status' => 'confirmed',
                ]);

                PaymentAllocation::create([
                    'student_payment_id' => $payment->id,
                    'student_charge_id' => $charge->id,
                    'amount' => $paid,
                ]);

                PaymentReceipt::create([
                    'student_payment_id' => $payment->id,
                    'receipt_number' => 'RCPT-'.$student->student_code,
                    'issued_at' => '2026-08-25',
                    'status' => 'issued',
                ]);
            }

            $offering = $offerings[$index % $offerings->count()];
            $subjectEnrollment = SubjectEnrollment::create([
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'subject_id' => $offering->subject_id,
                'subject_offering_id' => $offering->id,
                'curriculum_plan_id' => $offering->curriculum_plan_id,
                'course_id' => $activeCourse->id,
                'career_id' => $offering->career_id,
                'group_id' => $offering->group_id,
                'semester' => $offering->semester,
                'enrolled_at' => '2026-09-01',
                'completed_at' => $index >= 4 ? '2026-12-20' : null,
                'status' => $index === 1 ? 'failed' : ($index >= 4 ? 'passed' : 'enrolled'),
            ]);

            $sheet = GradeSheet::firstOrCreate([
                'subject_offering_id' => $offering->id,
                'sheet_type' => 'ordinary',
                'call_number' => 1,
                'partial_number' => null,
            ], [
                'professor_id' => $offering->professor_id,
                'grading_scale_id' => $scale->id,
                'course_id' => $offering->course_id,
                'career_id' => $offering->career_id,
                'group_id' => $offering->group_id,
                'subject_id' => $offering->subject_id,
                'status' => $index >= 4 ? 'closed' : 'submitted',
                'opened_at' => '2026-12-01',
                'submitted_at' => '2026-12-18 12:00:00',
                'signed_at' => $index >= 4 ? '2026-12-19 09:00:00' : null,
                'closed_at' => $index >= 4 ? '2026-12-20 09:00:00' : null,
            ]);

            $component = GradeComponent::firstOrCreate([
                'subject_offering_id' => $offering->id,
                'code' => 'FINAL',
            ], [
                'name' => 'Evaluacion final',
                'type' => 'final',
                'term' => 'final',
                'weight' => 100,
                'max_score' => 100,
                'is_required' => true,
                'due_date' => '2026-12-15',
                'status' => 'active',
                'sort_order' => 1,
            ]);

            $gradeValue = [92, 55, 76, 81, 68, 97][$index];
            $levelCode = $gradeValue >= 90 ? 'A' : ($gradeValue >= 80 ? 'B' : ($gradeValue >= 70 ? 'C' : ($gradeValue >= 60 ? 'D' : 'F')));
            $grade = Grade::create([
                'subject_enrollment_id' => $subjectEnrollment->id,
                'student_id' => $student->id,
                'subject_id' => $offering->subject_id,
                'professor_id' => $offering->professor_id,
                'grade_sheet_id' => $sheet->id,
                'grade_component_id' => $component->id,
                'grading_scale_id' => $scale->id,
                'grading_scale_level_id' => $scale->levels()->where('code', $levelCode)->value('id'),
                'value' => $gradeValue,
                'raw_value' => $gradeValue,
                'normalized_value' => $gradeValue,
                'weight' => 100,
                'evaluation_type' => 'final',
                'attempt_type' => 'ordinary',
                'call_number' => 1,
                'is_final' => true,
                'evaluated_at' => '2026-12-15',
                'published_at' => '2026-12-15 10:00:00',
                'locked_at' => $index >= 4 ? '2026-12-20 09:00:00' : null,
                'status' => 'published',
            ]);

            GradeAuditLog::create([
                'grade_id' => $grade->id,
                'old_grade' => null,
                'new_grade' => $gradeValue,
                'old_status' => null,
                'new_status' => 'published',
                'reason' => 'Carga de nota demo para '.$student->student_code,
                'changed_at' => '2026-12-15 10:00:00',
            ]);

            $session = ClassSession::create([
                'subject_offering_id' => $offering->id,
                'course_id' => $offering->course_id,
                'career_id' => $offering->career_id,
                'group_id' => $offering->group_id,
                'subject_id' => $offering->subject_id,
                'professor_id' => $offering->professor_id,
                'session_date' => '2026-09-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT),
                'starts_at' => '08:00',
                'ends_at' => '10:00',
                'classroom' => 'Demo '.$index,
                'topic' => 'Clase demo '.$student->student_code,
                'delivery_mode' => $index === 3 ? 'virtual' : 'presencial',
                'status' => 'completed',
            ]);

            AttendanceRecord::create([
                'class_session_id' => $session->id,
                'student_id' => $student->id,
                'subject_enrollment_id' => $subjectEnrollment->id,
                'status' => $index === 1 ? 'absent' : ($index === 2 ? 'late' : 'present'),
                'minutes_late' => $index === 2 ? 15 : 0,
                'justified' => $index === 1,
                'recorded_at' => '2026-09-15 08:10:00',
            ]);

            Certificate::create([
                'certificate_code' => 'CERT-'.$student->student_code,
                'student_id' => $student->id,
                'type' => $index === 1 ? 'financial_status_certificate' : 'grade_certificate',
                'course_id' => $activeCourse->id,
                'enrollment_id' => $enrollment->id,
                'generated_at' => '2026-12-21 09:00:00',
                'verification_code' => 'VERIFY'.$student->student_code.'2026',
                'status' => 'generated',
                'snapshot_data' => [
                    'student' => [
                        'student_code' => $student->student_code,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'status' => $student->status,
                    ],
                    'course' => ['name' => $activeCourse->name],
                    'grade' => $gradeValue,
                    'issued_at' => '2026-12-21T09:00:00Z',
                ],
            ]);
        });

        collect([
            ['ASP-2026-0002', 'Nadia', 'Suarez', 'P20260002', 'submitted', 72],
            ['ASP-2026-0003', 'Oscar', 'Pineda', 'P20260003', 'waitlisted', 69],
            ['ASP-2026-0004', 'Iris', 'Valdes', 'P20260004', 'rejected', 48],
        ])->each(function (array $row, int $index) use ($institution, $campus, $businessCareer, $activeCourse, $businessGroup): void {
            $applicant = Applicant::create([
                'institution_id' => $institution->id,
                'campus_id' => $campus->id,
                'career_id' => $businessCareer->id,
                'course_id' => $activeCourse->id,
                'group_id' => $businessGroup->id,
                'applicant_code' => $row[0],
                'first_name' => $row[1],
                'last_name' => $row[2],
                'document_type' => 'pasaporte',
                'document_number' => $row[3],
                'email' => strtolower($row[1]).'.demo@example.edu',
                'application_date' => '2026-06-'.str_pad((string) (20 + $index), 2, '0', STR_PAD_LEFT),
                'source' => $index === 0 ? 'web' : 'referral',
                'status' => $row[4],
            ]);

            ApplicationDocument::create([
                'applicant_id' => $applicant->id,
                'type' => 'identity_document',
                'name' => 'Documento de identidad',
                'status' => $index === 2 ? 'rejected' : 'verified',
                'verified_at' => $index === 2 ? null : '2026-06-25 09:00:00',
                'rejection_reason' => $index === 2 ? 'Documento ilegible.' : null,
            ]);

            AdmissionInterview::create([
                'applicant_id' => $applicant->id,
                'scheduled_at' => '2026-06-26 10:00:00',
                'completed_at' => $index === 0 ? null : '2026-06-26 10:30:00',
                'score' => $row[5],
                'result' => $row[4] === 'submitted' ? 'scheduled' : $row[4],
                'notes' => 'Entrevista demo.',
            ]);

            if ($row[4] !== 'submitted') {
                AdmissionDecision::create([
                    'applicant_id' => $applicant->id,
                    'decision' => $row[4],
                    'decision_date' => '2026-06-28',
                    'score' => $row[5],
                    'reason' => 'Decision demo de admisiones.',
                    'conditions' => ['financial_clearance_required' => $row[4] === 'waitlisted'],
                ]);
            }
        });

        Finance::create([
            'student_id' => $students[1]->id,
            'amount' => 120,
            'currency' => 'USD',
            'concept' => 'tuition',
            'payment_method' => 'manual',
            'payment_reference' => 'LEGACY-DEBT-EST-0003',
            'paid_at' => null,
            'status' => 'pending',
        ]);
    }

    private function seedPermissions()
    {
        $permissions = [
            ['users.manage', 'Gestionar usuarios', 'security'],
            ['roles.view', 'Consultar roles y permisos', 'security'],
            ['catalogs.view', 'Consultar catalogos academicos', 'catalogs'],
            ['catalogs.manage', 'Gestionar catalogos academicos', 'catalogs'],
            ['curriculum.view', 'Consultar planes de estudio', 'curriculum'],
            ['curriculum.manage', 'Gestionar planes de estudio', 'curriculum'],
            ['groups.view', 'Consultar grupos', 'groups'],
            ['groups.manage', 'Gestionar grupos', 'groups'],
            ['students.view', 'Consultar estudiantes', 'students'],
            ['students.manage', 'Gestionar estudiantes', 'students'],
            ['admissions.manage', 'Gestionar aspirantes y admisiones', 'admissions'],
            ['enrollments.view', 'Consultar matriculas', 'enrollments'],
            ['enrollments.create', 'Crear matriculas', 'enrollments'],
            ['enrollments.manage', 'Gestionar matriculas', 'enrollments'],
            ['subject_enrollments.view', 'Consultar asignaturas matriculadas', 'academics'],
            ['subject_enrollments.manage', 'Gestionar asignaturas matriculadas', 'academics'],
            ['professors.view', 'Consultar profesores', 'professors'],
            ['professors.manage', 'Gestionar profesores', 'professors'],
            ['grades.view', 'Consultar calificaciones', 'grades'],
            ['grades.manage', 'Gestionar calificaciones', 'grades'],
            ['grades.configure', 'Configurar escalas y componentes de evaluacion', 'grades'],
            ['grades.sign', 'Firmar actas de calificaciones', 'grades'],
            ['grades.close', 'Cerrar actas academicas', 'grades'],
            ['grades.change.approve', 'Aprobar cambios de calificaciones cerradas', 'grades'],
            ['academic_history.view', 'Consultar historial academico', 'academics'],
            ['attendance.view', 'Consultar asistencia academica', 'attendance'],
            ['attendance.manage', 'Gestionar asistencia academica', 'attendance'],
            ['finances.view', 'Consultar finanzas', 'finances'],
            ['finances.manage', 'Gestionar obligaciones financieras', 'finances'],
            ['finances.payments.validate', 'Validar pagos', 'finances'],
            ['reports.academic.view', 'Consultar reportes academicos', 'reports'],
            ['reports.finance.view', 'Consultar reportes financieros', 'reports'],
            ['audit.view', 'Consultar auditoria e historial', 'audit'],
            ['support.impersonate', 'Soporte tecnico controlado', 'support'],
        ];

        return collect($permissions)->mapWithKeys(fn (array $permission) => [
            $permission[0] => Permission::create([
                'code' => $permission[0],
                'name' => $permission[1],
                'module' => $permission[2],
            ]),
        ]);
    }

    private function seedRoles($permissions)
    {
        $rolePermissions = [
            'super_admin' => $permissions->keys()->all(),
            'rector' => ['catalogs.view', 'curriculum.view', 'students.view', 'enrollments.view', 'attendance.view', 'finances.view', 'reports.academic.view', 'reports.finance.view', 'audit.view'],
            'institution_admin' => ['users.manage', 'roles.view', 'catalogs.manage', 'curriculum.manage', 'groups.manage', 'students.manage', 'professors.manage', 'reports.academic.view', 'audit.view'],
            'academic_secretary' => ['catalogs.view', 'curriculum.view', 'groups.view', 'students.manage', 'enrollments.manage', 'subject_enrollments.manage', 'grades.view', 'attendance.view', 'academic_history.view', 'reports.academic.view'],
            'registrar' => ['students.manage', 'enrollments.manage', 'academic_history.view', 'grades.view', 'grades.close', 'grades.change.approve', 'reports.academic.view', 'audit.view'],
            'admissions_officer' => ['students.manage', 'admissions.manage', 'catalogs.view', 'groups.view', 'reports.academic.view'],
            'finance_manager' => ['students.view', 'enrollments.view', 'finances.manage', 'finances.payments.validate', 'reports.finance.view', 'audit.view'],
            'cashier' => ['students.view', 'finances.view', 'finances.payments.validate'],
            'academic_coordinator' => ['catalogs.view', 'curriculum.manage', 'groups.manage', 'students.view', 'subject_enrollments.manage', 'grades.configure', 'grades.view', 'grades.close', 'attendance.view', 'reports.academic.view'],
            'career_director' => ['curriculum.manage', 'groups.view', 'students.view', 'professors.view', 'grades.view', 'grades.sign', 'grades.change.approve', 'attendance.view', 'academic_history.view', 'reports.academic.view'],
            'department_head' => ['professors.manage', 'catalogs.view', 'grades.view', 'grades.sign', 'reports.academic.view'],
            'professor' => ['students.view', 'subject_enrollments.view', 'grades.manage', 'grades.sign', 'attendance.manage', 'attendance.view', 'academic_history.view'],
            'student' => ['academic_history.view', 'attendance.view', 'finances.view', 'enrollments.view', 'grades.view'],
            'auditor' => ['catalogs.view', 'students.view', 'enrollments.view', 'attendance.view', 'finances.view', 'grades.view', 'reports.academic.view', 'reports.finance.view', 'audit.view'],
            'support' => ['roles.view', 'audit.view', 'support.impersonate'],
            'reports_analyst' => ['reports.academic.view', 'reports.finance.view', 'students.view', 'attendance.view', 'finances.view', 'grades.view'],
            'lms_coordinator' => ['catalogs.view', 'curriculum.view', 'groups.view', 'professors.view', 'subject_enrollments.view'],
        ];

        $names = [
            'super_admin' => 'Super administrador',
            'rector' => 'Rector / Director general',
            'institution_admin' => 'Administrador institucional',
            'academic_secretary' => 'Secretaria academica',
            'registrar' => 'Registro academico',
            'admissions_officer' => 'Admisiones',
            'finance_manager' => 'Director financiero',
            'cashier' => 'Caja / Tesoreria',
            'academic_coordinator' => 'Coordinador academico',
            'career_director' => 'Director de carrera',
            'department_head' => 'Jefe de departamento',
            'professor' => 'Profesor',
            'student' => 'Estudiante',
            'auditor' => 'Auditor',
            'support' => 'Soporte tecnico',
            'reports_analyst' => 'Analista de reportes',
            'lms_coordinator' => 'Coordinador LMS / virtualidad',
        ];

        return collect($rolePermissions)->mapWithKeys(function (array $permissionCodes, string $roleCode) use ($permissions, $names) {
            $role = Role::create([
                'code' => $roleCode,
                'name' => $names[$roleCode],
                'description' => 'Rol institucional para '.$names[$roleCode].'.',
                'is_system' => true,
            ]);

            $role->permissions()->sync($permissions->only($permissionCodes)->pluck('id'));

            return [$roleCode => $role];
        });
    }

    private function seedUsers($roles, Student $student, Professor $professor, Institution $institution, Campus $campus): void
    {
        $users = [
            ['Sistema Admin', 'admin@example.edu', ['super_admin'], null, null],
            ['Secretaria Academica', 'secretaria@example.edu', ['academic_secretary'], null, null],
            ['Finanzas', 'finanzas@example.edu', ['finance_manager'], null, null],
            ['Profesor Demo', 'profesor@example.edu', ['professor'], null, $professor->id],
            ['Estudiante Demo', 'estudiante@example.edu', ['student'], $student->id, null],
        ];

        foreach ($users as [$name, $email, $roleCodes, $studentId, $professorId]) {
            $user = User::create([
                'institution_id' => $institution->id,
                'campus_id' => $campus->id,
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'status' => 'active',
                'student_id' => $studentId,
                'professor_id' => $professorId,
            ]);

            $user->roles()->sync(collect($roleCodes)->map(fn (string $code) => $roles[$code]->id));
        }
    }
}
