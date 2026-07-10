<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Photo $photo): JsonResponse
    {
        if (!\App\Models\Setting::enabled('allow_comments', true)) {
            return response()->json(['message' => 'Fitur komentar sedang dinonaktifkan.'], 403);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $photo->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // Load user for the response
        $comment->load('user');

        // Detect mentions (@username)
        preg_match_all('/@([a-zA-Z0-9_]+)/', $request->body, $matches);
        $mentionedUsernames = array_unique($matches[1] ?? []);
        $mentionedUserIds = [];

        foreach ($mentionedUsernames as $username) {
            $user = \App\Models\User::where('username', $username)->first();
            if ($user && $user->id !== Auth::id()) {
                $mentionedUserIds[] = $user->id;
                Notification::create([
                    'user_id' => $user->id,
                    'actor_id' => Auth::id(),
                    'type' => 'mention',
                    'notifiable_type' => Photo::class,
                    'notifiable_id' => $photo->id,
                    'data' => [
                        'message' => 'menyebut Anda dalam komentar.',
                        'comment_body' => str()->limit($comment->body, 50),
                        'photo_id' => $photo->uid,
                    ]
                ]);
            }
        }

        // Create notification for the photo owner (if not already mentioned)
        if ($photo->user_id !== Auth::id() && !in_array($photo->user_id, $mentionedUserIds)) {
            Notification::create([
                'user_id' => $photo->user_id,
                'actor_id' => Auth::id(),
                'type' => 'comment',
                'notifiable_type' => Photo::class,
                'notifiable_id' => $photo->id,
                'data' => [
                    'message' => 'mengomentari postingan Anda.',
                    'comment_body' => str()->limit($comment->body, 50),
                    'photo_id' => $photo->uid,
                ]
            ]);
        }

        return response()->json([
            'message' => 'Komentar berhasil ditambahkan!',
            'comment' => $comment,
            'user' => $comment->user,
        ]);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Ensure user can only delete their own comments
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Komentar dihapus!']);
    }
}
