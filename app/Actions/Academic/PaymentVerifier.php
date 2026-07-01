<?php

namespace App\Actions\Academic;

use App\Models\Finance;
use App\Models\FinancialConcept;
use App\Models\FinancialHold;
use App\Models\StudentCharge;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class PaymentVerifier
{
    public function studentCanEnroll(Student $student, ?int $courseId = null): bool
    {
        return $this->clearance($student, $courseId)['can_enroll'];
    }

    public function clearance(Student $student, ?int $courseId = null): array
    {
        $activeHold = FinancialHold::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->when($courseId, fn ($query) => $query->where(fn ($query) => $query->whereNull('course_id')->orWhere('course_id', $courseId)))
            ->exists();

        $requiredConceptIds = FinancialConcept::query()
            ->where('is_required_for_enrollment', true)
            ->where('is_active', true)
            ->pluck('id');

        $requiredCharges = StudentCharge::query()
            ->with('concept:id,code,name')
            ->where('student_id', $student->id)
            ->whereIn('financial_concept_id', $requiredConceptIds)
            ->when($courseId, fn ($query) => $query->where(fn ($query) => $query->whereNull('course_id')->orWhere('course_id', $courseId)))
            ->get();

        $missingConcepts = FinancialConcept::query()
            ->whereIn('id', $requiredConceptIds->diff($requiredCharges->pluck('financial_concept_id')))
            ->get(['id', 'code', 'name']);

        $openRequiredBalance = (float) $requiredCharges->sum('balance_amount');

        $previousDebt = (float) StudentCharge::query()
            ->where('student_id', $student->id)
            ->where('balance_amount', '>', 0)
            ->when($courseId, fn ($query) => $query->where(fn ($query) => $query->whereNull('course_id')->orWhere('course_id', '<>', $courseId)))
            ->sum('balance_amount');

        return [
            'student_id' => $student->id,
            'course_id' => $courseId,
            'can_enroll' => ! $activeHold && $missingConcepts->isEmpty() && $openRequiredBalance <= 0 && $previousDebt <= 0,
            'has_active_hold' => $activeHold,
            'missing_required_concepts' => $missingConcepts,
            'required_balance' => $openRequiredBalance,
            'previous_debt' => $previousDebt,
            'required_charges' => $requiredCharges,
        ];
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
