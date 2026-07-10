<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecoverPhotosFromStorage extends Command
{
    protected $signature = 'photos:recover
                            {--user-id= : Assign all recovered photos to this user ID (leave empty to use first admin)}
                            {--dry-run : Preview without inserting to database}';

    protected $description = 'Recover photos from Supabase Storage bucket back into the database';

    public function handle(): int
    {
        $this->info('🔍 Scanning Supabase Storage bucket...');

        // Get all original files
        $originals = Storage::disk('s3')->files('photos/originals');
        $thumbnails = Storage::disk('s3')->files('photos/thumbnails');

        if (empty($originals)) {
            $this->error('No files found in photos/originals/');
            return self::FAILURE;
        }

        $this->info('Found ' . count($originals) . ' original photos');
        $this->info('Found ' . count($thumbnails) . ' thumbnails');

        // Build thumbnail lookup map: filename => path
        $thumbMap = [];
        foreach ($thumbnails as $thumbPath) {
            $filename = basename($thumbPath);
            $thumbMap[$filename] = $thumbPath;
        }

        // Determine user to assign
        $userId = $this->option('user-id');
        if (!$userId) {
            // Get the first available user (ideally an admin)
            $admin = User::first();
            
            if (!$admin) {
                $this->error('No users found in database.');
                $this->info('Please register an account first in the browser at http://localhost:8001/register');
                return self::FAILURE;
            }
            $userId = $admin->id;
            $this->warn("No --user-id specified. Assigning all photos to user: {$admin->name} (ID: {$userId})");
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN mode — no data will be inserted.');
        }

        $bar = $this->output->createProgressBar(count($originals));
        $bar->start();

        $imported = 0;
        $skipped  = 0;

        foreach ($originals as $originalPath) {
            $filename = basename($originalPath);
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

            // Find matching thumbnail
            $thumbPath = $thumbMap[$filename] ?? null;

            // Derive a readable title from filename
            $title = Str::of($nameWithoutExt)
                ->replaceMatches('/[_\-]+/', ' ')
                ->trim()
                ->title()
                ->toString();

            // Keep it clean — remove random hash-looking names
            if (preg_match('/^[a-zA-Z0-9]{20,}$/', $nameWithoutExt)) {
                $title = 'Recovered Photo';
            }

            if (!$this->option('dry-run')) {
                $exists = Photo::where('image_path', $originalPath)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    Photo::create([
                        'user_id'        => $userId,
                        'title'          => $title,
                        'image_path'     => $originalPath,
                        'thumbnail_path' => $thumbPath ?? $originalPath,
                        'dominant_color' => null,
                        'width'          => 1200, // Default fallback
                        'height'         => 1600, // Default fallback
                    ]);
                    $imported++;
                }
            } else {
                $imported++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Recovery complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Photos found in storage', count($originals)],
                ['Thumbnails matched',       count($thumbMap)],
                ['Imported to database',     $imported],
                ['Skipped',                  $skipped],
            ]
        );

        if (!$this->option('dry-run')) {
            $this->info('💡 Tip: You can reassign photos to the correct user from the admin panel.');
        }

        return self::SUCCESS;
    }
}
