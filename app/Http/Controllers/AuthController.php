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

    // 🔐 LOGIN
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('dashboard')->with('success', 'Bienvenido al sistema SIGEJUB');
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
    // Es recomendable validar los datos antes de crear
   /* $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'role' => 'required' // Asegura que el rol sea obligatorio
    ]);*/

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role 
    ]);

    Activity::log('created', 'usuario', $user->id,
        "Se registró el usuario {$user->name}");

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

