<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateThumbnailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
        $this->queue = 'photos';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('jobs')->info("Starting thumbnail generation for Photo ID: {$this->photo->id}");

        $this->photo->update(['processing_status' => 'generating_thumbnails']);

        // E.g. generate small, medium and large crop sizes for grid layout
        // using spatie/laravel-medialibrary conversions or intervention image resize.

        sleep(1); // Simulate work

        // Finalize status to completed
        $this->photo->update([
            'processing_status' => 'completed',
            'is_processed' => true // assuming column exists or logs
        ]);

        Log::channel('jobs')->info("Thumbnails generated. Photo ID: {$this->photo->id} processing pipeline fully completed.");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error("GenerateThumbnailsJob failed for Photo ID: {$this->photo->id}. Error: {$exception->getMessage()}");
        $this->photo->update(['processing_status' => 'failed']);
    }
}
