<?php

namespace App\Http\Controllers\Api;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultyController extends ApiController
{
    protected string $modelClass = Faculty::class;

    protected array $relations = ['institution', 'campus', 'departments'];

    public function show(Faculty $faculty) { return $this->showRecord($faculty); }
    public function update(Request $request, Faculty $faculty) { return $this->updateRecord($request, $faculty); }
    public function destroy(Faculty $faculty) { return $this->destroyRecord($faculty); }

    protected function rules(?Model $record = null): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('faculties')
                    ->where(fn ($query) => $query->where('institution_id', request('institution_id')))
                    ->ignore($record?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
