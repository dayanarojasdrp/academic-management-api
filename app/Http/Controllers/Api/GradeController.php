<?php

namespace App\Http\Controllers\Api;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class GradeController extends ApiController
{
    protected string $modelClass = Grade::class;

    protected array $relations = ['student', 'subject', 'professor'];

    public function show(Grade $grade) { return $this->showRecord($grade); }
    public function update(Request $request, Grade $grade) { return $this->updateRecord($request, $grade); }
    public function destroy(Grade $grade) { return $this->destroyRecord($grade); }

    protected function afterSave(Model $record, Request $request): void
    {
        if (! $record->subjectEnrollment || $record->status !== 'published' || $record->value === null) {
            return;
        }

        $previousStatus = $record->subjectEnrollment->status;
        $record->subjectEnrollment->update([
            'status' => $record->value >= 60 ? 'passed' : 'failed',
            'completed_at' => $record->evaluated_at ?? now()->toDateString(),
        ]);
        $this->recordStatusChange($record->subjectEnrollment, $previousStatus, $record->subjectEnrollment->status, $request);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'subject_enrollment_id' => ['nullable', 'exists:subject_enrollments,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluation_type' => ['nullable', 'string', 'max:50'],
            'evaluated_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
