<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'student_id' => $this->student_id,
            'subject_id' => $this->subject_id,
            'subject_offering_id' => $this->subject_offering_id,
            'curriculum_plan_id' => $this->curriculum_plan_id,
            'course_id' => $this->course_id,
            'career_id' => $this->career_id,
            'group_id' => $this->group_id,
            'semester' => $this->semester,
            'enrolled_at' => $this->enrolled_at,
            'completed_at' => $this->completed_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'student' => $this->whenLoaded('student'),
            'subject' => $this->whenLoaded('subject'),
            'subject_offering' => $this->whenLoaded('subjectOffering'),
            'curriculum_plan' => $this->whenLoaded('curriculumPlan'),
            'course' => $this->whenLoaded('course'),
            'career' => $this->whenLoaded('career'),
            'group' => $this->whenLoaded('group'),
            'grades' => $this->whenLoaded('grades'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
