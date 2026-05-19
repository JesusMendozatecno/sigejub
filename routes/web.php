<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CajaNegraController;


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
| USER / PERFIL
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [UserController::class, 'index'])->name('usuarios.user');
    Route::put('/perfil/actualizar', [UserController::class, 'updateProfile'])->name('usuarios.update');
    Route::post('/perfil/avatar', [UserController::class, 'uploadAvatar'])->name('usuarios.avatar');
    Route::delete('/perfil/avatar', [UserController::class, 'deleteAvatar'])->name('usuarios.avatar-delete');
    Route::put('/perfil/password', [UserController::class, 'updatePassword'])->name('usuarios.password');
    Route::put('/perfil/configuracion', [UserController::class, 'updateSettings'])->name('usuarios.settings');
    Route::put('/perfil/notificaciones', [UserController::class, 'updateNotifications'])->name('usuarios.notifications');
    Route::put('/perfil/2fa', [UserController::class, 'toggle2FA'])->name('usuarios.2fa');
    Route::get('/perfil/sesiones', [UserController::class, 'getSessions'])->name('usuarios.sessions');
    Route::delete('/perfil/sesiones/{sessionId}', [UserController::class, 'destroySession'])->name('usuarios.session-delete');
    Route::post('/perfil/sesiones/cerrar-otras', [UserController::class, 'destroyOtherSessions'])->name('usuarios.session-kill');
    Route::get('/perfil/actividad', [UserController::class, 'getActivity'])->name('usuarios.activity');
    Route::get('/perfil/estadisticas', [UserController::class, 'getStats'])->name('usuarios.stats');

    // Admin-only dentro del perfil
    Route::get('/perfil/admin/usuarios', [UserController::class, 'adminUsers'])->name('usuarios.admin.users');
    Route::put('/perfil/admin/usuarios/{id}', [UserController::class, 'adminUpdateUser'])->name('usuarios.admin.update-user');
    Route::delete('/perfil/admin/usuarios/{id}', [UserController::class, 'adminDeleteUser'])->name('usuarios.admin.delete-user');
    Route::get('/perfil/admin/actividad', [UserController::class, 'adminActivity'])->name('usuarios.admin.activity');
    Route::post('/perfil/admin/config-global', [UserController::class, 'adminGlobalConfig'])->name('usuarios.admin.config');
});

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
Route::get('/solicitudes/por-mes', [SolicitudController::class, 'porMes'])->name('solicitudes.por-mes');
Route::get('/solicitudes/vencimientos', [SolicitudController::class, 'vencimientos'])->name('solicitudes.vencimientos');
Route::get('/solicitudes/exportar', [SolicitudController::class, 'exportarPDF'])->name('solicitudes.exportar');
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
/*
|-----------------------------
| ADMIN (solo admin)
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/usuarios', [AdminController::class, 'usuarios']);
    Route::get('/usuarios/{id}', [AdminController::class, 'showUsuario']);
    Route::put('/usuarios/{id}', [AdminController::class, 'updateUsuario']);
    Route::get('/actividades-detalle', [AdminController::class, 'actividades']);
    Route::get('/actividades-resumen', [AdminController::class, 'actividadResumen']);
    Route::post('/notificaciones', [AdminController::class, 'enviarNotificacion']);
});

Route::middleware('auth')->group(function () {
    Route::get('/notificaciones', [AdminController::class, 'misNotificaciones']);
    Route::get('/notificaciones/no-leidas', [AdminController::class, 'notificacionesNoLeidas']);
    Route::put('/notificaciones/{id}/leer', [AdminController::class, 'marcarLeida']);
    Route::put('/notificaciones/leer-todas', [AdminController::class, 'marcarTodasLeidas']);
});

/*
|-----------------------------
| CAJA NEGRA (admin only)
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/caja-negra', [CajaNegraController::class, 'index']);
    Route::get('/caja-negra/exportar', [CajaNegraController::class, 'exportar']);
    Route::get('/caja-negra/{id}', [CajaNegraController::class, 'show']);
    Route::get('/caja-negra-data/estadisticas', [CajaNegraController::class, 'stats']);
    Route::get('/caja-negra-data/usuarios', [CajaNegraController::class, 'usuarios']);
});

Route::get('/expedientes', [ExpedienteController::class, 'index']);
Route::get('/expedientes/buscar-trabajador', [ExpedienteController::class, 'buscarTrabajador']);
Route::post('/expedientes', [ExpedienteController::class, 'store']);
Route::get('/expedientes/{id}', [ExpedienteController::class, 'show']);
Route::put('/expedientes/{id}/notas', [ExpedienteController::class, 'updateNotas']);
Route::post('/expedientes/{id}/documentos', [ExpedienteController::class, 'subirDocumento']);
Route::put('/documentos/{id}/estado', [ExpedienteController::class, 'updateDocumentoStatus']);
Route::post('/documentos/{id}/reemplazar', [ExpedienteController::class, 'reemplazarDocumento']);