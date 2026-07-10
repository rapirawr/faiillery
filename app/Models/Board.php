<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'photos_count' => 'integer',
    ];

    /**
     * Get the cover image URL.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }

        return null;
    }

    /**
     * Get the first 3 photos for the board thumbnail preview (2x2 grid).
     */
    public function getPreviewPhotosAttribute()
    {
        return $this->photos()->latest('pins.created_at')->take(3)->get();
    }

    /**
     * The user who owns this board.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pins in this board.
     */
    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class);
    }

    /**
     * Photos pinned to this board.
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'pins')
            ->withPivot('user_id', 'created_at');
    }

    /**
     * Scope: only public boards.
     */
    public function scopePublic($query)
    {
        return $query->whereRaw('is_private = false');
    }
}
