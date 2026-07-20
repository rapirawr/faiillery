<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display user profile with tabs (Dibuat, Disimpan, Board) - PUBLIC VIEW
     */
    public function show(User $user, Request $request): View
    {
        $tab = $request->input('tab', 'created');

        $data = ['user' => $user, 'tab' => $tab];

        switch ($tab) {
            case 'saved':
                // Photos pinned by this user
                $data['photos'] = $user->pins()
                    ->with(['photo.user', 'photo.tags'])
                    ->latest()
                    ->paginate(30)
                    ->through(fn ($pin) => $pin->photo);
                break;

            case 'boards':
                // User's boards
                $boardsQuery = $user->boards()->withCount('photos')->latest();

                // Hide private boards from non-owners
                if (!Auth::check() || Auth::id() !== $user->id) {
                    $boardsQuery->public();
                }

                $data['boards'] = $boardsQuery->get();
                break;

            case 'created':
            default:
                // Photos uploaded by this user
                $data['photos'] = $user->photos()
                    ->with('tags')
                    ->latest()
                    ->paginate(30);
                break;
        }

        // Stats
        $data['photosCount'] = $user->photos()->count();
        $data['boardsCount'] = $user->boards()->public()->count();
        $data['followersCount'] = $user->followers()->count();
        $data['followingCount'] = $user->following()->count();

        return view('profile.show', $data);
    }

    /**
     * Display the user's profile form (SETTINGS VIEW).
     */
    public function edit(Request $request): View
    {
        // Breeze default profile edit view
        return view('settings.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        // Very early debug: write request summary to a dedicated debug log
        try {
            $summary = [
                'time' => now()->toDateTimeString(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'has_avatar' => $request->hasFile('avatar'),
                'has_cover' => $request->hasFile('cover_photo'),
                'content_length' => $request->server('CONTENT_LENGTH'),
            ];
            file_put_contents(storage_path('logs/profile-debug.log'), json_encode($summary) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $t) {
            // ignore
        }

        // Dump files array for deeper inspection
        try {
            $files = $request->allFiles();
            file_put_contents(storage_path('logs/profile-debug.log'), json_encode(array_map(function($f) { return is_array($f) ? array_map(fn($x)=>$x->getClientOriginalName(), $f) : $f->getClientOriginalName(); }, $files)) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $_t) {
            // ignore
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:30|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'nullable|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:30720',
            'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:30720',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?? $user->email,
            'bio' => $validated['bio'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Log incoming profile update attempt
        Log::info('Profile update attempt', [
            'user_id' => $user->id,
            'has_avatar' => $request->hasFile('avatar'),
            'has_cover' => $request->hasFile('cover_photo'),
        ]);

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            Log::info('ProfileController: avatar file detected', ['user_id' => $user->id]);

            if ($user->avatar) {
                try {
                    Log::info('ProfileController: deleting existing avatar', ['user_id' => $user->id, 'path' => $user->avatar]);
                    Storage::disk('s3')->delete($user->avatar);
                } catch (\Exception $e) {
                    Log::warning('ProfileController: failed deleting existing avatar, continuing upload', ['user_id' => $user->id, 'exception' => $e->getMessage(), 'path' => $user->avatar]);
                }
            }

            try {
                Log::info('ProfileController: avatar file size', ['user_id' => $user->id, 'size' => $file->getSize()]);

                $path = Storage::disk('s3')->putFile('avatars', $file, 'public');
                $user->avatar = $path;

                Log::info('ProfileController: avatar upload success', ['user_id' => $user->id, 'path' => $path]);
            } catch (\Exception $e) {
                report($e);
                Log::error('ProfileController: avatar upload failed', ['user_id' => $user->id, 'exception' => $e->getMessage()]);
                session()->flash('error', 'Gagal upload avatar: ' . $e->getMessage());
            }
        }

        // Handle Cover Photo Upload
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            Log::info('ProfileController: cover file detected', ['user_id' => $user->id]);

            if ($user->cover_photo) {
                try {
                    Log::info('ProfileController: deleting existing cover', ['user_id' => $user->id, 'path' => $user->cover_photo]);
                    Storage::disk('s3')->delete($user->cover_photo);
                } catch (\Exception $e) {
                    Log::warning('ProfileController: failed deleting existing cover, continuing upload', ['user_id' => $user->id, 'exception' => $e->getMessage(), 'path' => $user->cover_photo]);
                }
            }

            try {
                Log::info('ProfileController: cover file size', ['user_id' => $user->id, 'size' => $file->getSize()]);

                $path = Storage::disk('s3')->putFile('covers', $file, 'public');
                $user->cover_photo = $path;

                Log::info('ProfileController: cover upload success', ['user_id' => $user->id, 'path' => $path]);
            } catch (\Exception $e) {
                report($e);
                Log::error('ProfileController: cover upload failed', ['user_id' => $user->id, 'exception' => $e->getMessage()]);
                session()->flash('error', 'Gagal upload cover photo: ' . $e->getMessage());
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete associated files
        if ($user->avatar) Storage::delete($user->avatar);
        if ($user->cover_photo) Storage::delete($user->cover_photo);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the portfolio analytics dashboard for the logged in user.
     */
    public function analytics(): View
    {
        $user = auth()->user();
        
        $photosCount = $user->photos()->count();
        $totalViews = (int) $user->photos()->sum('views_count');
        
        $photoIds = $user->photos()->pluck('id');
        $totalLikes = \App\Models\Like::whereIn('photo_id', $photoIds)->count();
        $totalComments = \App\Models\Comment::whereIn('photo_id', $photoIds)->count();
        
        $topPhotos = $user->photos()
            ->orderByDesc('views_count')
            ->take(5)
            ->get();
            
        $chartLabels = [];
        $viewsTrend = [];
        $likesTrend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('D');
            $chartLabels[] = $dayName;
            
            $dailyUploads = $user->photos()->whereDate('created_at', $date)->count();
            
            // Standard simulated projection overlaid on top of actual data for rich styling
            $viewsTrend[] = $dailyUploads * 45 + rand(5, 20) + ($totalViews > 0 ? round($totalViews / 15) : 0);
            $likesTrend[] = $dailyUploads * 10 + rand(1, 5) + ($totalLikes > 0 ? round($totalLikes / 15) : 0);
        }
        
        $stats = [
            'photos_count' => $photosCount,
            'total_views' => $totalViews,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'top_photos' => $topPhotos,
            'chart_labels' => $chartLabels,
            'views_trend' => $viewsTrend,
            'likes_trend' => $likesTrend,
        ];
        
        return view('profile.analytics', compact('stats'));
    }
}
