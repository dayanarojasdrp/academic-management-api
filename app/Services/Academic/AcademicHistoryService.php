<?php

namespace App\Services\Academic;

use App\Models\Grade;
use App\Models\Student;
use App\Models\SubjectEnrollment;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcademicHistoryService
{
    public function summary(Student $student): array
    {
        $enrollments = $student->subjectEnrollments();
        $publishedGrades = $student->grades()->where('status', 'published');

        return [
            'student_id' => $student->id,
            'current_status' => $student->status,
            'admission_date' => $student->admission_date,
            'exit_date' => $student->exit_date,
            'exit_reason' => $student->exit_reason,
            'current_group' => $student->group()->with(['course:id,name', 'career:id,name,abbreviation'])->first(),
            'current_enrollment' => $student->currentEnrollment()->with(['startCourse:id,name', 'endCourse:id,name'])->first(),
            'subject_totals' => [
                'total' => (clone $enrollments)->count(),
                'enrolled' => (clone $enrollments)->where('status', 'enrolled')->count(),
                'passed' => (clone $enrollments)->where('status', 'passed')->count(),
                'failed' => (clone $enrollments)->where('status', 'failed')->count(),
                'withdrawn' => (clone $enrollments)->where('status', 'withdrawn')->count(),
            ],
            'credits' => [
                'passed' => (float) (clone $enrollments)
                    ->join('subjects', 'subject_enrollments.subject_id', '=', 'subjects.id')
                    ->where('subject_enrollments.status', 'passed')
                    ->sum('subjects.credits'),
                'attempted' => (float) (clone $enrollments)
                    ->join('subjects', 'subject_enrollments.subject_id', '=', 'subjects.id')
                    ->whereIn('subject_enrollments.status', ['enrolled', 'passed', 'failed'])
                    ->sum('subjects.credits'),
            ],
            'grades' => [
                'published_count' => (clone $publishedGrades)->count(),
                'average' => round((float) (clone $publishedGrades)->avg('value'), 2),
                'last_evaluated_at' => (clone $publishedGrades)->max('evaluated_at'),
            ],
        ];
    }

    public function subjectHistory(Student $student, Request $request)
    {
        $query = $this->subjectEnrollmentBaseQuery($student, $request)
            ->with([
                'subject:id,code,name,credits,weekly_hours',
                'course:id,name,start_date,end_date,status',
                'career:id,name,abbreviation',
                'group:id,name,course_id,career_id',
                'subjectOffering:id,subject_id,course_id,career_id,group_id,professor_id,capacity,status',
            ])
            ->withCount([
                'grades as published_grades_count' => fn (Builder $query) => $query->where('status', 'published'),
            ])
            ->withAvg([
                'grades as published_grade_average' => fn (Builder $query) => $query->where('status', 'published'),
            ], 'value')
            ->withMax([
                'grades as last_evaluated_at' => fn (Builder $query) => $query->where('status', 'published'),
            ], 'evaluated_at')
            ->orderByDesc('course_id')
            ->orderBy('semester')
            ->orderByDesc('id');

        return ApiQuery::paginate($query, $request);
    }

    public function kardex(Student $student, Request $request)
    {
        $query = $this->subjectEnrollmentBaseQuery($student, $request)
            ->whereIn('status', ['passed', 'failed', 'withdrawn'])
            ->with([
                'subject:id,code,name,credits,weekly_hours',
                'course:id,name,start_date,end_date,status',
                'career:id,name,abbreviation',
                'curriculumPlan:id,career_id,name,version,status,is_current',
            ])
            ->withAvg([
                'grades as final_grade' => fn (Builder $query) => $query->where('status', 'published'),
            ], 'value')
            ->withMax([
                'grades as completed_evaluation_at' => fn (Builder $query) => $query->where('status', 'published'),
            ], 'evaluated_at')
            ->orderByDesc('course_id')
            ->orderBy('semester')
            ->orderBy('subject_id');

        return ApiQuery::paginate($query, $request);
    }

    public function grades(Student $student, Request $request)
    {
        $query = Grade::query()
            ->where('student_id', $student->id)
            ->with([
                'subject:id,code,name,credits',
                'professor:id,subject_id,first_name,last_name,email',
                'subjectEnrollment:id,student_id,subject_id,course_id,career_id,group_id,semester,status',
                'subjectEnrollment.course:id,name,start_date,end_date,status',
            ])
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id');

        ApiQuery::applyEquals($query, $request, [
            'status' => 'status',
            'subject_id' => 'subject_id',
            'professor_id' => 'professor_id',
            'evaluation_type' => 'evaluation_type',
        ]);

        if ($request->filled('course_id')) {
            $query->whereHas('subjectEnrollment', fn (Builder $query) => $query
                ->where('course_id', $request->query('course_id')));
        }

        if ($request->filled('from')) {
            $query->whereDate('evaluated_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('evaluated_at', '<=', $request->query('to'));
        }

        return ApiQuery::paginate($query, $request);
    }

    private function subjectEnrollmentBaseQuery(Student $student, Request $request): Builder
    {
        $query = SubjectEnrollment::query()
            ->where('student_id', $student->id);

        ApiQuery::applyEquals($query, $request, [
            'status' => 'status',
            'course_id' => 'course_id',
            'career_id' => 'career_id',
            'group_id' => 'group_id',
            'semester' => 'semester',
            'subject_id' => 'subject_id',
            'curriculum_plan_id' => 'curriculum_plan_id',
        ]);

        if ($request->filled('from')) {
            $query->whereDate('enrolled_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('enrolled_at', '<=', $request->query('to'));
        }

        return $query;
    }
}
