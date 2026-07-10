<?php

namespace App\Jobs;

use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GenerateUserDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exportJob;
    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(ExportJob $exportJob)
    {
        $this->exportJob = $exportJob;
        $this->user = $exportJob->user;
        $this->queue = 'exports'; // Put on exports queue
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('jobs')->info("Starting User Data Export for User ID: {$this->user->id}");

        $this->exportJob->update(['status' => 'processing']);

        // Define file and directory paths
        $zipFileName = "export-user-{$this->user->id}-" . time() . '.zip';
        $tempPath = storage_path("app/temp-exports/{$zipFileName}");

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // 1. Add User Profile JSON Metadata
            $metadata = [
                'name' => $this->user->name,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'bio' => $this->user->bio,
                'registered_at' => $this->user->created_at->toIso8601String(),
                'exported_at' => now()->toIso8601String(),
            ];
            $zip->addFromString('profile_metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            // 2. Fetch User Photos and append them to zip
            $photos = $this->user->photos()->get();
            foreach ($photos as $photo) {
                // E.g. download or read local image and add
                // For mock, adding dummy file or path representation
                $zip->addFromString("photos/{$photo->id}.jpg", "A representation of photo source: {$photo->title}");
            }

            $zip->close();

            // Save from temp to local storage disk (or s3 in production)
            $storagePath = "exports/{$zipFileName}";
            Storage::disk('local')->put($storagePath, file_get_contents($tempPath));
            
            // Delete temp file
            unlink($tempPath);

            $this->exportJob->update([
                'status' => 'completed',
                'file_path' => $storagePath,
                'completed_at' => now(),
            ]);

            Log::channel('jobs')->info("User Data Export completed for User ID: {$this->user->id}. File stored at: {$storagePath}");
        } else {
            throw new \Exception("Could not open/create ZIP file at {$tempPath}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error("GenerateUserDataExportJob failed for User ID: {$this->user->id}. Error: {$exception->getMessage()}");
        $this->exportJob->update(['status' => 'failed']);
    }
}
