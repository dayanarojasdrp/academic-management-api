<?php

namespace App\Http\Controllers\Api\Admissions;

use App\Http\Controllers\Api\ApiController;
use App\Models\ApplicationDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationDocumentController extends ApiController
{
    protected string $modelClass = ApplicationDocument::class;

    protected array $relations = ['applicant', 'verifiedBy'];

    public function show(ApplicationDocument $applicationDocument): JsonResponse { return $this->showRecord($applicationDocument); }
    public function update(Request $request, ApplicationDocument $applicationDocument): JsonResponse { return $this->updateRecord($request, $applicationDocument); }
    public function destroy(ApplicationDocument $applicationDocument): JsonResponse { return $this->destroyRecord($applicationDocument); }

    protected function rules(?Model $record = null): array
    {
        return [
            'applicant_id' => ['required', 'exists:applicants,id'],
            'type' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:255'],
            'file_hash' => ['nullable', 'string', 'max:128'],
            'status' => ['nullable', 'string', 'max:30'],
            'verified_by_user_id' => ['nullable', 'exists:users,id'],
            'verified_at' => ['nullable', 'date'],
            'rejection_reason' => ['nullable', 'string'],
        ];
    }
}
