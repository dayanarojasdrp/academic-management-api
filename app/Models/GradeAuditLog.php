<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'old_grade',
        'new_grade',
        'old_status',
        'new_status',
        'reason',
        'changed_by_user_id',
        'changed_at',
    ];

    protected $casts = [
        'old_grade' => 'decimal:2',
        'new_grade' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_user_id'); }
}
