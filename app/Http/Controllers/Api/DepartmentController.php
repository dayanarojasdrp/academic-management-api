<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends ApiController
{
    protected string $modelClass = Department::class;

    protected array $relations = ['institution', 'faculty', 'campus'];

    public function show(Department $department) { return $this->showRecord($department); }
    public function update(Request $request, Department $department) { return $this->updateRecord($request, $department); }
    public function destroy(Department $department) { return $this->destroyRecord($department); }

    protected function rules(?Model $record = null): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('departments')
                    ->where(fn ($query) => $query->where('institution_id', request('institution_id')))
                    ->ignore($record?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
