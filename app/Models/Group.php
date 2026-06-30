<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = ['course_id', 'career_id', 'name', 'shift', 'status'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
