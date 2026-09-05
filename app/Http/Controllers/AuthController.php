<?php
// Controlador de autenticación: login, registro, dashboard y logout.
// Maneja tanto respuestas JSON (AJAX) como redirecciones HTTP tradicionales.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Activity;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;

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


}

