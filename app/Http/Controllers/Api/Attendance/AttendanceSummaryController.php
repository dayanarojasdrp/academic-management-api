<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceSummaryController extends Controller
{
    public function student(Student $student, Request $request): JsonResponse
    {
        $query = DB::table('attendance_records')
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->leftJoin('subjects', 'class_sessions.subject_id', '=', 'subjects.id')
            ->where('attendance_records.student_id', $student->id)
            ->selectRaw('
                subjects.id as subject_id,
                subjects.code as subject_code,
                subjects.name as subject_name,
                count(*) as total_sessions,
                sum(case when attendance_records.status = \'present\' then 1 else 0 end) as present_total,
                sum(case when attendance_records.status = \'absent\' then 1 else 0 end) as absent_total,
                sum(case when attendance_records.status = \'late\' then 1 else 0 end) as late_total,
                sum(case when attendance_records.justified = 1 then 1 else 0 end) as justified_total
            ')
            ->groupBy(['subjects.id', 'subjects.code', 'subjects.name'])
            ->orderBy('subjects.name');

        if ($request->filled('from')) {
            $query->whereDate('class_sessions.session_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('class_sessions.session_date', '<=', $request->query('to'));
        }

        $rows = $query->get()->map(function ($row) {
            $attended = (int) $row->present_total + (int) $row->late_total;
            $row->attendance_rate = $row->total_sessions > 0 ? round(($attended / (int) $row->total_sessions) * 100, 2) : 0;

            return $row;
        });

        return response()->json([
            'student' => $student->load('group.career'),
            'filters' => $request->query(),
            'data' => $rows,
        ]);
    }
}
