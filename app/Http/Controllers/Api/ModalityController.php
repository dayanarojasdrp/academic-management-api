<?php

namespace App\Http\Controllers\Api;

use App\Models\Modality;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModalityController extends ApiController
{
    protected string $modelClass = Modality::class;

    protected array $relations = ['institution'];

    public function show(Modality $modality) { return $this->showRecord($modality); }
    public function update(Request $request, Modality $modality) { return $this->updateRecord($request, $modality); }
    public function destroy(Modality $modality) { return $this->destroyRecord($modality); }

    protected function rules(?Model $record = null): array
    {
        return [
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('modalities')
                    ->where(fn ($query) => $query->where('institution_id', request('institution_id')))
                    ->ignore($record?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requires_classroom' => ['nullable', 'boolean'],
            'requires_online_platform' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
