<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeComponent extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'subject_offering_id',
        'code',
        'name',
        'type',
        'term',
        'weight',
        'max_score',
        'is_required',
        'due_date',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_required' => 'boolean',
        'due_date' => 'date',
    ];

    public function subjectOffering(): BelongsTo
    {
        return $this->belongsTo(SubjectOffering::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
