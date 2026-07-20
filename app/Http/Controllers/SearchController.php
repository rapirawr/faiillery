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

        // Check if query is a hex color code
        if (!empty($query) && preg_match('/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $query)) {
            $targetColor = $query;
            $allPhotos = $photosQuery->get();
            
            // Sort by Euclidean distance to dominant color
            $sorted = $allPhotos->sortBy(function ($photo) use ($targetColor) {
                return $this->colorDistance($photo->dominant_color ?? '#FFFFFF', $targetColor);
            });
            
            $page = $request->input('page', 1);
            $perPage = 30;
            $photos = new \Illuminate\Pagination\LengthAwarePaginator(
                $sorted->forPage($page, $perPage)->values(),
                $sorted->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            if (!empty($query)) {
                // Full-text search
                $photosQuery->search($query);
            }
            $photos = $photosQuery->latest()->paginate(30);
        }

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

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }

    private function colorDistance($c1, $c2)
    {
        list($r1, $g1, $b1) = $this->hexToRgb($c1);
        list($r2, $g2, $b2) = $this->hexToRgb($c2);
        return sqrt(pow($r1 - $r2, 2) + pow($g1 - $g2, 2) + pow($b1 - $b2, 2));
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
