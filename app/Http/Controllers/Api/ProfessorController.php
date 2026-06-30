<?php

namespace App\Http\Controllers\Api;

use App\Models\Professor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessorController extends ApiController
{
    protected string $modelClass = Professor::class;

    protected array $relations = ['subject'];

    public function show(Professor $professor) { return $this->showRecord($professor); }
    public function update(Request $request, Professor $professor) { return $this->updateRecord($request, $professor); }
    public function destroy(Professor $professor) { return $this->destroyRecord($professor); }

    protected function rules(?Model $record = null): array
    {
        return [
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'professor_code' => ['required', 'string', 'max:50', Rule::unique('professors')->ignore($record?->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('professors')->ignore($record?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
