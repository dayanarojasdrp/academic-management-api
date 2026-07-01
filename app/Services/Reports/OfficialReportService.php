<?php

namespace App\Services\Reports;

use App\Models\Student;
use App\Services\Academic\AcademicHistoryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficialReportService
{
    public function enrollmentByPeriod(Request $request): array
    {
        $query = DB::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'enrollments.start_course_id', '=', 'courses.id')
            ->selectRaw('
                enrollments.start_course_id as course_id,
                courses.name as course_name,
                groups.institution_id,
                groups.campus_id,
                groups.faculty_id,
                groups.department_id,
                groups.modality_id,
                careers.id as career_id,
                careers.name as career_name,
                groups.id as group_id,
                groups.name as group_name,
                enrollments.status,
                count(*) as total
            ')
            ->groupBy([
                'enrollments.start_course_id',
                'courses.name',
                'groups.institution_id',
                'groups.campus_id',
                'groups.faculty_id',
                'groups.department_id',
                'groups.modality_id',
                'careers.id',
                'careers.name',
                'groups.id',
                'groups.name',
                'enrollments.status',
            ])
            ->orderBy('courses.name')
            ->orderBy('careers.name')
            ->orderBy('groups.name');

        $this->applyAcademicScope($query, $request, 'groups');
        $this->applyDateRange($query, $request, 'enrollments.enrollment_date');

        return $this->payload('enrollment_by_period', $request, $query->get()->all());
    }

    public function delinquency(Request $request)
    {
        $asOf = $request->query('as_of', now()->toDateString());
        $query = DB::table('student_charges')
            ->join('students', 'student_charges.student_id', '=', 'students.id')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'student_charges.course_id', '=', 'courses.id')
            ->leftJoin('financial_concepts', 'student_charges.financial_concept_id', '=', 'financial_concepts.id')
            ->where('student_charges.balance_amount', '>', 0)
            ->whereDate('student_charges.due_date', '<=', $asOf)
            ->select([
                'students.id as student_id',
                'students.student_code',
                'students.first_name',
                'students.last_name',
                'students.status as student_status',
                'groups.name as group_name',
                'careers.name as career_name',
                'courses.name as course_name',
                'financial_concepts.name as concept_name',
                'student_charges.currency',
                'student_charges.original_amount',
                'student_charges.adjustment_amount',
                'student_charges.paid_amount',
                'student_charges.balance_amount',
                'student_charges.due_date',
                'student_charges.status',
            ])
            ->orderByDesc('student_charges.balance_amount')
            ->orderBy('student_charges.due_date');

        $this->applyAcademicScope($query, $request, 'groups');

        return $query->paginate($this->perPage($request));
    }

    public function gradesByGroup(Request $request)
    {
        $query = DB::table('subject_enrollments')
            ->join('students', 'subject_enrollments.student_id', '=', 'students.id')
            ->join('subjects', 'subject_enrollments.subject_id', '=', 'subjects.id')
            ->leftJoin('groups', 'subject_enrollments.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'subject_enrollments.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'subject_enrollments.course_id', '=', 'courses.id')
            ->leftJoin('grades', function ($join): void {
                $join->on('grades.subject_enrollment_id', '=', 'subject_enrollments.id')
                    ->where('grades.status', '=', 'published');
            })
            ->selectRaw('
                groups.id as group_id,
                groups.name as group_name,
                careers.name as career_name,
                courses.name as course_name,
                subjects.code as subject_code,
                subjects.name as subject_name,
                students.id as student_id,
                students.student_code,
                students.first_name,
                students.last_name,
                subject_enrollments.status as subject_status,
                avg(grades.value) as average_grade,
                max(grades.evaluated_at) as last_evaluated_at
            ')
            ->groupBy([
                'groups.id',
                'groups.name',
                'careers.name',
                'courses.name',
                'subjects.code',
                'subjects.name',
                'students.id',
                'students.student_code',
                'students.first_name',
                'students.last_name',
                'subject_enrollments.status',
            ])
            ->orderBy('groups.name')
            ->orderBy('subjects.name')
            ->orderBy('students.last_name');

        $this->applyAcademicScope($query, $request, 'groups');
        $this->applyEquals($query, $request, [
            'subject_id' => 'subject_enrollments.subject_id',
            'status' => 'subject_enrollments.status',
        ]);

        return $query->paginate($this->perPage($request));
    }

    public function gradeSheets(Request $request)
    {
        $query = DB::table('grade_sheets')
            ->leftJoin('subject_offerings', 'grade_sheets.subject_offering_id', '=', 'subject_offerings.id')
            ->leftJoin('professors', 'grade_sheets.professor_id', '=', 'professors.id')
            ->leftJoin('subjects', 'grade_sheets.subject_id', '=', 'subjects.id')
            ->leftJoin('groups', 'grade_sheets.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'grade_sheets.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'grade_sheets.course_id', '=', 'courses.id')
            ->leftJoin('grades', 'grade_sheets.id', '=', 'grades.grade_sheet_id')
            ->selectRaw('
                grade_sheets.id,
                grade_sheets.sheet_type,
                grade_sheets.call_number,
                grade_sheets.partial_number,
                grade_sheets.status,
                grade_sheets.opened_at,
                grade_sheets.submitted_at,
                grade_sheets.signed_at,
                grade_sheets.closed_at,
                courses.name as course_name,
                careers.name as career_name,
                groups.name as group_name,
                subjects.code as subject_code,
                subjects.name as subject_name,
                professors.professor_code,
                professors.first_name as professor_first_name,
                professors.last_name as professor_last_name,
                count(grades.id) as grades_count,
                avg(case when grades.status = \'published\' then grades.value end) as published_average,
                sum(case when grades.status = \'published\' and grades.value >= 60 then 1 else 0 end) as passed_count,
                sum(case when grades.status = \'published\' and grades.value < 60 then 1 else 0 end) as failed_count
            ')
            ->groupBy([
                'grade_sheets.id',
                'grade_sheets.sheet_type',
                'grade_sheets.call_number',
                'grade_sheets.partial_number',
                'grade_sheets.status',
                'grade_sheets.opened_at',
                'grade_sheets.submitted_at',
                'grade_sheets.signed_at',
                'grade_sheets.closed_at',
                'courses.name',
                'careers.name',
                'groups.name',
                'subjects.code',
                'subjects.name',
                'professors.professor_code',
                'professors.first_name',
                'professors.last_name',
            ])
            ->orderByDesc('grade_sheets.id');

        $this->applyAcademicScope($query, $request, 'subject_offerings');
        $this->applyEquals($query, $request, [
            'status' => 'grade_sheets.status',
            'course_id' => 'grade_sheets.course_id',
            'career_id' => 'grade_sheets.career_id',
            'group_id' => 'grade_sheets.group_id',
            'professor_id' => 'grade_sheets.professor_id',
        ]);

        return $query->paginate($this->perPage($request));
    }

    public function certificate(Student $student, Request $request, AcademicHistoryService $academicHistoryService): array
    {
        return $this->payload('certificate', $request, [
            'student' => $student->load(['group.course', 'group.career', 'currentEnrollment.startCourse']),
            'academic_summary' => $academicHistoryService->summary($student),
            'issued_at' => now()->toISOString(),
            'purpose' => $request->query('purpose', 'general'),
        ]);
    }

    public function kardex(Student $student, Request $request, AcademicHistoryService $academicHistoryService)
    {
        return $academicHistoryService->kardex($student, $request);
    }

    public function graduates(Request $request)
    {
        $query = DB::table('students')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'groups.course_id', '=', 'courses.id')
            ->where(function (Builder $query): void {
                $query->where('students.status', 'graduated')
                    ->orWhere('students.exit_reason', 'graduation');
            })
            ->select([
                'students.id',
                'students.student_code',
                'students.first_name',
                'students.last_name',
                'students.admission_date',
                'students.exit_date',
                'careers.name as career_name',
                'courses.name as course_name',
                'groups.name as group_name',
            ])
            ->orderByDesc('students.exit_date');

        $this->applyAcademicScope($query, $request, 'groups');
        $this->applyDateRange($query, $request, 'students.exit_date');

        return $query->paginate($this->perPage($request));
    }

    public function withdrawals(Request $request)
    {
        $query = DB::table('students')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'groups.course_id', '=', 'courses.id')
            ->where(function (Builder $query): void {
                $query->whereNotNull('students.exit_date')
                    ->orWhereIn('students.status', ['withdrawn', 'inactive', 'dropped']);
            })
            ->select([
                'students.id',
                'students.student_code',
                'students.first_name',
                'students.last_name',
                'students.status',
                'students.admission_date',
                'students.exit_date',
                'students.exit_reason',
                'careers.name as career_name',
                'courses.name as course_name',
                'groups.name as group_name',
            ])
            ->orderByDesc('students.exit_date');

        $this->applyAcademicScope($query, $request, 'groups');
        $this->applyDateRange($query, $request, 'students.exit_date');

        return $query->paginate($this->perPage($request));
    }

    public function retention(Request $request): array
    {
        $query = DB::table('students')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->leftJoin('courses', 'groups.course_id', '=', 'courses.id')
            ->selectRaw('
                courses.id as course_id,
                courses.name as course_name,
                careers.id as career_id,
                careers.name as career_name,
                count(students.id) as cohort_total,
                sum(case when students.status = \'active\' then 1 else 0 end) as active_total,
                sum(case when students.status in (\'withdrawn\', \'inactive\', \'dropped\') or students.exit_date is not null then 1 else 0 end) as withdrawal_total
            ')
            ->groupBy(['courses.id', 'courses.name', 'careers.id', 'careers.name'])
            ->orderBy('courses.name')
            ->orderBy('careers.name');

        $this->applyAcademicScope($query, $request, 'groups');

        $rows = $query->get()->map(function ($row) {
            $row->retention_rate = $row->cohort_total > 0
                ? round(((int) $row->active_total / (int) $row->cohort_total) * 100, 2)
                : 0;
            $row->withdrawal_rate = $row->cohort_total > 0
                ? round(((int) $row->withdrawal_total / (int) $row->cohort_total) * 100, 2)
                : 0;

            return $row;
        })->all();

        return $this->payload('retention', $request, $rows);
    }

    public function facultyPerformance(Request $request)
    {
        $query = DB::table('professors')
            ->leftJoin('subject_offerings', 'professors.id', '=', 'subject_offerings.professor_id')
            ->leftJoin('grade_sheets', 'professors.id', '=', 'grade_sheets.professor_id')
            ->leftJoin('grades', 'professors.id', '=', 'grades.professor_id')
            ->selectRaw('
                professors.id as professor_id,
                professors.professor_code,
                professors.first_name,
                professors.last_name,
                professors.institution_id,
                professors.campus_id,
                professors.faculty_id,
                professors.department_id,
                count(distinct subject_offerings.id) as offerings_count,
                count(distinct grade_sheets.id) as grade_sheets_count,
                sum(case when grade_sheets.status = \'closed\' then 1 else 0 end) as closed_sheets_count,
                count(grades.id) as grades_count,
                avg(case when grades.status = \'published\' then grades.value end) as published_average,
                sum(case when grades.status = \'published\' and grades.value >= 60 then 1 else 0 end) as passed_count,
                sum(case when grades.status = \'published\' and grades.value < 60 then 1 else 0 end) as failed_count
            ')
            ->groupBy([
                'professors.id',
                'professors.professor_code',
                'professors.first_name',
                'professors.last_name',
                'professors.institution_id',
                'professors.campus_id',
                'professors.faculty_id',
                'professors.department_id',
            ])
            ->orderBy('professors.last_name');

        $this->applyAcademicScope($query, $request, 'professors');

        return $query->paginate($this->perPage($request));
    }

    private function applyAcademicScope(Builder $query, Request $request, string $table): void
    {
        $columns = match ($table) {
            'professors' => [
                'institution_id' => 'professors.institution_id',
                'campus_id' => 'professors.campus_id',
                'faculty_id' => 'professors.faculty_id',
                'department_id' => 'professors.department_id',
            ],
            'groups' => [
                'institution_id' => 'groups.institution_id',
                'campus_id' => 'groups.campus_id',
                'faculty_id' => 'groups.faculty_id',
                'department_id' => 'groups.department_id',
                'modality_id' => 'groups.modality_id',
                'course_id' => 'groups.course_id',
                'career_id' => 'groups.career_id',
                'group_id' => 'groups.id',
            ],
            default => [
                'institution_id' => $table.'.institution_id',
                'campus_id' => $table.'.campus_id',
                'faculty_id' => $table.'.faculty_id',
                'department_id' => $table.'.department_id',
                'modality_id' => $table.'.modality_id',
                'course_id' => $table.'.course_id',
                'career_id' => $table.'.career_id',
                'group_id' => $table.'.group_id',
            ],
        };

        $this->applyEquals($query, $request, $columns);
    }

    private function applyEquals(Builder $query, Request $request, array $filters): void
    {
        foreach ($filters as $parameter => $column) {
            if ($request->filled($parameter)) {
                $query->where($column, $request->query($parameter));
            }
        }
    }

    private function applyDateRange(Builder $query, Request $request, string $column): void
    {
        if ($request->filled('from')) {
            $query->whereDate($column, '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate($column, '<=', $request->query('to'));
        }
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->integer('per_page', 50), 1), 200);
    }

    private function payload(string $report, Request $request, mixed $data): array
    {
        return [
            'report' => $report,
            'generated_at' => now()->toISOString(),
            'filters' => $request->query(),
            'data' => $data,
        ];
    }
}
