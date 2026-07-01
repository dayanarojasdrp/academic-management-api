<?php

namespace App\Http\Controllers\Api;

use App\Models\GradeComponent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeComponentController extends ApiController
{
    protected string $modelClass = GradeComponent::class;

    protected array $relations = ['subjectOffering.subject', 'subjectOffering.course'];

    public function show(GradeComponent $gradeComponent) { return $this->showRecord($gradeComponent); }
    public function update(Request $request, GradeComponent $gradeComponent) { return $this->updateRecord($request, $gradeComponent); }
    public function destroy(GradeComponent $gradeComponent) { return $this->destroyRecord($gradeComponent); }

    protected function rules(?Model $record = null): array
    {
        return [
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('grade_components')
                    ->where(fn ($query) => $query->where('subject_offering_id', request('subject_offering_id')))
                    ->ignore($record?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:40'],
            'term' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'numeric', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
