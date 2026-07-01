<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\GradeSheet;
use App\Models\Student;
use App\Services\Academic\AcademicHistoryService;
use App\Services\Reports\DocumentExportService;
use App\Services\Reports\OfficialReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function certificate(
        Student $student,
        Request $request,
        OfficialReportService $reports,
        AcademicHistoryService $academicHistory,
        DocumentExportService $exporter
    ): Response {
        $this->authorize('viewAcademicHistory', $student);

        $payload = $reports->certificate($student, $request, $academicHistory);
        $title = 'Constancia academica - '.$student->student_code;

        return $this->export($request, $exporter, 'certificate-'.$student->student_code, $title, $payload);
    }

    public function kardex(
        Student $student,
        Request $request,
        OfficialReportService $reports,
        AcademicHistoryService $academicHistory,
        DocumentExportService $exporter
    ): Response {
        $this->authorize('viewAcademicHistory', $student);

        $payload = $reports->kardex($student, $request, $academicHistory);
        $title = 'Kardex academico - '.$student->student_code;

        return $this->export($request, $exporter, 'kardex-'.$student->student_code, $title, $payload);
    }

    public function gradeSheet(GradeSheet $gradeSheet, Request $request, DocumentExportService $exporter): Response
    {
        $gradeSheet->load([
            'course',
            'career',
            'group',
            'subject',
            'professor',
            'grades.student',
            'grades.gradingScaleLevel',
        ]);

        $rows = $gradeSheet->grades->map(fn ($grade) => [
            'student_code' => $grade->student?->student_code,
            'student_name' => trim(($grade->student?->first_name ?? '').' '.($grade->student?->last_name ?? '')),
            'grade' => $grade->value,
            'level' => $grade->gradingScaleLevel?->label,
            'status' => $grade->status,
            'evaluated_at' => $grade->evaluated_at,
        ])->all();

        $payload = [
            'acta' => [
                'id' => $gradeSheet->id,
                'course' => $gradeSheet->course?->name,
                'career' => $gradeSheet->career?->name,
                'group' => $gradeSheet->group?->name,
                'subject' => $gradeSheet->subject?->name,
                'professor' => trim(($gradeSheet->professor?->first_name ?? '').' '.($gradeSheet->professor?->last_name ?? '')),
                'status' => $gradeSheet->status,
            ],
            'data' => $rows,
        ];

        return $this->export($request, $exporter, 'grade-sheet-'.$gradeSheet->id, 'Acta de calificaciones '.$gradeSheet->id, $payload);
    }

    public function delinquency(Request $request, OfficialReportService $reports, DocumentExportService $exporter): Response
    {
        $payload = $reports->delinquency($request);

        return $this->export($request, $exporter, 'delinquency-report', 'Reporte de morosidad', $payload);
    }

    private function export(Request $request, DocumentExportService $exporter, string $filename, string $title, mixed $payload): Response
    {
        $format = strtolower($request->query('format', 'pdf'));

        if ($format === 'csv' || $format === 'excel') {
            return $exporter->csv($filename, $exporter->rowsFromPayload($payload));
        }

        return $exporter->pdf($filename, $title, $exporter->linesFromPayload($title, $payload));
    }
}
