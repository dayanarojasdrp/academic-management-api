<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'code',
        'name',
        'legal_name',
        'tax_identifier',
        'country',
        'timezone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function campuses(): HasMany { return $this->hasMany(Campus::class); }
    public function faculties(): HasMany { return $this->hasMany(Faculty::class); }
    public function departments(): HasMany { return $this->hasMany(Department::class); }
    public function modalities(): HasMany { return $this->hasMany(Modality::class); }
    public function careers(): HasMany { return $this->hasMany(Career::class); }
    public function courses(): HasMany { return $this->hasMany(Course::class); }
}
