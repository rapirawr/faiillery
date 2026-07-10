<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-dominant-colors {--force : Process all photos regardless of current color}')]
#[Description('Calculate and update dominant colors for photos')]
class BackfillDominantColors extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = \App\Models\Photo::query();

        if (!$this->option('force')) {
            $query->where(function($q) {
                $q->whereNull('dominant_color')
                  ->orWhere('dominant_color', '#808080');
            });
        }

        $photos = $query->get();
        $this->info("Backfilling " . $photos->count() . " photos...");

        foreach ($photos as $photo) {
            try {
                $url = $photo->image_url;
                $contents = @file_get_contents($url);
                if (!$contents) {
                    $this->error("Failed to fetch image: " . $url);
                    continue;
                }

                $tempPath = tempnam(sys_get_temp_dir(), 'color_');
                file_put_contents($tempPath, $contents);

                $mimeType = mime_content_type($tempPath);
                $image = match ($mimeType) {
                    'image/jpeg' => @imagecreatefromjpeg($tempPath),
                    'image/png' => @imagecreatefrompng($tempPath),
                    'image/gif' => @imagecreatefromgif($tempPath),
                    'image/webp' => @imagecreatefromwebp($tempPath),
                    default => null,
                };

                if ($image) {
                    $tmp = imagecreatetruecolor(1, 1);
                    imagecopyresampled($tmp, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));
                    $rgb = imagecolorat($tmp, 0, 0);
                    
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $color = sprintf('#%02x%02x%02x', $r, $g, $b);
                    $photo->update(['dominant_color' => $color]);
                    
                    $this->info("Updated photo {$photo->id} with color {$color}");

                    imagedestroy($image);
                    imagedestroy($tmp);
                } else {
                    $this->error("Failed to process image content for photo {$photo->id}");
                }

                unlink($tempPath);
            } catch (\Exception $e) {
                $this->error("Error processing photo {$photo->id}: " . $e->getMessage());
            }
        }

        $this->info("Backfill complete!");
    }
}
