<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'start_course_id' => $this->start_course_id,
            'end_course_id' => $this->end_course_id,
            'enrollment_date' => $this->enrollment_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'student' => $this->whenLoaded('student'),
            'start_course' => $this->whenLoaded('startCourse'),
            'end_course' => $this->whenLoaded('endCourse'),
            'subject_enrollments' => $this->whenLoaded('subjectEnrollments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
