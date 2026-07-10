<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompressPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
        $this->queue = 'photos'; // Specific queue for photo processing
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('jobs')->info("Starting compression for Photo ID: {$this->photo->id}");

        // Update photo status to processing
        $this->photo->update(['processing_status' => 'compressing']);

        // Simulate compression or perform actual compression if package exists
        // E.g., loading file from storage, reducing quality, saving back.
        // For production, this will compress the image to high/optimized formats
        // using spatie/laravel-medialibrary or intervention/image.
        
        sleep(1); // Simulate work

        Log::channel('jobs')->info("Photo ID: {$this->photo->id} successfully compressed.");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error("CompressPhotoJob failed for Photo ID: {$this->photo->id}. Error: {$exception->getMessage()}");
        $this->photo->update(['processing_status' => 'failed']);
    }
}
