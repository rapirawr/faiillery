<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\Pin;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoService
{
    /**
     * Upload a new photo with thumbnail generation.
     */
    public function upload(
        User $user,
        UploadedFile $file,
        string $title,
        ?string $description = null,
        ?string $tags = '',
        ?int $boardId = null,
    ): Photo {
        $mimeType = $file->getMimeType();
        $isImage = str_starts_with($mimeType, 'image/');

        $width = 800;
        $height = 600;

        if ($isImage) {
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0] ?? 800;
                $height = $imageInfo[1] ?? 600;
            }
        }

        // Store original file on the configured S3 disk with public visibility
        $disk = Storage::disk('s3');
        $imagePath = $disk->putFile('photos/originals', $file, 'public');

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($file, $imagePath);

        // Get dominant color
        $dominantColor = $this->getDominantColor($file);

        // Create photo record
        $photo = $user->photos()->create([
            'title' => $title,
            'description' => $description,
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
            'width' => $width,
            'height' => $height,
            'dominant_color' => $dominantColor,
        ]);

        // Auto-tagging AI (suggested tags from title and description)
        $suggestedTags = $this->suggestTags($title, $description);
        $finalTags = !empty($tags) ? $tags . ',' . $suggestedTags : $suggestedTags;

        // Attach tags
        if (!empty($finalTags)) {
            $this->syncTags($photo, $finalTags);
        }

        // Pin to board if specified
        if ($boardId) {
            Pin::create([
                'user_id' => $user->id,
                'photo_id' => $photo->id,
                'board_id' => $boardId,
            ]);
            $photo->increment('pins_count');

            $board = \App\Models\Board::find($boardId);
            if ($board) {
                $board->increment('photos_count');
            }
        }

        return $photo;
    }

    /**
     * Generate a thumbnail for the uploaded image.
     * Uses GD library for basic thumbnail generation.
     */
    protected function generateThumbnail(UploadedFile $file, string $originalPath): string
    {
        $mimeType = $file->getMimeType();
        if (!str_starts_with($mimeType, 'image/')) {
            return $originalPath;
        }

        $imageInfo = @getimagesize($file->getPathname());
        if (!$imageInfo) {
            return $originalPath;
        }

        $maxWidth = 400;
        $extension = $file->getClientOriginalExtension();
        $thumbnailName = 'photos/thumbnails/' . pathinfo($originalPath, PATHINFO_FILENAME) . '_thumb.' . $extension;

        // Get original dimensions
        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];

        // Calculate new dimensions (maintain aspect ratio)
        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) ($origHeight * $ratio);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        // Check if GD library exists
        if (!function_exists('imagecreatefromjpeg')) {
            // Fallback: use original as thumbnail if GD is missing
            return $originalPath;
        }

        // Create image from file
        $mimeType = $imageInfo['mime'];
        $sourceImage = match ($mimeType) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file->getPathname()) : null,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file->getPathname()) : null,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($file->getPathname()) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getPathname()) : null,
            default => null,
        };

        if (!$sourceImage) {
            // Fallback: use original as thumbnail
            return $originalPath;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }

        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save thumbnail to temp file, then store via S3 disk
        $tempPath = tempnam(sys_get_temp_dir(), 'thumb_');

        match ($mimeType) {
            'image/jpeg' => imagejpeg($thumbnail, $tempPath, 85),
            'image/png' => imagepng($thumbnail, $tempPath, 8),
            'image/gif' => imagegif($thumbnail, $tempPath),
            'image/webp' => imagewebp($thumbnail, $tempPath, 85),
            default => imagejpeg($thumbnail, $tempPath, 85),
        };

        // Store thumbnail on the same S3 disk with public visibility
        Storage::disk('s3')->put($thumbnailName, file_get_contents($tempPath), 'public');

        // Cleanup
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        unlink($tempPath);

        return $thumbnailName;
    }

    /**
     * Sync tags from comma-separated string.
     */
    public function syncTags(Photo $photo, ?string $tagsString): void
    {
        if (empty($tagsString)) {
            $photo->tags()->detach();
            return;
        }

        $tagNames = array_filter(
            array_map('trim', explode(',', $tagsString))
        );

        $tagIds = [];
        foreach ($tagNames as $name) {
            if (!empty($name)) {
                $tag = Tag::findOrCreateByName($name);
                $tagIds[] = $tag->id;
            }
        }

        $photo->tags()->sync($tagIds);
    }

    /**
     * Delete a photo and its files.
     */
    public function delete(Photo $photo): void
    {
        // Delete files from the S3 disk
        try {
            $disk = Storage::disk('s3');
            $disk->delete($photo->image_path);
            if ($photo->thumbnail_path && $photo->thumbnail_path !== $photo->image_path) {
                $disk->delete($photo->thumbnail_path);
            }
        } catch (\Exception $e) {
            // Ignore if file not found or storage error - we want to proceed with database deletion
            \Log::warning("Failed to delete storage file for photo {$photo->id}: " . $e->getMessage());
        }

        // Delete the record (cascade will handle likes, pins, tags)
        $photo->delete();
    }
    /**
     * Suggest tags based on title and description.
     */
    protected function suggestTags(string $title, ?string $description = null): string
    {
        $text = $title . ' ' . ($description ?? '');
        $text = strtolower($text);
        
        // Remove special chars except spaces
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Split into words
        $words = explode(' ', $text);
        
        // Filter: min 4 chars, not common words (stop words)
        $stopWords = [
            'dan', 'yang', 'dari', 'untuk', 'pada', 'adalah', 'dengan', 'saya', 'anda', 'ini', 'itu', 
            'juga', 'akan', 'bisa', 'ada', 'tidak', 'ia', 'ke', 'the', 'and', 'for', 'with', 'this', 'that'
        ];
        
        $suggested = array_filter($words, function($word) use ($stopWords) {
            $word = trim($word);
            return strlen($word) >= 4 && !in_array($word, $stopWords);
        });
        
        // Unique and limited to 5 tags
        $suggested = array_unique($suggested);
        return implode(', ', array_slice($suggested, 0, 5));
    }

    /**
     * Get the dominant color of an image (average color).
     */
    protected function getDominantColor(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        
        $image = match ($mimeType) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file->getPathname()) : null,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file->getPathname()) : null,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($file->getPathname()) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getPathname()) : null,
            default => null,
        };

        if (!$image) {
            return '#e0e0e0'; // Default gray
        }

        // Resize to 1x1 to get average color
        $tmp = imagecreatetruecolor(1, 1);
        imagecopyresampled($tmp, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));
        $rgb = imagecolorat($tmp, 0, 0);
        
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($image);
        imagedestroy($tmp);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
