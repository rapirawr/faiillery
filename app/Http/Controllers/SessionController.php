<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * List all active database sessions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        // Get sessions from sessions database table
        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($request) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'device_name' => $session->device_name ?? 'Unknown Device',
                    'location' => $session->location ?? 'Unknown Location',
                    'last_active' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current' => $session->id === $request->session()->getId(),
                ];
            });

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke / logout a specific session by session ID.
     */
    public function destroy(string $id): JsonResponse
    {
        $userId = Auth::id();

        // Delete the session row from database
        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil dicabut.'
        ]);
    }

    /**
     * Revoke all other sessions for the user.
     */
    public function revokeOthers(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semua sesi lain berhasil dicabut.'
        ]);
    }
}
