<?php

namespace App\Http\Controllers\Api;

use App\Models\GradingScaleLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradingScaleLevelController extends ApiController
{
    protected string $modelClass = GradingScaleLevel::class;

    protected array $relations = ['gradingScale'];

    public function show(GradingScaleLevel $gradingScaleLevel) { return $this->showRecord($gradingScaleLevel); }
    public function update(Request $request, GradingScaleLevel $gradingScaleLevel) { return $this->updateRecord($request, $gradingScaleLevel); }
    public function destroy(GradingScaleLevel $gradingScaleLevel) { return $this->destroyRecord($gradingScaleLevel); }

    protected function rules(?Model $record = null): array
    {
        return [
            'grading_scale_id' => ['required', 'exists:grading_scales,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('grading_scale_levels')
                    ->where(fn ($query) => $query->where('grading_scale_id', request('grading_scale_id')))
                    ->ignore($record?->id),
            ],
            'label' => ['required', 'string', 'max:255'],
            'min_value' => ['required', 'numeric'],
            'max_value' => ['required', 'numeric', 'gte:min_value'],
            'grade_points' => ['nullable', 'numeric'],
            'is_passing' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
