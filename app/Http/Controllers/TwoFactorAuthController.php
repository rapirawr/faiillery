<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorAuthController extends Controller
{
    /**
     * Setup 2FA: Generate secret key and display instructions.
     */
    public function setup(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Normally, we'd use Google2FA package to generate key:
        // $google2fa = app('pragmarx.google2fa');
        // $secret = $google2fa->generateSecretKey();
        
        $secret = 'MOCKSECRET' . Str::random(16);

        // Store temp secret key in user settings
        UserSetting::setValue($user->id, 'two_fa_temp_secret', $secret);

        return response()->json([
            'success' => true,
            'secret' => $secret,
            // In production: return QR Code inline
            // 'qr_code' => $google2fa->getQRCodeInline(config('app.name'), $user->email, $secret)
        ]);
    }

    /**
     * Verify 2FA code and enable 2FA if valid.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $tempSecret = UserSetting::getValue($user->id, 'two_fa_temp_secret');

        if (!$tempSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Lakukan setup 2FA terlebih dahulu.'
            ], 400);
        }

        // Normally verify code:
        // $google2fa = app('pragmarx.google2fa');
        // $valid = $google2fa->verifyKey($tempSecret, $request->code);
        
        $valid = $request->code === '123456'; // Mock code verification for demo/testing

        if ($valid) {
            // Save active secret, enable 2FA, generate recovery codes
            UserSetting::setValue($user->id, 'two_fa_secret', $tempSecret);
            UserSetting::setValue($user->id, 'two_fa_enabled', '1');
            
            // Generate recovery codes
            $recoveryCodes = [];
            for ($i = 0; $i < 4; $i++) {
                $recoveryCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
            }
            UserSetting::setValue($user->id, 'two_fa_recovery_codes', json_encode($recoveryCodes));

            // Clear temp secret
            UserSetting::setValue($user->id, 'two_fa_temp_secret', null);

            return response()->json([
                'success' => true,
                'message' => '2FA berhasil diaktifkan.',
                'recovery_codes' => $recoveryCodes
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode verifikasi tidak cocok.'
        ], 422);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        UserSetting::setValue($user->id, 'two_fa_enabled', '0');
        UserSetting::setValue($user->id, 'two_fa_secret', null);
        UserSetting::setValue($user->id, 'two_fa_recovery_codes', null);

        return response()->json([
            'success' => true,
            'message' => '2FA berhasil dinonaktifkan.'
        ]);
    }
}
