<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeSheet extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'subject_offering_id',
        'professor_id',
        'grading_scale_id',
        'course_id',
        'career_id',
        'group_id',
        'subject_id',
        'sheet_type',
        'call_number',
        'partial_number',
        'status',
        'opened_at',
        'submitted_at',
        'signed_at',
        'closed_at',
        'signed_by_user_id',
        'closed_by_user_id',
        'signature_hash',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'submitted_at' => 'datetime',
        'signed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function subjectOffering(): BelongsTo { return $this->belongsTo(SubjectOffering::class); }
    public function professor(): BelongsTo { return $this->belongsTo(Professor::class); }
    public function gradingScale(): BelongsTo { return $this->belongsTo(GradingScale::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function career(): BelongsTo { return $this->belongsTo(Career::class); }
    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function signedByUser(): BelongsTo { return $this->belongsTo(User::class, 'signed_by_user_id'); }
    public function closedByUser(): BelongsTo { return $this->belongsTo(User::class, 'closed_by_user_id'); }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
