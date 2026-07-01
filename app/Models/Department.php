<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'institution_id',
        'faculty_id',
        'campus_id',
        'code',
        'name',
        'status',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function faculty(): BelongsTo { return $this->belongsTo(Faculty::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function careers(): HasMany { return $this->hasMany(Career::class); }
    public function professors(): HasMany { return $this->hasMany(Professor::class); }
}
