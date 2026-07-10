<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $collection = Auth::user()->collections()->create([
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public', true),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Koleksi berhasil dibuat!',
                'collection' => $collection,
            ]);
        }

        return back()->with('success', 'Koleksi berhasil dibuat!');
    }

    /**
     * Toggle photo in collection.
     */
    public function togglePhoto(Request $request, Collection $collection)
    {
        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

        if ($collection->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $photoId = $request->photo_id;
        $exists = $collection->photos()->where('photo_id', $photoId)->exists();

        if ($exists) {
            $collection->photos()->detach($photoId);
            $attached = false;
        } else {
            $collection->photos()->attach($photoId);
            $attached = true;
        }

        return response()->json([
            'success' => true,
            'attached' => $attached,
            'message' => $attached ? 'Foto berhasil disimpan ke koleksi!' : 'Foto dihapus dari koleksi.',
        ]);
    }

    /**
     * Get user's collections.
     */
    public function index(Request $request)
    {
        $photoId = $request->query('photo_id');
        $collections = Auth::user()->collections()
            ->withCount('photos')
            ->with(['photos' => function($query) use ($photoId) {
                if ($photoId) $query->where('photo_id', $photoId);
            }])
            ->latest()
            ->get();

        if ($photoId) {
            $collections = $collections->map(function($collection) {
                $collection->is_attached = $collection->photos->count() > 0;
                unset($collection->photos);
                return $collection;
            });
        }

        return response()->json($collections);
    }
}
