<?php
// Controlador de perfil de usuario y administración de usuarios (admin).
// Gestiona: perfil, avatar, contraseña, configuraciones, notificaciones, 2FA,
// sesiones activas, actividad personal, y funciones admin (CRUD usuarios, config global).

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Activity;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        $activities = Activity::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get();

        $stats = [
            'total_trabajadores_capturados' => Activity::where('user_id', $user->id)->where('accion', 'created')->where('tipo_entidad', 'trabajador')->count(),
            'total_solicitudes_gestionadas' => Activity::where('user_id', $user->id)->where('tipo_entidad', 'solicitud')->count(),
            'total_expedientes_movidos' => Activity::where('user_id', $user->id)->where('tipo_entidad', 'documento')->count(),
            'dias_en_sistema' => $user->created_at->diffInDays(now()),
        ];

        $users = [];
        if ($user->rol === 'admin') {
            $users = User::orderBy('created_at', 'desc')->paginate(20);
        }

        return view('usuarios.user', compact('user', 'sessions', 'activities', 'stats', 'users'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'correo' => 'required|string|email|max:255|unique:users,correo,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->correo = $request->correo;
        $user->telefono = $request->telefono;
        $user->fecha_nacimiento = $request->fecha_nacimiento;
        $user->save();

        return response()->json(['mensaje' => 'Perfil actualizado correctamente.']);
    }

    public function uploadAvatar(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'mensaje' => 'Foto de perfil actualizada.',
            'avatar' => asset('storage/' . $path),
        ]);
    }

    public function deleteAvatar()
    {
        $user = Auth::user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }
        return response()->json(['mensaje' => 'Foto de perfil eliminada.']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['mensaje' => 'La contraseña actual no es correcta.'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente.']);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'tema' => 'in:light,dark',
            'idioma' => 'in:es,en',
            'color_acento' => 'string|max:7',
        ]);

        if ($request->has('tema')) $user->tema = $request->tema;
        if ($request->has('idioma')) $user->idioma = $request->idioma;
        if ($request->has('color_acento')) $user->color_acento = $request->color_acento;
        $user->save();

        return response()->json(['mensaje' => 'Configuración actualizada.']);
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'notificacion_correo' => 'in:all,important,none',
            'notificacion_sistema' => 'in:all,important,none',
            'perfil_publico' => 'boolean',
        ]);

        if ($request->has('notificacion_correo')) $user->notificacion_correo = $request->notificacion_correo;
        if ($request->has('notificacion_sistema')) $user->notificacion_sistema = $request->notificacion_sistema;
        if ($request->has('perfil_publico')) $user->perfil_publico = $request->boolean('perfil_publico');
        $user->save();

        return response()->json(['mensaje' => 'Preferencias actualizadas.']);
    }

    public function toggle2FA(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'activado' => 'required|boolean',
        ]);

        $user->verificacion_dos_pasos = $request->activado;
        if (!$request->activado) {
            $user->secreto_2fa = null;
        }
        $user->save();

        return response()->json([
            'mensaje' => $request->activado ? 'Verificación en dos pasos activada.' : 'Verificación en dos pasos desactivada.',
            'activado' => $user->verificacion_dos_pasos,
        ]);
    }

    public function getSessions()
    {
        $currentSessionId = session()->getId();
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $session->is_current = $session->id === $currentSessionId;
                $session->ultima_actividad_humans = \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
                return $session;
            });

        return response()->json($sessions);
    }

    public function destroySession($sessionId)
    {
        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$session) {
            return response()->json(['mensaje' => 'Sesión no encontrada.'], 404);
        }

        DB::table('sessions')->where('id', $sessionId)->delete();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }

    public function destroyOtherSessions()
    {
        $currentSessionId = session()->getId();
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return response()->json(['mensaje' => 'Sesiones cerradas en otros dispositivos.']);
    }

    public function getActivity()
    {
        $activities = Activity::where('user_id', Auth::id())
            ->with('user')
            ->latest()
            ->take(100)
            ->get();

        return response()->json($activities);
    }

    public function getStats()
    {
        $user = Auth::user();
        $now = now();

        $stats = [
            'total_trabajadores_capturados' => Activity::where('user_id', $user->id)->where('accion', 'created')->where('tipo_entidad', 'trabajador')->count(),
            'total_solicitudes_gestionadas' => Activity::where('user_id', $user->id)->where('tipo_entidad', 'solicitud')->count(),
            'total_expedientes_movidos' => Activity::where('user_id', $user->id)->where('tipo_entidad', 'documento')->count(),
            'dias_en_sistema' => $user->created_at->diffInDays($now),
            'ultimo_acceso' => $user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Primer ingreso',
            'ultima_ip' => $user->ultimo_acceso_ip ?? 'Desconocida',
            'miembro_desde' => $user->created_at->format('d M Y'),
        ];

        return response()->json($stats);
    }

    public function adminUsers(Request $request)
    {
        abort_unless(Auth::user()->rol === 'admin', 403);
        $rol = $request->get('rol', '');
        $search = $request->get('search', '');
        $query = User::query();
        if ($rol) $query->where('rol', $rol);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%");
            });
        }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function adminUpdateUser(Request $request, $id)
    {
        abort_unless(Auth::user()->rol === 'admin', 403);
        $user = User::findOrFail($id);
        $request->validate(['rol' => 'required|in:analista,admin']);
        $oldRol = $user->rol;
        $user->rol = $request->rol;
        $user->save();
        Activity::log('updated', 'usuario', $user->id, "Rol de {$user->nombre} cambiado de {$oldRol} a {$request->rol}");
        return response()->json(['mensaje' => 'Rol actualizado.']);
    }

    public function adminDeleteUser($id)
    {
        abort_unless(Auth::user()->rol === 'admin', 403);
        if ((int)$id === (int)Auth::id()) {
            return response()->json(['mensaje' => 'No puedes eliminar tu propio usuario.'], 422);
        }
        $user = User::findOrFail($id);
        if ($user->avatar) Storage::disk('public')->delete($user->avatar);
        $user->delete();
        return response()->json(['mensaje' => 'Usuario eliminado.']);
    }

    public function adminActivity()
    {
        abort_unless(Auth::user()->rol === 'admin', 403);
        $activities = Activity::with('user')->latest()->take(100)->get();
        return response()->json($activities);
    }

    public function adminGlobalConfig(Request $request)
    {
        abort_unless(Auth::user()->rol === 'admin', 403);
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'default_theme' => 'nullable|in:light,dark',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        if ($request->has('app_name')) {
            updateEnvValue('APP_NAME', $request->app_name);
        }
        if ($request->has('default_theme')) {
            updateEnvValue('APP_DEFAULT_THEME', $request->default_theme);
        }
        if ($request->has('maintenance_mode')) {
            $mode = $request->boolean('maintenance_mode') ? 'down' : 'up';
            if ($mode === 'down') {
                \Illuminate\Support\Facades\Artisan::call('down');
            } else {
                \Illuminate\Support\Facades\Artisan::call('up');
            }
        }

        return response()->json(['mensaje' => 'Configuración global actualizada.']);
    }
}

if (!function_exists('updateEnvValue')) {
    function updateEnvValue($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $escaped = str_replace(['"', '\\'], ['\"', '\\\\'], $value);
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}=\"{$escaped}\"",
                file_get_contents($path)
            ));
        }
    }
}
