<?php

namespace App\Http\Controllers\Api\Finance;

use App\Actions\Academic\PaymentVerifier;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialClearanceController extends Controller
{
    public function show(Request $request, Student $student, PaymentVerifier $paymentVerifier): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
        ]);

        return response()->json($paymentVerifier->clearance($student, $validated['course_id'] ?? null));
    }
}
