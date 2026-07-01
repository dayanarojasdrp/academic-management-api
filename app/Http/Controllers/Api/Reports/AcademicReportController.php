<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Academic\AcademicHistoryService;
use App\Services\Reports\OfficialReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicReportController extends Controller
{
    public function enrollmentByPeriod(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->enrollmentByPeriod($request));
    }

    public function gradesByGroup(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->gradesByGroup($request));
    }

    public function gradeSheets(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->gradeSheets($request));
    }

    public function certificate(
        Student $student,
        Request $request,
        OfficialReportService $reports,
        AcademicHistoryService $academicHistory
    ): JsonResponse {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($reports->certificate($student, $request, $academicHistory));
    }

    public function kardex(
        Student $student,
        Request $request,
        OfficialReportService $reports,
        AcademicHistoryService $academicHistory
    ): JsonResponse {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($reports->kardex($student, $request, $academicHistory));
    }

    public function graduates(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->graduates($request));
    }

    public function withdrawals(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->withdrawals($request));
    }

    public function retention(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->retention($request));
    }

    public function facultyPerformance(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->facultyPerformance($request));
    }
}
