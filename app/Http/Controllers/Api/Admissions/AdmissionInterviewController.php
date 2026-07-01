<?php

namespace App\Http\Controllers\Api\Admissions;

use App\Http\Controllers\Api\ApiController;
use App\Models\AdmissionInterview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionInterviewController extends ApiController
{
    protected string $modelClass = AdmissionInterview::class;

    protected array $relations = ['applicant', 'interviewer'];

    public function show(AdmissionInterview $admissionInterview): JsonResponse { return $this->showRecord($admissionInterview); }
    public function update(Request $request, AdmissionInterview $admissionInterview): JsonResponse { return $this->updateRecord($request, $admissionInterview); }
    public function destroy(AdmissionInterview $admissionInterview): JsonResponse { return $this->destroyRecord($admissionInterview); }

    protected function rules(?Model $record = null): array
    {
        return [
            'applicant_id' => ['required', 'exists:applicants,id'],
            'interviewer_user_id' => ['nullable', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'result' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
