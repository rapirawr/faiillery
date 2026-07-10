<?php

namespace App\Http\Controllers;

use App\Models\ExportJob;
use App\Models\StorageUsage;
use App\Models\UserSetting;
use App\Services\StorageQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the full settings page with real backend data.
     */
    public function showSettings(Request $request): View
    {
        $user = $request->user();
        $storageUsage = (new StorageQuotaService())->getUsage($user);

        // Recent export jobs for this user (last 5)
        $exportJobs = ExportJob::where('user_id', $user->id)
            ->orderBy('requested_at', 'desc')
            ->take(5)
            ->get();

        return view('settings.index', [
            'user'         => $user,
            'storageUsage' => $storageUsage,
            'exportJobs'   => $exportJobs,
        ]);
    }


    /**
     * Save user preferences via AJAX.
     */
    public function savePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:100',
            'settings.*.value' => 'nullable|string|max:5000',
        ]);

        $userId = Auth::id();

        foreach ($request->input('settings') as $setting) {
            UserSetting::setValue($userId, $setting['key'], $setting['value']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
        ]);
    }

    /**
     * Save notification preferences via AJAX.
     */
    public function saveNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'notifications' => 'required|array',
        ]);

        $userId = Auth::id();

        foreach ($request->input('notifications') as $key => $value) {
            UserSetting::setValue($userId, 'notif_' . $key, $value ? '1' : '0');
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferensi notifikasi berhasil disimpan.',
        ]);
    }

    /**
     * Trigger data export – creates a real ExportJob record.
     */
    public function exportData(Request $request): JsonResponse
    {
        $type   = $request->input('type', 'all'); // all, albums, activity
        $userId = Auth::id();

        // Create an export job record
        ExportJob::create([
            'user_id'      => $userId,
            'status'       => 'pending',
            'file_path'    => null,
            'requested_at' => now(),
            'completed_at' => null,
        ]);

        // In production you would dispatch: GenerateUserDataExportJob::dispatch(Auth::user(), $type);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan ekspor data sedang diproses. Kamu akan menerima notifikasi email saat data siap diunduh.',
            'type'    => $type,
        ]);
    }

    /**
     * Get all user settings as JSON.
     */
    public function getSettings(): JsonResponse
    {
        $settings = UserSetting::getAllForUser(Auth::id());

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }
}
