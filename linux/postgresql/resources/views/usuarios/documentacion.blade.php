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
    <div class="doc-tab-content active doc-content-scroll" id="tab-info">
        <div class="doc-section">
            <h2><i class="fas fa-circle-info" style="color:var(--accent);"></i> Información General</h2>
            <p><strong>SIGEJUB</strong> (Sistema Integral de Gestión de Jubilaciones) es una plataforma web desarrollada para la <strong>Universidad Politécnica Territorial de Yaracuy (UPTYAB)</strong> que automatiza y centraliza la gestión de expedientes, solicitudes de jubilación, cálculos de prestaciones sociales y nómina del personal docente, administrativo y obrero.</p>

            <h3>Tecnologías Utilizadas</h3>
            <p>
                <span class="tag tag-blue">PHP 8.4</span>
                <span class="tag tag-purple">Laravel 12</span>
                <span class="tag tag-green">MySQL (MariaDB)</span>
                <span class="tag tag-orange">JavaScript (ES6+)</span>
                <span class="tag tag-blue">HTML5 / CSS3</span>
                <span class="tag tag-green">Blade (Motor de plantillas)</span>
                <span class="tag tag-orange">Font Awesome 6</span>
                <span class="tag tag-blue">Chart.js</span>
                <span class="tag tag-purple">DomPDF</span>
                <span class="tag tag-orange">PhpSpreadsheet</span>
                <span class="tag tag-blue">Cropper.js</span>
                <span class="tag tag-red">Apache (XAMPP)</span>
            </p>

            <h3>Lenguajes y librerías</h3>
            <ul>
                <li><strong>Backend:</strong> PHP 8.4 (Laravel 12 Framework) con Eloquent ORM</li>
                <li><strong>Frontend:</strong> JavaScript vanilla (ES6+), HTML5, CSS3 — sin frameworks de frontend</li>
                <li><strong>Base de Datos:</strong> MySQL (MariaDB)</li>
                <li><strong>Plantillas:</strong> Blade (motor de plantillas de Laravel)</li>
                <li><strong>Gráficos:</strong> Chart.js (<code>public/js/chartjs/chart.umd.min.js</code>)</li>
                <li><strong>PDF:</strong> DomPDF (comprobantes de prestaciones y exportaciones)</li>
                <li><strong>Excel:</strong> PhpSpreadsheet (<code>phpoffice/phpspreadsheet</code>) para importación/exportación de nómina</li>
                <li><strong>Recorte de imágenes:</strong> Cropper.js (avatar de perfil)</li>
            </ul>

            <h3>Estructura del Sistema</h3>
            <p>El sistema sigue el patrón <strong>MVC (Modelo-Vista-Controlador)</strong> de Laravel:</p>
            <ul>
                <li><strong>app/Models/</strong> — Modelos Eloquent (User, Trabajador, Solicitud, Expediente, Nomina, Prestacion, TasaCambio, FormulaPrestacion, Prima, Cargo, Area, Grado, NivelInstruccion, TipoContrato, Sueldo, TipoJubilacion, Activity, Changelog, etc.)</li>
                <li><strong>app/Http/Controllers/</strong> — Controladores (Auth, User/Trabajador/Solicitud/Expediente/Prestaciones/Nomina/NominaExport/Admin/CajaNegra/MasterData/TasaCambio/FormulaPrestacion/Backup/Changelog)</li>
                <li><strong>app/Services/</strong> — Servicios (NotificationService, ValidationService)</li>
                <li><strong>database/migrations/</strong> — Migraciones de base de datos (incluye tablas maestras)</li>
                <li><strong>routes/web.php</strong> — Definición de rutas web (web + API AJAX)</li>
                <li><strong>resources/views/</strong> — Vistas Blade (dashboard/secciones, auth, usuarios, partials, pdf)</li>
                <li><strong>public/js/dashboard.js</strong> — Lógica principal del panel (vanilla JS)</li>
                <li><strong>public/css/dashboard/</strong> — Estilos CSS del panel</li>
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
                         │ HTTP (Apache :80)
