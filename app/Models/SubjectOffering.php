<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectOffering extends Model
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
        'group_id',
        'curriculum_plan_id',
        'subject_id',
        'professor_id',
        'semester',
        'capacity',
        'reserved_seats',
        'modality',
        'status',
        'starts_at',
        'ends_at',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function faculty(): BelongsTo { return $this->belongsTo(Faculty::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function modalityCatalog(): BelongsTo { return $this->belongsTo(Modality::class, 'modality_id'); }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function career(): BelongsTo { return $this->belongsTo(Career::class); }
    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function curriculumPlan(): BelongsTo { return $this->belongsTo(CurriculumPlan::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function professor(): BelongsTo { return $this->belongsTo(Professor::class); }

    public function schedules(): HasMany
    {
        return $this->hasMany(SubjectOfferingSchedule::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class);
    }

    public function gradeSheets(): HasMany
    {
        return $this->hasMany(GradeSheet::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }
}
