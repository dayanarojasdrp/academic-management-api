<?php

namespace App\Http\Controllers\Api\Finance;

use App\Actions\Academic\StudentAccountService;
use App\Http\Controllers\Api\ApiController;
use App\Models\StudentCharge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentChargeController extends ApiController
{
    protected string $modelClass = StudentCharge::class;

    protected array $relations = ['student', 'course', 'concept', 'adjustments', 'allocations.payment'];

    public function store(Request $request, StudentAccountService $accountService): JsonResponse
    {
        $charge = $accountService->createCharge($request->validate($this->rules()));
        $this->recordStatusChange($charge, null, $charge->status, $request);

        return response()->json($charge->load($this->relations), 201);
    }

    public function show(StudentCharge $studentCharge) { return $this->showRecord($studentCharge); }
    public function update(Request $request, StudentCharge $studentCharge) { return $this->updateRecord($request, $studentCharge); }
    public function destroy(StudentCharge $studentCharge) { return $this->destroyRecord($studentCharge); }

    public function adjust(Request $request, StudentCharge $studentCharge, StudentAccountService $accountService): JsonResponse
    {
        $adjustment = $accountService->applyAdjustment($studentCharge, $request->validate([
            'type' => ['required', 'string', 'max:40'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['nullable', 'string', 'max:30'],
            'reason' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'approved_at' => ['nullable', 'date'],
        ]));

        return response()->json($adjustment->load('charge.concept'), 201);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'financial_concept_id' => ['required', 'exists:financial_concepts,id'],
            'original_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
