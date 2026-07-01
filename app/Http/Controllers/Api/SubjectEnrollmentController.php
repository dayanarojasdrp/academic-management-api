<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\RegisterSubjectEnrollment;
use App\Http\Resources\SubjectEnrollmentResource;
use App\Models\SubjectEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectEnrollmentController extends ApiController
{
    protected string $modelClass = SubjectEnrollment::class;

    protected array $relations = ['enrollment', 'student', 'subject', 'subjectOffering.schedules', 'curriculumPlan', 'course', 'career', 'group', 'grades'];

    public function show(SubjectEnrollment $subjectEnrollment) { return $this->showRecord($subjectEnrollment); }
    public function update(Request $request, SubjectEnrollment $subjectEnrollment) { return $this->updateRecord($request, $subjectEnrollment); }
    public function destroy(SubjectEnrollment $subjectEnrollment) { return $this->destroyRecord($subjectEnrollment); }

    public function store(Request $request, RegisterSubjectEnrollment $registerSubjectEnrollment): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $subjectEnrollment = $registerSubjectEnrollment->handle($validated);
        $this->recordStatusChange($subjectEnrollment, null, $subjectEnrollment->status, $request);

        return (new SubjectEnrollmentResource($subjectEnrollment->load($this->relations)))
            ->response()
            ->setStatusCode(201);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'curriculum_plan_id' => ['nullable', 'exists:curriculum_plans,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:20'],
            'enrolled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:enrolled_at'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

}
