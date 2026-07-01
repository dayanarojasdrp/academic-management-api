<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = ['institution_id', 'campus_id', 'name', 'start_date', 'end_date', 'status'];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function startingEnrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'start_course_id');
    }

    public function endingEnrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'end_course_id');
    }
}
