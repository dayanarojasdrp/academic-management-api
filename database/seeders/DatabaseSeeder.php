<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Course;
use App\Models\CurriculumPlan;
use App\Models\Enrollment;
use App\Models\Finance;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Professor;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
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
        $career = Career::create([
            'name' => 'Ingenieria Informatica',
            'abbreviation' => 'INF',
            'description' => 'Carrera orientada al desarrollo de software y sistemas de informacion.',
        ]);

        $course = Course::create([
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
            'name' => 'Plan Regular',
            'version' => '2026',
            'duration_semesters' => 10,
            'status' => 'active',
        ]);

        $plan->subjects()->sync($subjects->mapWithKeys(fn (Subject $subject, int $index) => [
            $subject->id => ['semester' => 1, 'is_required' => true],
        ]));

        $group = Group::create([
            'course_id' => $course->id,
            'career_id' => $career->id,
            'name' => 'INF-1A',
            'shift' => 'diurno',
            'status' => 'active',
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

        $professor = Professor::create([
            'subject_id' => $subjects->last()->id,
            'professor_code' => 'PROF-0001',
            'first_name' => 'Carlos',
            'last_name' => 'Rodriguez',
            'email' => 'carlos.rodriguez@example.edu',
            'status' => 'active',
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
        $student->update(['current_enrollment_id' => $enrollment->id]);

        $subjectEnrollment = SubjectEnrollment::create([
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'subject_id' => $subjects->last()->id,
            'course_id' => $course->id,
            'career_id' => $career->id,
            'group_id' => $group->id,
            'enrolled_at' => '2026-09-01',
            'status' => 'enrolled',
        ]);

        Grade::create([
            'subject_enrollment_id' => $subjectEnrollment->id,
            'student_id' => $student->id,
            'subject_id' => $subjects->last()->id,
            'professor_id' => $professor->id,
            'value' => 95,
            'evaluation_type' => 'final',
            'evaluated_at' => '2026-12-15',
            'status' => 'published',
        ]);
    }
}
