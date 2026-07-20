<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index()
    {
        $conversations = Conversation::where('user_one_id', Auth::id())
            ->orWhere('user_two_id', Auth::id())
            ->with(['userOne', 'userTwo', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('last_message_at')
            ->get();

        return view('messages.index', compact('conversations'));
    }

    /**
     * Display messages for a specific conversation.
     */
    public function show(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('messages.index');
        }

        $conversation = Conversation::where(function($query) use ($user) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $user->id);
        })->orWhere(function($query) use ($user) {
            $query->where('user_one_id', $user->id)->where('user_two_id', Auth::id());
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min(Auth::id(), $user->id),
                'user_two_id' => max(Auth::id(), $user->id),
            ]);
        }

        $messages = $conversation->messages()->with('sender')->oldest()->get();

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation', 'user', 'messages'));
    }

    /**
     * Store a new message.
     */
    public function store(Request $request, User $user)
    {
        if (!\App\Models\Setting::enabled('allow_messages', true)) {
            return back()->with('error', 'Fitur pesan sedang dinonaktifkan.');
        }

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $conversation = Conversation::where(function($query) use ($user) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $user->id);
        })->orWhere(function($query) use ($user) {
            $query->where('user_one_id', $user->id)->where('user_two_id', Auth::id());
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min(Auth::id(), $user->id),
                'user_two_id' => max(Auth::id(), $user->id),
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        }

        return back();
    }

    /**
     * Share a photo into a direct message chat.
     */
    public function sharePhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!\App\Models\Setting::enabled('allow_messages', true)) {
            return response()->json(['success' => false, 'message' => 'Fitur pesan sedang dinonaktifkan.'], 403);
        }

        $request->validate([
            'username' => 'required|string|exists:users,username',
            'photo_url' => 'required|url',
            'photo_title' => 'required|string',
        ]);

        $recipient = User::where('username', $request->username)->firstOrFail();
        
        if ($recipient->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak bisa mengirim pesan ke diri sendiri.'], 422);
        }

        $conversation = Conversation::where(function($query) use ($recipient) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $recipient->id);
        })->orWhere(function($query) use ($recipient) {
            $query->where('user_one_id', $recipient->id)->where('user_two_id', Auth::id());
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min(Auth::id(), $recipient->id),
                'user_two_id' => max(Auth::id(), $recipient->id),
            ]);
        }

        $body = "Membagikan karya: *" . $request->photo_title . "*\n" . $request->photo_url;
        
        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil dibagikan ke obrolan.',
        ]);
    }

    /**
     * Get list of active chat partners for sharing.
     */
    public function getConversationsList(): \Illuminate\Http\JsonResponse
    {
        $conversations = Conversation::where('user_one_id', Auth::id())
            ->orWhere('user_two_id', Auth::id())
            ->with(['userOne', 'userTwo'])
            ->orderByDesc('last_message_at')
            ->take(8)
            ->get()
            ->map(function ($convo) {
                $otherUser = $convo->user_one_id === Auth::id() ? $convo->userTwo : $convo->userOne;
                return [
                    'username' => $otherUser->username,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->avatar_url,
                ];
            });

        return response()->json($conversations);
    }
}
