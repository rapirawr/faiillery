<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && isset(auth()->user()->is_admin) && auth()->user()->is_admin) {
            // Matikan Debugbar jika sedang di area Admin agar tampilan bersih
            if (class_exists('\Barryvdh\Debugbar\Facades\Debugbar')) {
                \Barryvdh\Debugbar\Facades\Debugbar::disable();
            }
            return $next($request);
        }

        return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
