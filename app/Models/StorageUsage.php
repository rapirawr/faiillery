<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageUsage extends Model
{
    protected $fillable = ['user_id', 'used_bytes', 'quota_bytes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get percentage of storage used.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->quota_bytes === 0) return 0;
        return round(($this->used_bytes / $this->quota_bytes) * 100, 2);
    }
}
