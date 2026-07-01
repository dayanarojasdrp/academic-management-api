<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'institution_id',
        'code',
        'name',
        'city',
        'state',
        'country',
        'address',
        'status',
    ];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function faculties(): HasMany { return $this->hasMany(Faculty::class); }
    public function departments(): HasMany { return $this->hasMany(Department::class); }
    public function groups(): HasMany { return $this->hasMany(Group::class); }
}
