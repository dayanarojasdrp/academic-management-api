<?php

namespace App\Http\Controllers\Api;

use App\Models\SubjectOffering;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubjectOfferingController extends ApiController
{
    protected string $modelClass = SubjectOffering::class;

    protected array $relations = ['course', 'career', 'group', 'curriculumPlan', 'subject', 'professor', 'schedules'];

    public function show(SubjectOffering $subjectOffering) { return $this->showRecord($subjectOffering); }
    public function update(Request $request, SubjectOffering $subjectOffering) { return $this->updateRecord($request, $subjectOffering); }
    public function destroy(SubjectOffering $subjectOffering) { return $this->destroyRecord($subjectOffering); }

    protected function rules(?Model $record = null): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'career_id' => ['required', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'curriculum_plan_id' => ['required', 'exists:curriculum_plans,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'reserved_seats' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'modality' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:30'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
