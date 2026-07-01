<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CurriculumPlan;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Finance;
use App\Models\FinancialConcept;
use App\Models\Grade;
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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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

        Grade::create([
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

        $this->seedUsers($roles, $student, $professor, $institution, $campus);
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
            'rector' => ['catalogs.view', 'curriculum.view', 'students.view', 'enrollments.view', 'finances.view', 'reports.academic.view', 'reports.finance.view', 'audit.view'],
            'institution_admin' => ['users.manage', 'roles.view', 'catalogs.manage', 'curriculum.manage', 'groups.manage', 'students.manage', 'professors.manage', 'reports.academic.view', 'audit.view'],
            'academic_secretary' => ['catalogs.view', 'curriculum.view', 'groups.view', 'students.manage', 'enrollments.manage', 'subject_enrollments.manage', 'grades.view', 'academic_history.view', 'reports.academic.view'],
            'registrar' => ['students.manage', 'enrollments.manage', 'academic_history.view', 'grades.view', 'grades.close', 'grades.change.approve', 'reports.academic.view', 'audit.view'],
            'admissions_officer' => ['students.manage', 'admissions.manage', 'catalogs.view', 'groups.view'],
            'finance_manager' => ['students.view', 'enrollments.view', 'finances.manage', 'finances.payments.validate', 'reports.finance.view', 'audit.view'],
            'cashier' => ['students.view', 'finances.view', 'finances.payments.validate'],
            'academic_coordinator' => ['catalogs.view', 'curriculum.manage', 'groups.manage', 'students.view', 'subject_enrollments.manage', 'grades.configure', 'grades.view', 'grades.close', 'reports.academic.view'],
            'career_director' => ['curriculum.manage', 'groups.view', 'students.view', 'professors.view', 'grades.view', 'grades.sign', 'grades.change.approve', 'academic_history.view', 'reports.academic.view'],
            'department_head' => ['professors.manage', 'catalogs.view', 'grades.view', 'grades.sign', 'reports.academic.view'],
            'professor' => ['students.view', 'subject_enrollments.view', 'grades.manage', 'grades.sign', 'academic_history.view'],
            'student' => ['academic_history.view', 'finances.view', 'enrollments.view', 'grades.view'],
            'auditor' => ['catalogs.view', 'students.view', 'enrollments.view', 'finances.view', 'grades.view', 'reports.academic.view', 'reports.finance.view', 'audit.view'],
            'support' => ['roles.view', 'audit.view', 'support.impersonate'],
            'reports_analyst' => ['reports.academic.view', 'reports.finance.view', 'students.view', 'finances.view', 'grades.view'],
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
