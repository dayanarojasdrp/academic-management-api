<?php

namespace App\Http\Requests\Enrollments;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Enrollment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'start_course_id' => ['required', 'exists:courses,id'],
            'end_course_id' => ['nullable', 'exists:courses,id'],
            'enrollment_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
