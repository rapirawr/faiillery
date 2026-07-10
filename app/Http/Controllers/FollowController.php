<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Toggle follow/unfollow for a user.
     */
    public function toggle(User $user): JsonResponse
    {
        if (!\App\Models\Setting::enabled('allow_follows', true)) {
            return response()->json(['message' => 'Fitur follow sedang dinonaktifkan.'], 403);
        }

        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 400);
        }

        if ($currentUser->isFollowing($user)) {
            $currentUser->following()->detach($user->id);
            $isFollowing = false;
        } else {
            $currentUser->following()->attach($user->id);
            $isFollowing = true;

            // Create notification
            Notification::create([
                'user_id' => $user->id,
                'actor_id' => $currentUser->id,
                'type' => 'follow',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'message' => 'mulai mengikuti Anda.',
                ]
            ]);
        }

        return response()->json([
            'following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
        ]);
    }
}
