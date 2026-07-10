<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page with masonry grid of photos.
     */
    public function index(Request $request)
    {
        $photos = Photo::with(['user', 'tags'])
            ->when(auth()->check(), function ($q) {
                $q->withExists(['pins as is_pinned' => function ($query) {
                    $query->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->paginate(30);


        // For infinite scroll AJAX requests
        if ($request->ajax()) {
            $html = '';
            foreach ($photos as $photo) {
                $html .= view('components.photo-card', compact('photo'))->render();
            }

            return response()->json([
                'html'      => $html,
                'next_page' => $photos->nextPageUrl(),
                'has_more'  => $photos->hasMorePages(),
            ]);
        }

        return view('home.index', compact('photos'));
    }
}