<?php

namespace App\Actions\Academic;

use App\Models\Enrollment;
use App\Models\SubjectEnrollment;
use Illuminate\Support\Facades\DB;

class RegisterSubjectEnrollment
{
    public function handle(array $data): SubjectEnrollment
    {
        return DB::transaction(function () use ($data): SubjectEnrollment {
            $enrollment = Enrollment::with('student.group')->lockForUpdate()->findOrFail($data['enrollment_id']);

            return SubjectEnrollment::create(array_merge($data, [
                'student_id' => $data['student_id'] ?? $enrollment->student_id,
                'course_id' => $data['course_id'] ?? $enrollment->start_course_id,
                'career_id' => $data['career_id'] ?? $enrollment->student?->group?->career_id,
                'group_id' => $data['group_id'] ?? $enrollment->student?->group_id,
                'enrolled_at' => $data['enrolled_at'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'enrolled',
            ]));
        });
    }
}
