<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CurriculumPlan extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = ['career_id', 'name', 'version', 'duration_semesters', 'status'];

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)
            ->withPivot(['semester', 'is_required'])
            ->withTimestamps();
    }
}
