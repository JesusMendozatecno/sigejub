<?php
// Middleware que verifica el rol del usuario autenticado.
// Reutilizable en cualquier ruta o grupo de rutas que requiera permisos específicos.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'No autenticado',
                ], 401);
            }
            return redirect('/login');
        }

        if (!in_array($user->rol, $roles)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'No tiene permisos para acceder a este recurso.',
                ], 403);
            }
            abort(403, 'No tiene permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
