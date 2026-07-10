<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'photo_id', 'body'])]
class Comment extends Model
{
    use HasFactory;

    /**
     * The user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The photo the comment belongs to.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
