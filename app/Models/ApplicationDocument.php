<?php

namespace App\Models;

use App\Models\Concerns\HasStatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use HasFactory;
    use HasStatusHistory;

    protected $fillable = [
        'applicant_id',
        'type',
        'name',
        'file_path',
        'file_hash',
        'status',
        'verified_by_user_id',
        'verified_at',
        'rejection_reason',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
