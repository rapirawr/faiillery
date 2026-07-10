<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_id',
        'reason',
        'description',
        'status',
    ];

    /**
     * The user who reported the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The photo that was reported.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
