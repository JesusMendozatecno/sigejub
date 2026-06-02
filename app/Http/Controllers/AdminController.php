<?php

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
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $role = $request->get('role', '');
        $search = $request->get('search', '');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    public function showUsuario($id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function updateUsuario(Request $request, $id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:analista,admin',
        ]);

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        Activity::log('updated', 'usuario', $user->id,
            "Rol de {$user->name} cambiado de {$oldRole} a {$request->role}"
        );

        return response()->json(['message' => 'Permisos actualizados correctamente.']);
    }

    public function actividades(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $type = $request->get('type', '');
        $days = $request->get('days', 7);

        $query = Activity::query();

        if ($type) {
            $query->where('subject_type', $type);
        }

        if ($days > 0) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $activities = $query->with('user')->latest()->take(100)->get();

        return response()->json($activities);
    }

    public function actividadResumen(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $days = $request->get('days', 7);
        $type = $request->get('type', '');
        $since = now()->subDays($days);

        $query = Activity::where('created_at', '>=', $since);

        if ($type) {
            $query->where('subject_type', $type);
        }

        $resumen = $query->selectRaw('DATE(created_at) as fecha, subject_type, COUNT(*) as total')
            ->groupBy('fecha', 'subject_type')
            ->orderBy('fecha')
            ->get();

        return response()->json($resumen);
    }

    public function enviarNotificacion(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Acceso no autorizado');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
        ]);

        $receptor = User::findOrFail($request->user_id);

        $notif = UserNotification::create([
            'user_id' => $request->user_id,
            'from_user_id' => auth()->id(),
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type ?? 'info',
        ]);

        Activity::log('created', 'notificacion', $notif->id,
            auth()->user()->name . " envió notificación a {$receptor->name}"
        );

        try {
            Mail::raw(
                "Has recibido un mensaje de " . auth()->user()->name . ":\n\n" .
                "Asunto: {$request->title}\n\n" .
                "{$request->message}\n\n" .
                "---\nSistema SIGEJUB - Arquitectura de Confianza",
                function ($message) use ($receptor, $request) {
                    $message->to($receptor->email, $receptor->name)
                        ->subject('SIGEJUB - ' . $request->title);
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error al enviar email de notificación: ' . $e->getMessage());
        }

        DashboardCache::flushNotifications($request->user_id);

        return response()->json(['message' => 'Notificación enviada correctamente.']);
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
                ->where('is_read', false)
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

        $notif->update(['is_read' => true]);

        DashboardCache::flushNotifications(auth()->id());

        return response()->json(['message' => 'Notificación marcada como leída.']);
    }

    public function marcarTodasLeidas()
    {
        UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        DashboardCache::flushNotifications(auth()->id());

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas.']);
    }
}
