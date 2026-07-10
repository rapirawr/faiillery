<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoShare extends Model
{
    protected $fillable = ['photo_id', 'token', 'password_hash', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * Check if the shared link has expired.
     */
    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) return false;
        return $this->expires_at->isPast();
    }
}
