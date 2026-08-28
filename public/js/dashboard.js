/**
 * dashboard.js — Script principal del dashboard.
 * Gestiona navegación por pestañas, sidebar, carga de secciones y modales.
 */

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

window.limpiarCacheSigejub = function() {
    var keys = [];
    for (var i = 0; i < localStorage.length; i++) {
        var key = localStorage.key(i);
        if (key && key.startsWith('sigejub_cache_')) keys.push(key);
    }
    keys.forEach(function(k) { localStorage.removeItem(k); });
};

function inicializarDashboard() {
    if (window.SIGEJUB_THEME === 'dark') {
        document.body.classList.add('dark-mode');
    }
    if (window.SIGEJUB_THEME === 'modern') {
        document.body.classList.add('theme-modern');
    }

    var fontClasses = ['font-sistema', 'font-moderna', 'font-condensada', 'font-mono', 'font-serif'];
    var font = window.SIGEJUB_FONT || 'sistema';
    document.body.classList.remove.apply(document.body.classList, fontClasses);
    if (font !== 'sistema') document.body.classList.add('font-' + font);

    // Notification counter
    cargarContadorNoLeidas();
    setInterval(cargarContadorNoLeidas, 30000);

    // Sidebar toggle
    var sidebar = document.querySelector('.sidebar');
    var toggle = document.getElementById('sidebarToggle');
    var overlay = document.getElementById('sidebarOverlay');
    function closeSidebar() { if (sidebar) sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); }
    if (toggle) {
        toggle.addEventListener('click', function(e) { e.stopPropagation(); if (sidebar) sidebar.classList.toggle('open'); if (overlay) overlay.classList.toggle('show'); });
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);
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

// === USER DROPDOWN ===
function toggleDropdown() {
    document.getElementById('userDropdown')?.classList.toggle('open');
}

window.addEventListener('click', function(e) {
    var dropdown = document.getElementById('userDropdown');
    if (dropdown && !dropdown.contains(e.target)) dropdown.classList.remove('open');
});

// === NOTIFICACIONES ===
var notifAbierto = false;

function toggleNotifDropdown() {
    var menu = document.getElementById('notifMenu');
    if (!menu) return;
    notifAbierto = !notifAbierto;
    menu.style.display = notifAbierto ? 'block' : 'none';
    if (notifAbierto) cargarNotificaciones();
}

async function cargarNotificaciones() {
    var list = document.getElementById('notifList');
    if (!list) return;
    try {
        var result = await cachedFetch('/notificaciones', { ttl: 30000 });
        var data = result.data;
        if (!data.length) {
            list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:20px;font-size:0.85rem;">Sin notificaciones</p>';
            return;
        }
        list.innerHTML = '';
        data.forEach(function(n) {
            var de = n.from_user ? n.from_user.nombre : 'Sistema';
            var fecha = new Date(n.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            var div = document.createElement('div');
            div.className = 'notif-item' + (n.leida ? '' : ' unread');
            div.onclick = function() { marcarLeida(n.id); };
            div.innerHTML = '<div class="notif-title">' + escaparHTML(n.titulo) + '</div><div class="notif-msg">' + escaparHTML(n.mensaje) + '</div><div class="notif-time">' + escaparHTML(de) + ' — ' + escaparHTML(fecha) + '</div>';
            list.appendChild(div);
        });
    } catch (err) { console.error('Error al cargar notificaciones:', err); }
}

async function cargarContadorNoLeidas() {
    try {
        var result = await cachedFetch('/notificaciones/no-leidas', { ttl: 30000 });
        var data = result.data;
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

async function marcarLeida(id) {
    try {
        await fetch('/notificaciones/' + id + '/leer', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
        cargarNotificaciones();
        cargarContadorNoLeidas();
    } catch (err) { console.error('Error al marcar leída:', err); }
}

async function marcarTodasLeidas() {
    try {
        await fetch('/notificaciones/leer-todas', { method: 'PUT', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
        cargarNotificaciones();
        cargarContadorNoLeidas();
        if (typeof mostrarToast !== 'undefined') mostrarToast('Todas las notificaciones marcadas como leídas.', 'info');
    } catch (err) { console.error('Error al marcar todas:', err); }
}

// Cerrar menú de notificaciones al hacer clic fuera
window.addEventListener('click', function(e) {
    var notif = document.getElementById('notifDropdown');
    if (notif && !notif.contains(e.target) && notifAbierto) {
        notifAbierto = false;
        var menu = document.getElementById('notifMenu');
        if (menu) menu.style.display = 'none';
    }
});

// === GLOBAL THEME CONFIG ===
window.cambiarTema = async function(tema) {
    // Smooth transition: add a class that enables transitions on all elements
    document.documentElement.classList.add('theme-switching');

    document.body.classList.remove('dark-mode', 'theme-modern');
    if (tema === 'dark') document.body.classList.add('dark-mode');
    if (tema === 'modern') document.body.classList.add('theme-modern');

    // Save to localStorage for instant load on next visit
    try { localStorage.setItem('sigejub_theme', tema); } catch (e) {}

    // Remove transition class after animation completes
    setTimeout(function() {
        document.documentElement.classList.remove('theme-switching');
    }, 400);

    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta?.content;
    try {
        await fetch('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ tema: tema }),
        });
    } catch (err) {}
};

window.cambiarColor = async function(color) {
    document.documentElement.style.setProperty('--accent', color);
    document.querySelectorAll('.color-preset').forEach(function(el) { el.classList.remove('selected'); });
    var preset = document.querySelector('.color-preset[data-color="' + color + '"]');
    if (preset) preset.classList.add('selected');
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta?.content;
    try {
        await fetch('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ color_acento: color }),
        });
    } catch (err) {}
};

// === INACTIVITY AUTO-LOGOUT (idle timer) ===
(function() {
    var INACTIVITY_TIMEOUT = 15 * 60;
    var WARNING_TIME = 14 * 60;
    var PING_INTERVAL = 60;
    var idleSeconds = 0;
    var warningShown = false;
    var inactivityTimer = null;
    var pingTimer = null;
    var token = document.querySelector('meta[name="csrf-token"]')?.content;

    function resetIdleTimer() {
        idleSeconds = 0;
        warningShown = false;
        clearInterval(inactivityTimer);
        clearInterval(pingTimer);
        inactivityTimer = setInterval(tick, 1000);
        pingTimer = setInterval(sendPing, PING_INTERVAL * 1000);
    }

    function tick() {
        idleSeconds++;
        if (idleSeconds >= WARNING_TIME && !warningShown) {
            warningShown = true;
            if (typeof mostrarToast === 'function') {
                mostrarToast('Tu sesión está por expirar por inactividad. Mueve el mouse o presiona una tecla para continuar.', 'warning');
            }
        }
        if (idleSeconds >= INACTIVITY_TIMEOUT) {
            clearInterval(inactivityTimer);
            clearInterval(pingTimer);
            if (typeof mostrarToast === 'function') {
                mostrarToast('Cerrando sesión por inactividad...', 'info');
            }
            setTimeout(function() {
                fetch('/logout', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token }
                }).then(function() {
                    window.location.href = '/login';
                }).catch(function() {
                    window.location.href = '/login';
                });
            }, 1500);
        }
    }

    function sendPing() {
        if (idleSeconds === 0) {
            fetch('/actividad/ping', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' }
            }).catch(function() {});
        }
    }

    var activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    activityEvents.forEach(function(ev) {
        document.addEventListener(ev, resetIdleTimer, { passive: true });
    });

    resetIdleTimer();
})();
