<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = ['student_payment_id', 'student_charge_id', 'amount'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(StudentPayment::class, 'student_payment_id');
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(StudentCharge::class, 'student_charge_id');
    }
}
