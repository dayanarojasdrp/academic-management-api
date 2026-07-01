<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\OfficialReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    public function delinquency(Request $request, OfficialReportService $reports): JsonResponse
    {
        return response()->json($reports->delinquency($request));
    }
}
