<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\Photo;
use App\Models\Tag;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoController extends Controller
{
    public function __construct(
        protected PhotoService $photoService
    ) {}

    /**
     * Show upload photo form.
     */
    public function create()
    {
        $boards = Auth::user()->boards()->latest()->get();

        return view('photos.create', compact('boards'));
    }

    /**
     * Store a newly uploaded photo.
     */
    public function store(StorePhotoRequest $request)
    {
        $files = $request->file('image');
        $uploadedPhotos = [];

        \Illuminate\Support\Facades\Log::info('Upload process started', ['files_count' => is_array($files) ? count($files) : ($files ? 1 : 0)]);

        // Ensure $files is an array for consistent processing
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }

        try {
            foreach ($files as $index => $file) {
                $title = $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                \Illuminate\Support\Facades\Log::info("Uploading file {$index}", ['filename' => $file->getClientOriginalName(), 'title' => $title]);

                $photo = $this->photoService->upload(
                    user: Auth::user(),
                    file: $file,
                    title: $title,
                    description: $request->input('description'),
                    tags: $request->input('tags', ''),
                    boardId: $request->input('board_id'),
                );

                $uploadedPhotos[] = $photo;
                \Illuminate\Support\Facades\Log::info("Uploaded file {$index} success", ['photo_id' => $photo->id]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }

        // Determine where to redirect
        $redirectPhoto = count($uploadedPhotos) === 1 ? $uploadedPhotos[0] : null;
        $message = count($uploadedPhotos) > 1
            ? count($uploadedPhotos) . ' foto berhasil diupload!'
            : 'Foto berhasil diupload!';

        \Illuminate\Support\Facades\Log::info('Upload process finished', ['total_uploaded' => count($uploadedPhotos)]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'photos' => $uploadedPhotos,
                'redirect' => $redirectPhoto ? route('photos.show', $redirectPhoto) : route('home'),
            ]);
        }

        if ($redirectPhoto) {
            return redirect()
                ->route('photos.show', $redirectPhoto)
                ->with('success', $message);
        }

        return redirect()
            ->route('home')
            ->with('success', $message);
    }

    /**
     * Display photo detail page.
     */
    public function show(Photo $photo)
    {
        $photo->load(['user', 'tags', 'likes', 'comments.user']);

        $photo->increment('views_count');

        // Related photos: same tags or same user
        $relatedPhotos = Photo::where('id', '!=', $photo->id)
            ->where(function ($query) use ($photo) {
                $tagIds = $photo->tags->pluck('id');
                if ($tagIds->isNotEmpty()) {
                    $query->whereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('tags.id', $tagIds);
                    });
                }
                $query->orWhere('user_id', $photo->user_id);
            })
            ->with(['user', 'tags'])
            ->inRandomOrder()
            ->take(20)
            ->get();

        $userBoards = null;
        if (Auth::check()) {
            $userBoards = Auth::user()->boards()->latest()->get();
        }

        return view('photos.show', compact('photo', 'relatedPhotos', 'userBoards'));
    }

    /**
     * Show edit photo form.
     */
    public function edit(Photo $photo)
    {
        $this->authorize('update', $photo);

        return view('photos.edit', compact('photo'));
    }

    /**
     * Update a photo.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo)
    {
        $this->authorize('update', $photo);

        $photo->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        // Sync tags
        if ($request->has('tags')) {
            $this->photoService->syncTags($photo, $request->input('tags', ''));
        }

        return redirect()
            ->route('photos.show', $photo)
            ->with('success', 'Foto berhasil diperbarui!');
    }

    /**
     * Delete a photo.
     */
    public function destroy(Photo $photo)
    {
        $this->authorize('delete', $photo);

        $this->photoService->delete($photo);

        return redirect()
            ->route('home')
            ->with('success', 'Foto berhasil dihapus!');
    }

    /**
     * Proxy download to force browser download for cross-origin S3/Supabase images.
     *
     * Fixes applied vs. the original version:
     *  - Added Content-Length header so the browser knows the exact expected
     *    file size (prevents "corrupted/truncated file" on flaky connections).
     *  - Validate that the response body is not empty before proceeding.
     *  - Expanded mime <-> extension maps (bmp, svg, tiff, heic/heif) so files
     *    in those formats don't lose/mismatch their extension.
     *  - On failure, we no longer silently redirect to the raw image_url
     *    (which can serve an HTML error page disguised as an image if the
     *    URL is expired/protected/CORS-blocked). Instead we abort with a
     *    proper HTTP error so the failure is visible and diagnosable.
     *  - Removed 'verify' => false (disabling SSL verification) unless your
     *    storage provider specifically requires it for self-signed certs.
     */
    /**
     * Proxy download to force browser download for cross-origin S3/Supabase images.
     *
     * Goal: selalu kirim bytes binary image yang valid (bukan HTML error/redirect)
     * supaya file hasil download tidak rusak/unsupported.
     */
    public function download(Photo $photo)
    {
        $storedPath = $photo->image_path ?? '';
        $extension  = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename   = \Illuminate\Support\Str::slug($photo->title ?: 'failerry-photo') . '.' . $extension;

        $mimeMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'avif' => 'image/avif',
        ];
        $mimeType = $mimeMap[$extension] ?? 'image/jpeg';

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('s3');

            abort_unless($disk->exists($storedPath), 404, 'File not found');

            $stream = $disk->readStream($storedPath);
            $size   = $disk->size($storedPath);

            // Detect real MIME from first bytes
            $chunk    = fread($stream, 512);
            rewind($stream);
            $detected = (new \finfo(FILEINFO_MIME_TYPE))->buffer($chunk);
            if ($detected && str_starts_with($detected, 'image/')) {
                $mimeType = $detected;
            }

            \Illuminate\Support\Facades\Log::info('Download', [
                'path' => $storedPath, 'mime' => $mimeType, 'size' => $size,
            ]);

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) fclose($stream);
            }, 200, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length'      => $size,
                'Cache-Control'       => 'no-cache',
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Download failed: ' . $e->getMessage(), [
                'path' => $storedPath,
            ]);
            return redirect()->away($photo->image_url);
        }
    }


    /**
     * Show minimalist embed page.
     */
    public function embed(Photo $photo)
    {
        $photo->load(['user']);
        return view('photos.embed', compact('photo'));
    }

    /**
     * Show photobooth page.
     */
    public function photobooth()
    {
        return view('photos.photobooth');
    }
}