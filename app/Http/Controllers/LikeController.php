<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Photo;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Toggle like on a photo.
     */
    public function toggle(Photo $photo): JsonResponse
    {
        if (!\App\Models\Setting::enabled('allow_likes', true)) {
            return response()->json(['message' => 'Fitur like sedang dinonaktifkan.'], 403);
        }

        $user = Auth::user();

        $existingLike = Like::where([
            'user_id' => $user->id,
            'photo_id' => $photo->id,
        ])->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $photo->decrement('likes_count');

            return response()->json([
                'success' => true,
                'liked' => false,
                'message' => 'Like dihapus.',
                'likes_count' => $photo->fresh()->likes_count,
            ]);
        }

        // Like
        Like::create([
            'user_id' => $user->id,
            'photo_id' => $photo->id,
        ]);
        $photo->increment('likes_count');

        // Create notification for photo owner (if not liking own photo)
        if ($photo->user_id !== $user->id) {
            Notification::create([
                'user_id' => $photo->user_id,
                'actor_id' => $user->id,
                'type' => 'like',
                'notifiable_type' => Photo::class,
                'notifiable_id' => $photo->id,
                'data' => [
                    'message' => 'menyukai foto Anda: ' . $photo->title,
                    'photo_id' => $photo->id,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'liked' => true,
            'message' => 'Foto disukai!',
            'likes_count' => $photo->fresh()->likes_count,
        ]);
    }
}
