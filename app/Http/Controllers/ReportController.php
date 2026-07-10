<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request, Photo $photo)
    {
        $request->validate([
            'reason' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if user has already reported this photo
        $existing = Report::where('user_id', auth()->id())
            ->where('photo_id', $photo->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah melaporkan foto ini.');
        }

        Report::create([
            'user_id' => auth()->id(),
            'photo_id' => $photo->id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Laporan Anda telah dikirim dan akan segera ditinjau oleh tim moderasi.');
    }
}
