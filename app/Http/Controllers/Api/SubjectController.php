<?php

namespace App\Http\Controllers\Api;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends ApiController
{
    protected string $modelClass = Subject::class;

    protected array $relations = ['professors'];

    public function show(Subject $subject) { return $this->showRecord($subject); }
    public function update(Request $request, Subject $subject) { return $this->updateRecord($request, $subject); }
    public function destroy(Subject $subject) { return $this->destroyRecord($subject); }

    protected function rules(?Model $record = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('subjects')->ignore($record?->id)],
            'name' => ['required', 'string', 'max:255'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:100'],
            'weekly_hours' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
