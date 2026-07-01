<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Api\ApiController;
use App\Models\ClassSession;
use App\Models\SubjectEnrollment;
use App\Models\SubjectOffering;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassSessionController extends ApiController
{
    protected string $modelClass = ClassSession::class;

    protected array $relations = ['subjectOffering', 'course', 'career', 'group', 'subject', 'professor', 'records.student'];

    public function index(Request $request): JsonResponse
    {
        $query = ClassSession::query()
            ->with(['course:id,name', 'career:id,name', 'group:id,name', 'subject:id,code,name', 'professor:id,professor_code,first_name,last_name'])
            ->orderByDesc('session_date')
            ->orderByDesc('starts_at')
            ->orderByDesc('id');

        ApiQuery::applyEquals($query, $request, [
            'subject_offering_id' => 'subject_offering_id',
            'course_id' => 'course_id',
            'career_id' => 'career_id',
            'group_id' => 'group_id',
            'subject_id' => 'subject_id',
            'professor_id' => 'professor_id',
            'status' => 'status',
        ]);

        if ($request->filled('from')) {
            $query->whereDate('session_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('session_date', '<=', $request->query('to'));
        }

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(ClassSession $classSession): JsonResponse
    {
        return $this->showRecord($classSession);
    }

    public function update(Request $request, ClassSession $classSession): JsonResponse
    {
        return $this->updateRecord($request, $classSession);
    }

    public function destroy(ClassSession $classSession): JsonResponse
    {
        return $this->destroyRecord($classSession);
    }

    protected function afterSave(Model $record, Request $request): void
    {
        if (! $record instanceof ClassSession) {
            return;
        }

        $offering = SubjectOffering::query()->find($record->subject_offering_id);
        if (! $offering) {
            return;
        }

        $record->fill([
            'course_id' => $record->course_id ?? $offering->course_id,
            'career_id' => $record->career_id ?? $offering->career_id,
            'group_id' => $record->group_id ?? $offering->group_id,
            'subject_id' => $record->subject_id ?? $offering->subject_id,
            'professor_id' => $record->professor_id ?? $offering->professor_id,
        ]);
        $record->saveQuietly();
    }

    public function generateRecords(Request $request, ClassSession $classSession): JsonResponse
    {
        $payload = $request->validate([
            'default_status' => ['nullable', 'string', 'max:30'],
        ]);

        $created = DB::transaction(function () use ($request, $payload, $classSession): int {
            $enrollments = SubjectEnrollment::query()
                ->where('subject_offering_id', $classSession->subject_offering_id)
                ->whereIn('status', ['enrolled', 'active', 'passed', 'failed'])
                ->get(['id', 'student_id']);

            $created = 0;
            foreach ($enrollments as $enrollment) {
                $record = $classSession->records()->firstOrCreate([
                    'student_id' => $enrollment->student_id,
                ], [
                    'subject_enrollment_id' => $enrollment->id,
                    'recorded_by_user_id' => $request->user()?->id,
                    'status' => $payload['default_status'] ?? 'present',
                    'recorded_at' => now(),
                ]);

                if ($record->wasRecentlyCreated) {
                    $created++;
                }
            }

            return $created;
        });

        return response()->json([
            'class_session_id' => $classSession->id,
            'created_records' => $created,
            'records' => $classSession->fresh()->load($this->relations)->records,
        ], 201);
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'career_id' => ['nullable', 'exists:careers,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'session_date' => ['required', 'date'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i', 'after:starts_at'],
            'classroom' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'delivery_mode' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
