<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_code' => $this->student_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date,
            'admission_date' => $this->admission_date,
            'exit_date' => $this->exit_date,
            'exit_reason' => $this->exit_reason,
            'status' => $this->status,
            'group' => $this->whenLoaded('group'),
            'current_enrollment' => $this->whenLoaded('currentEnrollment'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
