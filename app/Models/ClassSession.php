<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'subject_offering_id',
        'course_id',
        'career_id',
        'group_id',
        'subject_id',
        'professor_id',
        'session_date',
        'starts_at',
        'ends_at',
        'classroom',
        'topic',
        'delivery_mode',
        'status',
        'notes',
    ];

    public function subjectOffering(): BelongsTo { return $this->belongsTo(SubjectOffering::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function career(): BelongsTo { return $this->belongsTo(Career::class); }
    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function professor(): BelongsTo { return $this->belongsTo(Professor::class); }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
