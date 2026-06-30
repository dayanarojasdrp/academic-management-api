<?php

namespace App\Actions\Academic;

use App\Models\Finance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class PaymentVerifier
{
    public function studentCanEnroll(Student $student): bool
    {
        return $student->finances()
            ->where('concept', 'enrollment')
            ->where('status', 'paid')
            ->exists();
    }

    public function markPaid(Finance $finance, array $data): Finance
    {
        return DB::transaction(function () use ($finance, $data): Finance {
            $finance->update([
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'],
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'status' => 'paid',
            ]);

            return $finance->fresh();
        });
    }
}
