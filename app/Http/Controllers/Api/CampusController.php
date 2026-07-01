<?php

namespace App\Http\Controllers\Api;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampusController extends ApiController
{
    protected string $modelClass = Campus::class;

    protected array $relations = ['institution'];

    public function show(Campus $campus) { return $this->showRecord($campus); }
    public function update(Request $request, Campus $campus) { return $this->updateRecord($request, $campus); }
    public function destroy(Campus $campus) { return $this->destroyRecord($campus); }

    protected function rules(?Model $record = null): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('campuses')
                    ->where(fn ($query) => $query->where('institution_id', request('institution_id')))
                    ->ignore($record?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
