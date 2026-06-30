<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends ApiController
{
    protected string $modelClass = Group::class;

    protected array $relations = ['course', 'career', 'students'];

    public function show(Group $group) { return $this->showRecord($group); }
    public function update(Request $request, Group $group) { return $this->updateRecord($request, $group); }
    public function destroy(Group $group) { return $this->destroyRecord($group); }

    public function students(Group $group): JsonResponse
    {
        return response()->json(
            $group->students()
                ->with(['group.course', 'group.career', 'currentEnrollment'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(50)
        );
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'career_id' => ['required', 'exists:careers,id'],
            'name' => ['required', 'string', 'max:255'],
            'shift' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
