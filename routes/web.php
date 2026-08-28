<?php
// Archivo principal de rutas web del sistema SIGEJUB.
// Define todas las rutas HTTP: autenticación, CRUD de entidades, API AJAX, exportaciones y administración.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\PrestacionesController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CajaNegraController;
use App\Http\Controllers\NominaExportController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\MasterDataController;


/*
|-----------------------------
| INICIO & LOGIN
|-----------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

/*
|-----------------------------
| REGISTRO
|-----------------------------
*/
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');

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
| ACTIVITY PING (keep-alive)
|-----------------------------
*/
Route::post('/actividad/ping', function () {
    session(['ultima_actividad' => now()]);
    return response()->json(['estado' => 'ok']);
})->middleware(['auth', 'throttle:60,1']);

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
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::get('/perfil/admin/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.admin.users');
        Route::put('/perfil/admin/usuarios/{id}', [AdminController::class, 'updateUsuario'])->name('usuarios.admin.update-user');
        Route::delete('/perfil/admin/usuarios/{id}', [AdminController::class, 'deleteUsuario'])->name('usuarios.admin.delete-user');
        Route::get('/perfil/admin/actividad', [AdminController::class, 'actividades'])->name('usuarios.admin.activity');
        Route::post('/perfil/admin/config-global', [UserController::class, 'adminGlobalConfig'])->name('usuarios.admin.config');
    });

    // Documentación / Changelog
    Route::get('/documentacion', [App\Http\Controllers\ChangelogController::class, 'view'])->name('documentacion');
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::post('/documentacion/generar', [App\Http\Controllers\ChangelogController::class, 'generate'])->name('documentacion.generate');
    });
    Route::get('/documentacion/api', [App\Http\Controllers\ChangelogController::class, 'index'])->name('documentacion.api');
});

