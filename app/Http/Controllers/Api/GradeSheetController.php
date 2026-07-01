<?php

namespace App\Http\Controllers\Api;

use App\Models\GradeSheet;
use App\Models\SubjectOffering;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GradeSheetController extends ApiController
{
    protected string $modelClass = GradeSheet::class;

    protected array $relations = ['subjectOffering', 'professor', 'gradingScale', 'course', 'career', 'group', 'subject', 'grades'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GradeSheet::class);

        return parent::index($request);
    }

    public function show(GradeSheet $gradeSheet)
    {
        $this->authorize('view', $gradeSheet);

        return $this->showRecord($gradeSheet);
    }

    public function destroy(GradeSheet $gradeSheet)
    {
        $this->authorize('delete', $gradeSheet);

        return $this->destroyRecord($gradeSheet);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', GradeSheet::class);

        $validated = $this->hydrateFromOffering($request->validate($this->rules()));
        $gradeSheet = GradeSheet::create($validated);
        $this->recordStatusChange($gradeSheet, null, $gradeSheet->status, $request);

        return response()->json($gradeSheet->load($this->relations), 201);
    }

    public function update(Request $request, GradeSheet $gradeSheet): JsonResponse
    {
        $this->authorize('update', $gradeSheet);

        $previousStatus = $gradeSheet->status;
        $gradeSheet->update($this->hydrateFromOffering($request->validate($this->rules($gradeSheet))));
        $this->recordStatusChange($gradeSheet, $previousStatus, $gradeSheet->status, $request);

        return response()->json($gradeSheet->fresh()->load($this->relations));
    }

    public function submit(Request $request, GradeSheet $gradeSheet): JsonResponse
    {
        $this->authorize('submit', $gradeSheet);

        $this->transition($request, $gradeSheet, ['draft', 'reopened'], 'submitted', ['submitted_at' => now()]);

        return response()->json($gradeSheet->fresh()->load($this->relations));
    }

    public function sign(Request $request, GradeSheet $gradeSheet): JsonResponse
    {
        $this->authorize('sign', $gradeSheet);

        $payload = $request->validate([
            'signature_secret' => ['nullable', 'string', 'max:255'],
            'status_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->transition($request, $gradeSheet, ['submitted'], 'signed', [
            'signed_at' => now(),
            'signed_by_user_id' => $request->user()?->id,
            'signature_hash' => hash('sha256', implode('|', [
                $gradeSheet->id,
                $request->user()?->id,
                $gradeSheet->updated_at,
                $payload['signature_secret'] ?? '',
            ])),
        ]);

        $gradeSheet->grades()->where('status', 'published')->update(['signed_at' => now()]);

        return response()->json($gradeSheet->fresh()->load($this->relations));
    }

    public function close(Request $request, GradeSheet $gradeSheet): JsonResponse
    {
        $this->authorize('close', $gradeSheet);

        $this->transition($request, $gradeSheet, ['signed'], 'closed', [
            'closed_at' => now(),
            'closed_by_user_id' => $request->user()?->id,
        ]);

        $gradeSheet->grades()->where('status', 'published')->update(['locked_at' => now()]);

        return response()->json($gradeSheet->fresh()->load($this->relations));
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'grading_scale_id' => ['nullable', 'exists:grading_scales,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'sheet_type' => ['nullable', 'string', 'max:40'],
            'call_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'partial_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['nullable', 'string', 'max:30'],
            'opened_at' => ['nullable', 'date'],
            'submitted_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function hydrateFromOffering(array $data): array
    {
        $offering = SubjectOffering::query()->findOrFail($data['subject_offering_id']);

        return array_merge($data, [
            'course_id' => $data['course_id'] ?? $offering->course_id,
            'career_id' => $data['career_id'] ?? $offering->career_id,
            'group_id' => $data['group_id'] ?? $offering->group_id,
            'subject_id' => $data['subject_id'] ?? $offering->subject_id,
            'professor_id' => $data['professor_id'] ?? $offering->professor_id,
            'opened_at' => $data['opened_at'] ?? now()->toDateString(),
        ]);
    }

    private function transition(Request $request, GradeSheet $gradeSheet, array $allowedFrom, string $status, array $attributes): void
    {
        if (! in_array($gradeSheet->status, $allowedFrom, true)) {
            throw ValidationException::withMessages([
                'status' => 'El acta no puede pasar de '.$gradeSheet->status.' a '.$status.'.',
            ]);
        }

        $previousStatus = $gradeSheet->status;
        $gradeSheet->update(array_merge($attributes, ['status' => $status]));
        $this->recordStatusChange($gradeSheet, $previousStatus, $status, $request);
    }
}
