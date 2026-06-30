<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\EnrollStudent;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends ApiController
{
    protected string $modelClass = Enrollment::class;

    protected array $relations = ['student', 'startCourse', 'endCourse'];

    public function store(Request $request, EnrollStudent $enrollStudent): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $enrollment = $enrollStudent->handle($validated);
        $this->recordStatusChange($enrollment, null, $enrollment->status, $request);

        return (new EnrollmentResource($enrollment->load($this->relations)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Enrollment $enrollment) { return $this->showRecord($enrollment); }
    public function update(Request $request, Enrollment $enrollment) { return $this->updateRecord($request, $enrollment); }
    public function destroy(Enrollment $enrollment) { return $this->destroyRecord($enrollment); }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'start_course_id' => ['required', 'exists:courses,id'],
            'end_course_id' => ['nullable', 'exists:courses,id'],
            'enrollment_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
