<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\ApiController;
use App\Models\FinancialConcept;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancialConceptController extends ApiController
{
    protected string $modelClass = FinancialConcept::class;

    public function show(FinancialConcept $financialConcept) { return $this->showRecord($financialConcept); }
    public function update(Request $request, FinancialConcept $financialConcept) { return $this->updateRecord($request, $financialConcept); }
    public function destroy(FinancialConcept $financialConcept) { return $this->destroyRecord($financialConcept); }

    protected function rules(?Model $record = null): array
    {
        return [
            'code' => ['required', 'string', 'max:80', Rule::unique('financial_concepts')->ignore($record?->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:40'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_required_for_enrollment' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
