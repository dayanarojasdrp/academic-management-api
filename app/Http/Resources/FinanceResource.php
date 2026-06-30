<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'enrollment_id' => $this->enrollment_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'concept' => $this->concept,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'status' => $this->status,
            'student' => $this->whenLoaded('student'),
            'enrollment' => $this->whenLoaded('enrollment'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
