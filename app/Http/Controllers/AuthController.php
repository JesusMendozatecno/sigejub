<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 🔵 MOSTRAR LOGIN
    public function loginForm()
    {
        return view('auth.login');
    }

    // 🔐 LOGIN (AJAX + JSON)
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->save();

            if ($request->expectsJson()) {
                $request->session()->flash('success', 'Bienvenido al sistema SIGEJUB');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bienvenido al sistema SIGEJUB',
                    'redirect' => '/dashboard'
                ]);
            }

            return redirect('dashboard')->with('success', 'Bienvenido al sistema SIGEJUB');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales incorrectas'
            ], 422);
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas'
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
        'name' => 'required|string|max:255',
        'surname' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'required|string|max:20',
        'fecha_nacimiento' => 'required|date',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:analista,admin',
    ]);

    $user = User::create([
        'name' => $request->name,
        'surname' => $request->surname,
        'email' => $request->email,
        'phone' => $request->phone,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'password' => Hash::make($request->password),
        'role' => $request->role,
    ]);

    Activity::log('created', 'usuario', $user->id,
        "Se registró el usuario {$user->name} {$user->surname}");

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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }


}

