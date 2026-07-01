<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\ApiController;
use App\Models\FinancialHold;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FinancialHoldController extends ApiController
{
    protected string $modelClass = FinancialHold::class;

    protected array $relations = ['student', 'course'];

    public function show(FinancialHold $financialHold) { return $this->showRecord($financialHold); }
    public function update(Request $request, FinancialHold $financialHold) { return $this->updateRecord($request, $financialHold); }
    public function destroy(FinancialHold $financialHold) { return $this->destroyRecord($financialHold); }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'reason' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'placed_at' => ['nullable', 'date'],
            'released_at' => ['nullable', 'date'],
            'released_by' => ['nullable', 'exists:users,id'],
            'release_reason' => ['nullable', 'string'],
        ];
    }
}
