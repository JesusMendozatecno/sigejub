{{-- dashboard/index.blade.php - Layout principal del dashboard con sidebar de navegación, header, secciones dinámicas, loading overlay y notificaciones. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEJUB - Sistema de Jubilaciones</title>

    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}">
    
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ filemtime(public_path('css/dashboard/dashboard.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dark-mode.css') }}?v={{ filemtime(public_path('css/dashboard/dark-mode.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/modern-theme.css') }}?v={{ filemtime(public_path('css/dashboard/modern-theme.css')) }}">
    {{-- Estilos de las ventanas modales --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/modal.css') }}?v={{ filemtime(public_path('css/dashboard/secciones/modal.css')) }}">
    {{-- Capa de modernización visual (tokens claro/oscuro/modern + componentes).
         Va después de modal.css para darle color de acento a sus botones/campos,
         y ANTES de responsive.css para no romper los ajustes de pantalla. --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/rediseno.css') }}?v={{ filemtime(public_path('css/dashboard/rediseno.css')) }}">
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
        /* Color de acento del usuario (personalización de perfil).
           --accent-dark se deriva automáticamente para degradados/tintes. */
        :root {
            --accent: {{ auth()->user()->color_acento ?? '#1a365d' }};
            --accent-dark: color-mix(in srgb, var(--accent) 72%, #000 28%);
            --accent-soft: color-mix(in srgb, var(--accent) 12%, transparent);
        }

        /* ============================================================
           NOTIFICACIONES DEL HEADER
           El trigger y el badge viven dentro de .notif-dropdown, que es
           el contenedor posicionado. El menú se muestra con .open.
           ============================================================ */
        .notif-dropdown { position: relative; overflow: visible; }

        .notif-trigger {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: none;
            border: none;
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 1.05rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .notif-trigger:hover { background: var(--bg-surface); color: var(--accent); }
        .notif-trigger:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        .notif-badge {
            display: none;
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            align-items: center;
            justify-content: center;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            border-radius: 999px;
            border: 2px solid var(--accent);
        }
        .notif-badge.show { display: flex; }

        .notif-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 360px;
            max-width: calc(100vw - 24px);
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
            z-index: 9999;
            overflow: hidden;
            animation: notifFadeIn 0.18s ease;
        }
        .notif-menu.open { display: block; }
        @keyframes notifFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }
        .notif-header-title { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); }
        .notif-mark-all {
            background: none;
            border: none;
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
        .notif-mark-all:hover { text-decoration: underline; }

        .notif-list { padding: 8px; max-height: 380px; overflow-y: auto; }
        .notif-empty { text-align: center; color: var(--text-muted); padding: 24px 12px; font-size: 0.85rem; margin: 0; }

        .notif-item {
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s;
            margin-bottom: 2px;
        }
        .notif-item:hover { background: var(--bg-surface); }
        .notif-item.unread { background: var(--info-light); border-left: 3px solid var(--info); }
        .notif-item .notif-time { font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; }
        .notif-item .notif-title { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); }
        .notif-item .notif-msg { font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px; line-height: 1.3; }

        /* Cabecera izquierda: botón + marca (sin exceso de separación) */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-4);
            padding: 10px 20px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            height: var(--sidebar-topbar-height);
            box-shadow: var(--shadow-sm);
            z-index: 201;
            flex-wrap: nowrap;
        }
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            min-width: 0;
        }

        .main-content .dashboard-render-zone > .content-section { background: transparent; }
        .dashboard-render-zone > .content-section > *:not(.modal-overlay):not(.modal-tasa):not(.modal-resultado) {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-card);
        }
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
        <div class="top-bar-left">
            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Plegar o desplegar el menú lateral" title="Plegar / Desplegar menú">
                <i class="fas fa-chevron-left sidebar-toggle-icon" aria-hidden="true"></i>
            </button>
            <div class="header-brand">
                <div class="logo-box" style="background: #1a365d; color: white; padding: 8px; border-radius: 8px; font-size: 1.2rem;">🏛️</div>
                <div>
                    <h3 style="margin:0; font-size: 1.1rem; color: #1e293b;">SIGEJUB</h3>
                    <span style="font-size: 0.65rem; color: #64748b; font-weight: bold; letter-spacing: 0.5px; display: block;">ARQUITECTURA DE CONFIANZA</span>
                </div>
            </div>
        </div>

    <div class="header-actions">
        <button class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleHeaderTheme()" title="Cambiar tema de la página" style="background:none;border:none;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;transition:background 0.2s, color 0.2s;">
            <i id="themeToggleIcon" class="fas fa-moon" size="18"></i>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
            <button class="notif-trigger" onclick="toggleNotifDropdown()" type="button" aria-label="Notificaciones" aria-expanded="false">
                <i class="fas fa-bell" aria-hidden="true"></i>
                <span class="notif-badge" id="notifBadge">0</span>
            </button>
            <div class="notif-menu" id="notifMenu" role="menu" aria-label="Lista de notificaciones">
                <div class="notif-header">
                    <strong class="notif-header-title">Notificaciones</strong>
                    <button class="notif-mark-all" onclick="marcarTodasLeidas()" type="button">Marcar todas leídas</button>
                </div>
                <div class="notif-list" id="notifList">
                    <p class="notif-empty">Cargando...</p>
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
                <li class="menu-item" data-target="nomina"><i class="fas fa-file-invoice-dollar" size="18"></i> Nómina</li>
                <li class="menu-item" data-target="solicitudes"><i class="fas fa-file-lines" size="18"></i> Solicitudes</li>
                <li class="menu-item" data-target="expedientes"><i class="fas fa-folder" size="18"></i> Expedientes</li>
                <li class="menu-item" data-target="prestaciones"><i class="fas fa-wallet" size="18"></i> Prestaciones</li>
                @if(Auth::user()->rol === 'superadmin')
                <li class="menu-item" data-target="primas"><i class="fas fa-coins" size="18"></i> Primas</li>
                @endif
                <li class="menu-item" data-target="tasas-cambio"><i class="fas fa-dollar-sign" size="18"></i> Tasa de Cambio</li>
                @if(in_array(Auth::user()->rol, ['admin', 'superadmin']))
                <li class="menu-item" data-target="cargos-grados"><i class="fas fa-address-card" size="18"></i> Cargos y Grados</li>
                @endif
                <li class="menu-item" data-target="reportes"><i class="fas fa-chart-bar" size="18"></i> Reportes</li>
                @if(in_array(Auth::user()->rol, ['admin', 'superadmin']))
                <li class="menu-item" data-target="caja-negra"><i class="fas fa-hard-drive" size="18"></i> Historial</li>
                @endif
                <li class="menu-item" data-target="ayuda"><i class="fas fa-circle-question" size="18"></i> Ayuda</li>
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

            <div id="ayuda" class="content-section">
                @include('dashboard.secciones.ayuda')
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
{{-- Gestor central de ventanas modales: solo cierran con su botón (clic fuera y ESC desactivados) --}}
<script defer src="{{ asset('js/dashboard/modales.js') }}?v={{ filemtime(public_path('js/dashboard/modales.js')) }}"></script>
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
    var preloads = [
        fetch('/actividades').catch(function(){}),
        fetch('/solicitudes?per_page=1').catch(function(){}),
        fetch('/trabajadores?per_page=1').catch(function(){}),
        fetch('/expedientes?per_page=1').catch(function(){}),
        fetch('/expedientes/listos-aprobacion').catch(function(){}),
        fetch('/solicitudes/por-mes').catch(function(){}),
        fetch('/solicitudes/vencimientos').catch(function(){}),
        fetch('/trabajadores-stats/dashboard').catch(function(){}),
        fetch('/notificaciones/no-leidas').catch(function(){}),
    ];
    // La caja negra solo es visible para admin/superadmin; se omite para el rol usuario.
    var rolPreload = window.SIGEJUB_ROL || '';
    if (rolPreload === 'admin' || rolPreload === 'superadmin') {
        preloads.push(fetch('/caja-negra?per_page=1').catch(function(){}));
    }
    Promise.all(preloads).then(function() {
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

// === Sincronización automática de la tasa USD/VES ===
// Consulta la API oficial (BCV) y actualiza la tasa automáticamente cuando su valor cambia.
// Frecuencia: configurada en .env con TASAS_INTERVALO_MINUTOS (TASAS_CACHE_TTL).
(function() {
    var INTERVALO_MIN = parseInt('{{ config('services.tasas_cambio.intervalo_minutos', 60) }}', 10) || 60;
    if (INTERVALO_MIN < 5) INTERVALO_MIN = 60;
    var INTERVALO_MS = INTERVALO_MIN * 60000;
    var sincronizando = false;

    async function sincronizarAutomatica() {
        if (sincronizando) return;
        sincronizando = true;
        try {
            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            var resp = await fetch('/tasas-cambio/sincronizar', {
                method: 'POST',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':token}
            });
            var data = await resp.json();
            if (data.estado === 'success' && data.cambiada) {
                window.dispatchEvent(new CustomEvent('sigejub:tasa-actualizada', { detail: data.tasa || null }));
            }
        } catch(e) {
            console.warn('SIGEJUB: error en sincronización automática de tasa:', e);
        } finally {
            sincronizando = false;
        }
    }

    async function iniciarAutoSincronizacion() {
        try {
            var resp = await fetch('/tasas-cambio/estado');
            var data = await resp.json();
            if (data.disponible && data.tasa) {
                var minutos = parseInt(data.tasa.minutos_desde || 0, 10);
                var apiCfg = data.tasa.api_configurada;
                // Si está desactualizada (más de 2h) o su consulta superó el intervalo, refrescar en silencio.
                if (apiCfg && (minutos >= 120 || minutos >= INTERVALO_MIN)) {
                    sincronizarAutomatica();
                }
            } else {
                // Sin tasa registrada: intentar obtenerla de la API en silencio.
                sincronizarAutomatica();
            }
        } catch(e) {
            console.warn('SIGEJUB: error al iniciar auto-sincronización de tasa:', e);
        }
        setInterval(sincronizarAutomatica, INTERVALO_MS);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(iniciarAutoSincronizacion, 8000);
    });
})();
</script>
</body>
</html>