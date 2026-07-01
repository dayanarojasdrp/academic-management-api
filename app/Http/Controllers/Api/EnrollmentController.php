<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\EnrollStudent;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends ApiController
{
    protected string $modelClass = Enrollment::class;

    protected array $relations = ['student', 'startCourse', 'endCourse'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Enrollment::class);

        return parent::index($request);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Enrollment::class);

        /** @var EnrollStudent $enrollStudent */
        $enrollStudent = app(EnrollStudent::class);
        $validated = $request->validate($this->rules());
        $enrollment = $enrollStudent->handle($validated);
        $this->recordStatusChange($enrollment, null, $enrollment->status, $request);

        return (new EnrollmentResource($enrollment->load($this->relations)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Enrollment $enrollment)
    {
        $this->authorize('view', $enrollment);

        return $this->showRecord($enrollment);
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $this->authorize('update', $enrollment);

        return $this->updateRecord($request, $enrollment);
    }

    public function destroy(Enrollment $enrollment)
    {
        $this->authorize('delete', $enrollment);

        return $this->destroyRecord($enrollment);
    }

    public function submit(Request $request, Enrollment $enrollment): JsonResponse
    {
        return $this->transition($request, $enrollment, ['draft', 'pending'], 'pending_payment');
    }

    public function confirmPayment(Request $request, Enrollment $enrollment): JsonResponse
    {
        return $this->transition($request, $enrollment, ['pending_payment', 'pending'], 'payment_confirmed');
    }

    public function activate(Request $request, Enrollment $enrollment): JsonResponse
    {
        $response = $this->transition($request, $enrollment, ['payment_confirmed', 'pending_payment', 'active'], 'active');
        $enrollment->student()->update(['status' => 'active', 'current_enrollment_id' => $enrollment->id]);

        return $response;
    }

    public function cancel(Request $request, Enrollment $enrollment): JsonResponse
    {
        return $this->transition($request, $enrollment, ['draft', 'pending', 'pending_payment', 'payment_confirmed', 'active'], 'cancelled');
    }

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

    private function transition(Request $request, Enrollment $enrollment, array $allowedFrom, string $status): JsonResponse
    {
        if (! in_array($enrollment->status, $allowedFrom, true)) {
            throw ValidationException::withMessages([
                'status' => 'La matricula no puede pasar de '.$enrollment->status.' a '.$status.'.',
            ]);
        }

        $previousStatus = $enrollment->status;
        $enrollment->update(['status' => $status]);
        $this->recordStatusChange($enrollment, $previousStatus, $status, $request);

        return response()->json($enrollment->fresh()->load($this->relations));
    }
}
