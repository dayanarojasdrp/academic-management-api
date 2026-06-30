<?php

namespace App\Http\Controllers\Api;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends ApiController
{
    protected string $modelClass = Enrollment::class;

    protected array $relations = ['student', 'startCourse', 'endCourse'];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $student = Student::findOrFail($validated['student_id']);

        $hasValidPayment = $student->finances()
            ->where('concept', 'enrollment')
            ->where('status', 'paid')
            ->exists();

        if (! $hasValidPayment) {
            throw ValidationException::withMessages([
                'student_id' => 'El estudiante no puede matricularse hasta tener un pago de matricula validado con status paid.',
            ]);
        }

        $enrollment = Enrollment::create($validated);
        $student->update(['current_enrollment_id' => $enrollment->id]);
        $this->recordStatusChange($enrollment, null, $enrollment->status, $request);

        return response()->json($enrollment->load($this->relations), 201);
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
