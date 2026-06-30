<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'group_id',
        'current_enrollment_id',
        'student_code',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'birth_date',
        'admission_date',
        'exit_date',
        'exit_reason',
        'status',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function currentEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'current_enrollment_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(SubjectEnrollment::class);
    }
}
