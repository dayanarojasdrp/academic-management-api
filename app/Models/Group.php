<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'institution_id',
        'campus_id',
        'faculty_id',
        'department_id',
        'modality_id',
        'course_id',
        'career_id',
        'name',
        'shift',
        'status',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function faculty(): BelongsTo { return $this->belongsTo(Faculty::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function modality(): BelongsTo { return $this->belongsTo(Modality::class); }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
