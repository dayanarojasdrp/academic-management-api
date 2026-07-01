<?php

namespace App\Http\Controllers\Api;

use App\Models\CurriculumPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CurriculumPlanController extends ApiController
{
    protected string $modelClass = CurriculumPlan::class;

    protected array $relations = ['career', 'subjects'];

    public function show(CurriculumPlan $curriculumPlan) { return $this->showRecord($curriculumPlan); }
    public function update(Request $request, CurriculumPlan $curriculumPlan) { return $this->updateRecord($request, $curriculumPlan); }
    public function destroy(CurriculumPlan $curriculumPlan) { return $this->destroyRecord($curriculumPlan); }

    protected function rules(?Model $record = null): array
    {
        return [
            'career_id' => ['required', 'exists:careers,id'],
            'effective_course_id' => ['nullable', 'exists:courses,id'],
            'expires_course_id' => ['nullable', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:30'],
            'duration_semesters' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'is_current' => ['nullable', 'boolean'],
            'subjects' => ['nullable', 'array'],
            'subjects.*.id' => ['required_with:subjects', 'exists:subjects,id'],
            'subjects.*.semester' => ['nullable', 'integer', 'min:1', 'max:20'],
            'subjects.*.is_required' => ['nullable', 'boolean'],
            'subjects.*.prerequisite_subject_id' => ['nullable', 'exists:subjects,id'],
            'subjects.*.minimum_passing_grade' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    protected function afterSave(Model $record, Request $request): void
    {
        if (! $request->has('subjects')) {
            return;
        }

        $subjects = collect($request->input('subjects', []))->mapWithKeys(fn (array $subject) => [
            $subject['id'] => [
                'semester' => $subject['semester'] ?? null,
                'is_required' => $subject['is_required'] ?? true,
                'prerequisite_subject_id' => $subject['prerequisite_subject_id'] ?? null,
                'minimum_passing_grade' => $subject['minimum_passing_grade'] ?? 60,
            ],
        ]);

        $record->subjects()->sync($subjects);
    }
}
