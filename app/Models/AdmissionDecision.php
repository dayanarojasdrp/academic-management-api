<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'decided_by_user_id',
        'decision',
        'decision_date',
        'valid_until',
        'score',
        'reason',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
