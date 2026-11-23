{{-- documentacion.blade.php - Documentación del sistema: Información, Arquitectura, Módulos, Novedades, Seguridad y Cambios (changelog). Actualizada al 22/11/2026. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación — SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ substr(md5_file(public_path('css/dashboard/dashboard.min.css')), 0, 10) }}">
    @if(file_exists(public_path('css/dashboard/table-layout.css')))
    <link rel="stylesheet" href="{{ asset('css/dashboard/table-layout.css') }}?v={{ substr(md5_file(public_path('css/dashboard/table-layout.css')), 0, 10) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --accent: {{ auth()->user()->color_acento ?? '#1a365d' }};
            --accent-dark: color-mix(in srgb, var(--accent) 72%, #000 28%);
        }
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
        .doc-content-scroll { max-height: calc(100vh - 170px); overflow-y: auto; padding-right: 6px; }
        .doc-content-scroll::-webkit-scrollbar { width: 8px; }
        .doc-content-scroll::-webkit-scrollbar-track { background: transparent; }
        .doc-content-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        body.dark-mode .doc-content-scroll::-webkit-scrollbar-thumb { background: #475569; }

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
<link rel="stylesheet" href="{{ asset('css/scrollbars.css') }}?v={{ substr(md5_file(public_path('css/scrollbars.css')), 0, 10) }}">
</head>
<body class="{{ auth()->user()->tema === 'dark' ? 'dark-mode' : '' }}">
<div class="doc-wrapper">
    <div class="doc-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('usuarios.user') }}" class="btn-back" title="Volver a Mi Perfil">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-book" style="color:var(--accent);"></i> Documentación del Sistema</h1>
            <span class="tag tag-gray" style="white-space:nowrap;">Actualizada: 22/11/2026 · v1.2</span>
        </div>
    </div>

    <div class="doc-tabs">
        <button class="doc-tab active" data-tab="info" onclick="cambiarTab('info')"><i class="fas fa-info-circle"></i> Información del Sistema</button>
        <button class="doc-tab" data-tab="arquitectura" onclick="cambiarTab('arquitectura')"><i class="fas fa-sitemap"></i> Arquitectura</button>
        <button class="doc-tab" data-tab="modulos" onclick="cambiarTab('modulos')"><i class="fas fa-puzzle-piece"></i> Módulos</button>
        <button class="doc-tab" data-tab="novedades" onclick="cambiarTab('novedades')"><i class="fas fa-star"></i> Novedades</button>
        <button class="doc-tab" data-tab="seguridad" onclick="cambiarTab('seguridad')"><i class="fas fa-shield"></i> Seguridad</button>
        <button class="doc-tab" data-tab="cambios" onclick="cambiarTab('cambios')"><i class="fas fa-clock-rotate-left"></i> Cambios</button>
    </div>

    <!-- TAB: INFORMACIÓN DEL SISTEMA -->
    <div class="doc-tab-content active doc-content-scroll" id="tab-info">
        <div class="doc-section">
            <h2><i class="fas fa-circle-info" style="color:var(--accent);"></i> Información General</h2>
            <p><strong>SIGEJUB</strong> (Sistema Integral de Gestión de Jubilaciones) es una plataforma web desarrollada para la <strong>Universidad Politécnica Territorial de Yaracuy (UPTYAB)</strong> que automatiza y centraliza la gestión de trabajadores, solicitudes de jubilación, expedientes digitales, cálculo de prestaciones sociales, cláusulas de la convención colectiva, primas, tasa de cambio y nómina mensual del personal docente, administrativo y obrero. Incluye auditoría (caja negra), copias de seguridad, notificaciones, perfil de usuario con 2FA y documentación del sistema.</p>

            <h3>Tecnologías Utilizadas</h3>
            <p>
                <span class="tag tag-blue">PHP 8.2+</span>
                <span class="tag tag-purple">Laravel 12</span>
                <span class="tag tag-green">MySQL / MariaDB 10.4</span>
                <span class="tag tag-green">PostgreSQL 18</span>
                <span class="tag tag-orange">JavaScript (ES6+)</span>
                <span class="tag tag-blue">HTML5 / CSS3</span>
                <span class="tag tag-green">Blade (Motor de plantillas)</span>
                <span class="tag tag-orange">Font Awesome 6</span>
                <span class="tag tag-blue">Chart.js</span>
                <span class="tag tag-purple">DomPDF</span>
                <span class="tag tag-orange">PhpSpreadsheet</span>
                <span class="tag tag-blue">Cropper.js</span>
                <span class="tag tag-red">Apache (XAMPP)</span>
                <span class="tag tag-gray">Git (changelog)</span>
            </p>

            <h3>Lenguajes y librerías</h3>
            <ul>
                <li><strong>Backend:</strong> PHP 8.2+ (Laravel 12 Framework) con Eloquent ORM</li>
                <li><strong>Frontend:</strong> JavaScript vanilla (ES6+), HTML5, CSS3 — sin frameworks de frontend</li>
                <li><strong>Base de Datos:</strong> MySQL/MariaDB (producción) y PostgreSQL 18 (soporte configurable desde <code>config/database.php</code>; 40 tablas y 48 migraciones en ambos)</li>
                <li><strong>Plantillas:</strong> Blade (motor de plantillas de Laravel)</li>
                <li><strong>Gráficos:</strong> Chart.js (<code>public/js/chartjs/chart.umd.min.js</code>) — solicitudes por estatus, por mes (vista general y por tipo de jubilación) y trabajadores por tipo de nómina</li>
                <li><strong>PDF:</strong> DomPDF (comprobantes de prestaciones, solicitudes, expedientes y caja negra)</li>
                <li><strong>Excel:</strong> PhpSpreadsheet (<code>phpoffice/phpspreadsheet</code>) para importación/exportación de nómina</li>
                <li><strong>Recorte de imágenes:</strong> Cropper.js (avatar de perfil y fotos)</li>
                <li><strong>Respaldo de archivos en BD:</strong> tablas <code>archivos_bd</code> y <code>archivos_bd_bloques</code> (base64 por bloques) para reconstruir <code>storage/</code> con <code>php artisan archivos:restaurar</code></li>
            </ul>

            <h3>Estructura del Sistema</h3>
            <p>El sistema sigue el patrón <strong>MVC (Modelo-Vista-Controlador)</strong> de Laravel:</p>
            <ul>
                <li><strong>app/Models/ (28):</strong> User, Trabajador, Solicitud, Expediente, Documento, Nomina, NominaTrabajador, Prestacion, Prima, ReciboPago, AntecedenteServicio, AntecedenteDocumento, ClausulaConvencion, TasaCambio, FormulaPrestacion, Cargo, Area, Grado, NivelInstruccion, TipoContrato, TipoJubilacion, Unidad, Sueldo, Activity, Changelog, ArchivoBd, ArchivoBdBloque, UserNotification</li>
                <li><strong>app/Http/Controllers/ (20):</strong> Auth, User, Admin, Trabajador, AntecedenteServicio, Solicitud, Expediente, Prestaciones, Nomina, NominaExport, ClausulaConvencion, MasterData, TasaCambio, FormulaPrestacion, Reporte, Estadisticas, CajaNegra, Backup, Changelog, Controller</li>
                <li><strong>app/Services/ (11):</strong> NotificationService, ValidationService, WorkflowService, AuditService, BackupService, ArchivoDbService, ImageStorageService, DashboardCache, NominaImportService, NominaExportService, TasaCambioService</li>
                <li><strong>app/Http/Middleware/:</strong> RoleMiddleware (roles) y VerificarInactividad (auto-logout)</li>
                <li><strong>app/Console/Commands/ (5):</strong> <code>changelog:generate</code>, <code>sigejub:limpiar-datos</code>, <code>archivos:respaldar</code>, <code>archivos:restaurar</code>, <code>tasa:cambio:sincronizar</code></li>
                <li><strong>database/migrations/:</strong> 48 migraciones (incluye catálogos, antecedentes, archivos en BD, snapshot de solicitudes, nómina mensual, expediente sin solicitud, cláusulas e Incapacidad)</li>
                <li><strong>routes/web.php:</strong> rutas web + API AJAX (autenticación, perfil, administración y módulos)</li>
                <li><strong>resources/views/:</strong> Vistas Blade (dashboard/secciones —13—, auth, usuarios, partials, pdf, reportes)</li>
                <li><strong>public/js/:</strong> dashboard.js, sesion2.js, profile.js, theme-welcome.js, dashboard/modales.js, secciones/trabajador.js y librerías (Chart.js, Cropper.js)</li>
                <li><strong>public/css/dashboard/:</strong> estilos del panel (dashboard.min.css y secciones.css)</li>
            </ul>
        </div>
    </div>

    <!-- TAB: ARQUITECTURA -->
    <div class="doc-tab-content doc-content-scroll" id="tab-arquitectura">
        <div class="doc-section">
            <h2><i class="fas fa-sitemap" style="color:var(--accent);"></i> Arquitectura del Sistema</h2>
            <p>Diagrama de la arquitectura general del sistema:</p>
            <pre style="background:#f8fafc;border-radius:10px;padding:20px;font-size:0.8rem;line-height:1.5;overflow-x:auto;white-space:pre-wrap;">
┌─────────────────────────────────────────────────────┐
│                   NAVEGADOR WEB                       │
│  ┌──────────┐  ┌──────────┐  ┌───────────────────┐  │
│  │Dashboard │  │  Perfil   │  │  Documentación    │  │
│  │ (Blade)  │  │ (Blade)  │  │  (Blade + Changelog)│  │
│  └─────┬────┘  └────┬─────┘  └────────┬──────────┘  │
│        │            │                 │              │
│  ┌─────┴────────────┴─────────────────┴──────────┐  │
│  │            JavaScript (Fetch API)             │  │
│  │   AJAX — JSON — CSRF Token — Session Cookie   │  │
│  └─────────────────────┬─────────────────────────┘  │
└────────────────────────┼────────────────────────────┘
                         │ HTTP (Apache :8001 / :80)
┌────────────────────────┼────────────────────────────┐
│               LARAVEL 12 (PHP 8.2+)                 │
│  ┌─────────────────────┴─────────────────────────┐  │
│  │              routes/web.php                     │  │
│  └──────┬──────────┬──────────┬──────────────────┘  │
│         │          │          │                      │
│  ┌──────┴──┐ ┌─────┴─────┐ ┌─┴──────────────┐      │
│  │Controllers│ │ Middleware │ │   Services     │      │
│  │ Auth,User │ │ auth,csrf │ │ Notification,  │      │
│  │ Trabajador│ │ role,     │ │ Validation     │      │
│  │ Solicitud │ │ inactivity│ └────────────────┘      │
│  │ Expedient │ └───────────┘                        │
│  │ Nomina,etc│                                     │
│  └──────┬──┘                                         │
│         │ Eloquent ORM                               │
│  ┌──────┴──────────────────────────────────────┐     │
│  │            Models (Eloquent)                 │     │
│  │  User, Trabajador, Solicitud,               │     │
│  │  Expediente, Nomina, Prestacion,            │     │
│  │  TasaCambio, FormulaPrestacion, Prima,      │     │
│  │  Activity, Changelog, ...                   │     │
│  └──────┬──────────────────────────────────────┘     │
│         │                                            │
│  ┌──────┴──────────────────────────────────────┐     │
│  │       Base de Datos (MySQL / MariaDB)        │     │
│  │  users, trabajadores, solicitudes,           │     │
│  │  expedientes, documentos, nominas,           │     │
│  │  nomina_trabajador, prestaciones, primas,    │     │
│  │  tasas_cambio, formulas_prestaciones,        │     │
│  │  cargos, areas, grados, niveles_instruccion, │     │
│  │  sueldos, tipos_contrato, tipos_jubilacion,  │     │
│  │  activities, notifications, changelogs,      │     │
│  │  sessions                                    │     │
│  └─────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────┘
            </pre>

            <p><strong>Componentes actuales:</strong> 20 controladores, 28 modelos Eloquent, 11 servicios (<code>NotificationService</code>, <code>ValidationService</code>, <code>WorkflowService</code>, <code>AuditService</code>, <code>BackupService</code>, <code>ArchivoDbService</code>, <code>ImageStorageService</code>, <code>DashboardCache</code>, <code>NominaImportService</code>, <code>NominaExportService</code>, <code>TasaCambioService</code>), 5 comandos Artisan propios, 2 middlewares y 48 migraciones. La capa de datos funciona con <strong>MySQL/MariaDB</strong> en producción y admite <strong>PostgreSQL 18</strong>; ambas con 40 tablas y el mismo esquema.</p>

            <h3>Flujo de Datos</h3>
            <ol>
                <li>El navegador realiza peticiones HTTP (GET/POST/PUT/DELETE) al servidor Apache (puerto 8001 en desarrollo)</li>
                <li>Laravel enruta la petición al controlador correspondiente según <code>routes/web.php</code></li>
                <li>Los middlewares verifican autenticación, CSRF, rol y tiempo de inactividad</li>
                <li>El controlador interactúa con los Models vía Eloquent ORM para leer/escribir en la BD</li>
                <li>Las respuestas se devuelven como JSON (para AJAX) o vistas Blade (para navegación directa)</li>
                <li>El frontend JavaScript procesa las respuestas JSON y actualiza el DOM dinámicamente</li>
            </ol>
        </div>
    </div>

    <!-- TAB: MÓDULOS -->
    <div class="doc-tab-content doc-content-scroll" id="tab-modulos">
        <div class="doc-section">
            <h2><i class="fas fa-puzzle-piece" style="color:var(--accent);"></i> Módulos del Sistema</h2>

            <table class="doc-table">
                <thead><tr><th>Módulo</th><th>Descripción</th><th>Controlador</th><th>Ruta Principal</th></tr></thead>
                <tbody>
                    <tr><td><strong>Inicio</strong></td><td>Panel principal con estadísticas, accesos rápidos, gráficos y actividad reciente</td><td>AuthController@dashboard, EstadisticasController</td><td>/dashboard</td></tr>
                    <tr><td><strong>Autenticación</strong></td><td>Login, registro, recuperación de contraseña, logout y protección por throttle</td><td>AuthController</td><td>/login, /register, /password/*</td></tr>
                    <tr><td><strong>Trabajadores</strong></td><td>CRUD de trabajadores, autocompletado, estadísticas, foto, Category, cláusulas previas y antecedentes de servicio (dentro/fuera del ente) con documentos</td><td>TrabajadorController, AntecedenteServicioController</td><td>/trabajadores, /antecedentes/*</td></tr>
                    <tr><td><strong>Solicitudes</strong></td><td>Gestiones de jubilación con estatus, agrupación por mes, vencimientos, estadísticas, snapshot del trabajador y exportación PDF</td><td>SolicitudController</td><td>/solicitudes</td></tr>
                    <tr><td><strong>Expedientes</strong></td><td>Expedientes digitales (con o sin solicitud) con carpetas alfabéticas, documentos, notas, carta de aprobación, foto de carnet, recibos de nómina y cláusulas asignadas</td><td>ExpedienteController</td><td>/expedientes</td></tr>
                    <tr><td><strong>Nómina</strong></td><td>Planilla organizada por <strong>año y mes</strong>, importación/exportación Excel, alta/baja de trabajadores y conteo mensual</td><td>NominaController, NominaExportController</td><td>/nomina</td></tr>
                    <tr><td><strong>Prestaciones</strong></td><td>Cálculo de prestaciones sociales (LOTTT + cláusulas de convención), fideicomiso manual, guardado y comprobante PDF</td><td>PrestacionesController</td><td>/prestaciones</td></tr>
                    <tr><td><strong>Primas</strong></td><td>Administración de las primas aplicables al cálculo salarial</td><td>MasterDataController</td><td>/master/prima</td></tr>
                    <tr><td><strong>Tasa de Cambio</strong></td><td>Consulta de tasa VES/USD (automática/manual), historial, estado y sincronización con proveedor (BCV/Monitor)</td><td>TasaCambioController, TasaCambioService</td><td>/tasas-cambio</td></tr>
                    <tr><td><strong>Catálogos del sistema</strong></td><td>Cargos, grados, unidades/departamentos, niveles de instrucción, <strong>Categoría</strong> (antes tipo de contrato), dedicaciones, cláusulas de convención y sueldos</td><td>MasterDataController, ClausulaConvencionController</td><td>/master/{tipo}, /clausulas-convencion</td></tr>
                    <tr><td><strong>Reportes</strong></td><td>Gráficos de trabajadores por tipo de nómina, solicitudes por estatus y por mes (vista <em>General</em> / <em>Por tipo de jubilación</em>), resumen general y próximos jubilados</td><td>EstadisticasController, ReporteController</td><td>/reportes, /estadisticas/dashboard</td></tr>
                    <tr><td><strong>Fórmulas</strong></td><td>Definición y administración de fórmulas parametrizadas para el cálculo de prestaciones</td><td>FormulaPrestacionController</td><td>/formulas-prestaciones</td></tr>
                    <tr><td><strong>Historial (Caja Negra)</strong></td><td>Auditoría de cambios con exportación PDF/CSV y copias de seguridad (generar, verificar, descargar, eliminar y restaurar)</td><td>CajaNegraController, BackupController</td><td>/caja-negra, /backups</td></tr>
                    <tr><td><strong>Notificaciones</strong></td><td>Envío de notificaciones (admin), bandeja del usuario, marcar como leídas y contador de no leídas</td><td>AdminController</td><td>/notificaciones</td></tr>
                    <tr><td><strong>Perfil</strong></td><td>Cuenta, avatar con recorte, contraseña, tema/acento, preferencias de notificación, 2FA, sesiones activas, actividad y estadísticas</td><td>UserController</td><td>/perfil</td></tr>
                    <tr><td><strong>Usuarios (Admin)</strong></td><td>Administración de usuarios, actividad global y configuración global</td><td>AdminController</td><td>/usuarios, /perfil/admin/*</td></tr>
                    <tr><td><strong>Ayuda</strong></td><td>Guías de uso y ayuda del sistema</td><td>(vista estática)</td><td>/dashboard → Ayuda</td></tr>
                    <tr><td><strong>Documentación</strong></td><td>Documentación del sistema y changelog generado desde <code>git log</code></td><td>ChangelogController</td><td>/documentacion</td></tr>
                </tbody>
            </table>

            <h3>Base de Datos</h3>
            <p><strong>40 tablas y 48 migraciones</strong> (MySQL/MariaDB en producción y PostgreSQL 18 como alternativa configurable):</p>
            <table class="doc-table">
                <thead><tr><th>Tabla</th><th>Propósito</th></tr></thead>
                <tbody>
                    <tr><td><code>users</code>, <code>password_reset_tokens</code>, <code>sessions</code></td><td>Usuarios (usuario, admin, superadmin), recuperación de contraseña y sesiones</td></tr>
                    <tr><td><code>trabajadores</code></td><td>Empleados UPTYAB: datos personales, salario, años de servicio, categoría, dedicación, cláusulas previas (JSON) y soft delete</td></tr>
                    <tr><td><code>solicitudes</code></td><td>Solicitudes de jubilación con estatus, snapshot del trabajador y soft delete</td></tr>
                    <tr><td><code>expedientes</code>, <code>documentos</code></td><td>Expedientes digitales (solicitud opcional), carta de aprobación, foto de carnet y documentos adjuntos con estatus</td></tr>
                    <tr><td><code>nominas</code>, <code>nomina_trabajador</code></td><td>Planillas por <strong>año y mes</strong> y detalle por trabajador (pivot)</td></tr>
                    <tr><td><code>prestaciones</code>, <code>primas</code></td><td>Cálculo de prestaciones (monto, sueldo integral, primas, tasa, fideicomiso, detalle JSON) y catálogo de primas</td></tr>
                    <tr><td><code>recibos_pago</code></td><td>Comprobantes de nómina adjuntos al expediente (período, monto USD, archivo)</td></tr>
                    <tr><td><code>antecedentes_servicio</code>, <code>antecedente_documentos</code></td><td>Períodos laborales dentro/fuera del ente y documentos de respaldo</td></tr>
                    <tr><td><code>clausulas_convencion</code>, <code>expediente_clausula</code></td><td>Cláusulas de la convención colectiva y asignación por expediente</td></tr>
                    <tr><td><code>tasas_cambio</code>, <code>formulas_prestaciones</code></td><td>Tasa VES/USD (automática/manual) y fórmulas parametrizadas (JSON)</td></tr>
                    <tr><td><code>cargos</code>, <code>areas</code>, <code>grados</code>, <code>niveles_instruccion</code></td><td>Catálogos maestros de cargos, áreas, grados y niveles académicos</td></tr>
                    <tr><td><code>tipos_contrato</code>, <code>tipos_jubilacion</code>, <code>dedicaciones</code>, <code>unidades</code></td><td><strong>Categoría</strong>, tipo de jubilación (Antigüedad, Especial, <strong>Incapacidad</strong>), dedicación docente y unidades/departamentos</td></tr>
                    <tr><td><code>estudios</code>, <code>actividades_universitarias</code>, <code>sueldos</code></td><td>Estudios, actividad universitaria y sueldos por grado/nivel</td></tr>
                    <tr><td><code>activities</code>, <code>changelogs</code>, <code>notifications</code></td><td>Caja negra, historial de commits y notificaciones</td></tr>
                    <tr><td><code>archivos_bd</code>, <code>archivos_bd_bloques</code></td><td>Copia de archivos del sistema en base de datos (base64 por bloques) para restaurar <code>storage/</code></td></tr>
                    <tr><td><code>migrations</code>, <code>cache</code>, <code>cache_locks</code>, <code>jobs</code>, <code>job_batches</code>, <code>failed_jobs</code></td><td>Control de migraciones y tablas internas de Laravel</td></tr>
                </tbody>
            </table>

            <h3>API JSON Endpoints</h3>
            <p>El sistema expone múltiples endpoints AJAX para consumo desde el frontend (todos requieren autenticación):</p>
            <table class="doc-table">
                <thead><tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td><code>/trabajadores</code>, <code>/trabajadores/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>Lista paginada, creación y CRUD de trabajadores</td></tr>
                    <tr><td><code>/trabajadores/autocomplete</code>, <code>/trabajadores-stats/dashboard</code></td><td>GET</td><td>Autocompletado y estadísticas</td></tr>
                    <tr><td><code>/trabajadores/{id}/antecedentes</code>, <code>/antecedentes/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de antecedentes de servicio</td></tr>
                    <tr><td><code>/antecedentes/{id}/documentos</code>, <code>/antecedente-documentos/{id}</code></td><td>POST/DELETE</td><td>Subir/eliminar documentos de antecedentes</td></tr>
                    <tr><td><code>/solicitudes</code>, <code>/solicitudes/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de solicitudes</td></tr>
                    <tr><td><code>/solicitudes/por-mes</code>, <code>/solicitudes/vencimientos</code>, <code>/solicitudes/estadisticas</code></td><td>GET</td><td>Agrupación, vencimientos y estadísticas</td></tr>
                    <tr><td><code>/solicitudes/exportar</code></td><td>GET</td><td>Exporta solicitudes a PDF</td></tr>
                    <tr><td><code>/expedientes</code>, <code>/expedientes/{id}</code></td><td>GET/POST/PUT</td><td>Lista, creación (sin solicitud) y detalle; notas</td></tr>
                    <tr><td><code>/expedientes/buscar-trabajador</code>, <code>/expedientes/listos-aprobacion</code></td><td>GET</td><td>Búsqueda y expedientes listos para aprobación</td></tr>
                    <tr><td><code>/expedientes/{id}/documentos</code>, <code>/expedientes/{id}/carta-aprobacion</code>, <code>/expedientes/{id}/foto-carnet</code></td><td>POST</td><td>Documentos, carta y foto de carnet</td></tr>
                    <tr><td><code>/documentos/{id}/estado</code>, <code>/documentos/{id}/reemplazar</code></td><td>PUT/POST</td><td>Cambio de estatus y reemplazo de documentos</td></tr>
                    <tr><td><code>/expedientes/{id}/recibos</code>, <code>/recibos-pago/{id}</code></td><td>GET/POST/DELETE</td><td>Recibos de nómina del expediente</td></tr>
                    <tr><td><code>/expedientes/{id}/clausulas</code></td><td>GET/PUT</td><td>Cláusulas asignadas al expediente</td></tr>
                    <tr><td><code>/expedientes/{id}/exportar</code></td><td>GET</td><td>Exporta expediente a PDF</td></tr>
                    <tr><td><code>/nomina</code>, <code>/nomina/anios</code></td><td>GET</td><td>Planilla por año/mes y años disponibles</td></tr>
                    <tr><td><code>/nomina/trabajador/{id}/actualizar</code>, <code>.../eliminar</code></td><td>POST</td><td>Alta/baja de trabajadores en la planilla</td></tr>
                    <tr><td><code>/exportar/nomina</code>, <code>/importar/nomina</code></td><td>GET/POST</td><td>Descarga e importación de planilla Excel</td></tr>
                    <tr><td><code>/prestaciones</code>, <code>/prestaciones/{id}</code></td><td>GET/POST</td><td>Lista, guardado y detalle para cálculo</td></tr>
                    <tr><td><code>/prestaciones/{id}/comprobante</code>, <code>.../fideicomiso-documento</code></td><td>POST</td><td>Comprobante PDF y documento de fideicomiso</td></tr>
                    <tr><td><code>/clausulas-convencion</code>, <code>/clausulas-convencion/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de cláusulas de convención (admin)</td></tr>
                    <tr><td><code>/formulas-prestaciones</code>, <code>/formulas-prestaciones/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de fórmulas</td></tr>
                    <tr><td><code>/tasas-cambio</code>, <code>/tasas-cambio/actual</code>, <code>/tasas-cambio/estado</code>, <code>/tasas-cambio/historial</code></td><td>GET</td><td>Consulta, tasa vigente, estado e historial</td></tr>
                    <tr><td><code>/tasas-cambio</code>, <code>/tasas-cambio/sincronizar</code></td><td>POST</td><td>Crear tasa manual y sincronizar con proveedor</td></tr>
                    <tr><td><code>/master/{tipo}</code>, <code>/master/{tipo}/{id}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de catálogos (cargos, grados, unidades, categoría, dedicación, etc.)</td></tr>
                    <tr><td><code>/estadisticas/dashboard</code>, <code>/reportes/proximos-jubilados</code>, <code>/reportes/relacion-cargos/{id}</code></td><td>GET</td><td>Estadísticas, próximos jubilados y relación de cargos</td></tr>
                    <tr><td><code>/caja-negra</code>, <code>/caja-negra/exportar</code>, <code>/caja-negra/exportar-csv</code></td><td>GET</td><td>Historial y exportaciones PDF/CSV</td></tr>
                    <tr><td><code>/caja-negra-data/estadisticas</code>, <code>/caja-negra-data/usuarios</code></td><td>GET</td><td>Estadísticas y usuarios de auditoría</td></tr>
                    <tr><td><code>/backups</code>, <code>/backups/generar</code></td><td>GET/POST</td><td>Lista y generación de copias de seguridad</td></tr>
                    <tr><td><code>/backups/{archivo}/verificar</code>, <code>/descargar</code>, <code>/restaurar</code></td><td>GET/POST/DELETE</td><td>Verificar, descargar, eliminar y restaurar (solo superadmin)</td></tr>
                    <tr><td><code>/notificaciones</code>, <code>/notificaciones/no-leidas</code>, <code>/notificaciones/{id}/leer</code></td><td>GET/PUT</td><td>Bandeja, contador y lectura de notificaciones</td></tr>
                    <tr><td><code>/perfil</code>, <code>/perfil/sesiones</code>, <code>/perfil/2fa</code>, <code>/perfil/actividad</code></td><td>GET/PUT/POST/DELETE</td><td>Perfil, sesiones, 2FA, actividad y estadísticas</td></tr>
                    <tr><td><code>/usuarios</code>, <code>/perfil/admin/*</code></td><td>GET/PUT/DELETE/POST</td><td>Administración de usuarios y configuración global</td></tr>
                    <tr><td><code>/actividades</code>, <code>/actividad/ping</code></td><td>GET/POST</td><td>Actividad reciente y keep-alive de sesión</td></tr>
                    <tr><td><code>/api/public/stats</code></td><td>GET</td><td>Estadísticas públicas</td></tr>
                    <tr><td><code>/documentacion</code>, <code>/documentacion/api</code>, <code>/documentacion/generar</code></td><td>GET/POST</td><td>Documentación, API del changelog y generación desde git</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: NOVEDADES -->
    <div class="doc-tab-content doc-content-scroll" id="tab-novedades">
        <div class="doc-section">
            <h2><i class="fas fa-star" style="color:var(--accent);"></i> Novedades y Funcionalidades Actuales</h2>
            <p>Resumen de las capacidades incluidas en la versión actual del sistema (22/11/2026):</p>

            <h3>Trabajadores y antecedentes</h3>
            <ul>
                <li><strong>Antecedentes en otra institución:</strong> sección en el formulario de trabajador para registrar períodos laborales previos en otros entes, con cargo, condición, dedicación, fechas y documentos de respaldo.</li>
                <li><strong>Categoría:</strong> el catálogo antes llamado «tipo de contrato» se presenta ahora como <em>Categoría</em> en el modal de trabajador y en Catálogos.</li>
                <li>Selección previa de cláusulas de convención en el trabajador, que se copian automáticamente al crear su expediente.</li>
            </ul>

            <h3>Nómina mensual</h3>
            <ul>
                <li>La planilla se organiza por <strong>año → mes</strong> (rejilla de meses), con importación/exportación Excel y alta/baja de trabajadores por planilla.</li>
                <li>El resumen muestra el <strong>número de trabajadores</strong> y el total general de cada mes.</li>
                <li>Nueva columna <code>mes</code> en <code>nominas</code> con índice <code>(periodo, mes)</code>.</li>
            </ul>

            <h3>Solicitudes y expedientes</h3>
            <ul>
                <li><strong>Expediente sin solicitud:</strong> ya no es obligatorio tener una solicitud aprobada; <code>solicitud_id</code> es opcional.</li>
                <li><strong>Incapacidad:</strong> la modalidad «Invalidez» pasó a denominarse <em>Incapacidad</em> (código <code>INC</code>) en catálogos y solicitudes existentes.</li>
                <li>Expedientes con cláusulas asignadas, recibos de nómina, carta de aprobación, foto de carnet, notas y documentos con estatus.</li>
                <li>Listado en secciones alfabéticas con paginación y retorno al grupo de origen desde el detalle.</li>
            </ul>

            <h3>Prestaciones y convención colectiva</h3>
            <ul>
                <li><strong>Cláusulas de convención:</strong> catálogo configurable (número, nombre, tipo de cálculo, valor, por año) —p. ej. Cláusula 55 de la IV Convención Colectiva— asignable al trabajador y al expediente.</li>
                <li>El cálculo de prestaciones incorpora el monto de las cláusulas (<code>montoClausula</code>) al resultado, además del fideicomiso registrado manualmente.</li>
                <li>Comprobante PDF con el detalle de prestaciones, cláusulas y fideicomiso.</li>
            </ul>

            <h3>Reportes</h3>
            <ul>
                <li>Gráfico de <strong>solicitudes por mes</strong> con dos vistas: <em>General</em> y <em>Por tipo de jubilación</em>.</li>
                <li>Gráficos de trabajadores por tipo de nómina y solicitudes por estatus, con redimensionamiento automático al cambiar de pestaña.</li>
            </ul>

            <h3>Plataforma y confiabilidad</h3>
            <ul>
                <li>Soporte para <strong>PostgreSQL 18</strong> además de MySQL/MariaDB (40 tablas, 48 migraciones en ambos).</li>
                <li>Respaldos: generación, verificación, descarga, eliminación y restauración (esta última restringida a superadmin), con retención automática.</li>
                <li><strong>Archivos dentro de la base de datos:</strong> tablas <code>archivos_bd</code> y <code>archivos_bd_bloques</code> guardan una copia en base64 de avatares, fotos y documentos para reconstruir <code>storage/</code> con <code>php artisan archivos:restaurar</code>.</li>
                <li>Vista de documentación con este changelog generado automáticamente desde <code>git log</code>.</li>
            </ul>
        </div>
    </div>

    <!-- TAB: CAMBIOS -->
    <div class="doc-tab-content doc-content-scroll" id="tab-cambios">
        <div class="doc-section">
            <h2><i class="fas fa-clock-rotate-left" style="color:var(--accent);"></i> Historial de Cambios (Changelog)</h2>
            <p>Registro generado a partir de los commits de Git. Se actualiza automáticamente al abrir esta página y puede regenerarse manualmente.</p>
            @if(isset($grouped) && $grouped->count() > 0)
                @foreach($grouped as $fecha => $entradas)
                    <h3>{{ $fecha }}</h3>
                    <table class="doc-table">
                        <thead><tr><th>Commit</th><th>Tipo</th><th>Cambio</th><th>Sección</th><th>Autor</th></tr></thead>
                        <tbody>
                            @foreach($entradas as $entrada)
                                @php
                                    $claseTag = ['feat' => 'tag-green', 'fix' => 'tag-orange', 'change' => 'tag-blue', 'docs' => 'tag-purple', 'refactor' => 'tag-gray', 'perf' => 'tag-red'][strtolower($entrada->tipo)] ?? 'tag-gray';
                                @endphp
                                <tr>
                                    <td><code>{{ substr($entrada->hash_commit, 0, 8) }}</code></td>
                                    <td><span class="tag {{ $claseTag }}">{{ $entrada->tipo }}</span></td>
                                    <td>
                                        {{ $entrada->mensaje_commit }}
                                        @if($entrada->descripcion)<br><small style="color:#94a3b8;">{{ \Illuminate\Support\Str::limit($entrada->descripcion, 160) }}</small>@endif
                                    </td>
                                    <td>{{ $entrada->seccion ?: '—' }}</td>
                                    <td>{{ $entrada->nombre_autor }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <div class="doc-empty">
                    <i class="fas fa-clock-rotate-left"></i>
                    <h2>Sin cambios registrados</h2>
                    <p>Ejecuta <code>php artisan changelog:generate</code> para importar el historial desde Git.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB: SEGURIDAD -->
    <div class="doc-tab-content doc-content-scroll" id="tab-seguridad">
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
                <li>Tres roles: <strong>superadmin</strong> y <strong>admin</strong> (acceso completo y panel de administración) y <strong>usuario</strong> (gestión de trabajadores/solicitudes/expedientes)</li>
                <li>Verificación de rol en rutas y controladores: middleware <code>role:admin,superadmin</code> para las áreas de administración</li>
                <li>Las vistas de administración solo se renderizan si el usuario es admin/superadmin</li>
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
                <li>Archivos (avatares, fotografías, documentos) almacenados en <code>storage/app/public/</code> y servidos vía symlink</li>
                <li>Sistema de logging de actividad <strong>Caja Negra</strong> (<code>activities</code>) para auditoría de cambios</li>
                <li><strong>Soft delete</strong> en trabajadores (columna <code>deleted_at</code>) para prevenir pérdida de datos</li>
                <li><strong>Copias de seguridad</strong> (backups) generables desde el panel para respaldo de la base de datos</li>
                <li><strong>Autenticación 2FA</strong> y opción de <strong>"Cerrar otras sesiones"</strong> desde el perfil del usuario</li>
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
