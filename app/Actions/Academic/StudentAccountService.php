<?php

namespace App\Actions\Academic;

use App\Models\FinancialAdjustment;
use App\Models\FinancialConcept;
use App\Models\PaymentAllocation;
use App\Models\PaymentReceipt;
use App\Models\Student;
use App\Models\StudentCharge;
use App\Models\StudentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAccountService
{
    public function createCharge(array $data): StudentCharge
    {
        return DB::transaction(function () use ($data): StudentCharge {
            $concept = FinancialConcept::findOrFail($data['financial_concept_id']);
            $amount = (float) ($data['original_amount'] ?? $concept->default_amount);

            return StudentCharge::create([
                'student_id' => $data['student_id'],
                'enrollment_id' => $data['enrollment_id'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'financial_concept_id' => $concept->id,
                'original_amount' => $amount,
                'adjustment_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => $amount,
                'currency' => $data['currency'] ?? $concept->currency,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'status' => $amount <= 0 ? 'paid' : 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function applyAdjustment(StudentCharge $charge, array $data): FinancialAdjustment
    {
        return DB::transaction(function () use ($charge, $data): FinancialAdjustment {
            $charge = StudentCharge::query()->lockForUpdate()->findOrFail($charge->id);
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'El ajuste debe ser mayor que cero.']);
            }

            $adjustment = FinancialAdjustment::create([
                'student_charge_id' => $charge->id,
                'student_id' => $charge->student_id,
                'type' => $data['type'],
                'amount' => $amount,
                'status' => $data['status'] ?? 'approved',
                'reason' => $data['reason'] ?? null,
                'approved_by' => $data['approved_by'] ?? null,
                'approved_at' => $data['approved_at'] ?? now(),
            ]);

            if ($adjustment->status === 'approved') {
                $charge->adjustment_amount += $amount;
                $charge->balance_amount = max(0, $charge->original_amount - $charge->adjustment_amount - $charge->paid_amount);
                $charge->status = $this->chargeStatus($charge);
                $charge->save();
            }

            return $adjustment;
        });
    }

    public function recordPayment(array $data): StudentPayment
    {
        return DB::transaction(function () use ($data): StudentPayment {
            $amount = (float) $data['amount'];
            $student = Student::query()->lockForUpdate()->findOrFail($data['student_id']);

            $payment = StudentPayment::create([
                'student_id' => $student->id,
                'enrollment_id' => $data['enrollment_id'] ?? null,
                'amount' => $amount,
                'unallocated_amount' => $amount,
                'currency' => $data['currency'] ?? 'USD',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'],
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'confirmed',
                'received_by' => $data['received_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['allocations'] ?? [] as $allocation) {
                $this->allocate($payment, (int) $allocation['student_charge_id'], (float) $allocation['amount']);
            }

            if (empty($data['allocations'])) {
                $this->autoAllocate($payment);
            }

            PaymentReceipt::create([
                'student_payment_id' => $payment->id,
                'receipt_number' => $data['receipt_number'] ?? 'RCPT-'.str_pad((string) $payment->id, 8, '0', STR_PAD_LEFT),
                'issued_at' => now(),
                'status' => 'issued',
                'metadata' => $data['receipt_metadata'] ?? null,
            ]);

            return $payment->fresh(['allocations.charge.concept', 'receipt']);
        });
    }

    private function autoAllocate(StudentPayment $payment): void
    {
        $charges = StudentCharge::query()
            ->where('student_id', $payment->student_id)
            ->where('currency', $payment->currency)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($charges as $charge) {
            if ($payment->unallocated_amount <= 0) {
                return;
            }

            $this->allocate($payment, $charge->id, min($payment->unallocated_amount, $charge->balance_amount));
            $payment->refresh();
        }
    }

    private function allocate(StudentPayment $payment, int $chargeId, float $amount): void
    {
        $payment = StudentPayment::query()->lockForUpdate()->findOrFail($payment->id);
        $charge = StudentCharge::query()->lockForUpdate()->findOrFail($chargeId);

        if ($payment->student_id !== $charge->student_id) {
            throw ValidationException::withMessages(['allocations' => 'El pago y el cargo no pertenecen al mismo estudiante.']);
        }

        if ($payment->currency !== $charge->currency) {
            throw ValidationException::withMessages(['currency' => 'La moneda del pago no coincide con la moneda del cargo.']);
        }

        if ($amount <= 0 || $amount > $payment->unallocated_amount || $amount > $charge->balance_amount) {
            throw ValidationException::withMessages(['allocations' => 'La asignacion del pago no es valida.']);
        }

        PaymentAllocation::create([
            'student_payment_id' => $payment->id,
            'student_charge_id' => $charge->id,
            'amount' => $amount,
        ]);

        $payment->unallocated_amount -= $amount;
        $payment->save();

        $charge->paid_amount += $amount;
        $charge->balance_amount = max(0, $charge->original_amount - $charge->adjustment_amount - $charge->paid_amount);
        $charge->status = $this->chargeStatus($charge);
        $charge->save();
    }

    private function chargeStatus(StudentCharge $charge): string
    {
        if ($charge->balance_amount <= 0) {
            return 'paid';
        }

        if ($charge->paid_amount > 0 || $charge->adjustment_amount > 0) {
            return 'partial';
        }

        if ($charge->due_date && $charge->due_date < now()->toDateString()) {
            return 'overdue';
        }

        return 'pending';
    }
}
