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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    
</head>
<body>

<header class="top-bar">
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
                <i data-lucide="bell" size="20"></i>
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
                        <i data-lucide="user" size="20"></i>
                    @endif
                </div>
                <div style="text-align: left; display: block;">
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; line-height: 1; color: #1e293b;">{{ Auth::user()->name }}</p>
                </div>
                <i data-lucide="chevron-down" size="14" style="color: #64748b;"></i>
            </div>

            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <p style="margin:0; font-size: 0.85rem; font-weight: bold; color: #1e293b;">{{ Auth::user()->name }}</p>
                    <span style="font-size: 0.75rem; color: #64748b;">{{ Auth::user()->email }}</span>
                </div>

                <a href="{{ route('usuarios.user') }}" class="dropdown-item">
                    <i data-lucide="settings" size="16"></i> Gestionar cuenta
                </a>

                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 4px 0;">

                <form method="POST" action="{{ route('logout') }}" onsubmit="mostrarCargando('Cerrando sesión...')" style="margin: 0;">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i data-lucide="log-out" size="16"></i> Cerrar sesión
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
                <li class="menu-item active" data-target="inicio"><i data-lucide="home" size="18"></i> Inicio</li>
                <li class="menu-item" data-target="trabajadores"><i data-lucide="users" size="18"></i> Trabajadores</li>
                <li class="menu-item" data-target="solicitudes"><i data-lucide="file-text" size="18"></i> Solicitudes</li>
                <li class="menu-item" data-target="expedientes"><i data-lucide="folder" size="18"></i> Expedientes</li>
                <li class="menu-item" data-target="prestaciones"><i data-lucide="wallet" size="18"></i> Prestaciones</li>
                <li class="menu-item" data-target="reportes"><i data-lucide="bar-chart-3" size="18"></i> Reportes</li>
                @if(Auth::user()->role === 'admin')
                <li class="menu-item" data-target="administrar"><i data-lucide="shield" size="18"></i> Administrar</li>
                <li class="menu-item" data-target="caja-negra"><i data-lucide="hard-drive" size="18"></i> Historial</li>
                @endif

            </ul>
        </nav>
    </aside>

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
            <div id="administrar" class="content-section">
                @include('dashboard.secciones.administrar')
            </div>
            <div id="caja-negra" class="content-section">
                @include('dashboard.secciones.caja-negra')
            </div>
            @endif

        </div>
    </main>

</div>

<style>
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
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('open');
    }

    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    // === NOTIFICACIONES ===
    let notifAbierto = false;

    function toggleNotifDropdown() {
        const menu = document.getElementById('notifMenu');
        if (!menu) return;
        notifAbierto = !notifAbierto;
        menu.style.display = notifAbierto ? 'block' : 'none';
        if (notifAbierto) cargarNotificaciones();
    }

    async function cargarNotificaciones() {
        const list = document.getElementById('notifList');
        if (!list) return;
        try {
            const resp = await fetch('/notificaciones');
            if (!resp.ok) {
                const text = await resp.text();
                console.error('Error en notificaciones:', resp.status, text.substring(0, 200));
                list.innerHTML = '<p style="text-align:center;color:#ef4444;padding:20px;font-size:0.85rem;">Error al cargar</p>';
                return;
            }
            const data = await resp.json();
            if (!data.length) {
                list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:20px;font-size:0.85rem;">Sin notificaciones</p>';
                return;
            }
            list.innerHTML = '';
            data.forEach(n => {
                const de = n.from_user ? n.from_user.name : 'Sistema';
                const fecha = new Date(n.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
                const div = document.createElement('div');
                div.className = 'notif-item' + (n.is_read ? '' : ' unread');
                div.onclick = () => marcarLeida(n.id);
                div.innerHTML = `
                    <div class="notif-title">${n.title}</div>
                    <div class="notif-msg">${n.message}</div>
                    <div class="notif-time">${de} — ${fecha}</div>
                `;
                list.appendChild(div);
            });
        } catch (err) {
            console.error('Error al cargar notificaciones:', err);
        }
    }

    async function cargarContadorNoLeidas() {
        try {
            const resp = await fetch('/notificaciones/no-leidas');
            if (!resp.ok) {
                const text = await resp.text();
                console.error('Error en notificaciones/no-leidas:', resp.status, text.substring(0, 200));
                return;
            }
            const data = await resp.json();
            const badge = document.getElementById('notifBadge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.add('show');
                } else {
                    badge.classList.remove('show');
                }
            }
        } catch (err) {
            console.error('Error al cargar contador:', err);
        }
    }

    async function marcarLeida(id) {
        try {
            await fetch(`/notificaciones/${id}/leer`, { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
            cargarNotificaciones();
            cargarContadorNoLeidas();
        } catch (err) {
            console.error('Error al marcar leída:', err);
        }
    }

    async function marcarTodasLeidas() {
        try {
            await fetch('/notificaciones/leer-todas', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
            cargarNotificaciones();
            cargarContadorNoLeidas();
            mostrarToast('Todas las notificaciones marcadas como leídas.', 'info');
        } catch (err) {
            console.error('Error al marcar todas:', err);
        }
    }

    // Cerrar menú de notificaciones al hacer clic fuera
    window.addEventListener('click', function(e) {
        const notif = document.getElementById('notifDropdown');
        if (notif && !notif.contains(e.target) && notifAbierto) {
            notifAbierto = false;
            document.getElementById('notifMenu').style.display = 'none';
        }
    });

    // Cargar contador al inicio y cada 30 segundos
    document.addEventListener('DOMContentLoaded', () => {
        cargarContadorNoLeidas();
        setInterval(cargarContadorNoLeidas, 30000);
    });
</script>

<script src="{{ asset('js/secciones/trabajador.js') }}"></script>
<script src="{{ asset('js/sesion2.js') }}"></script>

@include('partials.toast')

@if(session('success'))
    <script>mostrarToast('{{ session('success') }}', 'success');</script>
@endif
@if($errors->any())
    <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
@endif
</body>
</html>