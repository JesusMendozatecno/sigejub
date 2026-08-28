<?php
// Controlador de administración de usuarios, notificaciones y actividades.
// Solo accesible por usuarios con rol 'admin'. Proporciona CRUD de usuarios, envío de notificaciones,
// y consulta de actividades con filtros (tipo, fechas, resumen estadístico).

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use App\Models\UserNotification;
use App\Services\DashboardCache;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function usuarios(Request $request)
    {
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

    public function updateUsuario(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        $request->validate([
            'rol' => 'required|in:usuario,admin,superadmin',
        ]);

        $nuevoRol = $request->rol;

        // Jerarquía de roles:
        // - superadmin puede cambiar a cualquiera
        // - admin solo puede cambiar a 'usuario'
        // - usuario no puede cambiar roles (no llega aquí por middleware)
        if ($currentUser->rol === 'admin' && $nuevoRol !== 'usuario') {
            return response()->json(['mensaje' => 'Solo puedes asignar el rol de Usuario.'], 403);
        }

        if ($currentUser->rol === 'admin' && $user->rol === 'superadmin') {
            return response()->json(['mensaje' => 'No puedes modificar el rol de un Superadmin.'], 403);
        }

        $oldRol = $user->rol;
        $user->rol = $nuevoRol;
        $user->save();

        Activity::log('updated', 'usuario', $user->id,
            "Rol de {$user->nombre} cambiado de {$oldRol} a {$nuevoRol}"
        );

        return response()->json(['mensaje' => 'Permisos actualizados correctamente.']);
    }

    public function deleteUsuario($id)
    {
        if ((int)$id === (int)auth()->id()) {
            return response()->json(['mensaje' => 'No puedes eliminar tu propio usuario.'], 422);
        }
        $user = User::findOrFail($id);
        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        return response()->json(['mensaje' => 'Usuario eliminado.']);
    }

    public function actividades(Request $request)
    {
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

    public function actividadReciente()
    {
        return response()->json(
            Activity::latest()->take(20)->get()
        );
    }

    public function enviarNotificacion(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|string|max:50',
        ]);

        $receptor = User::findOrFail($request->user_id);

        NotificationService::send(
            $request->user_id,
            $request->titulo,
            $request->mensaje,
            $request->tipo ?? 'info'
        );

        NotificationService::sendEmail(
            $receptor,
            'SIGEJUB - ' . $request->titulo,
            "Has recibido un mensaje de " . auth()->user()->nombre . ":\n\n" .
            "Asunto: {$request->titulo}\n\n" .
            "{$request->mensaje}\n\n" .
            "---\nSistema SIGEJUB - Arquitectura de Confianza"
        );

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
