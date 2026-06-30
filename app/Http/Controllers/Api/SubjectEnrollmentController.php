<?php

namespace App\Http\Controllers\Api;

use App\Models\Enrollment;
use App\Models\SubjectEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectEnrollmentController extends ApiController
{
    protected string $modelClass = SubjectEnrollment::class;

    protected array $relations = ['enrollment', 'student', 'subject', 'course', 'career', 'group', 'grades'];

    public function show(SubjectEnrollment $subjectEnrollment) { return $this->showRecord($subjectEnrollment); }
    public function update(Request $request, SubjectEnrollment $subjectEnrollment) { return $this->updateRecord($request, $subjectEnrollment); }
    public function destroy(SubjectEnrollment $subjectEnrollment) { return $this->destroyRecord($subjectEnrollment); }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $enrollment = Enrollment::with('student.group')->findOrFail($validated['enrollment_id']);

        $subjectEnrollment = SubjectEnrollment::create($this->completeEnrollmentData($validated, $enrollment));
        $this->recordStatusChange($subjectEnrollment, null, $subjectEnrollment->status, $request);

        return response()->json($subjectEnrollment->load($this->relations), 201);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'enrolled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:enrolled_at'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $enrollment = Enrollment::with('student.group')->find($record->enrollment_id);

        if (! $enrollment) {
            return;
        }

        $record->fill($this->completeEnrollmentData($record->toArray(), $enrollment));
        $record->saveQuietly();
    }

    private function completeEnrollmentData(array $data, Enrollment $enrollment): array
    {
        return array_merge($data, [
            'student_id' => $data['student_id'] ?? $enrollment->student_id,
            'course_id' => $data['course_id'] ?? $enrollment->start_course_id,
            'career_id' => $data['career_id'] ?? $enrollment->student?->group?->career_id,
            'group_id' => $data['group_id'] ?? $enrollment->student?->group_id,
            'enrolled_at' => $data['enrolled_at'] ?? now()->toDateString(),
            'status' => $data['status'] ?? 'enrolled',
        ]);
    }
}
