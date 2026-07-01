<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectPrerequisite extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_plan_id',
        'subject_id',
        'prerequisite_subject_id',
        'minimum_grade',
    ];

    public function curriculumPlan(): BelongsTo { return $this->belongsTo(CurriculumPlan::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function prerequisiteSubject(): BelongsTo { return $this->belongsTo(Subject::class, 'prerequisite_subject_id'); }
}
