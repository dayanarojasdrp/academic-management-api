<?php

namespace App\Actions\Academic;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollStudent
{
    public function __construct(private readonly PaymentVerifier $paymentVerifier)
    {
    }

    public function handle(array $data): Enrollment
    {
        return DB::transaction(function () use ($data): Enrollment {
            $student = Student::query()->lockForUpdate()->findOrFail($data['student_id']);

            if (! $this->paymentVerifier->studentCanEnroll($student)) {
                throw ValidationException::withMessages([
                    'student_id' => 'El estudiante no puede matricularse hasta tener un pago de matricula validado con status paid.',
                ]);
            }

            $enrollment = Enrollment::create($data);
            $student->update(['current_enrollment_id' => $enrollment->id]);

            return $enrollment;
        });
    }
}
