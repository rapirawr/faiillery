<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenace = Cache::remember('cms.maintenance_mode', 60, fn () => Setting::get('maintenance_mode', '0'));

        if ($isMaintenace === '1') {
            // Admins bypass maintenance mode
            if (auth()->check() && auth()->user()->is_admin) {
                return $next($request);
            }

            $message = Cache::remember('cms.maintenance_message', 60, fn () =>
                Setting::get('maintenance_message', 'Kami sedang melakukan pemeliharaan. Kembali lagi sebentar.')
            );

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            return response()->view('maintenance', ['message' => $message], 503);
        }

        return $next($request);
    }
}
