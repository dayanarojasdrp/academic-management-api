<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingScale extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'code',
        'name',
        'min_value',
        'max_value',
        'passing_value',
        'decimal_places',
        'is_default',
        'status',
        'description',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'passing_value' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(GradingScaleLevel::class);
    }
}
