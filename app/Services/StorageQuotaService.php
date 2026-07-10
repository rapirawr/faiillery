<?php

namespace App\Services;

use App\Models\StorageUsage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StorageQuotaService
{
    /**
     * Get or initialize the storage usage details for a user.
     */
    public function getUsage(User $user): StorageUsage
    {
        return StorageUsage::firstOrCreate(
            ['user_id' => $user->id],
            [
                'used_bytes' => 0,
                'quota_bytes' => 26843545600 // 25 GB default
            ]
        );
    }

    /**
     * Check if user has exceeded their storage quota.
     */
    public function isQuotaExceeded(User $user, int $incomingBytes = 0): bool
    {
        $usage = $this->getUsage($user);
        return ($usage->used_bytes + $incomingBytes) >= $usage->quota_bytes;
    }

    /**
     * Recalculate and cache user's current storage usage based on database photos.
     */
    public function recalculate(User $user): int
    {
        // Calculate size of all photos uploaded by user
        // E.g. Sum of file size column in photos table
        $totalBytes = (int) $user->photos()->sum('file_size'); // assuming file_size exists on photos

        $usage = $this->getUsage($user);
        $usage->update(['used_bytes' => $totalBytes]);

        // Cache the usage value
        Cache::put("user_storage_used_{$user->id}", $totalBytes, now()->addDay());

        Log::info("Recalculated storage usage for User ID: {$user->id}. Total: {$totalBytes} bytes");

        return $totalBytes;
    }

    /**
     * Increment storage usage after file upload.
     */
    public function addBytes(User $user, int $bytes): void
    {
        $usage = $this->getUsage($user);
        $usage->increment('used_bytes', $bytes);

        // Update cache
        Cache::increment("user_storage_used_{$user->id}", $bytes);
    }

    /**
     * Decrement storage usage after file deletion.
     */
    public function removeBytes(User $user, int $bytes): void
    {
        $usage = $this->getUsage($user);
        $newBytes = max(0, $usage->used_bytes - $bytes);
        $usage->update(['used_bytes' => $newBytes]);

        // Update cache
        Cache::put("user_storage_used_{$user->id}", $newBytes, now()->addDay());
    }
}
