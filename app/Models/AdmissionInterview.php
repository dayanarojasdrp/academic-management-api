<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionInterview extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'applicant_id',
        'interviewer_user_id',
        'scheduled_at',
        'completed_at',
        'score',
        'result',
        'notes',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_user_id');
    }
}
