<?php
// Controlador de administración de usuarios, notificaciones y actividades.
// Solo accesible por usuarios con rol 'admin'. Proporciona CRUD de usuarios, envío de notificaciones,
// y consulta de actividades con filtros (tipo, fechas, resumen estadístico).

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use App\Models\UserNotification;
use App\Services\DashboardCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function usuarios(Request $request)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $rol = $request->get('rol', '');
        $search = $request->get('search', '');

        $query = User::query();

        if ($rol) {
            $query->where('rol', $rol);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    public function showUsuario($id)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function updateUsuario(Request $request, $id)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $user = User::findOrFail($id);

        $request->validate([
            'rol' => 'required|in:analista,admin',
        ]);

        $oldRol = $user->rol;
        $user->rol = $request->rol;
        $user->save();

        Activity::log('updated', 'usuario', $user->id,
            "Rol de {$user->nombre} cambiado de {$oldRol} a {$request->rol}"
        );

        return response()->json(['mensaje' => 'Permisos actualizados correctamente.']);
    }

    public function actividades(Request $request)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $tipo = $request->get('tipo', '');
        $days = $request->get('days', 7);

        $query = Activity::query();

        if ($tipo) {
            $query->where('tipo_entidad', $tipo);
        }

        if ($days > 0) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $activities = $query->with('user')->latest()->take(100)->get();

        return response()->json($activities);
    }

    public function actividadResumen(Request $request)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $days = $request->get('days', 7);
        $tipo = $request->get('tipo', '');
        $since = now()->subDays($days);

        $query = Activity::where('created_at', '>=', $since);

        if ($tipo) {
            $query->where('tipo_entidad', $tipo);
        }

        $resumen = $query->selectRaw('DATE(created_at) as fecha, tipo_entidad, COUNT(*) as total')
            ->groupBy('fecha', 'tipo_entidad')
            ->orderBy('fecha')
            ->get();

        return response()->json($resumen);
    }

    public function enviarNotificacion(Request $request)
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Acceso no autorizado');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|string|max:50',
        ]);

        $receptor = User::findOrFail($request->user_id);

        $notif = UserNotification::create([
            'user_id' => $request->user_id,
            'from_user_id' => auth()->id(),
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'tipo' => $request->tipo ?? 'info',
        ]);

        Activity::log('created', 'notificacion', $notif->id,
            auth()->user()->nombre . " envió notificación a {$receptor->nombre}"
        );

        try {
            Mail::raw(
                "Has recibido un mensaje de " . auth()->user()->nombre . ":\n\n" .
                "Asunto: {$request->titulo}\n\n" .
                "{$request->mensaje}\n\n" .
                "---\nSistema SIGEJUB - Arquitectura de Confianza",
                function ($message) use ($receptor, $request) {
                    $message->to($receptor->correo, $receptor->nombre)
                        ->subject('SIGEJUB - ' . $request->titulo);
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error al enviar email de notificación: ' . $e->getMessage());
        }

        DashboardCache::flushNotifications($request->user_id);

        return response()->json(['mensaje' => 'Notificación enviada correctamente.']);
    }

    public function misNotificaciones()
    {
        $userId = auth()->id();
        $notifs = Cache::remember(DashboardCache::key('notificaciones', $userId), 120, function () use ($userId) {
            return UserNotification::where('user_id', $userId)
                ->with('fromUser')
                ->latest()
                ->take(20)
                ->get();
        });

        return response()->json($notifs);
    }

    public function notificacionesNoLeidas()
    {
        $userId = auth()->id();
        $data = Cache::remember(DashboardCache::key('notificaciones.no_leidas', $userId), 120, function () use ($userId) {
            $count = UserNotification::where('user_id', $userId)
                ->where('leida', false)
                ->count();
            return ['count' => $count];
        });

        return response()->json($data);
    }

    public function marcarLeida($id)
    {
        $notif = UserNotification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notif->update(['leida' => true]);

        DashboardCache::flushNotifications(auth()->id());

        return response()->json(['mensaje' => 'Notificación marcada como leída.']);
    }

    public function marcarTodasLeidas()
    {
        UserNotification::where('user_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        DashboardCache::flushNotifications(auth()->id());

        return response()->json(['mensaje' => 'Todas las notificaciones marcadas como leídas.']);
    }
}
