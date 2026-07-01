<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'class_session_id',
        'student_id',
        'subject_enrollment_id',
        'recorded_by_user_id',
        'status',
        'minutes_late',
        'justified',
        'evidence_path',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'justified' => 'boolean',
    ];

    public function classSession(): BelongsTo { return $this->belongsTo(ClassSession::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subjectEnrollment(): BelongsTo { return $this->belongsTo(SubjectEnrollment::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_user_id'); }
}
