<?php

namespace App\Http\Requests\Grades;

use App\Models\GradeChangeRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreGradeChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GradeChangeRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'requested_by_user_id' => ['nullable', 'exists:users,id'],
            'approved_by_user_id' => ['nullable', 'exists:users,id'],
            'current_value' => ['nullable', 'numeric'],
            'requested_value' => ['required', 'numeric'],
            'status' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string'],
            'decision_reason' => ['nullable', 'string'],
            'decided_at' => ['nullable', 'date'],
        ];
    }
}
