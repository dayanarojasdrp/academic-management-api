<?php

namespace App\Http\Controllers\Api\Finance;

use App\Actions\Academic\StudentAccountService;
use App\Http\Controllers\Api\ApiController;
use App\Models\StudentPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentPaymentController extends ApiController
{
    protected string $modelClass = StudentPayment::class;

    protected array $relations = ['student', 'enrollment', 'allocations.charge.concept', 'receipt'];

    public function store(Request $request, StudentAccountService $accountService): JsonResponse
    {
        $payment = $accountService->recordPayment($request->validate($this->rules()));
        $this->recordStatusChange($payment, null, $payment->status, $request);

        return response()->json($payment->load($this->relations), 201);
    }

    public function show(StudentPayment $studentPayment) { return $this->showRecord($studentPayment); }
    public function update(Request $request, StudentPayment $studentPayment) { return $this->updateRecord($request, $studentPayment); }
    public function destroy(StudentPayment $studentPayment) { return $this->destroyRecord($studentPayment); }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['required', 'string', 'max:255', Rule::unique('student_payments')->ignore($record?->id)],
            'paid_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'received_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'receipt_number' => ['nullable', 'string', 'max:255', 'unique:payment_receipts,receipt_number'],
            'receipt_metadata' => ['nullable', 'array'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.student_charge_id' => ['required_with:allocations', 'exists:student_charges,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ];
    }
}
