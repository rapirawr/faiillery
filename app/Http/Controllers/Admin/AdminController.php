<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\User;
use App\Models\Board;
use App\Models\Comment;
use Illuminate\Http\Request;

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
            'users' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = now()->subDays($i)->format('D');
            $chartData['photos'][] = Photo::whereDate('created_at', $date)->count();
            $chartData['users'][] = User::whereDate('created_at', $date)->count();
        }

        // Storage usage — query langsung ke S3/Supabase bucket
        $totalUsedBytes  = $this->getBucketStorageUsed();
        $totalQuotaBytes = \App\Models\StorageUsage::sum('quota_bytes') ?: 0;
        $storagePercent  = $totalQuotaBytes > 0
            ? round(($totalUsedBytes / $totalQuotaBytes) * 100, 1)
            : 0;

        $stats = [
            'users_count' => User::count(),
            'photos_count' => Photo::count(),
            'boards_count' => Board::count(),
            'comments_count' => Comment::count(),
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
                        return $mb >= 1024
                            ? round($mb / 1024, 2) . ' GB'
                            : $mb . ' MB';
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
            'latest_users' => User::latest()->take(5)->get(),
            'latest_photos' => Photo::with('user')->latest()->take(5)->get(),
            'chart' => $chartData,
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * User Management
     */
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
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
            $user->is_admin = !$user->is_admin;
            $user->save();
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

        // Store original admin ID in session if you want to switch back later
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

        $user->delete();
        return back()->with('success', 'User berhasil dihapus selamanya.');
    }

    /**
     * Photo Management
     */
    public function photos()
    {
        $photos = Photo::with('user')->latest()->paginate(20);
        return view('admin.photos', compact('photos'));
    }

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
            'message' => 'required|string|max:500',
            'duration' => 'required|string'
        ]);

        // Deactivate previous ones
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
            'message' => $request->message,
            'is_active' => true,
            'ends_at' => $ends_at
        ]);

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

    /**
     * Calculate total bytes used in the S3/Supabase bucket.
     * Paginates through all objects to get accurate total.
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
                \Log::warning('Admin: Failed to calculate bucket storage: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Delete Photo
     */
    public function deletePhoto(Photo $photo)
    {
        $photo->delete();
        return back()->with('success', 'Foto berhasil dihapus oleh Admin.');
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
            'status' => $request->status // resolved, dismissed
        ]);

        return back()->with('success', 'Laporan berhasil diperbarui.');
    }
}
