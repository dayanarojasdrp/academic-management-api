<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectOfferingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_offering_id',
        'weekday',
        'starts_at',
        'ends_at',
        'classroom',
    ];

    public function offering(): BelongsTo
    {
        return $this->belongsTo(SubjectOffering::class, 'subject_offering_id');
    }
}
