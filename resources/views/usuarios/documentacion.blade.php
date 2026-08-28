{{-- documentacion.blade.php - Página de documentación del sistema con tabs: Información, Arquitectura, Módulos y Seguridad. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación — SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ filemtime(public_path('css/dashboard/dashboard.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root { --accent: {{ auth()->user()->color_acento ?? '#1a365d' }}; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; min-height: 100vh; }
        .doc-wrapper { max-width: 1100px; margin: 0 auto; padding: 24px; }
        .doc-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .doc-header h1 { font-size: 1.5rem; color: #0f172a; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
        .btn-back { width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s; text-decoration: none; flex-shrink: 0; }
        .btn-back:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

        .doc-tabs { display: flex; gap: 4px; margin-bottom: 20px; background: white; border-radius: 12px; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); flex-wrap: wrap; }
        .doc-tab { padding: 10px 20px; border-radius: 8px; border: none; background: transparent; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #64748b; transition: all 0.15s; }
        .doc-tab:hover { background: #f1f5f9; color: #0f172a; }
        .doc-tab.active { background: var(--accent); color: white; }
        .doc-tab-content { display: none; }
        .doc-tab-content.active { display: block; }

        .doc-section { background: white; border-radius: 16px; padding: 32px; margin-bottom: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .doc-section h2 { font-size: 1.2rem; color: #0f172a; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 10px; }
        .doc-section h3 { font-size: 1rem; color: #1e293b; font-weight: 600; margin: 20px 0 10px; }
        .doc-section p, .doc-section li { font-size: 0.9rem; color: #475569; line-height: 1.6; }
        .doc-section ul { padding-left: 20px; }
        .doc-section li { margin-bottom: 6px; }
        .doc-section .tag { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; margin: 2px; }
        .tag-blue { background: #dbeafe; color: #1d4ed8; }
        .tag-green { background: #dcfce7; color: #16a34a; }
        .tag-purple { background: #ede9fe; color: #7c3aed; }
        .tag-orange { background: #fed7aa; color: #c2410c; }
        .tag-red { background: #fecaca; color: #dc2626; }
        .tag-gray { background: #f1f5f9; color: #475569; }

        table.doc-table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 0.85rem; }
        table.doc-table th { text-align: left; padding: 10px 12px; background: #f8fafc; color: #475569; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #e2e8f0; }
        table.doc-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        table.doc-table tr:hover td { background: #f8fafc; }

        .doc-empty { background: white; border-radius: 16px; padding: 60px 40px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .doc-empty i { font-size: 3rem; color: #cbd5e1; margin-bottom: 16px; }
        .doc-empty h2 { font-size: 1.1rem; color: #64748b; margin: 0 0 8px; font-weight: 600; }
        .doc-empty p { font-size: 0.85rem; color: #94a3b8; margin: 0; }

        body.dark-mode { background: #0f172a; }
        body.dark-mode .doc-header h1 { color: #f1f5f9; }
        body.dark-mode .btn-back { background: #1e293b; border-color: #334155; color: #94a3b8; }
        body.dark-mode .doc-tabs { background: #1e293b; }
        body.dark-mode .doc-tab { color: #94a3b8; }
        body.dark-mode .doc-tab:hover { background: #334155; color: #e2e8f0; }
        body.dark-mode .doc-section { background: #1e293b; }
        body.dark-mode .doc-section h2 { color: #f1f5f9; }
        body.dark-mode .doc-section h3 { color: #e2e8f0; }
        body.dark-mode .doc-section p, body.dark-mode .doc-section li { color: #94a3b8; }
        body.dark-mode table.doc-table th { background: #334155; color: #94a3b8; }
        body.dark-mode table.doc-table td { color: #cbd5e1; border-color: #334155; }
        body.dark-mode .doc-empty { background: #1e293b; }
        body.dark-mode .doc-empty h2 { color: #94a3b8; }
        body.dark-mode .doc-empty i { color: #475569; }
        body.dark-mode .doc-section .tag { opacity: 0.9; }
    </style>
</head>
<body class="{{ auth()->user()->tema === 'dark' ? 'dark-mode' : '' }}">
<div class="doc-wrapper">
    <div class="doc-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('usuarios.user') }}" class="btn-back" title="Volver a Mi Perfil">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-book" style="color:var(--accent);"></i> Documentación del Sistema</h1>
        </div>
    </div>

    <div class="doc-tabs">
        <button class="doc-tab active" data-tab="info" onclick="cambiarTab('info')"><i class="fas fa-info-circle"></i> Información del Sistema</button>
        <button class="doc-tab" data-tab="arquitectura" onclick="cambiarTab('arquitectura')"><i class="fas fa-sitemap"></i> Arquitectura</button>
        <button class="doc-tab" data-tab="modulos" onclick="cambiarTab('modulos')"><i class="fas fa-puzzle-piece"></i> Módulos</button>
        <button class="doc-tab" data-tab="seguridad" onclick="cambiarTab('seguridad')"><i class="fas fa-shield"></i> Seguridad</button>
    </div>

    <!-- TAB: INFORMACIÓN DEL SISTEMA -->
    <div class="doc-tab-content active" id="tab-info">
        <div class="doc-section">
            <h2><i class="fas fa-circle-info" style="color:var(--accent);"></i> Información General</h2>
            <p><strong>SIGEJUB</strong> (Sistema Integral de Gestión de Jubilaciones) es una plataforma web desarrollada para la <strong>Universidad Politécnica Territorial de Yaracuy (UPTYAB)</strong> que automatiza y centraliza la gestión de expedientes, solicitudes de jubilación, cálculos de prestaciones sociales y nómina del personal docente, administrativo y obrero.</p>

            <h3>Tecnologías Utilizadas</h3>
            <p>
                <span class="tag tag-blue">PHP 8.4</span>
                <span class="tag tag-purple">Laravel 12</span>
                <span class="tag tag-green">MySQL / SQLite</span>
                <span class="tag tag-orange">JavaScript (ES6+)</span>
                <span class="tag tag-blue">HTML5 / CSS3</span>
                <span class="tag tag-purple">Tailwind CSS</span>
                <span class="tag tag-green">Font Awesome 6</span>
                <span class="tag tag-orange">Chart.js</span>
                <span class="tag tag-blue">Mermaid.js</span>
                <span class="tag tag-purple">PhpSpreadsheet</span>
                <span class="tag tag-orange">Cropper.js</span>
                <span class="tag tag-red">Apache (XAMPP)</span>
            </p>

            <h3>Lenguajes</h3>
            <ul>
                <li><strong>Backend:</strong> PHP 8.4 (Laravel 12 Framework)</li>
                <li><strong>Frontend:</strong> JavaScript (ES6+), HTML5, CSS3</li>
                <li><strong>Base de Datos:</strong> MySQL (MariaDB) o SQLite</li>
                <li><strong>Plantillas:</strong> Blade (motor de plantillas de Laravel)</li>
            </ul>

            <h3>Estructura del Sistema</h3>
            <p>El sistema sigue el patrón <strong>MVC (Modelo-Vista-Controlador)</strong> de Laravel:</p>
            <ul>
                <li><strong>app/Models/</strong> — Modelos Eloquent (Trabajador, User, Solicitud, Nomina, etc.)</li>
                <li><strong>app/Http/Controllers/</strong> — Controladores (Auth, User, Trabajador, Solicitud, Admin, etc.)</li>
                <li><strong>app/Services/</strong> — Servicios (NominaExportService, DashboardCache)</li>
                <li><strong>resources/views/</strong> — Vistas Blade (dashboard, auth, usuarios, partials)</li>
                <li><strong>public/js/</strong> — JavaScript del lado del cliente</li>
                <li><strong>public/css/</strong> — Estilos CSS</li>
                <li><strong>database/migrations/</strong> — Migraciones de base de datos</li>
                <li><strong>routes/web.php</strong> — Definición de rutas web</li>
            </ul>
        </div>
    </div>

    <!-- TAB: ARQUITECTURA -->
    <div class="doc-tab-content" id="tab-arquitectura">
        <div class="doc-section">
            <h2><i class="fas fa-sitemap" style="color:var(--accent);"></i> Arquitectura del Sistema</h2>
            <p>Diagrama de la arquitectura general del sistema:</p>
            <pre style="background:#f8fafc;border-radius:10px;padding:20px;font-size:0.8rem;line-height:1.5;overflow-x:auto;white-space:pre-wrap;">
┌─────────────────────────────────────────────────────┐
│                   NAVEGADOR WEB                       │
│  ┌──────────┐  ┌──────────┐  ┌───────────────────┐  │
│  │Dashboard │  │  Perfil   │  │  Documentación    │  │
│  │ (Blade)  │  │ (Blade)  │  │  (Blade + Info)   │  │
│  └─────┬────┘  └────┬─────┘  └────────┬──────────┘  │
│        │            │                 │              │
│  ┌─────┴────────────┴─────────────────┴──────────┐  │
│  │              JavaScript (Fetch API)            │  │
│  │   AJAX — JSON — CSRF Token — Session Cookie   │  │
│  └─────────────────────┬─────────────────────────┘  │
└────────────────────────┼────────────────────────────┘
                         │ HTTP (Apache :8081)
┌────────────────────────┼────────────────────────────┐
│                LARAVEL (PHP 8.4)                     │
│  ┌─────────────────────┴─────────────────────────┐  │
│  │              routes/web.php                     │  │
│  └──────┬──────────┬──────────┬──────────────────┘  │
│         │          │          │                      │
│  ┌──────┴──┐ ┌─────┴─────┐ ┌─┴──────────────┐      │
│  │Controllers│ │ Middleware │ │ Services       │      │
│  │ Auth,User │ │ auth,csrf │ │ NominaExport,  │      │
│  │ Trabajador│ │ inactividad│ │ DashboardCache │      │
│  │ Solicitud │ │ throttle  │ └────────────────┘      │
│  │ Admin,etc │ └───────────┘                         │
│  └──────┬──┘                                         │
│         │ Eloquent ORM                               │
│  ┌──────┴──────────────────────────────────────┐     │
│  │              Models (Eloquent)               │     │
│  │  User, Trabajador, Solicitud,               │     │
│  │  Nomina, Activity,                           │     │
│  │  UserNotification                            │     │
│  └──────┬──────────────────────────────────────┘     │
│         │                                            │
│  ┌──────┴──────────────────────────────────────┐     │
│  │          Base de Datos (MySQL/SQLite)        │     │
│  │  users, trabajadores, solicitudes,           │     │
│  │  expedientes, nominas, prestaciones_sociales │     │
│  │  activities, notifications,                  │     │
│  │  sessions                                    │     │
│  └─────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────┘
            </pre>

            <h3>Flujo de Datos</h3>
            <ol>
                <li>El navegador realiza peticiones HTTP (GET/POST/PUT/DELETE) al servidor Apache en puerto 8081</li>
                <li>Laravel enruta la petición al controlador correspondiente según <code>routes/web.php</code></li>
                <li>Los middlewares verifican autenticación, CSRF, inactividad y permisos</li>
                <li>El controlador interactúa con los Models vía Eloquent ORM para leer/escribir en la BD</li>
                <li>Las respuestas se devuelven como JSON (para AJAX) o vistas Blade (para navegación directa)</li>
                <li>El frontend JavaScript procesa las respuestas JSON y actualiza el DOM dinámicamente</li>
            </ol>
        </div>
    </div>

    <!-- TAB: MÓDULOS -->
    <div class="doc-tab-content" id="tab-modulos">
        <div class="doc-section">
            <h2><i class="fas fa-puzzle-piece" style="color:var(--accent);"></i> Módulos del Sistema</h2>

            <table class="doc-table">
                <thead><tr><th>Módulo</th><th>Descripción</th><th>Controlador</th><th>Ruta Principal</th></tr></thead>
                <tbody>
                    <tr><td><strong>Autenticación</strong></td><td>Login, registro, logout, recuperación de contraseña</td><td>AuthController</td><td>/login, /register</td></tr>
                    <tr><td><strong>Dashboard</strong></td><td>Panel principal con resumen de datos y acceso rápido a módulos</td><td>AuthController@dashboard</td><td>/dashboard</td></tr>
                    <tr><td><strong>Trabajadores</strong></td><td>CRUD completo de trabajadores, directorio, filtros, búsqueda</td><td>TrabajadorController</td><td>/trabajadores</td></tr>
                    <tr><td><strong>Solicitudes</strong></td><td>Gestión de solicitudes de jubilación con estatus y filtros</td><td>SolicitudController</td><td>/solicitudes</td></tr>
                    <tr><td><strong>Expedientes</strong></td><td>Expedientes digitales con documentos, notas y aprobaciones</td><td>ExpedienteController</td><td>/expedientes</td></tr>
                    <tr><td><strong>Prestaciones</strong></td><td>Cálculo y visualización de prestaciones sociales según LOTTT</td><td>PrestacionesController</td><td>/prestaciones</td></tr>
                    <tr><td><strong>Nómina</strong></td><td>Exportación de planilla de nómina en Excel con PhpSpreadsheet</td><td>NominaExportController</td><td>/exportar/nomina</td></tr>
                    <tr><td><strong>Reportes</strong></td><td>Estadísticas generales del sistema con métricas en tiempo real</td><td>(AJAX a varios)</td><td>/reportes</td></tr>
                    <tr><td><strong>Perfil</strong></td><td>Gestión de cuenta, avatar, seguridad, sesiones, actividad</td><td>UserController</td><td>/perfil</td></tr>
                    <tr><td><strong>Admin</strong></td><td>Panel de administración: usuarios, permisos, actividad global</td><td>AdminController, UserController</td><td>/usuarios</td></tr>
                    <tr><td><strong>Historial</strong></td><td>Caja Negra: auditoría completa de cambios del sistema</td><td>CajaNegraController</td><td>/caja-negra</td></tr>
                    <tr><td><strong>Documentación</strong></td><td>Documentación completa del sistema</td><td>ChangelogController</td><td>/documentacion</td></tr>
                </tbody>
            </table>

            <h3>Base de Datos</h3>
            <p>Tablas principales del sistema:</p>
            <table class="doc-table">
                <thead><tr><th>Tabla</th><th>Propósito</th><th>Columnas Clave</th></tr></thead>
                <tbody>
                    <tr><td><code>users</code></td><td>Usuarios del sistema</td><td>nombre, correo, password, rol, avatar, tema</td></tr>
                    <tr><td><code>trabajadores</code></td><td>Registro de empleados UPTYAB</td><td>cedula, nombres, apellidos, cargo, sueldo_base, fecha_ingreso</td></tr>
                    <tr><td><code>solicitudes</code></td><td>Solicitudes de jubilación</td><td>trabajador_id, tipo, estatus, fecha_solicitud</td></tr>
                    <tr><td><code>expedientes</code></td><td>Expedientes digitales</td><td>trabajador_id, notas, estatus</td></tr>
                    <tr><td><code>nominas</code></td><td>Cálculos mensuales de nómina</td><td>trabajador_id, sueldo_base, total_asignacion, total_deduccion, neto_a_cobrar</td></tr>
                    <tr><td><code>prestaciones_sociales</code></td><td>Cálculo de prestaciones LOTTT</td><td>trabajador_id, antiguedad_dias, salario_integral, total_prestaciones</td></tr>
                    <tr><td><code>activities</code></td><td>Bitácora de actividad</td><td>user_id, accion, tipo_entidad, entidad_id, descripcion</td></tr>
                    <tr><td><code>sessions</code></td><td>Sesiones de usuario</td><td>user_id, last_activity, user_agent, ip_address</td></tr>
                </tbody>
            </table>

            <h3>API JSON Endpoints</h3>
            <p>El sistema expone múltiples endpoints AJAX para consumo desde el frontend:</p>
            <table class="doc-table">
                <thead><tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td><code>/trabajadores</code></td><td>GET</td><td>Lista paginada de trabajadores</td></tr>
                    <tr><td><code>/trabajadores/{id}</code></td><td>GET/PUT/DELETE</td><td>CRUD individual de trabajador</td></tr>
                    <tr><td><code>/trabajadores-stats/dashboard</code></td><td>GET</td><td>Estadísticas de trabajadores</td></tr>
                    <tr><td><code>/solicitudes</code></td><td>GET/POST</td><td>Lista y creación de solicitudes</td></tr>
                    <tr><td><code>/solicitudes/por-mes</code></td><td>GET</td><td>Solicitudes agrupadas por mes</td></tr>
                    <tr><td><code>/solicitudes/vencimientos</code></td><td>GET</td><td>Próximos vencimientos</td></tr>
                    <tr><td><code>/expedientes</code></td><td>GET/POST</td><td>Lista y creación de expedientes</td></tr>
                    <tr><td><code>/expedientes/buscar-trabajador</code></td><td>GET</td><td>Búsqueda de trabajador para expediente</td></tr>
                    <tr><td><code>/exportar/nomina</code></td><td>GET</td><td>Descarga planilla Excel de nómina</td></tr>
                    <tr><td><code>/caja-negra</code></td><td>GET</td><td>Historial de auditoría</td></tr>
                    <tr><td><code>/usuarios</code></td><td>GET</td><td>Lista de usuarios (admin)</td></tr>
                    <tr><td><code>/actividades</code></td><td>GET</td><td>Actividad reciente del sistema</td></tr>
                    <tr><td><code>/notificaciones</code></td><td>GET</td><td>Notificaciones del usuario</td></tr>
                    <tr><td><code>/actividad/ping</code></td><td>POST</td><td>Keep-alive de sesión</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: SEGURIDAD -->
    <div class="doc-tab-content" id="tab-seguridad">
        <div class="doc-section">
            <h2><i class="fas fa-shield" style="color:var(--accent);"></i> Seguridad del Sistema</h2>

            <h3>Autenticación</h3>
            <ul>
                <li>Login con credenciales <strong>correo + contraseña</strong> utilizando el sistema de autenticación nativo de Laravel</li>
                <li>Contraseñas hasheadas con <strong>bcrypt</strong> (Hash::make de Laravel)</li>
                <li>Protección contra fuerza bruta: middleware <strong>throttle:5,1</strong> (5 intentos por minuto en login)</li>
                <li>Sesiones manejadas vía <strong>cookie + base de datos</strong> con expiración configurable</li>
            </ul>

            <h3>Autorización y Roles</h3>
            <ul>
                <li>Dos roles: <strong>admin</strong> (acceso completo, panel de administración) y <strong>analista</strong> (gestión de trabajadores/solicitudes/expedientes)</li>
                <li>Verificación de rol en cada controlador: <code>abort_unless(auth()->user()?->rol === 'admin', 403)</code></li>
                <li>Las vistas de administración solo se renderizan si el usuario es admin</li>
            </ul>

            <h3>CSRF (Cross-Site Request Forgery)</h3>
            <ul>
                <li>Todos los formularios incluyen <code>@csrf</code> de Blade</li>
                <li>Todas las peticiones AJAX/POST incluyen header <strong>X-CSRF-TOKEN</strong> extraído del meta tag</li>
                <li>Las rutas POST/PUT/DELETE están protegidas por el middleware <strong>VerifyCsrfToken</strong> de Laravel</li>
            </ul>

            <h3>Protección de Sesiones</h3>
            <ul>
                <li><strong>Auto-logout por inactividad</strong>: temporizador JavaScript de 15 minutos con advertencia a los 14 min</li>
                <li>Middleware <strong>VerificarInactividad</strong> en el servidor como defensa en profundidad (30 min)</li>
                <li>Ping de actividad periódico a <code>/actividad/ping</code> para rastrear interacción real</li>
                <li>Visualización y cierre de sesiones activas desde el perfil de usuario</li>
                <li>Opción "Cerrar otras sesiones" para invalidar sesiones en otros dispositivos</li>
            </ul>

            <h3>Validación de Datos</h3>
            <ul>
                <li>Validación de formularios del lado del servidor con el sistema <strong>Validator/Request</strong> de Laravel</li>
                <li>Validación adicional del lado del cliente en JavaScript (formato de cédula, campos requeridos)</li>
                <li>Escapado de HTML en todas las salidas: <code>escaparHTML()</code> en JS, <code>@{{ }}</code> automático en Blade</li>
                <li>Protección contra XSS mediante el escapado automático de Blade</li>
            </ul>

            <h3>Seguridad Adicional</h3>
            <ul>
                <li><strong>APP_KEY</strong> única en .env para encriptación de cookies y sesiones</li>
                <li>Archivos de avatar almacenados en <code>storage/app/public/</code> y servidos vía symlink</li>
                <li>Sistema de logging de actividad (Caja Negra) para auditoría de cambios</li>
                <li><strong>Soft delete</strong> en trabajadores (columna <code>deleted_at</code>) para prevenir pérdida de datos</li>
                <li><strong>SQLite</strong> como base de datos portátil opcional sin configuración de servidor</li>
            </ul>
        </div>
    </div>

</div>

<script>
window.cambiarTab = function(tabId) {
    document.querySelectorAll('.doc-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.doc-tab-content').forEach(function(c) { c.classList.remove('active'); });
    document.querySelector('.doc-tab[data-tab="' + tabId + '"]')?.classList.add('active');
    document.getElementById('tab-' + tabId)?.classList.add('active');
};

window.addEventListener('DOMContentLoaded', function() {
    if ('{{ auth()->user()->tema }}' === 'dark') document.body.classList.add('dark-mode');
});
</script>
</body>
</html>
