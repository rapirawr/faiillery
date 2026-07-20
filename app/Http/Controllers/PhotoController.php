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
            ? count($uploadedPhotos) . ' file berhasil diunggah!'
            : 'File berhasil diunggah!';

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
            ->with('success', 'Postingan berhasil diperbarui!');
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
            ->with('success', 'Postingan berhasil dihapus!');
    }

    /**
     * Proxy download to force browser download for cross-origin S3/Supabase images.
     *
     * FIX: rewind() pada remote S3 stream tidak me-reset pointer ke byte 0.
     * Akibatnya fpassthru() melewatkan 512 byte pertama yang sudah di-fread()
     * untuk deteksi MIME — membuat file hasil download corrupt/truncated.
     *
     * Solusi: hapus fread+rewind, deteksi MIME cukup dari extension saja.
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
            'bmp'  => 'image/bmp',
            'svg'  => 'image/svg+xml',
            'tiff' => 'image/tiff',
            'heic' => 'image/heic',
            'heif' => 'image/heif',
            'mp4'  => 'video/mp4',
            'mov'  => 'video/quicktime',
            'webm' => 'video/webm',
            'ogg'  => 'video/ogg',
        ];
        $mimeType = $mimeMap[$extension] ?? 'application/octet-stream';

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('s3');

            abort_unless($disk->exists($storedPath), 404, 'File not found in storage');

            // Ambil ukuran SEBELUM membuka stream
            $size = $disk->size($storedPath);

            // Buka stream SEKALI dan TIDAK fread/rewind — langsung stream penuh
            $stream = $disk->readStream($storedPath);

            abort_unless(is_resource($stream), 500, 'Failed to open file stream');

            \Illuminate\Support\Facades\Log::info('Photo download', [
                'photo_id' => $photo->id,
                'path'     => $storedPath,
                'mime'     => $mimeType,
                'size'     => $size,
            ]);

            // Flush output buffer agar tidak ada karakter/whitespace yang bercampur
            while (ob_get_level()) {
                ob_end_clean();
            }

            return response()->stream(function () use ($stream) {
                // Stream langsung ke output tanpa buffer tambahan
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, [
                'Content-Type'              => $mimeType,
                'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
                'Content-Length'            => $size,
                'Cache-Control'             => 'no-store, no-cache, must-revalidate',
                'X-Content-Type-Options'    => 'nosniff',
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Photo download failed', [
                'photo_id' => $photo->id,
                'path'     => $storedPath,
                'error'    => $e->getMessage(),
            ]);

            abort(500, 'Download gagal: ' . $e->getMessage());
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