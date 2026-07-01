<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeChangeRequest extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'grade_id',
        'requested_by_user_id',
        'approved_by_user_id',
        'current_value',
        'requested_value',
        'status',
        'reason',
        'decision_reason',
        'decided_at',
    ];

    protected $casts = [
        'current_value' => 'decimal:2',
        'requested_value' => 'decimal:2',
        'decided_at' => 'datetime',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
