<?php

namespace App\Models\Concerns;

use App\Models\StatusHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatusHistory
{
    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'trackable')->latest('changed_at');
    }
}
