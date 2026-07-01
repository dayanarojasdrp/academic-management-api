<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_charge_id',
        'student_id',
        'type',
        'amount',
        'status',
        'reason',
        'approved_by',
        'approved_at',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(StudentCharge::class, 'student_charge_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
