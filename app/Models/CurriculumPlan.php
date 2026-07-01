<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumPlan extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'career_id',
        'effective_course_id',
        'expires_course_id',
        'name',
        'version',
        'duration_semesters',
        'status',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)
            ->withPivot(['semester', 'is_required', 'prerequisite_subject_id', 'minimum_passing_grade'])
            ->withTimestamps();
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(SubjectPrerequisite::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(SubjectOffering::class);
    }
}
