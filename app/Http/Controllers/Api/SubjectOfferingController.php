<?php

namespace App\Http\Controllers\Api;

use App\Models\SubjectOffering;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectOfferingController extends ApiController
{
    protected string $modelClass = SubjectOffering::class;

    protected array $relations = ['institution', 'campus', 'faculty', 'department', 'modalityCatalog', 'course', 'career', 'group', 'curriculumPlan', 'subject', 'professor', 'schedules'];

    public function show(SubjectOffering $subjectOffering) { return $this->showRecord($subjectOffering); }
    public function update(Request $request, SubjectOffering $subjectOffering) { return $this->updateRecord($request, $subjectOffering); }
    public function destroy(SubjectOffering $subjectOffering) { return $this->destroyRecord($subjectOffering); }

    public function students(SubjectOffering $subjectOffering, Request $request): JsonResponse
    {
        $query = $subjectOffering->subjectEnrollments()
            ->whereIn('subject_enrollments.status', ['enrolled', 'active', 'passed', 'failed'])
            ->whereHas('enrollment', fn ($query) => $query->whereIn('status', ['active', 'enrolled']))
            ->with(['student:id,student_code,first_name,last_name,email,status', 'enrollment:id,status,enrollment_date'])
            ->orderBy('student_id');

        return response()->json(ApiQuery::paginate($query, $request));
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'career_id' => ['required', 'exists:careers,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'modality_id' => ['nullable', 'exists:modalities,id'],
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