┌────────────────────────┼────────────────────────────┐
│               LARAVEL (PHP 8.4)                     │
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

            <h3>Flujo de Datos</h3>
            <ol>
                <li>El navegador realiza peticiones HTTP (GET/POST/PUT/DELETE) al servidor Apache</li>
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
                    <tr><td><strong>Inicio</strong></td><td>Panel principal con estadísticas y acceso rápido a los módulos</td><td>AuthController@dashboard</td><td>/dashboard</td></tr>
                    <tr><td><strong>Autenticación</strong></td><td>Login, registro, logout y protección por throttle</td><td>AuthController</td><td>/login, /register</td></tr>
                    <tr><td><strong>Trabajadores</strong></td><td>CRUD completo de trabajadores, autocompletado y estadísticas</td><td>TrabajadorController</td><td>/trabajadores</td></tr>
                    <tr><td><strong>Solicitudes</strong></td><td>Gestión de solicitudes de jubilación con estatus, por mes y exportación PDF</td><td>SolicitudController</td><td>/solicitudes</td></tr>
                    <tr><td><strong>Expedientes</strong></td><td>Expedientes digitales con documentos, notas, carta de aprobación y foto de carnet</td><td>ExpedienteController</td><td>/expedientes</td></tr>
                    <tr><td><strong>Nómina</strong></td><td>Importar/exportar planilla de nómina en Excel y organización por años</td><td>NominaController, NominaExportController</td><td>/nomina</td></tr>
                    <tr><td><strong>Prestaciones</strong></td><td>Cálculo de prestaciones sociales según LOTTT, guardado y comprobante PDF</td><td>PrestacionesController</td><td>/prestaciones</td></tr>
                    <tr><td><strong>Reportes</strong></td><td>Estadísticas generales del sistema con métricas en tiempo real</td><td>(AJAX a varios)</td><td>/reportes</td></tr>
                    <tr><td><strong>Fórmulas</strong></td><td>Definición y administración de fórmulas para el cálculo de prestaciones</td><td>FormulaPrestacionController</td><td>/formulas-prestaciones</td></tr>
                    <tr><td><strong>Tasa de Cambio</strong></td><td>Consulta y sincronización de la tasa de cambio (VES/USD)</td><td>TasaCambioController</td><td>/tasas-cambio</td></tr>
                    <tr><td><strong>Cargos y Grados</strong></td><td>Tablas maestras: cargos, áreas, grados, niveles, sueldos y tipos de contrato</td><td>MasterDataController</td><td>/master/{tipo}</td></tr>
                    <tr><td><strong>Primas</strong></td><td>Administración de las primas aplicables en el cálculo salarial</td><td>(sección del dashboard)</td><td>/dashboard</td></tr>
                    <tr><td><strong>Historial (Caja Negra)</strong></td><td>Auditoría completa de cambios y generación de copias de seguridad</td><td>CajaNegraController, BackupController</td><td>/caja-negra, /backups</td></tr>
                    <tr><td><strong>Perfil</strong></td><td>Gestión de cuenta, avatar, seguridad, sesiones y actividad del usuario</td><td>UserController</td><td>/perfil</td></tr>
                    <tr><td><strong>Usuarios (Admin)</strong></td><td>Administración de usuarios y actividad global del sistema</td><td>AdminController</td><td>/usuarios</td></tr>
                    <tr><td><strong>Documentación</strong></td><td>Documentación del sistema y changelog generado desde git log</td><td>ChangelogController</td><td>/documentacion</td></tr>
                </tbody>
            </table>

            <h3>Base de Datos</h3>
            <p>Tablas principales del sistema (MySQL / MariaDB):</p>
            <table class="doc-table">
                <thead><tr><th>Tabla</th><th>Propósito</th></tr></thead>
                <tbody>
                    <tr><td><code>users</code></td><td>Usuarios del sistema (rol: usuario, admin, superadmin)</td></tr>
                    <tr><td><code>trabajadores</code></td><td>Registro de empleados UPTYAB (con campos de salario, años de servicio y primas)</td></tr>
                    <tr><td><code>solicitudes</code></td><td>Solicitudes de jubilación</td></tr>
                    <tr><td><code>expedientes</code></td><td>Expedientes digitales (con carta de aprobación y notas)</td></tr>
                    <tr><td><code>documentos</code></td><td>Documentos adjuntos por expediente</td></tr>
                    <tr><td><code>nominas</code> / <code>nomina_trabajador</code></td><td>Nóminas mensuales y detalle por trabajador (pivot)</td></tr>
                    <tr><td><code>prestaciones</code></td><td>Cálculo de prestaciones sociales (monto, sueldo integral, total primas, tasa)</td></tr>
                    <tr><td><code>primas</code></td><td>Catálogo de primas aplicables (familiar, hijo, profesionalización, etc.)</td></tr>
                    <tr><td><code>tasas_cambio</code></td><td>Registro de tasas de cambio (VES/USD)</td></tr>
                    <tr><td><code>formulas_prestaciones</code></td><td>Fórmulas parametrizadas para el cálculo de prestaciones</td></tr>
                    <tr><td><code>cargos</code>, <code>areas</code>, <code>grados</code></td><td>Tablas maestras de cargos, áreas y grados</td></tr>
                    <tr><td><code>niveles_instruccion</code>, <code>tipos_contrato</code>, <code>tipos_jubilacion</code></td><td>Catálogos de nivel académico, tipo de contrato y tipo de jubilación</td></tr>
                    <tr><td><code>sueldos</code></td><td>Sueldos parametrizados por grado y nivel de instrucción</td></tr>
                    <tr><td><code>activities</code></td><td>Caja negra: bitácora de actividad del sistema</td></tr>
                    <tr><td><code>notifications</code></td><td>Notificaciones del sistema y del usuario</td></tr>
                    <tr><td><code>changelogs</code></td><td>Registro de cambios (changelog) generado desde git log</td></tr>
                    <tr><td><code>sessions</code></td><td>Sesiones de usuario</td></tr>
                </tbody>
            </table>

            <h3>API JSON Endpoints</h3>
            <p>El sistema expone múltiples endpoints AJAX para consumo desde el frontend (todos requieren autenticación):</p>
            <table class="doc-table">
                <thead><tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td><code>/trabajadores</code></td><td>GET/POST</td><td>Lista paginada y creación de trabajadores</td></tr>
                    <tr><td><code>/trabajadores/autocomplete</code></td><td>GET</td><td>Autocompletado de trabajadores</td></tr>
                    <tr><td><code>/trabajadores/{id}</code></td><td>GET/PUT/DELETE</td><td>CRUD individual de trabajador</td></tr>
                    <tr><td><code>/trabajadores-stats/dashboard</code></td><td>GET</td><td>Estadísticas de trabajadores</td></tr>
                    <tr><td><code>/solicitudes</code></td><td>GET/POST</td><td>Lista y creación de solicitudes</td></tr>
                    <tr><td><code>/solicitudes/por-mes</code></td><td>GET</td><td>Solicitudes agrupadas por mes</td></tr>
                    <tr><td><code>/solicitudes/vencimientos</code></td><td>GET</td><td>Próximos vencimientos</td></tr>
                    <tr><td><code>/solicitudes/estadisticas</code></td><td>GET</td><td>Estadísticas de solicitudes</td></tr>
                    <tr><td><code>/solicitudes/exportar</code></td><td>GET</td><td>Exporta solicitudes a PDF</td></tr>
                    <tr><td><code>/expedientes</code></td><td>GET/POST</td><td>Lista y creación de expedientes</td></tr>
                    <tr><td><code>/expedientes/buscar-trabajador</code></td><td>GET</td><td>Búsqueda de trabajador para expediente</td></tr>
                    <tr><td><code>/expedientes/listos-aprobacion</code></td><td>GET</td><td>Expedientes listos para aprobación</td></tr>
                    <tr><td><code>/expedientes/{id}/documentos</code></td><td>POST</td><td>Sube documento al expediente</td></tr>
                    <tr><td><code>/expedientes/{id}/carta-aprobacion</code></td><td>POST</td><td>Sube carta de aprobación</td></tr>
                    <tr><td><code>/expedientes/{id}/foto-carnet</code></td><td>POST</td><td>Actualiza foto de carnet</td></tr>
                    <tr><td><code>/nomina</code></td><td>GET</td><td>Nómina (con filtro por año)</td></tr>
                    <tr><td><code>/nomina/anios</code></td><td>GET</td><td>Años disponibles en la nómina</td></tr>
                    <tr><td><code>/exportar/nomina</code></td><td>GET</td><td>Descarga planilla Excel de nómina</td></tr>
                    <tr><td><code>/importar/nomina</code></td><td>POST</td><td>Importa nómina desde Excel</td></tr>
                    <tr><td><code>/prestaciones</code></td><td>GET/POST</td><td>Lista y guardado de prestaciones</td></tr>
                    <tr><td><code>/prestaciones/{id}</code></td><td>GET</td><td>Detalle del trabajador para cálculo</td></tr>
                    <tr><td><code>/prestaciones/{id}/comprobante</code></td><td>POST</td><td>Genera comprobante PDF de prestaciones</td></tr>
                    <tr><td><code>/formulas-prestaciones</code></td><td>GET/POST</td><td>Lista y creación de fórmulas</td></tr>
                    <tr><td><code>/tasas-cambio</code></td><td>GET/POST</td><td>Lista y creación de tasas de cambio</td></tr>
                    <tr><td><code>/tasas-cambio/actual</code></td><td>GET</td><td>Tasa de cambio actual</td></tr>
                    <tr><td><code>/tasas-cambio/sincronizar</code></td><td>POST</td><td>Sincroniza tasa desde proveedor</td></tr>
                    <tr><td><code>/master/{tipo}</code></td><td>GET/POST/PUT/DELETE</td><td>CRUD de tablas maestras (cargos, grados, etc.)</td></tr>
                    <tr><td><code>/caja-negra</code></td><td>GET</td><td>Historial de auditoría</td></tr>
                    <tr><td><code>/caja-negra-data/estadisticas</code></td><td>GET</td><td>Estadísticas de la caja negra</td></tr>
                    <tr><td><code>/backups</code></td><td>GET/POST</td><td>Lista y generación de copias de seguridad</td></tr>
                    <tr><td><code>/usuarios</code></td><td>GET</td><td>Lista de usuarios (admin)</td></tr>
                    <tr><td><code>/actividades</code></td><td>GET</td><td>Actividad reciente del sistema</td></tr>
                    <tr><td><code>/notificaciones</code></td><td>GET</td><td>Notificaciones del usuario</td></tr>
                    <tr><td><code>/actividad/ping</code></td><td>POST</td><td>Keep-alive de sesión</td></tr>
                    <tr><td><code>/documentacion/api</code></td><td>GET</td><td>Lista de cambios (changelog)</td></tr>
                </tbody>
            </table>
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
