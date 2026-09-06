<?php
// Controlador de autenticación: login, registro, dashboard y logout.
// Maneja tanto respuestas JSON (AJAX) como redirecciones HTTP tradicionales.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Activity;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Muestra el formulario de inicio de sesión
    public function loginForm()
    {
        return view('auth.login');
    }

    // 🔐 LOGIN (AJAX + JSON)
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('correo', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->ultimo_acceso = now();
            $user->ultimo_acceso_ip = $request->ip();
            $user->save();

            AuditService::registrar(
                'login',
                'usuario',
                $user->id,
                "Inicio de sesión del usuario {$user->nombre} {$user->apellido}"
            );

            if ($request->expectsJson()) {
                $request->session()->flash('success', 'Bienvenido al sistema SIGEJUB');
                return response()->json([
                    'estado' => 'success',
                    'mensaje' => 'Bienvenido al sistema SIGEJUB',
                    'redirect' => '/dashboard'
                ]);
            }

            return redirect('dashboard')->with('success', 'Bienvenido al sistema SIGEJUB');
        }

        AuditService::registrar(
            'login_failed',
            'usuario',
            null,
            "Inicio de sesión fallido para " . ($request->correo ?? 'correo desconocido'),
            null,
            ['correo_intentado' => $request->correo ?? null],
            ['resultado' => 'fallido']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Credenciales incorrectas'
            ], 422);
        }

        return back()->withErrors([
            'correo' => 'Credenciales incorrectas'
        ]);
    }

    // 🟢 MOSTRAR REGISTRO
    public function registerForm()
    {
        return view('auth.register');
    }

    // 👤 REGISTRAR USUARIO
 public function register(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        'apellido' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        'correo' => 'required|string|email|max:255|unique:users',
        'telefono' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
        'fecha_nacimiento' => 'required|date|before:today',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
    ]);

    $user = User::create([
        'nombre' => $request->nombre,
        'apellido' => $request->apellido,
        'correo' => $request->correo,
        'telefono' => $request->telefono,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'password' => Hash::make($request->password),
        'rol' => 'usuario',
    ]);

    Activity::log('created', 'usuario', $user->id,
        "Se registró el usuario {$user->nombre} {$user->apellido}");

    return redirect('login')->with('success', 'Usuario registrado correctamente');
}

    // 🏠 DASHBOARD
    public function dashboard()
    {
        $trabajadores = \App\Models\Trabajador::orderBy('id', 'desc')->paginate(10);
        
        // Contamos el total para el badge de arriba
        $totalRegistrados = \App\Models\Trabajador::count();

        // Enviamos las variables a la vista
        return view('dashboard.index', compact('trabajadores', 'totalRegistrados'));
        //return view('dashboard.index');
    }

    // 🚪 LOGOUT
    public function logout(Request $request)
    {
        AuditService::registrar(
            'logout',
            'usuario',
            auth()->id(),
            "Cierre de sesión del usuario " . (auth()->user()->nombre ?? 'desconocido')
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // 🔑 MOSTRAR FORMULARIO "OLVIDASTE TU CONTRASEÑA"
    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // 📧 ENVIAR ENLACE DE RECUPERACIÓN DE CONTRASEÑA
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            ['correo' => $request->correo]
        );

        // Respuesta genérica para no revelar si el correo existe.
        return back()->with('status', __(
            $status === Password::RESET_LINK_SENT
                ? 'Te hemos enviado por correo el enlace de recuperación de contraseña.'
                : 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.'
        ));
    }

    // 🔓 MOSTRAR FORMULARIO PARA DEFINIR NUEVA CONTRASEÑA
    public function resetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'correo' => $request->query('correo', ''),
        ]);
    }

    // 🔒 GUARDAR NUEVA CONTRASEÑA
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'correo' => 'required|email',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        ]);

        $status = Password::reset(
            $request->only('correo', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                AuditService::registrar(
                    'password_reset',
                    'usuario',
                    $user->id,
                    "El usuario {$user->nombre} {$user->apellido} restableció su contraseña"
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Tu contraseña ha sido restablecida. Inicia sesión con tu nueva contraseña.');
        }

        return back()->withErrors(['correo' => __($status)]);
    }


}

