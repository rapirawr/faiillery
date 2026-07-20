<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'user_id',
        'title',
        'description',
        'image_path',
        'thumbnail_path',
        'width',
        'height',
        'dominant_color',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'likes_count' => 'integer',
        'pins_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($photo) {
            if (empty($photo->uid)) {
                $photo->uid = static::generateUniqueId();
            }
        });
    }

    /**
     * Generate a unique ID for the photo.
     */
    public static function generateUniqueId(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        
        do {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
        } while (static::withoutGlobalScopes()->where('uid', $randomString)->exists());

        return $randomString;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('exclude_shadowbanned', function ($builder) {
            if (auth()->check() && auth()->user()->is_admin) {
                return;
            }

            $builder->whereHas('user', function ($query) {
                $query->where(function ($q) {
                    $q->where('is_shadowbanned', 0)->orWhereNull('is_shadowbanned');
                });
                
                // If logged in, allow user to see their own shadowbanned content
                if (auth()->check()) {
                    $query->orWhere('users.id', auth()->id());
                }
            });
        });
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) return '';
        if (str_starts_with($this->image_path, 'http')) return $this->image_path;

        return Storage::disk('s3')->url($this->image_path);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (empty($this->thumbnail_path)) return '';
        if (str_starts_with($this->thumbnail_path, 'http')) return $this->thumbnail_path;

        return Storage::disk('s3')->url($this->thumbnail_path);
    }

    /**
     * Get the aspect ratio for masonry layout.
     */
    public function getAspectRatioAttribute(): float
    {
        if ($this->width && $this->height) {
            return $this->height / $this->width;
        }

        return 1.0;
    }

    /**
     * The user who uploaded this photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tags attached to this photo.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'photo_tag');
    }

    /**
     * Likes received by this photo.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Pins of this photo to boards.
     */
    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class);
    }

    /**
     * Boards this photo is pinned to.
     */
    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'pins')
            ->withPivot('user_id', 'created_at');
    }

    /**
     * Scope: search by title or description using full-text.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'description'], $term);
    }

    /**
     * Scope: filter by tag.
     */
    public function scopeWithTag($query, string $tagSlug)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    /**
     * Comments for this photo.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Collections that include this photo.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    /**
     * Check if the media is a video based on file extension.
     */
    public function isVideo(): bool
    {
        if (empty($this->image_path)) {
            return false;
        }

        $extension = strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', '3gp']);
    }
}

