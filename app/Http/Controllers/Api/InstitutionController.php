<?php

namespace App\Http\Controllers\Api;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitutionController extends ApiController
{
    protected string $modelClass = Institution::class;

    protected array $relations = ['campuses', 'faculties', 'departments', 'modalities'];

    public function show(Institution $institution) { return $this->showRecord($institution); }
    public function update(Request $request, Institution $institution) { return $this->updateRecord($request, $institution); }
    public function destroy(Institution $institution) { return $this->destroyRecord($institution); }

    protected function rules(?Model $record = null): array
    {
        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('institutions')->ignore($record?->id)],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:30'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
