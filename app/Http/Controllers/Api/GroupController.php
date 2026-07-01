<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends ApiController
{
    protected string $modelClass = Group::class;

    protected array $relations = ['institution', 'campus', 'faculty', 'department', 'modality', 'course', 'career', 'students'];

    public function show(Group $group) { return $this->showRecord($group); }
    public function update(Request $request, Group $group) { return $this->updateRecord($request, $group); }
    public function destroy(Group $group) { return $this->destroyRecord($group); }

    public function students(Group $group): JsonResponse
    {
        $query = $group->students()
            ->with(['group.course:id,name,start_date,end_date,status', 'group.career:id,name,abbreviation', 'currentEnrollment'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id');

        return \App\Http\Resources\StudentResource::collection(ApiQuery::paginate($query, request(), 50))->response();
    }

    protected function rules(?Model $record = null): array
    {
        $institutionId = request('institution_id', $record?->institution_id);
        $campusId = request('campus_id', $record?->campus_id);
        $courseId = request('course_id', $record?->course_id);
        $careerId = request('career_id', $record?->career_id);

        return [
            'course_id' => ['required', 'exists:courses,id'],
            'career_id' => ['required', 'exists:careers,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'modality_id' => ['nullable', 'exists:modalities,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups')
                    ->where(fn ($query) => $query
                        ->where('institution_id', $institutionId)
                        ->where('campus_id', $campusId)
                        ->where('course_id', $courseId)
                        ->where('career_id', $careerId))
                    ->ignore($record?->id),
            ],
            'shift' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
