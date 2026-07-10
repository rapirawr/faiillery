<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['message', 'is_active', 'ends_at'];

    protected $casts = [
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active announcements.
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true')
                     ->where(function ($q) {
                         $q->whereNull('ends_at')
                           ->orWhereRaw('ends_at > CURRENT_TIMESTAMP');
                     });
    }
}
