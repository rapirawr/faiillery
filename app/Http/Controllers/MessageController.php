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
}
