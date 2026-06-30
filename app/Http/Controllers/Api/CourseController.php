<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\SubjectEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends ApiController
{
    protected string $modelClass = Course::class;

    protected array $relations = ['groups'];

    public function show(Course $course) { return $this->showRecord($course); }
    public function update(Request $request, Course $course) { return $this->updateRecord($request, $course); }
    public function destroy(Course $course) { return $this->destroyRecord($course); }

    public function subjectEnrollments(Course $course): JsonResponse
    {
        return response()->json(
            SubjectEnrollment::query()
                ->where('course_id', $course->id)
                ->with(['student', 'subject', 'career', 'group', 'grades'])
                ->latest('id')
                ->paginate(50)
        );
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
