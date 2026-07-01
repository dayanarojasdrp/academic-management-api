<?php

namespace App\Http\Controllers\Api\Admissions;

use App\Http\Controllers\Api\ApiController;
use App\Models\Applicant;
use App\Models\AdmissionDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionDecisionController extends ApiController
{
    protected string $modelClass = AdmissionDecision::class;

    protected array $relations = ['applicant', 'decidedBy'];

    public function show(AdmissionDecision $admissionDecision): JsonResponse { return $this->showRecord($admissionDecision); }
    public function update(Request $request, AdmissionDecision $admissionDecision): JsonResponse { return $this->updateRecord($request, $admissionDecision); }
    public function destroy(AdmissionDecision $admissionDecision): JsonResponse { return $this->destroyRecord($admissionDecision); }

    protected function afterSave(Model $record, Request $request): void
    {
        if (! $record instanceof AdmissionDecision || ! in_array($record->decision, ['approved', 'rejected', 'waitlisted'], true)) {
            return;
        }

        $applicant = Applicant::query()->find($record->applicant_id);
        if (! $applicant || $applicant->status === 'converted') {
            return;
        }

        $previousStatus = $applicant->status;
        $applicant->update(['status' => $record->decision]);
        $this->recordStatusChange($applicant, $previousStatus, $record->decision, $request);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'applicant_id' => ['required', 'exists:applicants,id'],
            'decided_by_user_id' => ['nullable', 'exists:users,id'],
            'decision' => ['required', 'string', 'max:30'],
            'decision_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:decision_date'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reason' => ['nullable', 'string'],
            'conditions' => ['nullable', 'array'],
        ];
    }
}
