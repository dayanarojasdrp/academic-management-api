<?php

namespace App\Http\Requests\Students;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Student::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'group_id' => ['nullable', 'exists:groups,id'],
            'current_enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'student_code' => ['required', 'string', 'max:50', Rule::unique('students')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:80', Rule::unique('students')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students')],
            'phone' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'admission_date' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
