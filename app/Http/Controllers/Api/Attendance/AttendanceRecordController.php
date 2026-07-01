<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Api\ApiController;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\SubjectEnrollment;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceRecordController extends ApiController
{
    protected string $modelClass = AttendanceRecord::class;

    protected array $relations = ['classSession.subject', 'student', 'subjectEnrollment', 'recordedBy'];

    public function index(Request $request): JsonResponse
    {
        $query = AttendanceRecord::query()
            ->with($this->relations)
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->select('attendance_records.*')
            ->orderByDesc('class_sessions.session_date')
            ->orderBy('attendance_records.student_id');

        ApiQuery::applyEquals($query, $request, [
            'class_session_id' => 'attendance_records.class_session_id',
            'student_id' => 'attendance_records.student_id',
            'subject_enrollment_id' => 'attendance_records.subject_enrollment_id',
            'status' => 'attendance_records.status',
            'subject_offering_id' => 'class_sessions.subject_offering_id',
            'group_id' => 'class_sessions.group_id',
            'subject_id' => 'class_sessions.subject_id',
        ]);

        if ($request->filled('from')) {
            $query->whereDate('class_sessions.session_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('class_sessions.session_date', '<=', $request->query('to'));
        }

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(AttendanceRecord $attendanceRecord): JsonResponse { return $this->showRecord($attendanceRecord); }
    public function update(Request $request, AttendanceRecord $attendanceRecord): JsonResponse { return $this->updateRecord($request, $attendanceRecord); }
    public function destroy(AttendanceRecord $attendanceRecord): JsonResponse { return $this->destroyRecord($attendanceRecord); }

    protected function afterSave(Model $record, Request $request): void
    {
        if (! $record instanceof AttendanceRecord) {
            return;
        }

        $session = ClassSession::query()->find($record->class_session_id);
        $subjectEnrollmentId = $record->subject_enrollment_id;

        if (! $subjectEnrollmentId && $session) {
            $subjectEnrollmentId = SubjectEnrollment::query()
                ->where('student_id', $record->student_id)
                ->where('subject_offering_id', $session->subject_offering_id)
                ->value('id');
        }

        $record->fill([
            'subject_enrollment_id' => $subjectEnrollmentId,
            'recorded_by_user_id' => $record->recorded_by_user_id ?? $request->user()?->id,
            'recorded_at' => $record->recorded_at ?? now(),
        ]);
        $record->saveQuietly();
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'class_session_id' => ['required', 'exists:class_sessions,id'],
            'student_id' => ['required', 'exists:students,id'],
            'subject_enrollment_id' => ['nullable', 'exists:subject_enrollments,id'],
            'recorded_by_user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'max:30'],
            'minutes_late' => ['nullable', 'integer', 'min:0', 'max:600'],
            'justified' => ['nullable', 'boolean'],
            'evidence_path' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
