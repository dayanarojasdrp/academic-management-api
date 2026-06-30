<?php

namespace App\Http\Controllers\Api;

use App\Models\Career;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CareerController extends ApiController
{
    protected string $modelClass = Career::class;

    protected array $relations = ['curriculumPlans', 'groups'];

    public function show(Career $career)
    {
        return $this->showRecord($career);
    }

    public function update(Request $request, Career $career)
    {
        return $this->updateRecord($request, $career);
    }

    public function destroy(Career $career)
    {
        return $this->destroyRecord($career);
    }

    public function subjects(Career $career): JsonResponse
    {
        $subjects = Subject::query()
            ->whereHas('curriculumPlans', fn ($query) => $query->where('career_id', $career->id))
            ->with(['curriculumPlans' => fn ($query) => $query->where('career_id', $career->id)])
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    public function subjectEnrollments(Career $career): JsonResponse
    {
        return response()->json(
            $career->hasMany(\App\Models\SubjectEnrollment::class)
                ->with(['student', 'subject', 'course', 'group', 'grades'])
                ->latest('id')
                ->paginate(50)
        );
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('careers')->ignore($record?->id)],
            'abbreviation' => ['required', 'string', 'max:20', Rule::unique('careers')->ignore($record?->id)],
            'description' => ['nullable', 'string'],
        ];
    }
}
