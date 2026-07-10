<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeduplicatePhotos extends Command
{
    protected $signature = 'photos:deduplicate {--dry-run : Only show what would be deleted}';
    protected $description = 'Remove duplicate photo records keeping only the oldest one for each image path';

    public function handle()
    {
        $this->info('🔍 Searching for duplicate image paths...');

        $duplicates = Photo::select('image_path')
            ->groupBy('image_path')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('image_path');

        if ($duplicates->isEmpty()) {
            $this->info('✨ No duplicates found!');
            return self::SUCCESS;
        }

        $this->warn('Found ' . $duplicates->count() . ' paths with duplicates.');

        $totalDeleted = 0;

        foreach ($duplicates as $path) {
            // Get all photos with this path, ordered by ID (keep the oldest one)
            $photos = Photo::where('image_path', $path)
                ->orderBy('id', 'asc')
                ->get();

            $keep = $photos->shift(); // Remove the first one from the list (the one to keep)
            
            $this->info("Path: {$path}");
            $this->line("  Keeping: ID {$keep->id}");

            foreach ($photos as $photo) {
                $this->error("  Deleting: ID {$photo->id}");
                if (!$this->option('dry-run')) {
                    // Use forceDelete or delete depending on if you use SoftDeletes
                    // Just regular delete here since we want to cleanup DB
                    $photo->delete();
                }
                $totalDeleted++;
            }
        }

        if ($this->option('dry-run')) {
            $this->info("DRY RUN: Would have deleted {$totalDeleted} duplicate records.");
        } else {
            $this->info("✅ Successfully deleted {$totalDeleted} duplicate records.");
        }

        return self::SUCCESS;
    }
}
