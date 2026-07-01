<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\PaymentVerifier;
use App\Http\Resources\StudentResource;
use App\Models\Student;
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

    public function show(Student $student) { return $this->showRecord($student); }
    public function update(Request $request, Student $student) { return $this->updateRecord($request, $student); }
    public function destroy(Student $student) { return $this->destroyRecord($student); }

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
        $courseId = request()->integer('course_id') ?: null;

        return response()->json([
            'student_id' => $student->id,
            'can_enroll' => $paymentVerifier->studentCanEnroll($student, $courseId),
            'required_payment_concept' => 'enrollment',
            'clearance' => $paymentVerifier->clearance($student, $courseId),
            'latest_payments' => $student->finances()->latest('id')->take(10)->get(),
        ]);
    }

    public function academicHistory(Student $student): JsonResponse
    {
        return response()->json([
            'student' => $student->load(['group.course', 'group.career', 'currentEnrollment']),
            'subject_enrollments' => $student->subjectEnrollments()
                ->with(['subject', 'course', 'career', 'group', 'grades.professor'])
                ->latest('id')
                ->get(),
            'passed_subjects' => $student->subjectEnrollments()
                ->where('status', 'passed')
                ->with(['subject', 'course', 'career', 'grades.professor'])
                ->get(),
        ]);
    }
}
