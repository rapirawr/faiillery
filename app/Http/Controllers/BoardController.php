<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    /**
     * Show create board form.
     */
    public function create()
    {
        return view('boards.create');
    }

    /**
     * Store a new board.
     */
    public function store(StoreBoardRequest $request)
    {
        $board = Auth::user()->boards()->create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_private' => $request->boolean('is_private'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Board berhasil dibuat!',
                'board' => $board,
            ]);
        }

        return redirect()
            ->route('boards.show', $board)
            ->with('success', 'Board berhasil dibuat!');
    }

    /**
     * Show a board with its pinned photos.
     */
    public function show(Board $board)
    {
        // Don't show private boards to non-owners
        if ($board->is_private && (!Auth::check() || Auth::id() !== $board->user_id)) {
            abort(403, 'Board ini bersifat privat.');
        }

        $board->load('user');
        $photos = $board->photos()
            ->with(['user', 'tags'])
            ->latest('pins.created_at')
            ->paginate(30);

        return view('boards.show', compact('board', 'photos'));
    }

    /**
     * Show edit board form.
     */
    public function edit(Board $board)
    {
        $this->authorize('update', $board);

        return view('boards.edit', compact('board'));
    }

    /**
     * Update a board.
     */
    public function update(UpdateBoardRequest $request, Board $board)
    {
        $this->authorize('update', $board);

        $board->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()
            ->route('boards.show', $board)
            ->with('success', 'Board berhasil diperbarui!');
    }

    /**
     * Delete a board.
     */
    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);

        $board->delete();

        return redirect()
            ->route('profile.show', Auth::user())
            ->with('success', 'Board berhasil dihapus!');
    }
}
