<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search photos by title, description, or tags.
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $tag = $request->input('tag');

        $photosQuery = Photo::with(['user', 'tags']);

        if ($tag) {
            // Filter by tag
            $photosQuery->withTag($tag);
            $activeTag = Tag::where('slug', $tag)->first();
        } else {
            $activeTag = null;
        }

        if (!empty($query)) {
            // Full-text search
            $photosQuery->search($query);
        }

        $photos = $photosQuery->latest()->paginate(30);

        // For AJAX live search
        if ($request->ajax()) {
            $html = '';
            foreach ($photos as $photo) {
                $html .= view('components.photo-card', compact('photo'))->render();
            }

            return response()->json([
                'html' => $html,
                'total' => $photos->total(),
                'next_page' => $photos->nextPageUrl(),
                'has_more' => $photos->hasMorePages(),
            ]);
        }

        // Popular tags for sidebar
        $popularTags = Tag::withCount('photos')
            ->orderByDesc('photos_count')
            ->take(20)
            ->get();

        return view('search.index', compact('photos', 'query', 'activeTag', 'popularTags'));
    }

    /**
     * Live search suggestions (AJAX).
     */
    public function suggest(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $photos = Photo::where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'thumbnail_path')
            ->take(5)
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'thumbnail' => $photo->thumbnail_url,
                    'url' => route('photos.show', $photo),
                ];
            });

        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->select('name', 'slug')
            ->take(5)
            ->get()
            ->map(function ($tag) {
                return [
                    'name' => $tag->name,
                    'url' => route('search', ['tag' => $tag->slug]),
                ];
            });

        return response()->json([
            'photos' => $photos,
            'tags' => $tags,
        ]);
    }
}
