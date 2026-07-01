<?php

namespace App\Http\Requests\SubjectEnrollments;

use App\Models\SubjectEnrollment;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SubjectEnrollment::class) ?? false;
    }

    public function rules(): array
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
