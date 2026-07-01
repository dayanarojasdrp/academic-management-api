<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCharge extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'course_id',
        'financial_concept_id',
        'original_amount',
        'adjustment_amount',
        'paid_amount',
        'balance_amount',
        'currency',
        'issue_date',
        'due_date',
        'status',
        'notes',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(FinancialConcept::class, 'financial_concept_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(FinancialAdjustment::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
