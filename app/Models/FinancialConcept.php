<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'default_amount',
        'currency',
        'is_required_for_enrollment',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_required_for_enrollment' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function charges(): HasMany
    {
        return $this->hasMany(StudentCharge::class);
    }
}
