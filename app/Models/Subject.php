<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = ['code', 'name', 'credits', 'weekly_hours'];

    public function curriculumPlans(): BelongsToMany
    {
        return $this->belongsToMany(CurriculumPlan::class)
            ->withPivot(['semester', 'is_required'])
            ->withTimestamps();
    }

    public function professors(): HasMany
    {
        return $this->hasMany(Professor::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(SubjectOffering::class);
    }
}
