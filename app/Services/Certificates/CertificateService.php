<?php

namespace App\Services\Certificates;

use App\Models\Certificate;
use App\Models\Student;
use App\Services\Academic\AcademicHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateService
{
    public function __construct(private readonly AcademicHistoryService $academicHistory)
    {
    }

    public function generate(array $data, Request $request): Certificate
    {
        $student = Student::query()
            ->with(['group.career', 'group.course', 'currentEnrollment.startCourse'])
            ->findOrFail($data['student_id']);

        $snapshot = [
            'type' => $data['type'],
            'purpose' => $data['purpose'] ?? 'general',
            'student' => [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'document_type' => $student->document_type,
                'document_number' => $student->document_number,
                'email' => $student->email,
                'status' => $student->status,
                'admission_date' => $student->admission_date,
            ],
            'group' => $student->group?->only(['id', 'name', 'shift', 'status']),
            'career' => $student->group?->career?->only(['id', 'name', 'abbreviation']),
            'course' => $student->currentEnrollment?->startCourse?->only(['id', 'name', 'start_date', 'end_date', 'status']),
            'enrollment' => $student->currentEnrollment?->only(['id', 'status', 'enrollment_date']),
            'academic_summary' => $this->academicHistory->summary($student),
            'issued_at' => now()->toISOString(),
        ];

        if (in_array($data['type'], ['grade_certificate', 'kardex', 'transcript'], true)) {
            $snapshot['academic_record'] = $this->academicHistory->kardex($student, $request);
        }

        return Certificate::create([
            'certificate_code' => $this->nextCode(),
            'student_id' => $student->id,
            'type' => $data['type'],
            'course_id' => $data['course_id'] ?? $student->currentEnrollment?->start_course_id,
            'enrollment_id' => $data['enrollment_id'] ?? $student->current_enrollment_id,
            'generated_by_user_id' => $request->user()?->id,
            'generated_at' => now(),
            'verification_code' => Str::upper(Str::random(24)),
            'status' => 'generated',
            'snapshot_data' => $snapshot,
        ]);
    }

    private function nextCode(): string
    {
        return 'CERT-'.now()->format('Ymd').'-'.str_pad((string) (Certificate::query()->whereDate('created_at', now()->toDateString())->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
