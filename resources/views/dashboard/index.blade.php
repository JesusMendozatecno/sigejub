<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEJUB - Sistema de Jubilaciones</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/components.css') }}">

    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/inicio.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/solicitud.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/expediente.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/trabajadores.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/trabajadores2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/prestaciones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/reportes.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Funcionalidad de cambio de pestañas dinámicas */
        .content-section { display: none; animation: fadeIn 0.3s ease; }
        .content-section.active { display: block; }
        .menu-item { cursor: pointer; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* Ajustes estéticos de badges y utilidades */
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-activo { background: #e6fcf5; color: #0ca678; }
        .status-jubilado { background: #e7f5ff; color: #1c7ed6; }
        .status-suspension { background: #fff5f5; color: #fa5252; }
        .data-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-box" style="background: #1a365d; color: white; padding: 8px; border-radius: 8px;">🏛️</div>
        <div>
            <h3 style="margin:0; font-size: 1.1rem;">Gestión Jubilaciones</h3>
            <span style="font-size: 0.7rem; color: #667085;">ARQUITECTURA DE CONFIANZA</span>
        </div>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item active" data-target="inicio"><i data-lucide="home"></i> Inicio</li>
            <li class="menu-item" data-target="trabajadores"><i data-lucide="users"></i> Trabajadores</li>
            <li class="menu-item" data-target="solicitudes"><i data-lucide="file-text"></i> Solicitudes</li>
            <li class="menu-item" data-target="expedientes"><i data-lucide="folder"></i> Expedientes</li>
            <li class="menu-item" data-target="prestaciones"><i data-lucide="wallet"></i> Prestaciones</li>
            <li class="menu-item" data-target="reportes"><i data-lucide="bar-chart-3"></i> Reportes</li>
        </ul>
    </nav>

    <div class="sidebar-footer" style="padding: 20px; border-top: 1px solid #eee;">
        <a href="{{ route('usuarios.user') }}" class="user-profile-mini" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; padding: 8px; border-radius: 8px;">
            <style>
                .user-profile-mini:hover { background-color: #f1f5f9; }
            </style>
            <div class="avatar" style="width: 35px; height: 35px; border-radius: 50%; background: #dbeafe; color: #1e3a8a; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="settings" size="18"></i>
            </div>
            <div style="flex-grow: 1;">
                <p style="margin:0; font-size: 0.85rem; font-weight: bold; line-height: 1;">{{ Auth::user()->name }}</p>
                <span style="font-size: 0.75rem; color: #666;">Gestionar cuenta</span>
            </div>
            <i data-lucide="chevron-right" size="14" style="color: #94a3b8;"></i>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="top-bar">
        <div class="search-container">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Buscar por nombre o cédula...">
        </div>
        <div class="user-info" style="display:flex; gap: 20px; align-items: center;">
            <i data-lucide="bell"></i>
            <i data-lucide="settings"></i>
            <span style="color: #1a365d; font-weight: bold;">Sistema de Jubilaciones</span>
        </div>
    </header>

    <div class="dashboard-render-zone" style="margin-top: 20px; flex-grow: 1;">
        
        <div id="inicio" class="content-section active">
            @include('dashboard.secciones.inicio')
        </div>

        <div id="trabajadores" class="content-section">
            @include('dashboard.secciones.trabajadores')
        </div>

        <div id="solicitudes" class="content-section">
            @include('dashboard.secciones.solicitudes')
        </div>

        <div id="expedientes" class="content-section">
            @include('dashboard.secciones.expedientes')
        </div>

        <div id="prestaciones" class="content-section">
            @include('dashboard.secciones.prestaciones')
        </div>

        <div id="reportes" class="content-section">
            @include('dashboard.secciones.reportes')
        </div>

    </div>

    <footer style="margin-top: 40px; padding: 20px 0; border-top: 1px solid #eee;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-logout" style="background: none; border: 1px solid #fa5252; color: #fa5252; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="log-out" size="16"></i> Cerrar sesión
            </button>
        </form>
    </footer>
</main>



<script src="{{ asset('js/trabajador.js') }}"></script>
<script src="{{ asset('js/sesion2.js') }}"></script>
<script src="{{ asset('js/tabla1.js') }}"></script>
<script src="{{ asset('js/expedientelevel1.js') }}"></script>

</body>
</html>