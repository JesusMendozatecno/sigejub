<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEJUB - Sistema de Jubilaciones</title>

    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="icon" href="{{ asset('img/favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('img/favicon-16.png') }}" sizes="16x16" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}">
    
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ filemtime(public_path('css/dashboard/dashboard.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root { --accent: {{ auth()->user()->accent_color ?? '#1a365d' }}; }
        .notif-trigger { position: relative; padding: 6px; border-radius: 8px; transition: background 0.2s; }
        .notif-trigger:hover { background: #f1f5f9; }
        .notif-badge { display: none; }
        .notif-badge.show { display: flex !important; }
        .notif-item { padding: 10px 12px; border-radius: 8px; cursor: pointer; transition: background 0.15s; margin-bottom: 2px; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #eff6ff; border-left: 3px solid #2563eb; }
        .notif-item .notif-time { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }
        .notif-item .notif-title { font-size: 0.8rem; font-weight: 600; color: #0f172a; }
        .notif-item .notif-msg { font-size: 0.78rem; color: #475569; margin-top: 2px; line-height: 1.3; }
    </style>
</head>
<body>

<header class="top-bar">
    <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>
    <div class="header-brand">
        <div class="logo-box" style="background: #1a365d; color: white; padding: 8px; border-radius: 8px; font-size: 1.2rem;">🏛️</div>
        <div>
            <h3 style="margin:0; font-size: 1.1rem; color: #1e293b;">SIGEJUB</h3>
            <span style="font-size: 0.65rem; color: #64748b; font-weight: bold; letter-spacing: 0.5px; display: block;">ARQUITECTURA DE CONFIANZA</span>
        </div>
    </div>

    <div class="header-actions">
        <div class="notif-dropdown" id="notifDropdown">
            <button class="notif-trigger" onclick="toggleNotifDropdown()" style="background:none;border:none;color:#64748b;cursor:pointer;display:flex;align-items:center;position:relative;">
                <i class="fas fa-bell" size="20"></i>
                <span class="notif-badge" id="notifBadge" style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;align-items:center;justify-content:center;">0</span>
            </button>
            <div class="notif-menu" id="notifMenu" style="display:none;position:absolute;top:100%;right:0;width:360px;background:white;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.15);z-index:9999;margin-top:8px;max-height:400px;overflow-y:auto;">
                <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <strong style="font-size:0.9rem;color:#0f172a;">Notificaciones</strong>
                    <button onclick="marcarTodasLeidas()" style="background:none;border:none;color:#2563eb;font-size:0.75rem;cursor:pointer;font-weight:600;">Marcar todas leídas</button>
                </div>
                <div id="notifList" style="padding:8px;">
                    <p style="text-align:center;color:#94a3b8;padding:20px;font-size:0.85rem;">Cargando...</p>
                </div>
            </div>
        </div>

        <div class="user-dropdown" id="userDropdown">
            <div class="dropdown-trigger" onclick="toggleDropdown()">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar">
                    @else
                        <i class="fas fa-user" size="20"></i>
                    @endif
                </div>
                <div style="text-align: left; display: block;">
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; line-height: 1; color: #1e293b;">{{ Auth::user()->name }}</p>
                </div>
                <i class="fas fa-chevron-down" size="14" style="color: #64748b;"></i>
            </div>

            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; color: #1e293b;">{{ Auth::user()->name }}</p>
                    <span style="font-size: 0.75rem; color: #64748b;">{{ Auth::user()->email }}</span>
                </div>

                <a href="{{ route('usuarios.user') }}" class="dropdown-item">
                    <i class="fas fa-gear" size="16"></i> Gestionar cuenta
                </a>

                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 4px 0;">

                <form method="POST" action="{{ route('logout') }}" onsubmit="mostrarCargando('Cerrando sesión...')" style="margin: 0;">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-right-from-bracket" size="16"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<div class="app-container">

    <aside class="sidebar">
        <nav class="sidebar-menu">
            <ul>
                <li class="menu-item active" data-target="inicio"><i class="fas fa-house" size="18"></i> Inicio</li>
                <li class="menu-item" data-target="trabajadores"><i class="fas fa-users" size="18"></i> Trabajadores</li>
                <li class="menu-item" data-target="solicitudes"><i class="fas fa-file-lines" size="18"></i> Solicitudes</li>
                <li class="menu-item" data-target="expedientes"><i class="fas fa-folder" size="18"></i> Expedientes</li>
                <li class="menu-item" data-target="prestaciones"><i class="fas fa-wallet" size="18"></i> Prestaciones</li>
                <li class="menu-item" data-target="reportes"><i class="fas fa-chart-bar" size="18"></i> Reportes</li>
                @if(Auth::user()->role === 'admin')
                <li class="menu-item" data-target="caja-negra"><i class="fas fa-hard-drive" size="18"></i> Historial</li>
                @endif

            </ul>
        </nav>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <div class="dashboard-render-zone">
            
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

            @if(Auth::user()->role === 'admin')
    
            <div id="caja-negra" class="content-section">
                @include('dashboard.secciones.caja-negra')
            </div>
            @endif

        </div>
    </main>

</div>

<script>window.SIGEJUB_THEME='{{ auth()->user()->theme }}';</script>
<script defer src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>

<script defer src="{{ asset('js/secciones/trabajador.js') }}?v={{ filemtime(public_path('js/secciones/trabajador.js')) }}"></script>
<script defer src="{{ asset('js/sesion2.js') }}?v={{ filemtime(public_path('js/sesion2.js')) }}"></script>

@include('partials.toast')

@if(session('success'))
    <script>mostrarToast('{{ session('success') }}', 'success');</script>
@endif
@if($errors->any())
    <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
@endif
</body>
</html>