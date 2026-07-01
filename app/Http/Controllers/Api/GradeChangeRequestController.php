<?php

namespace App\Http\Controllers\Api;

use App\Actions\Academic\GradebookService;
use App\Models\GradeChangeRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeChangeRequestController extends ApiController
{
    protected string $modelClass = GradeChangeRequest::class;

    protected array $relations = ['grade.student', 'grade.subject', 'requestedByUser', 'approvedByUser'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GradeChangeRequest::class);

        return parent::index($request);
    }

    public function show(GradeChangeRequest $gradeChangeRequest)
    {
        $this->authorize('view', $gradeChangeRequest);

        return $this->showRecord($gradeChangeRequest);
    }

    public function update(Request $request, GradeChangeRequest $gradeChangeRequest) { return $this->updateRecord($request, $gradeChangeRequest); }
    public function destroy(GradeChangeRequest $gradeChangeRequest) { return $this->destroyRecord($gradeChangeRequest); }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', GradeChangeRequest::class);

        $validated = $request->validate($this->rules());
        $grade = \App\Models\Grade::query()->findOrFail($validated['grade_id']);
        $gradeChangeRequest = GradeChangeRequest::create(array_merge($validated, [
            'requested_by_user_id' => $validated['requested_by_user_id'] ?? $request->user()?->id,
            'current_value' => $validated['current_value'] ?? $grade->value,
            'status' => $validated['status'] ?? 'pending',
        ]));
        $this->recordStatusChange($gradeChangeRequest, null, $gradeChangeRequest->status, $request);

        return response()->json($gradeChangeRequest->load($this->relations), 201);
    }

    public function approve(
        Request $request,
        GradeChangeRequest $gradeChangeRequest,
        GradebookService $gradebookService
    ): JsonResponse {
        $this->authorize('approve', $gradeChangeRequest);

        $payload = $request->validate([
            'decision_reason' => ['nullable', 'string'],
        ]);

        if ($gradeChangeRequest->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'La solicitud ya fue decidida.']);
        }

        DB::transaction(function () use ($request, $payload, $gradeChangeRequest, $gradebookService): void {
            $grade = $gradeChangeRequest->grade()->lockForUpdate()->firstOrFail();
            $grade->fill($gradebookService->prepare([
                'subject_enrollment_id' => $grade->subject_enrollment_id,
                'raw_value' => $gradeChangeRequest->requested_value,
                'change_authorized_by_user_id' => $request->user()?->id,
                'change_reason' => $gradeChangeRequest->reason,
                'status' => 'published',
            ], $grade));
            $grade->save();
            $gradebookService->closeImpactedSubjectEnrollment($grade->fresh(['gradingScale', 'subjectEnrollment']));

            $previousStatus = $gradeChangeRequest->status;
            $gradeChangeRequest->update([
                'approved_by_user_id' => $request->user()?->id,
                'status' => 'approved',
                'decision_reason' => $payload['decision_reason'] ?? null,
                'decided_at' => now(),
            ]);
            $this->recordStatusChange($gradeChangeRequest, $previousStatus, 'approved', $request);
        });

        return response()->json($gradeChangeRequest->fresh()->load($this->relations));
    }

    public function reject(Request $request, GradeChangeRequest $gradeChangeRequest): JsonResponse
    {
        $this->authorize('reject', $gradeChangeRequest);

        $payload = $request->validate([
            'decision_reason' => ['required', 'string'],
        ]);

        if ($gradeChangeRequest->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'La solicitud ya fue decidida.']);
        }

        $previousStatus = $gradeChangeRequest->status;
        $gradeChangeRequest->update([
            'approved_by_user_id' => $request->user()?->id,
            'status' => 'rejected',
            'decision_reason' => $payload['decision_reason'],
            'decided_at' => now(),
        ]);
        $this->recordStatusChange($gradeChangeRequest, $previousStatus, 'rejected', $request);

        return response()->json($gradeChangeRequest->fresh()->load($this->relations));
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'requested_by_user_id' => ['nullable', 'exists:users,id'],
            'approved_by_user_id' => ['nullable', 'exists:users,id'],
            'current_value' => ['nullable', 'numeric'],
            'requested_value' => ['required', 'numeric'],
            'status' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string'],
            'decision_reason' => ['nullable', 'string'],
            'decided_at' => ['nullable', 'date'],
        ];
    }

}
