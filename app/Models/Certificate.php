<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'certificate_code',
        'student_id',
        'type',
        'course_id',
        'enrollment_id',
        'generated_by_user_id',
        'generated_at',
        'verification_code',
        'file_path',
        'status',
        'snapshot_data',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'snapshot_data' => 'array',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function enrollment(): BelongsTo { return $this->belongsTo(Enrollment::class); }
    public function generatedBy(): BelongsTo { return $this->belongsTo(User::class, 'generated_by_user_id'); }
}
