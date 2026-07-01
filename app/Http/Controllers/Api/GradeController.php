<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\GradebookService;
use App\Models\Grade;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends ApiController
{
    protected string $modelClass = Grade::class;

    protected array $relations = [
        'student',
        'subject',
        'professor',
        'subjectEnrollment',
        'gradeSheet',
        'gradeComponent',
        'gradingScale',
        'gradingScaleLevel',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = Grade::query()
            ->with($this->relations)
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id');

        ApiQuery::applyEquals($query, $request, [
            'student_id' => 'student_id',
            'subject_enrollment_id' => 'subject_enrollment_id',
            'subject_id' => 'subject_id',
            'professor_id' => 'professor_id',
            'grade_sheet_id' => 'grade_sheet_id',
            'grade_component_id' => 'grade_component_id',
            'grading_scale_id' => 'grading_scale_id',
            'status' => 'status',
            'evaluation_type' => 'evaluation_type',
            'attempt_type' => 'attempt_type',
            'call_number' => 'call_number',
            'partial_number' => 'partial_number',
            'is_final' => 'is_final',
        ]);

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(Grade $grade) { return $this->showRecord($grade); }
    public function destroy(Grade $grade) { return $this->destroyRecord($grade); }

    public function store(Request $request, GradebookService $gradebookService): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $grade = DB::transaction(function () use ($request, $validated, $gradebookService): Grade {
            $grade = new Grade();
            $grade->fill($gradebookService->prepare($validated));
            $grade->save();
            $gradebookService->closeImpactedSubjectEnrollment($grade->fresh(['gradingScale', 'subjectEnrollment']));
            $this->recordStatusChange($grade, null, $grade->status, $request);

            return $grade;
        });

        return response()->json($grade->fresh()->load($this->relations), 201);
    }

    public function update(Request $request, Grade $grade, GradebookService $gradebookService): JsonResponse
    {
        $validated = $request->validate($this->rules($grade));

        $grade = DB::transaction(function () use ($request, $validated, $grade, $gradebookService): Grade {
            $previousStatus = $grade->status;
            $grade->fill($gradebookService->prepare($validated, $grade));
            $grade->save();
            $gradebookService->closeImpactedSubjectEnrollment($grade->fresh(['gradingScale', 'subjectEnrollment']));
            $this->recordStatusChange($grade, $previousStatus, $grade->status, $request);

            return $grade;
        });

        return response()->json($grade->fresh()->load($this->relations));
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'student_id' => ['nullable', 'exists:students,id'],
            'subject_enrollment_id' => ['required', 'exists:subject_enrollments,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'grade_sheet_id' => ['nullable', 'exists:grade_sheets,id'],
            'grade_component_id' => ['nullable', 'exists:grade_components,id'],
            'grading_scale_id' => ['nullable', 'exists:grading_scales,id'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'raw_value' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluation_type' => ['nullable', 'string', 'max:50'],
            'attempt_type' => ['nullable', 'string', 'max:40'],
            'call_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'partial_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_final' => ['nullable', 'boolean'],
            'evaluated_at' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'locked_at' => ['nullable', 'date'],
            'change_authorized_by_user_id' => ['nullable', 'exists:users,id'],
            'change_reason' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
