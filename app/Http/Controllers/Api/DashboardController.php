<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        if ($request->filled('program_id') && ! $request->filled('career_id')) {
            $request->merge(['career_id' => $request->query('program_id')]);
        }

        if ($request->filled('academic_period_id') && ! $request->filled('course_id')) {
            $request->merge(['course_id' => $request->query('academic_period_id')]);
        }

        $students = DB::table('students')->leftJoin('groups', 'students.group_id', '=', 'groups.id');
        $enrollments = DB::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id');
        $charges = DB::table('student_charges')
            ->join('students', 'student_charges.student_id', '=', 'students.id')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id');

        $this->applyScope($students, $request, 'groups');
        $this->applyScope($enrollments, $request, 'groups');
        $this->applyScope($charges, $request, 'groups');

        $offerings = DB::table('subject_offerings');
        $this->applyScope($offerings, $request, 'subject_offerings');

        $certificates = DB::table('certificates')
            ->join('students', 'certificates.student_id', '=', 'students.id')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id');
        $this->applyScope($certificates, $request, 'groups');

        return response()->json([
            'kpis' => [
                'total_students' => (clone $students)->count('students.id'),
                'active_enrollments' => (clone $enrollments)->where('enrollments.status', 'active')->count('enrollments.id'),
                'pending_payments' => (clone $charges)->where('student_charges.balance_amount', '>', 0)->count('student_charges.id'),
                'courses_with_available_seats' => (clone $offerings)
                    ->whereRaw('capacity > reserved_seats')
                    ->whereIn('status', ['open', 'active'])
                    ->count('id'),
                'certificates_issued' => (clone $certificates)->where('certificates.status', 'generated')->count('certificates.id'),
                'failed_or_pending_processes' => $this->failedOrPendingProcesses($request),
            ],
            'research_metrics' => [
                'average_enrollment_completion_time' => $this->averageEnrollmentCompletionTime(),
                'manual_validations_required' => DB::table('student_payments')->whereIn('status', ['registered', 'pending', 'review'])->count(),
                'duplicate_student_records_detected' => $this->duplicateStudentRecords(),
                'certificate_generation_time' => 'server-side snapshot, immediate',
            ],
            'charts' => [
                'enrollments_by_period' => $this->enrollmentsByPeriod($request),
                'pending_processes_by_program' => $this->pendingProcessesByProgram($request),
            ],
            'tables' => [
                'program_indicators' => $this->programIndicators($request),
                'operational_processes' => $this->operationalProcesses(),
                'recent_certificates' => $this->recentCertificates(),
            ],
        ]);
    }

    private function failedOrPendingProcesses(Request $request): int
    {
        return DB::table('applicants')->whereIn('status', ['draft', 'submitted', 'returned', 'rejected'])->count()
            + DB::table('student_charges')->where('balance_amount', '>', 0)->count()
            + DB::table('grade_sheets')->whereIn('status', ['draft', 'submitted'])->count();
    }

    private function averageEnrollmentCompletionTime(): string
    {
        $driver = DB::connection()->getDriverName();
        $expression = match ($driver) {
            'mysql', 'mariadb' => 'avg(timestampdiff(second, enrollment_date, updated_at) / 86400)',
            'pgsql' => 'avg(extract(epoch from (updated_at - enrollment_date)) / 86400)',
            'sqlite' => 'avg(julianday(updated_at) - julianday(enrollment_date))',
            default => 'avg(0)',
        };

        $days = DB::table('enrollments')
            ->whereNotNull('enrollment_date')
            ->whereNotNull('updated_at')
            ->selectRaw($expression.' as days')
            ->value('days');

        return round((float) $days, 2).' days';
    }

    private function duplicateStudentRecords(): int
    {
        return DB::table('students')
            ->select('document_number')
            ->groupBy('document_number')
            ->havingRaw('count(*) > 1')
            ->count();
    }

    private function enrollmentsByPeriod(Request $request): array
    {
        $query = DB::table('enrollments')
            ->join('courses', 'enrollments.start_course_id', '=', 'courses.id')
            ->selectRaw('courses.name as period, count(enrollments.id) as value')
            ->groupBy('courses.name')
            ->orderBy('courses.name');

        return $query->get()->all();
    }

    private function pendingProcessesByProgram(Request $request): array
    {
        return DB::table('students')
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->leftJoin('careers', 'groups.career_id', '=', 'careers.id')
            ->whereIn('students.status', ['inactive', 'risk', 'pending'])
            ->selectRaw('coalesce(careers.name, \'Sin carrera\') as program, count(students.id) as value')
            ->groupBy('careers.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get()
            ->all();
    }

    private function programIndicators(Request $request): array
    {
        return DB::table('careers')
            ->leftJoin('groups', 'careers.id', '=', 'groups.career_id')
            ->leftJoin('students', 'groups.id', '=', 'students.group_id')
            ->selectRaw('careers.id, careers.name, count(students.id) as students_count')
            ->groupBy(['careers.id', 'careers.name'])
            ->orderBy('careers.name')
            ->get()
            ->all();
    }

    private function operationalProcesses(): array
    {
        return [
            ['process' => 'admissions', 'pending' => DB::table('applicants')->whereIn('status', ['draft', 'submitted'])->count()],
            ['process' => 'payments', 'pending' => DB::table('student_payments')->whereIn('status', ['registered', 'pending', 'review'])->count()],
            ['process' => 'grade_sheets', 'pending' => DB::table('grade_sheets')->whereIn('status', ['draft', 'submitted'])->count()],
        ];
    }

    private function recentCertificates(): array
    {
        return DB::table('certificates')
            ->join('students', 'certificates.student_id', '=', 'students.id')
            ->select(['certificates.id', 'certificates.certificate_code', 'certificates.type', 'certificates.generated_at', 'students.student_code'])
            ->orderByDesc('certificates.generated_at')
            ->limit(10)
            ->get()
            ->all();
    }

    private function applyScope($query, Request $request, string $table): void
    {
        foreach (['faculty_id', 'career_id', 'course_id', 'group_id'] as $filter) {
            if ($request->filled($filter)) {
                $column = $filter === 'group_id' && $table === 'groups' ? $table.'.id' : $table.'.'.$filter;
                $query->where($column, $request->query($filter));
            }
        }
    }
}
