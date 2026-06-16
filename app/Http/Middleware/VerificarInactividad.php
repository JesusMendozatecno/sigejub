<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarInactividad
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Skip check on the keep-alive ping endpoint
        if ($request->is('actividad/ping')) {
            return $next($request);
        }

        $timeout = config('auth.inactivity_timeout', 30);
        $lastActivity = session('ultima_actividad');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $timeout) {
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'Sesión expirada por inactividad.',
                    'redirect' => url('/login'),
                ], 401);
            }

            return redirect('/login')->withErrors([
                'correo' => 'Tu sesión ha expirado por inactividad. Inicia sesión nuevamente.',
            ]);
        }

        // Only update activity on non-AJAX requests (page navigation).
        // AJAX auto-polls (notifications, etc.) do NOT reset inactivity.
        // User activity is tracked by JS via /actividad/ping.
        if (!$request->ajax()) {
            session(['ultima_actividad' => now()]);
        }

        return $next($request);
    }
}