/*
|-----------------------------
| TRABAJADORES (CRUD + API)
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores.index');
    Route::get('/trabajadores/autocomplete', [TrabajadorController::class, 'autocomplete'])->name('trabajadores.autocomplete');
    Route::get('/trabajadores/{id}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
    Route::put('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
    Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');
    Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajador');
    Route::get('/trabajadores-stats/dashboard', [TrabajadorController::class, 'dashboardStats']);
    Route::get('/exportar/nomina', [NominaExportController::class, 'exportar'])->name('exportar.nomina');
    Route::post('/importar/nomina', [NominaExportController::class, 'importar'])->name('importar.nomina');
    Route::get('/nomina', [NominaController::class, 'index'])->name('nomina.index');
});

/*
|-----------------------------
| SOLICITUDES (CRUD + API)
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/por-mes', [SolicitudController::class, 'porMes'])->name('solicitudes.por-mes');
    Route::get('/solicitudes/vencimientos', [SolicitudController::class, 'vencimientos'])->name('solicitudes.vencimientos');
    Route::get('/solicitudes/estadisticas', [SolicitudController::class, 'estadisticas'])->name('solicitudes.estadisticas');
    Route::get('/solicitudes/exportar', [SolicitudController::class, 'exportarPDF'])->name('solicitudes.exportar');
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    Route::delete('/solicitudes/{id}', [SolicitudController::class, 'destroy'])->name('solicitudes.destroy');
});

/*
|-----------------------------
| ACTIVIDADES (historial)
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/actividades', [AdminController::class, 'actividadReciente']);
});

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
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/usuarios', [AdminController::class, 'usuarios']);
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
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/caja-negra', [CajaNegraController::class, 'index']);
    Route::get('/caja-negra/exportar', [CajaNegraController::class, 'exportar']);
    Route::get('/caja-negra/{id}', [CajaNegraController::class, 'show']);
    Route::get('/caja-negra-data/estadisticas', [CajaNegraController::class, 'stats']);
    Route::get('/caja-negra-data/usuarios', [CajaNegraController::class, 'usuarios']);
});

Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/backups', [BackupController::class, 'index']);
    Route::post('/backups/generar', [BackupController::class, 'generar'])->middleware('throttle:5,1');
    Route::get('/backups/{archivo}/descargar', [BackupController::class, 'descargar']);
    Route::delete('/backups/{archivo}', [BackupController::class, 'eliminar']);
});

Route::middleware('auth')->group(function () {
    Route::get('/prestaciones', [PrestacionesController::class, 'index']);
    Route::get('/prestaciones/{id}', [PrestacionesController::class, 'show']);
    Route::post('/prestaciones', [PrestacionesController::class, 'store']);
    Route::post('/prestaciones/{id}/comprobante', [PrestacionesController::class, 'comprobante']);

    Route::get('/expedientes', [ExpedienteController::class, 'index']);
    Route::get('/expedientes/listos-aprobacion', [ExpedienteController::class, 'listosParaAprobacion']);
    Route::get('/expedientes/buscar-trabajador', [ExpedienteController::class, 'buscarTrabajador']);
    Route::post('/expedientes', [ExpedienteController::class, 'store']);
    Route::get('/expedientes/{id}', [ExpedienteController::class, 'show']);
    Route::put('/expedientes/{id}/notas', [ExpedienteController::class, 'updateNotas']);
    Route::post('/expedientes/{id}/carta-aprobacion', [ExpedienteController::class, 'subirCartaAprobacion']);
    Route::post('/expedientes/{id}/documentos', [ExpedienteController::class, 'subirDocumento']);
    Route::put('/documentos/{id}/estado', [ExpedienteController::class, 'updateDocumentoStatus']);
    Route::post('/documentos/{id}/reemplazar', [ExpedienteController::class, 'reemplazarDocumento']);
    Route::post('/expedientes/{id}/foto-carnet', [ExpedienteController::class, 'updateFotoCarnet']);
});

/*
|-----------------------------
| TABLAS MAESTRAS (admin)
|-----------------------------
*/

Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/master/{tipo}', [MasterDataController::class, 'index'])->name('master.index');
    Route::post('/master/{tipo}', [MasterDataController::class, 'store'])->name('master.store');
    Route::get('/master/{tipo}/{id}', [MasterDataController::class, 'show'])->name('master.show');
    Route::put('/master/{tipo}/{id}', [MasterDataController::class, 'update'])->name('master.update');
    Route::delete('/master/{tipo}/{id}', [MasterDataController::class, 'destroy'])->name('master.destroy');
});

/*
|-----------------------------
| TASAS DE CAMBIO
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/tasas-cambio', [App\Http\Controllers\TasaCambioController::class, 'index'])->name('tasas.index');
    Route::get('/tasas-cambio/actual', [App\Http\Controllers\TasaCambioController::class, 'actual'])->name('tasas.actual');
    Route::get('/tasas-cambio/estado', [App\Http\Controllers\TasaCambioController::class, 'estado'])->name('tasas.estado');
    Route::get('/tasas-cambio/historial', [App\Http\Controllers\TasaCambioController::class, 'historial'])->name('tasas.historial');
});

Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::post('/tasas-cambio', [App\Http\Controllers\TasaCambioController::class, 'store'])->name('tasas.store');
    Route::post('/tasas-cambio/sincronizar', [App\Http\Controllers\TasaCambioController::class, 'sincronizar'])->name('tasas.sincronizar');
});

/*
|-----------------------------
| FÓRMULAS DE PRESTACIONES
|-----------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/formulas-prestaciones', [App\Http\Controllers\FormulaPrestacionController::class, 'index'])->name('formulas.index');
    Route::get('/formulas-prestaciones/{id}', [App\Http\Controllers\FormulaPrestacionController::class, 'show'])->name('formulas.show');
});

Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::post('/formulas-prestaciones', [App\Http\Controllers\FormulaPrestacionController::class, 'store'])->name('formulas.store');
    Route::put('/formulas-prestaciones/{id}', [App\Http\Controllers\FormulaPrestacionController::class, 'update'])->name('formulas.update');
    Route::delete('/formulas-prestaciones/{id}', [App\Http\Controllers\FormulaPrestacionController::class, 'destroy'])->name('formulas.destroy');
});

