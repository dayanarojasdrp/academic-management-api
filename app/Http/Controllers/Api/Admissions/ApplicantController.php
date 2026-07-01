<?php

namespace App\Http\Controllers\Api\Admissions;

use App\Http\Controllers\Api\ApiController;
use App\Models\Applicant;
use App\Models\Student;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApplicantController extends ApiController
{
    protected string $modelClass = Applicant::class;

    protected array $relations = ['institution', 'campus', 'career', 'course', 'group', 'student', 'documents', 'interviews', 'decisions'];

    public function index(Request $request): JsonResponse
    {
        $query = Applicant::query()
            ->with(['career:id,name,abbreviation', 'course:id,name', 'group:id,name', 'student:id,student_code'])
            ->orderByDesc('application_date')
            ->orderByDesc('id');

        ApiQuery::applyLike($query, $request, 'search', ['applicant_code', 'first_name', 'last_name', 'document_number', 'email']);
        ApiQuery::applyEquals($query, $request, [
            'institution_id' => 'institution_id',
            'campus_id' => 'campus_id',
            'career_id' => 'career_id',
            'course_id' => 'course_id',
            'group_id' => 'group_id',
            'status' => 'status',
        ]);

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(Applicant $applicant): JsonResponse
    {
        return $this->showRecord($applicant);
    }

    public function update(Request $request, Applicant $applicant): JsonResponse
    {
        return $this->updateRecord($request, $applicant);
    }

    public function destroy(Applicant $applicant): JsonResponse
    {
        return $this->destroyRecord($applicant);
    }

    public function submit(Request $request, Applicant $applicant): JsonResponse
    {
        $this->transition($request, $applicant, ['draft', 'returned'], 'submitted');

        return response()->json($applicant->fresh()->load($this->relations));
    }

    public function convert(Request $request, Applicant $applicant): JsonResponse
    {
        $payload = $request->validate([
            'student_code' => ['required', 'string', 'max:50', Rule::unique('students', 'student_code')],
            'group_id' => ['nullable', 'exists:groups,id'],
            'admission_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        if ($applicant->student_id) {
            throw ValidationException::withMessages(['student_id' => 'El aspirante ya fue convertido a estudiante.']);
        }

        if (Student::query()->where('document_number', $applicant->document_number)->exists()) {
            throw ValidationException::withMessages(['document_number' => 'Ya existe un estudiante con el documento del aspirante.']);
        }

        $latestDecision = $applicant->decisions()->latest('decision_date')->latest('id')->first();
        if (! $latestDecision || $latestDecision->decision !== 'approved') {
            throw ValidationException::withMessages(['decision' => 'Solo un aspirante aprobado puede convertirse a estudiante.']);
        }

        $student = DB::transaction(function () use ($request, $payload, $applicant): Student {
            $student = Student::create([
                'group_id' => $payload['group_id'] ?? $applicant->group_id,
                'student_code' => $payload['student_code'],
                'first_name' => $applicant->first_name,
                'last_name' => $applicant->last_name,
                'document_type' => $applicant->document_type,
                'document_number' => $applicant->document_number,
                'email' => $applicant->email,
                'phone' => $applicant->phone,
                'birth_date' => $applicant->birth_date,
                'admission_date' => $payload['admission_date'] ?? now()->toDateString(),
                'status' => $payload['status'] ?? 'active',
            ]);

            $previousStatus = $applicant->status;
            $applicant->update([
                'student_id' => $student->id,
                'group_id' => $student->group_id,
                'status' => 'converted',
            ]);
            $this->recordStatusChange($applicant, $previousStatus, 'converted', $request);

            return $student;
        });

        return response()->json([
            'applicant' => $applicant->fresh()->load($this->relations),
            'student' => $student->load(['group.career', 'group.course']),
        ], 201);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'applicant_code' => ['required', 'string', 'max:50', Rule::unique('applicants')->ignore($record?->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => [
                'required',
                'string',
                'max:80',
                Rule::unique('applicants')->where(fn ($query) => $query->where('document_type', request('document_type')))->ignore($record?->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'application_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function transition(Request $request, Applicant $applicant, array $allowedFrom, string $status): void
    {
        if (! in_array($applicant->status, $allowedFrom, true)) {
            throw ValidationException::withMessages(['status' => 'El aspirante no puede pasar de '.$applicant->status.' a '.$status.'.']);
        }

        $previousStatus = $applicant->status;
        $applicant->update(['status' => $status]);
        $this->recordStatusChange($applicant, $previousStatus, $status, $request);
    }
}
