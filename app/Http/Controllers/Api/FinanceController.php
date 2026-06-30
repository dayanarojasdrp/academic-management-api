<?php

namespace App\Http\Controllers\Api;

use App\Models\Finance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends ApiController
{
    protected string $modelClass = Finance::class;

    protected array $relations = ['student', 'enrollment'];

    public function show(Finance $finance) { return $this->showRecord($finance); }
    public function update(Request $request, Finance $finance) { return $this->updateRecord($request, $finance); }
    public function destroy(Finance $finance) { return $this->destroyRecord($finance); }

    public function markPaid(Request $request, Finance $finance): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['required', 'string', 'max:255', 'unique:finances,payment_reference,'.$finance->id],
            'paid_at' => ['nullable', 'date'],
            'status_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $previousStatus = $finance->status;
        $finance->update([
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'],
            'paid_at' => $validated['paid_at'] ?? now()->toDateString(),
            'status' => 'paid',
        ]);
        $this->recordStatusChange($finance, $previousStatus, 'paid', $request);

        return response()->json($finance->fresh()->load($this->relations));
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'concept' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:255', 'unique:finances,payment_reference,'.($record?->id ?? 'NULL')],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
