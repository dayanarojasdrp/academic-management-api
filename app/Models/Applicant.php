<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'institution_id',
        'campus_id',
        'career_id',
        'course_id',
        'group_id',
        'student_id',
        'applicant_code',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'birth_date',
        'application_date',
        'source',
        'status',
        'notes',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function career(): BelongsTo { return $this->belongsTo(Career::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(AdmissionInterview::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(AdmissionDecision::class);
    }
}
