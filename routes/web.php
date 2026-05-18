<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;


/*
|-----------------------------
| INICIO & LOGIN
|-----------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

/*
|-----------------------------
| REGISTRO
|-----------------------------
*/
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

/*
|-----------------------------
| DASHBOARD (PROTEGIDO)
|-----------------------------
*/
Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');

/*
|-----------------------------
| LOGOUT
|-----------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|-----------------------------
| USER
|-----------------------------
*/
// Ruta para ver la lista o el control de datos del usuario

Route::get('/perfil', [UserController::class, 'index'])->name('usuarios.user');
Route::put('/perfil/actualizar', [UserController::class, 'update'])->name('usuarios.update');

/*
|-----------------------------
| TRABAJADORES (CRUD + API)
|-----------------------------
*/
// Rutas API JSON para AJAX (tabla del directorio)
Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores.index');
Route::get('/trabajadores/{id}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
Route::put('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

// El store se mantiene igual
Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajador');