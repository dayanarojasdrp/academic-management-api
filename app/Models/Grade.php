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
        'value',
        'evaluation_type',
        'evaluated_at',
        'status',
        'notes',
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

    public function subjectEnrollment(): BelongsTo
    {
        return $this->belongsTo(SubjectEnrollment::class);
    }
}
