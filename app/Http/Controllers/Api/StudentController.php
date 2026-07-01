<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\PaymentVerifier;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\Academic\AcademicHistoryService;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends ApiController
{
    protected string $modelClass = Student::class;

    protected array $relations = ['group.career', 'group.course', 'currentEnrollment', 'enrollments'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        $query = Student::query()
            ->with(['group:id,name,course_id,career_id', 'currentEnrollment:id,student_id,start_course_id,status'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id');

        ApiQuery::applyLike($query, $request, 'search', ['student_code', 'first_name', 'last_name', 'document_number', 'email']);
        ApiQuery::applyEquals($query, $request, [
            'status' => 'status',
            'group_id' => 'group_id',
        ]);

        return StudentResource::collection(ApiQuery::paginate($query, $request))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Student::class);

        return $this->storeRecord($request);
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        return $this->showRecord($student);
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        return $this->updateRecord($request, $student);
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        return $this->destroyRecord($student);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'group_id' => ['nullable', 'exists:groups,id'],
            'current_enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'student_code' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($record?->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:80', Rule::unique('students')->ignore($record?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students')->ignore($record?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'admission_date' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function paymentStatus(Student $student, PaymentVerifier $paymentVerifier): JsonResponse
    {
        $this->authorize('view', $student);

        $courseId = request()->integer('course_id') ?: null;

        return response()->json([
            'student_id' => $student->id,
            'can_enroll' => $paymentVerifier->studentCanEnroll($student, $courseId),
            'required_payment_concept' => 'enrollment',
            'clearance' => $paymentVerifier->clearance($student, $courseId),
            'latest_payments' => $student->finances()->latest('id')->take(10)->get(),
        ]);
    }

    public function academicSummary(Student $student, AcademicHistoryService $academicHistoryService): JsonResponse
    {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($academicHistoryService->summary($student));
    }

    public function academicHistory(
        Student $student,
        Request $request,
        AcademicHistoryService $academicHistoryService
    ): JsonResponse {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($academicHistoryService->subjectHistory($student, $request));
    }

    public function kardex(
        Student $student,
        Request $request,
        AcademicHistoryService $academicHistoryService
    ): JsonResponse {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($academicHistoryService->kardex($student, $request));
    }

    public function grades(
        Student $student,
        Request $request,
        AcademicHistoryService $academicHistoryService
    ): JsonResponse {
        $this->authorize('viewAcademicHistory', $student);

        return response()->json($academicHistoryService->grades($student, $request));
    }
}
