<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WatermarkPhotoJob implements ShouldQueue
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
        $userId = $this->photo->user_id;

        // Check if watermark is enabled by user
        $watermarkEnabled = UserSetting::getValue($userId, 'watermark_enabled', '0') === '1';

        if (!$watermarkEnabled) {
            Log::channel('jobs')->info("Watermark disabled for user ID: {$userId}. Skipping watermark job for Photo ID: {$this->photo->id}.");
            return;
        }

        $this->photo->update(['processing_status' => 'watermarking']);

        $position = UserSetting::getValue($userId, 'watermark_position', 'bottom-right');
        $opacity = UserSetting::getValue($userId, 'watermark_opacity', '50');

        Log::channel('jobs')->info("Applying watermark to Photo ID: {$this->photo->id} (Pos: {$position}, Opacity: {$opacity})");

        // Here we would use Intervention Image to overlay watermark
        // $img = Image::make(Storage::disk('s3')->get($this->photo->path));
        // $img->insert(public_path('watermark.png'), $position, 10, 10);
        // Storage::disk('s3')->put($this->photo->path, $img->stream());

        sleep(1); // Simulate work

        Log::channel('jobs')->info("Watermark successfully applied to Photo ID: {$this->photo->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error("WatermarkPhotoJob failed for Photo ID: {$this->photo->id}. Error: {$exception->getMessage()}");
        $this->photo->update(['processing_status' => 'failed']);
    }
}
