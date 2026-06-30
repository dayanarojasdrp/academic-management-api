<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'student_id',
        'start_course_id',
        'end_course_id',
        'enrollment_date',
        'status',
        'notes',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function startCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'start_course_id');
    }

    public function endCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'end_course_id');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(SubjectEnrollment::class);
    }
}
