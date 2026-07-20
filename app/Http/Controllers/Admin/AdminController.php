<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Photo;
use App\Models\User;
use App\Models\Board;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin Dashboard Overview
     */
    public function dashboard()
    {
        // Chart data for last 7 days
        $chartData = [
            'labels' => [],
            'photos' => [],
            'users'  => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = now()->subDays($i)->format('D');
            $chartData['photos'][] = Photo::withoutGlobalScopes()->whereDate('created_at', $date)->count();
            $chartData['users'][]  = User::whereDate('created_at', $date)->count();
        }

        // Storage usage — query langsung ke S3/Supabase bucket
        $totalUsedBytes  = $this->getBucketStorageUsed();
        $totalQuotaBytes = \App\Models\StorageUsage::sum('quota_bytes') ?: 0;
        $storagePercent  = $totalQuotaBytes > 0
            ? round(($totalUsedBytes / $totalQuotaBytes) * 100, 1)
            : 0;

        // Fitur 3: Stats tambahan
        $likesCount   = Like::count();
        $followsCount = DB::table('follows')->count();
        $viewsCount   = Photo::withoutGlobalScopes()->sum('views_count');

        // Fitur 5: Online users (active in last 5 minutes via cache)
        $onlineCount = $this->getOnlineUserCount();

        $stats = [
            'users_count'    => User::count(),
            'photos_count'   => Photo::withoutGlobalScopes()->count(),
            'boards_count'   => Board::count(),
            'comments_count' => Comment::count(),
            // Fitur 3
            'likes_count'    => $likesCount,
            'follows_count'  => $followsCount,
            'views_count'    => $viewsCount,
            // Fitur 5
            'online_count'   => $onlineCount,
            // Storage
            'storage_used_bytes'  => $totalUsedBytes,
            'storage_quota_bytes' => $totalQuotaBytes,
            'storage_percent'     => $storagePercent,
            'db_size' => (function() {
                try {
                    $driver = \Illuminate\Support\Facades\DB::getDriverName();
                    if ($driver === 'pgsql') {
                        $result = \Illuminate\Support\Facades\DB::select("SELECT pg_size_pretty(pg_database_size(current_database())) as size");
                        return $result[0]->size ?? 'Unknown';
                    } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                        $result = \Illuminate\Support\Facades\DB::select("
                            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size
                            FROM information_schema.tables
                            WHERE table_schema = DATABASE()
                        ");
                        $mb = $result[0]->size ?? null;
                        if ($mb === null) return 'Unknown';
                        return $mb >= 1024 ? round($mb / 1024, 2) . ' GB' : $mb . ' MB';
                    } elseif ($driver === 'sqlite') {
                        $dbPath = \Illuminate\Support\Facades\DB::getDatabaseName();
                        if ($dbPath && file_exists($dbPath)) {
                            $bytes = filesize($dbPath);
                            if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
                            if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
                            if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
                            return $bytes . ' B';
                        }
                        return 'Unknown';
                    }
                    return 'Unknown';
                } catch (\Exception $e) {
                    return 'Unknown';
                }
            })(),
            'latest_users'  => User::latest()->take(5)->get(),
            'latest_photos' => Photo::withoutGlobalScopes()->with('user')->latest()->take(5)->get(),
            'chart'         => $chartData,
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // ─── Fitur 5: Online Users AJAX Endpoint ────────────────────────────────

    /**
     * Return the current online users count as JSON (for polling).
     */
    public function onlineUsers(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'count' => $this->getOnlineUserCount(),
        ]);
    }

    /**
     * Count users active in the last 5 minutes via cache keys.
     */
    private function getOnlineUserCount(): int
    {
        try {
            $keys = \Illuminate\Support\Facades\Cache::get('online_users', []);
            $now  = now()->timestamp;
            $active = collect($keys)->filter(fn($ts) => ($now - $ts) <= 300)->count();
            return $active;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ─── Fitur 2: User Management with Search & Filter ───────────────────────

    /**
     * User Management — supports search + role filter
     */
    public function users(Request $request)
    {
        $query = User::latest();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        $filter = $request->input('filter', 'all');
        match ($filter) {
            'admin'       => $query->where('is_admin', true),
            'verified'    => $query->where('is_verified', true),
            'shadowbanned' => $query->where('is_shadowbanned', true),
            default       => null,
        };

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users', compact('users', 'search', 'filter'));
    }

    /**
     * Toggle Admin Status
     */
    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus status admin diri sendiri!');
        }

        try {
            $old = $user->is_admin;
            $user->is_admin = !$user->is_admin;
            $user->save();

            $this->logAdminActivity(
                $user->is_admin ? 'grant_admin' : 'revoke_admin',
                'User', $user->id,
                ($user->is_admin ? 'Granted' : 'Revoked') . " admin for {$user->name}"
            );

            return back()->with('success', 'Status admin user berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update status admin: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Verified Badge
     */
    public function toggleVerified(User $user)
    {
        try {
            $user->is_verified = !$user->is_verified;
            $user->save();

            $this->logAdminActivity(
                $user->is_verified ? 'verify_user' : 'unverify_user',
                'User', $user->id,
                ($user->is_verified ? 'Verified' : 'Unverified') . " user {$user->name}"
            );

            return back()->with('success', 'Status verifikasi user diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update verifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Shadowban
     */
    public function toggleShadowban(User $user)
    {
        try {
            $user->is_shadowbanned = !$user->is_shadowbanned;
            $user->save();

            $this->logAdminActivity(
                $user->is_shadowbanned ? 'shadowban_user' : 'unshadowban_user',
                'User', $user->id,
                ($user->is_shadowbanned ? 'Shadowbanned' : 'Unshadowbanned') . " user {$user->name}"
            );

            return back()->with('success', 'Status shadowban user diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update shadowban: ' . $e->getMessage());
        }
    }

    /**
     * Impersonate User (Login as)
     */
    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda sudah login sebagai akun ini.');
        }

        $this->logAdminActivity('impersonate_user', 'User', $user->id, "Impersonated user {$user->name}");

        session(['impersonator_id' => auth()->id()]);
        auth()->loginUsingId($user->id);

        return redirect()->route('home')->with('success', "Sekarang Anda login sebagai {$user->name}");
    }

    /**
     * Delete User
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus diri sendiri!');
        }

        $this->logAdminActivity('delete_user', 'User', $user->id, "Deleted user {$user->name} ({$user->email})");

        $user->delete();
        return back()->with('success', 'User berhasil dihapus selamanya.');
    }

    // ─── Fitur 4: Photo Management with Bulk Delete ─────────────────────────

    /**
     * Photo Management
     */
    public function photos(Request $request)
    {
        $query = Photo::withoutGlobalScopes()->with('user');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $photos = $query->latest()->paginate(24)->withQueryString();

        return view('admin.photos', compact('photos', 'search'));
    }

    /**
     * Fitur 4: Bulk Delete Photos
     */
    public function bulkDeletePhotos(Request $request)
    {
        $request->validate([
            'photo_ids'   => 'required|array|min:1',
            'photo_ids.*' => 'integer|exists:photos,id',
        ]);

        $photos = Photo::withoutGlobalScopes()->whereIn('id', $request->photo_ids)->get();
        $count  = $photos->count();

        foreach ($photos as $photo) {
            $photo->delete();
        }

        $this->logAdminActivity('bulk_delete_photos', 'Photo', null, "Bulk deleted {$count} photos", [
            'ids' => $request->photo_ids,
        ]);

        return back()->with('success', "{$count} foto berhasil dihapus.");
    }

    /**
     * Delete single Photo
     */
    public function deletePhoto(Photo $photo)
    {
        $this->logAdminActivity('delete_photo', 'Photo', $photo->id, "Deleted photo \"{$photo->title}\"");
        $photo->delete();
        return back()->with('success', 'Foto berhasil dihapus oleh Admin.');
    }

    // ─── Announcement ─────────────────────────────────────────────────────────

    /**
     * Show Announcement Page
     */
    public function announcement()
    {
        $current = \App\Models\Announcement::active()->latest()->first();
        $history = \App\Models\Announcement::latest()->paginate(10);
        return view('admin.announcement', compact('current', 'history'));
    }

    /**
     * Send Global Announcement
     */
    public function sendAnnounce(Request $request)
    {
        if ($request->action === 'clear') {
            \App\Models\Announcement::whereRaw('is_active = true')->update(['is_active' => false]);
            return back()->with('success', 'Semua pengumuman aktif telah dinonaktifkan.');
        }

        $request->validate([
            'message'  => 'required|string|max:500',
            'duration' => 'required|string',
        ]);

        \App\Models\Announcement::whereRaw('is_active = true')->update(['is_active' => false]);

        $ends_at = null;
        if ($request->duration !== 'permanent') {
            $ends_at = match($request->duration) {
                '1h' => now()->addHour(),
                '1d' => now()->addDay(),
                '1w' => now()->addWeek(),
                default => null
            };
        }

        \App\Models\Announcement::create([
            'message'   => $request->message,
            'is_active' => true,
            'ends_at'   => $ends_at,
        ]);

        $this->logAdminActivity('send_announcement', 'Announcement', null, "Sent announcement: {$request->message}");

        return back()->with('success', 'Pengumuman baru berhasil disiarkan!');
    }

    /**
     * Admin Force Reset Password
     */
    public function resetPassword(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        $this->logAdminActivity('reset_password', 'User', $user->id, "Reset password for {$user->name}");

        return back()->with('success', 'Password user ' . $user->name . ' berhasil direset!');
    }

    /**
     * Delete Announcement from History
     */
    public function deleteAnnounce(\App\Models\Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'History pengumuman berhasil dihapus.');
    }

    // ─── Fitur 1: Activity Log ───────────────────────────────────────────────

    /**
     * Show admin activity log page.
     */
    public function activityLog(Request $request)
    {
        $query = AdminActivityLog::with('admin')->latest();

        // Filter by action type
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filter by admin user
        if ($adminId = $request->input('admin_id')) {
            $query->where('admin_id', $adminId);
        }

        $logs   = $query->paginate(50)->withQueryString();
        $admins = User::where('is_admin', true)->get();

        return view('admin.activity-log', compact('logs', 'admins'));
    }

    /**
     * Report Management
     */
    public function reports()
    {
        $reports = \App\Models\Report::with(['user', 'photo.user'])->latest()->paginate(20);
        return view('admin.reports', compact('reports'));
    }

    /**
     * Resolve / Dismiss Report
     */
    public function resolveReport(\App\Models\Report $report, Request $request)
    {
        $report->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Laporan berhasil diperbarui.');
    }

    // ─── Private Helpers ────────────────────────────────────────────────────

    /**
     * Log an admin action to the activity log.
     */
    private function logAdminActivity(
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $description = null,
        ?array $meta = null,
    ): void {
        try {
            AdminActivityLog::create([
                'admin_id'     => auth()->id(),
                'action'       => $action,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'description'  => $description,
                'meta'         => $meta,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to write admin activity log: ' . $e->getMessage());
        }
    }

    /**
     * Calculate total bytes used in the S3/Supabase bucket.
     */
    private function getBucketStorageUsed(): int
    {
        return \Illuminate\Support\Facades\Cache::remember('admin_bucket_total_bytes', 300, function () {
            try {
                $disk   = \Illuminate\Support\Facades\Storage::disk('s3');
                $client = $disk->getClient();
                $bucket = config('filesystems.disks.s3.bucket');

                $totalBytes = 0;
                $paginator  = $client->getPaginator('ListObjectsV2', ['Bucket' => $bucket]);

                foreach ($paginator as $page) {
                    foreach ($page['Contents'] ?? [] as $object) {
                        $totalBytes += (int) $object['Size'];
                    }
                }

                return $totalBytes;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Admin: Failed to calculate bucket storage: ' . $e->getMessage());
                return 0;
            }
        });
    }
}
