<?php

namespace App\Http\Requests\GradeSheets;

use App\Models\GradeSheet;
use Illuminate\Foundation\Http\FormRequest;

class StoreGradeSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GradeSheet::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'grading_scale_id' => ['nullable', 'exists:grading_scales,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'sheet_type' => ['nullable', 'string', 'max:40'],
            'call_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'partial_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'opened_at' => ['nullable', 'date'],
            'submitted_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
