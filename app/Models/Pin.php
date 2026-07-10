<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'photo_id',
        'board_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The user who created this pin.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The photo being pinned.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * The board this pin belongs to.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
