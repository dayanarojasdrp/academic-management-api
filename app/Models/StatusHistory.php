<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'previous_status',
        'new_status',
        'reason',
        'metadata',
        'changed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'changed_at' => 'datetime',
    ];

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }
}
