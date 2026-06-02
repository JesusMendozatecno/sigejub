// ============================================
// dashboard.js — Panel principal del SIGEJUB
// Ubicación: Dashboard administrativo
// Responsabilidades:
//   - Inicialización del dashboard (tema oscuro, sidebar, contadores)
//   - Toggle del menú lateral y overlay en móviles
//   - Dropdown de usuario (cerrar sesión, perfil)
//   - Gestión completa de notificaciones (listar, marcar leídas, contador)
//   - Configuración global de tema (oscuro/claro) y color de acento
// ============================================

// ============================================
// INICIALIZACIÓN DEL DASHBOARD
// ============================================
function inicializarDashboard() {
    // Aplica el tema oscuro si el usuario lo tenía guardado en la sesión
    if (window.SIGEJUB_THEME === 'dark') {
        document.body.classList.add('dark-mode');
    }

    // Polling cada 30s para actualizar el contador de notificaciones no leídas
    cargarContadorNoLeidas();
    setInterval(cargarContadorNoLeidas, 30000);

    // ============================================
    // SIDEBAR — Apertura/cierre en escritorio y móvil
    // ============================================
    var sidebar = document.querySelector('.sidebar');
    var toggle = document.getElementById('sidebarToggle');
    var overlay = document.getElementById('sidebarOverlay');

    // Cierra sidebar y overlay simultáneamente
    function closeSidebar() { if (sidebar) sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); }

    // Botón hamburguesa: alterna visibilidad del menú lateral
    if (toggle) {
        toggle.addEventListener('click', function(e) { e.stopPropagation(); if (sidebar) sidebar.classList.toggle('open'); if (overlay) overlay.classList.toggle('show'); });
    }

    // Clic en el overlay (fondo oscuro) cierra el menú en móviles
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // En móviles, al hacer clic en una opción del menú se cierra automáticamente
    document.querySelectorAll('.sidebar-menu .menu-item').forEach(function(item) {
        item.addEventListener('click', function() { if (window.innerWidth < 768) closeSidebar(); });
    });
}

document.addEventListener('DOMContentLoaded', inicializarDashboard);

window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        cargarContadorNoLeidas();
    }
});

// ============================================
// DROPDOWN DE USUARIO
// ============================================
function toggleDropdown() {
    // Alterna la visibilidad del menú desplegable de usuario (perfil/cerrar sesión)
    document.getElementById('userDropdown')?.classList.toggle('open');
}

// Cierra el dropdown si se hace clic fuera de él
window.addEventListener('click', function(e) {
    var dropdown = document.getElementById('userDropdown');
    if (dropdown && !dropdown.contains(e.target)) dropdown.classList.remove('open');
});

// ============================================
// NOTIFICACIONES — Listado, contador, marcado como leído
// ============================================
var notifAbierto = false;  // Estado del panel de notificaciones (abierto/cerrado)

// Abre/cierra el menú desplegable de notificaciones y carga los datos si se abre
function toggleNotifDropdown() {
    var menu = document.getElementById('notifMenu');
    if (!menu) return;
    notifAbierto = !notifAbierto;
    menu.style.display = notifAbierto ? 'block' : 'none';
    if (notifAbierto) cargarNotificaciones();
}

// GET /notificaciones — Obtiene y renderiza la lista de notificaciones del usuario
async function cargarNotificaciones() {
    var list = document.getElementById('notifList');
    if (!list) return;
    try {
        var resp = await fetch('/notificaciones');
        if (!resp.ok) {
            list.innerHTML = '<p style="text-align:center;color:#ef4444;padding:20px;font-size:0.85rem;">Error al cargar</p>';
            return;
        }
        var data = await resp.json();
        // Muestra mensaje si no hay notificaciones
        if (!data.length) {
            list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:20px;font-size:0.85rem;">Sin notificaciones</p>';
            return;
        }
        // Renderiza cada notificación como un elemento del listado
        list.innerHTML = '';
        data.forEach(function(n) {
            var de = n.from_user ? n.from_user.name : 'Sistema';
            // Formatea la fecha al estilo: "15 ene — 10:30"
            var fecha = new Date(n.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            var div = document.createElement('div');
            div.className = 'notif-item' + (n.is_read ? '' : ' unread');
            // Al hacer clic se marca como leída
            div.onclick = function() { marcarLeida(n.id); };
            div.innerHTML = '<div class="notif-title">' + n.title + '</div><div class="notif-msg">' + n.message + '</div><div class="notif-time">' + de + ' — ' + fecha + '</div>';
            list.appendChild(div);
        });
    } catch (err) { console.error('Error al cargar notificaciones:', err); }
}

// GET /notificaciones/no-leidas — Consulta el contador de no leídas y actualiza el badge
async function cargarContadorNoLeidas() {
    try {
        var resp = await fetch('/notificaciones/no-leidas');
        if (!resp.ok) return;
        var data = await resp.json();
        var badge = document.getElementById('notifBadge');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.add('show');
            } else {
                badge.classList.remove('show');
            }
        }
    } catch (err) { console.error('Error al cargar contador:', err); }
}

// PUT /notificaciones/{id}/leer — Marca una notificación como leída y refresca la vista
async function marcarLeida(id) {
    try {
        await fetch('/notificaciones/' + id + '/leer', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
        cargarNotificaciones();
        cargarContadorNoLeidas();
    } catch (err) { console.error('Error al marcar leída:', err); }
}

// PUT /notificaciones/leer-todas — Marca TODAS como leídas de una sola vez
async function marcarTodasLeidas() {
    try {
        await fetch('/notificaciones/leer-todas', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
        cargarNotificaciones();
        cargarContadorNoLeidas();
        if (typeof mostrarToast !== 'undefined') mostrarToast('Todas las notificaciones marcadas como leídas.', 'info');
    } catch (err) { console.error('Error al marcar todas:', err); }
}

// Cierra el menú de notificaciones si el usuario hace clic fuera del contenedor
window.addEventListener('click', function(e) {
    var notif = document.getElementById('notifDropdown');
    if (notif && !notif.contains(e.target) && notifAbierto) {
        notifAbierto = false;
        var menu = document.getElementById('notifMenu');
        if (menu) menu.style.display = 'none';
    }
});

// ============================================
// CONFIGURACIÓN GLOBAL — Tema oscuro/claro y color de acento
// Se persisten en el backend vía /perfil/configuracion
// ============================================

// PUT /perfil/configuracion — Cambia entre tema claro y oscuro
window.cambiarTema = async function(tema) {
    // Aplica/remueve la clase 'dark-mode' del <body>
    document.body.classList.toggle('dark-mode', tema === 'dark');
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta?.content;
    try {
        await fetch('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ theme: tema }),
        });
    } catch (err) {}  // Error silencioso — la UI ya refleja el cambio
};

// PUT /perfil/configuracion — Cambia el color de acento (variable CSS --accent)
window.cambiarColor = async function(color) {
    // Actualiza la variable CSS global para el color de acento
    document.documentElement.style.setProperty('--accent', color);
    // Marca visualmente el preset seleccionado
    document.querySelectorAll('.color-preset').forEach(function(el) { el.classList.remove('selected'); });
    var preset = document.querySelector('.color-preset[data-color="' + color + '"]');
    if (preset) preset.classList.add('selected');
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta?.content;
    try {
        await fetch('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ accent_color: color }),
        });
    } catch (err) {}  // Error silencioso — la UI ya refleja el cambio
};
