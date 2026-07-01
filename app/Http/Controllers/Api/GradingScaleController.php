<?php

namespace App\Http\Controllers\Api;

use App\Models\GradingScale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradingScaleController extends ApiController
{
    protected string $modelClass = GradingScale::class;

    protected array $relations = ['levels'];

    public function show(GradingScale $gradingScale) { return $this->showRecord($gradingScale); }
    public function update(Request $request, GradingScale $gradingScale) { return $this->updateRecord($request, $gradingScale); }
    public function destroy(GradingScale $gradingScale) { return $this->destroyRecord($gradingScale); }

    protected function rules(?Model $record = null): array
    {
        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('grading_scales')->ignore($record?->id)],
            'name' => ['required', 'string', 'max:255'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric', 'gt:min_value'],
            'passing_value' => ['nullable', 'numeric', 'gte:min_value', 'lte:max_value'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ];
    }
}
