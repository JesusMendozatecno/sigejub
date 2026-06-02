<?php

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
            'total_trabajadores_capturados' => Activity::where('user_id', $user->id)->where('action', 'created')->where('subject_type', 'trabajador')->count(),
            'total_solicitudes_gestionadas' => Activity::where('user_id', $user->id)->where('subject_type', 'solicitud')->count(),
            'total_expedientes_movidos' => Activity::where('user_id', $user->id)->where('subject_type', 'documento')->count(),
            'dias_en_sistema' => $user->created_at->diffInDays(now()),
        ];

        $users = [];
        if ($user->role === 'admin') {
            $users = User::orderBy('created_at', 'desc')->paginate(20);
        }

        return view('usuarios.user', compact('user', 'sessions', 'activities', 'stats', 'users'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return response()->json(['message' => 'Perfil actualizado correctamente.']);
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
            'message' => 'Foto de perfil actualizada.',
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
        return response()->json(['message' => 'Foto de perfil eliminada.']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta.'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'theme' => 'in:light,dark',
            'language' => 'in:es,en',
            'accent_color' => 'string|max:7',
        ]);

        if ($request->has('theme')) $user->theme = $request->theme;
        if ($request->has('language')) $user->language = $request->language;
        if ($request->has('accent_color')) $user->accent_color = $request->accent_color;
        $user->save();

        return response()->json(['message' => 'Configuración actualizada.']);
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'notification_email' => 'in:all,important,none',
            'notification_system' => 'in:all,important,none',
            'profile_public' => 'boolean',
        ]);

        if ($request->has('notification_email')) $user->notification_email = $request->notification_email;
        if ($request->has('notification_system')) $user->notification_system = $request->notification_system;
        if ($request->has('profile_public')) $user->profile_public = $request->boolean('profile_public');
        $user->save();

        return response()->json(['message' => 'Preferencias actualizadas.']);
    }

    public function toggle2FA(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user->two_factor_enabled = $request->enabled;
        if (!$request->enabled) {
            $user->two_factor_secret = null;
        }
        $user->save();

        return response()->json([
            'message' => $request->enabled ? 'Verificación en dos pasos activada.' : 'Verificación en dos pasos desactivada.',
            'enabled' => $user->two_factor_enabled,
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
                $session->last_activity_humans = \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
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
            return response()->json(['message' => 'Sesión no encontrada.'], 404);
        }

        DB::table('sessions')->where('id', $sessionId)->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function destroyOtherSessions()
    {
        $currentSessionId = session()->getId();
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return response()->json(['message' => 'Sesiones cerradas en otros dispositivos.']);
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
            'total_trabajadores_capturados' => Activity::where('user_id', $user->id)->where('action', 'created')->where('subject_type', 'trabajador')->count(),
            'total_solicitudes_gestionadas' => Activity::where('user_id', $user->id)->where('subject_type', 'solicitud')->count(),
            'total_expedientes_movidos' => Activity::where('user_id', $user->id)->where('subject_type', 'documento')->count(),
            'dias_en_sistema' => $user->created_at->diffInDays($now),
            'ultimo_acceso' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Primer ingreso',
            'ultima_ip' => $user->last_login_ip ?? 'Desconocida',
            'miembro_desde' => $user->created_at->format('d M Y'),
        ];

        return response()->json($stats);
    }

    public function adminUsers(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        $role = $request->get('role', '');
        $search = $request->get('search', '');
        $query = User::query();
        if ($role) $query->where('role', $role);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function adminUpdateUser(Request $request, $id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        $user = User::findOrFail($id);
        $request->validate(['role' => 'required|in:analista,admin']);
        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();
        Activity::log('updated', 'usuario', $user->id, "Rol de {$user->name} cambiado de {$oldRole} a {$request->role}");
        return response()->json(['message' => 'Rol actualizado.']);
    }

    public function adminDeleteUser($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        if ((int)$id === (int)Auth::id()) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }
        $user = User::findOrFail($id);
        if ($user->avatar) Storage::disk('public')->delete($user->avatar);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado.']);
    }

    public function adminActivity()
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        $activities = Activity::with('user')->latest()->take(100)->get();
        return response()->json($activities);
    }

    public function adminGlobalConfig(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
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

        return response()->json(['message' => 'Configuración global actualizada.']);
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
