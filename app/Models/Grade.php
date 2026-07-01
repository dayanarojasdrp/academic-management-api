<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'student_id',
        'subject_enrollment_id',
        'subject_id',
        'professor_id',
        'grade_sheet_id',
        'grade_component_id',
        'grading_scale_id',
        'grading_scale_level_id',
        'value',
        'raw_value',
        'normalized_value',
        'weight',
        'evaluation_type',
        'attempt_type',
        'call_number',
        'partial_number',
        'is_final',
        'evaluated_at',
        'published_at',
        'signed_at',
        'locked_at',
        'change_authorized_by_user_id',
        'change_reason',
        'status',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'raw_value' => 'decimal:2',
        'normalized_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_final' => 'boolean',
        'evaluated_at' => 'date',
        'published_at' => 'datetime',
        'signed_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function gradeSheet(): BelongsTo
    {
        return $this->belongsTo(GradeSheet::class);
    }

    public function gradeComponent(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class);
    }

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }

    public function gradingScaleLevel(): BelongsTo
    {
        return $this->belongsTo(GradingScaleLevel::class);
    }

    public function changeAuthorizedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'change_authorized_by_user_id');
    }

    public function subjectEnrollment(): BelongsTo
    {
        return $this->belongsTo(SubjectEnrollment::class);
    }
}
