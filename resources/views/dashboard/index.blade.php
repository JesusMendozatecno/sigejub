{{-- dashboard/index.blade.php - Layout principal del dashboard con sidebar de navegación, header, secciones dinámicas, loading overlay y notificaciones. --}}
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
    <link rel="stylesheet" href="{{ asset('css/dashboard/dark-mode.css') }}?v={{ filemtime(public_path('css/dashboard/dark-mode.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/modern-theme.css') }}?v={{ filemtime(public_path('css/dashboard/modern-theme.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/responsive.css') }}?v={{ filemtime(public_path('css/dashboard/responsive.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <script defer src="{{ asset('js/chartjs/chart.umd.min.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
    window.escaparHTML = function(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };
    window.cachedFetch = async function(url, options) {
        options = options || {};
        var ttl = options.ttl || 60000;
        var cacheKey = 'sigejub_cache_' + url;
        var cached = localStorage.getItem(cacheKey);
        if (cached) {
            try {
                var parsed = JSON.parse(cached);
                if (Date.now() - parsed.ts < ttl) {
                    return { data: parsed.data, fromCache: true };
                }
            } catch (e) {}
        }
        try {
            var resp = await fetch(url);
            var data = await resp.json();
            localStorage.setItem(cacheKey, JSON.stringify({ ts: Date.now(), data: data }));
            return { data: data, fromCache: false };
        } catch (e) {
            if (cached) {
                try { return { data: JSON.parse(cached).data, fromCache: true }; } catch (e2) {}
            }
            throw e;
        }
    };
    </script>

    <style>
        :root { --accent: {{ auth()->user()->color_acento ?? '#1a365d' }}; }
        .notif-trigger { position: relative; padding: 6px; border-radius: 8px; transition: background 0.2s; }
        .notif-trigger:hover { background: #f1f5f9; }
        .theme-toggle-btn:hover { background: #f1f5f9; color: #1e293b !important; }
        body.dark-mode .theme-toggle-btn:hover { background: #334155; color: #e2e8f0 !important; }
        .notif-badge { display: none; }
        .notif-badge.show { display: flex !important; }
        .notif-item { padding: 10px 12px; border-radius: 8px; cursor: pointer; transition: background 0.15s; margin-bottom: 2px; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #eff6ff; border-left: 3px solid #2563eb; }
        .notif-item .notif-time { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }
        .notif-item .notif-title { font-size: 0.8rem; font-weight: 600; color: #0f172a; }
        .notif-item .notif-msg { font-size: 0.78rem; color: #475569; margin-top: 2px; line-height: 1.3; }
        body { background: var(--bg-body) url('{{ asset("img/bg/dashboard-bg.jpg") }}') center/cover fixed no-repeat; }
        body.dark-mode { background: var(--bg-body) url('{{ asset("img/bg/dashboard-bg.jpg") }}') center/cover fixed no-repeat; background-blend-mode: overlay; }
        .app-container > .sidebar { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
        body.dark-mode .app-container > .sidebar { background: rgba(15,23,42,0.95); backdrop-filter: blur(10px); }
        .main-content .dashboard-render-zone > .content-section { background: transparent; }
        .dashboard-render-zone > .content-section > * { background: white; border-radius: 16px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
@include('partials.loading-overlay')
<script>document.getElementById('loading-overlay').classList.add('active');</script>
<script>
(function(){
    var theme = '{{ auth()->user()->tema }}';
    if (theme === 'dark') document.body.classList.add('dark-mode');
    if (theme === 'modern') document.body.classList.add('theme-modern');
    var saved = localStorage.getItem('sigejub_active_section');
    if (saved && saved !== 'inicio') {
        document.write('<style id="tmp-section-style">.content-section.active{display:none!important} #' + saved + '{display:block}</style>');
    }
})();
</script>

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
        <button class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleHeaderTheme()" title="Cambiar tema de la página" style="background:none;border:none;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;transition:background 0.2s, color 0.2s;">
            <i id="themeToggleIcon" class="fas fa-moon" size="18"></i>
        </button>
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
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; line-height: 1; color: #1e293b;">{{ Auth::user()->nombre }}</p>
                </div>
                <i class="fas fa-chevron-down" size="14" style="color: #64748b;"></i>
            </div>

            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; color: #1e293b;">{{ Auth::user()->nombre }}</p>
                    <span style="font-size: 0.75rem; color: #64748b;">{{ Auth::user()->correo }}</span>
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
                <li class="menu-item" data-target="nomina"><i class="fas fa-file-invoice-dollar" size="18"></i> Nómina</li>
                <li class="menu-item" data-target="prestaciones"><i class="fas fa-wallet" size="18"></i> Prestaciones</li>
                <li class="menu-item" data-target="reportes"><i class="fas fa-chart-bar" size="18"></i> Reportes</li>
                <li class="menu-item" data-target="formulas"><i class="fas fa-square-root-variable" size="18"></i> Fórmulas</li>
                <li class="menu-item" data-target="tasas-cambio"><i class="fas fa-dollar-sign" size="18"></i> Tasa de Cambio</li>
                @if(in_array(Auth::user()->rol, ['admin', 'superadmin']))
                <li class="menu-item" data-target="cargos-grados"><i class="fas fa-address-card" size="18"></i> Cargos y Grados</li>
                <li class="menu-item" data-target="caja-negra"><i class="fas fa-hard-drive" size="18"></i> Historial</li>
                @endif
                @if(Auth::user()->rol === 'superadmin')
                <li class="menu-item" data-target="primas"><i class="fas fa-coins" size="18"></i> Primas</li>
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

            <div id="nomina" class="content-section">
                @include('dashboard.secciones.nomina')
            </div>

            <div id="prestaciones" class="content-section">
                @include('dashboard.secciones.prestaciones')
            </div>

            <div id="reportes" class="content-section">
                @include('dashboard.secciones.reportes')
            </div>

            <div id="formulas" class="content-section">
                @include('dashboard.secciones.formulas')
            </div>

            <div id="tasas-cambio" class="content-section">
                @include('dashboard.secciones.tasas-cambio')
            </div>

            @if(in_array(Auth::user()->rol, ['admin', 'superadmin']))

            <div id="cargos-grados" class="content-section">
                @include('dashboard.secciones.cargos-grados')
            </div>

            <div id="caja-negra" class="content-section">
                @include('dashboard.secciones.caja-negra')
            </div>
            @endif

            @if(Auth::user()->rol === 'superadmin')
            <div id="primas" class="content-section">
                @include('dashboard.secciones.primas')
            </div>
            @endif

        </div>
    </main>

</div>

<script>window.SIGEJUB_THEME='{{ auth()->user()->tema }}'; window.SIGEJUB_ROL='{{ auth()->user()->rol }}';</script>
<script defer src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>

<script defer src="{{ asset('js/secciones/trabajador.js') }}?v={{ filemtime(public_path('js/secciones/trabajador.js')) }}"></script>
<script defer src="{{ asset('js/sesion2.js') }}?v={{ filemtime(public_path('js/sesion2.js')) }}"></script>

@include('partials.toast')

<script>
/* === Precarga del dashboard al cargar (solo en login→dashboard) === */
(function() {
    var overlay = document.getElementById('loading-overlay');
    var yaCargo = sessionStorage.getItem('sigejub_dashboard_cargado');
    if (yaCargo) {
        if (overlay) overlay.classList.remove('active');
        return;
    }
    if (!overlay || !overlay.classList.contains('active')) return;
    document.getElementById('loadingText').textContent = 'Preparando el sistema...';
    Promise.all([
        fetch('/actividades').catch(function(){}),
        fetch('/solicitudes?per_page=1').catch(function(){}),
        fetch('/trabajadores?per_page=1').catch(function(){}),
        fetch('/expedientes?per_page=1').catch(function(){}),
        fetch('/caja-negra?per_page=1').catch(function(){}),
        fetch('/expedientes/listos-aprobacion').catch(function(){}),
        fetch('/solicitudes/por-mes').catch(function(){}),
        fetch('/solicitudes/vencimientos').catch(function(){}),
        fetch('/trabajadores-stats/dashboard').catch(function(){}),
        fetch('/notificaciones/no-leidas').catch(function(){}),
    ]).then(function() {
        sessionStorage.setItem('sigejub_dashboard_cargado', '1');
        ocultarCargando();
    }).catch(function() {
        sessionStorage.setItem('sigejub_dashboard_cargado', '1');
        ocultarCargando();
    });
})();
</script>

@if(session('success'))
    <script>
    var checkBienvenida = setInterval(function() {
        var overlay = document.getElementById('loading-overlay');
        if (!overlay || !overlay.classList.contains('active')) {
            clearInterval(checkBienvenida);
            mostrarToast('{{ session('success') }}', 'success');
        }
    }, 200);
    </script>
@endif
@if($errors->any())
    <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
@endif

<script>
/* === Validación global: bloquear valores negativos en inputs numéricos === */
document.addEventListener('input', function(e) {
    if (e.target.type === 'number' && parseFloat(e.target.value) < 0) {
        e.target.value = 0;
    }
});
document.addEventListener('keypress', function(e) {
    if (e.target.type === 'number' && (e.key === '-' || e.key === 'Minus')) {
        e.preventDefault();
    }
});

/* === Auto-cierre de 3 minutos en vistas de detalle === */
(function() {
    let inactivityTimer = null;
    const TIMEOUT_MS = 180000;
    const SECCIONES_DETALLE = ['expediente-detalle', 'prestacion-detalle'];
    const LISTAS = { 'expediente-detalle': 'expedientes-lista', 'prestacion-detalle': 'prestaciones-lista' };

    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(function() {
            let algunaVisible = false;
            SECCIONES_DETALLE.forEach(function(id) {
                const el = document.getElementById(id);
                if (el && !el.classList.contains('hidden')) {
                    el.classList.add('hidden');
                    algunaVisible = true;
                    const listaId = LISTAS[id];
                    const lista = document.getElementById(listaId);
                    if (lista) lista.classList.remove('hidden');
                }
            });
            if (algunaVisible) {
                mostrarToast('Sesión inactiva: se cerró el detalle automáticamente.', 'info');
            }
        }, TIMEOUT_MS);
    }

    ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function(evt) {
        document.addEventListener(evt, resetTimer, { passive: true });
    });
    resetTimer();
})();

/* === Toggle rápido de tema (sol/luna) en el header === */
function syncHeaderThemeIcon() {
    var icon = document.getElementById('themeToggleIcon');
    if (!icon) return;
    var isDark = document.body.classList.contains('dark-mode');
    icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
window.toggleHeaderTheme = function() {
    var isDark = document.body.classList.contains('dark-mode');
    // Alternar entre claro y oscuro (si el usuario está en moderno, pasa a claro)
    var el = document.getElementById('themeToggleBtn');
    if (el) { el.style.pointerEvents = 'none'; setTimeout(function(){ el.style.pointerEvents = ''; }, 400); }
    window.cambiarTema(isDark ? 'light' : 'dark');
    syncHeaderThemeIcon();
};
document.addEventListener('DOMContentLoaded', syncHeaderThemeIcon);
</script>
</body>
</html>