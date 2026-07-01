<?php

namespace App\Http\Requests\Grades;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('grade')) ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['nullable', 'exists:students,id'],
            'subject_enrollment_id' => ['required', 'exists:subject_enrollments,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'grade_sheet_id' => ['nullable', 'exists:grade_sheets,id'],
            'grade_component_id' => ['nullable', 'exists:grade_components,id'],
            'grading_scale_id' => ['nullable', 'exists:grading_scales,id'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'raw_value' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluation_type' => ['nullable', 'string', 'max:50'],
            'attempt_type' => ['nullable', 'string', 'max:40'],
            'call_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'partial_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_final' => ['nullable', 'boolean'],
            'evaluated_at' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'locked_at' => ['nullable', 'date'],
            'change_authorized_by_user_id' => ['nullable', 'exists:users,id'],
            'change_reason' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
