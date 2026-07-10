<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'photo_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The user who liked the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The photo that was liked.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
