<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingScaleLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'grading_scale_id',
        'code',
        'label',
        'min_value',
        'max_value',
        'grade_points',
        'is_passing',
        'sort_order',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'grade_points' => 'decimal:2',
        'is_passing' => 'boolean',
    ];

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }
}
