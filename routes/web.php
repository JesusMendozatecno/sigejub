<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ExpedienteController;


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

/*
|-----------------------------
| SOLICITUDES (CRUD + API)
|-----------------------------
*/
Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');
Route::delete('/solicitudes/{id}', [SolicitudController::class, 'destroy'])->name('solicitudes.destroy');

/*
|-----------------------------
| ACTIVIDADES (historial)
|-----------------------------
*/
Route::get('/actividades', [ActivityController::class, 'index']);

/*
|-----------------------------
| EXPEDIENTES (CRUD + API)
|-----------------------------
*/
Route::get('/expedientes', [ExpedienteController::class, 'index']);
Route::get('/expedientes/buscar-trabajador', [ExpedienteController::class, 'buscarTrabajador']);
Route::post('/expedientes', [ExpedienteController::class, 'store']);
Route::get('/expedientes/{id}', [ExpedienteController::class, 'show']);
Route::put('/expedientes/{id}/notas', [ExpedienteController::class, 'updateNotas']);
Route::post('/expedientes/{id}/documentos', [ExpedienteController::class, 'subirDocumento']);
Route::put('/documentos/{id}/estado', [ExpedienteController::class, 'updateDocumentoStatus']);
Route::post('/documentos/{id}/reemplazar', [ExpedienteController::class, 'reemplazarDocumento']);