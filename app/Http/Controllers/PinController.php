<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Photo;
use App\Models\Pin;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinController extends Controller
{
    /**
     * Save/pin a photo to a board.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'photo_id' => 'required|exists:photos,id',
            'board_id' => 'required|exists:boards,id',
        ]);

        $board = Board::findOrFail($request->input('board_id'));

        // Ensure the board belongs to the authenticated user
        if ($board->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak bisa menyimpan ke board orang lain.',
            ], 403);
        }

        $photo = Photo::findOrFail($request->input('photo_id'));

        // Check if already pinned
        $exists = Pin::where([
            'user_id' => Auth::id(),
            'photo_id' => $photo->id,
            'board_id' => $board->id,
        ])->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Foto sudah disimpan di board ini.',
            ], 409);
        }

        Pin::create([
            'user_id' => Auth::id(),
            'photo_id' => $photo->id,
            'board_id' => $board->id,
        ]);

        // Update counter caches
        $photo->increment('pins_count');
        $board->increment('photos_count');

        // Create notification for photo owner (if not pinning own photo)
        if ($photo->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $photo->user_id,
                'actor_id' => Auth::id(),
                'type' => 'pin',
                'notifiable_type' => Photo::class,
                'notifiable_id' => $photo->id,
                'data' => [
                    'message' => 'menyimpan foto Anda ke board "' . $board->title . '"',
                    'photo_id' => $photo->id,
                    'board_id' => $board->id,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil disimpan ke "' . $board->title . '"!',
            'pins_count' => $photo->fresh()->pins_count,
        ]);
    }

    /**
     * Remove a pin (unsave photo from board).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'photo_id' => 'required|exists:photos,id',
            'board_id' => 'required|exists:boards,id',
        ]);

        $pin = Pin::where([
            'user_id' => Auth::id(),
            'photo_id' => $request->input('photo_id'),
            'board_id' => $request->input('board_id'),
        ])->first();

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'Pin tidak ditemukan.',
            ], 404);
        }

        $photo = $pin->photo;
        $board = $pin->board;

        $pin->delete();

        // Update counter caches
        $photo->decrement('pins_count');
        $board->decrement('photos_count');

        return response()->json([
            'success' => true,
            'message' => 'Foto dihapus dari board.',
            'pins_count' => $photo->fresh()->pins_count,
        ]);
    }
}
