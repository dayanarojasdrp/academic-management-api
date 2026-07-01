<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialHold extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'student_id',
        'course_id',
        'amount',
        'currency',
        'reason',
        'status',
        'placed_at',
        'released_at',
        'released_by',
        'release_reason',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
