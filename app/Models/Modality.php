<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modality extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'description',
        'requires_classroom',
        'requires_online_platform',
        'status',
    ];

    protected $casts = [
        'requires_classroom' => 'boolean',
        'requires_online_platform' => 'boolean',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function careers(): HasMany { return $this->hasMany(Career::class); }
    public function groups(): HasMany { return $this->hasMany(Group::class); }
    public function subjectOfferings(): HasMany { return $this->hasMany(SubjectOffering::class); }
}
