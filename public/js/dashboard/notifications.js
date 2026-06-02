// ============================================================
// notifications.js — Polling de notificaciones no leídas,
// alternancia del menú desplegable y marcado como leídas.
// Dependencias: dashboard.js (contiene toggleNotifDropdown,
// marcarTodasLeidas).
// ============================================================

(function() {
    'use strict';

    const BADGE = document.getElementById('notifBadge');
    const LIST  = document.getElementById('notifList');
    const MENU  = document.getElementById('notifMenu');
    let pollTimer = null;

    /**
     * Consulta al servidor las notificaciones no leídas y actualiza
     * el badge y la lista del menú desplegable.
     */
    async function pollNotificaciones() {
        if (!BADGE) return;
        try {
            const resp = await fetch('/notificaciones/no-leidas');
            if (!resp.ok) return;
            const data = await resp.json();
            const count = data.count || 0;

            // Actualiza el badge numérico en la campana
            BADGE.textContent = count;
            BADGE.classList.toggle('show', count > 0);

            // Actualiza la lista del menú si está abierto
            if (MENU && MENU.style.display !== 'none' && data.items) {
                renderLista(data.items);
            }
        } catch (_) { /* fallo silencioso — no romper la UI */ }
    }

    /**
     * Renderiza los ítems de notificaciones en el menú.
     */
    function renderLista(items) {
        if (!LIST) return;
        if (!items || !items.length) {
            LIST.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:20px;font-size:0.85rem;">Sin notificaciones</p>';
            return;
        }
        LIST.innerHTML = items.map(function(n) {
            const cls = n.leida === false ? 'notif-item unread' : 'notif-item';
            return '<div class="' + cls + '" onclick="marcarLeida(' + n.id + ')">' +
                '<div class="notif-title">' + (n.titulo || '') + '</div>' +
                (n.mensaje ? '<div class="notif-msg">' + n.mensaje + '</div>' : '') +
                '<div class="notif-time">' + (n.hace || '') + '</div>' +
                '</div>';
        }).join('');
    }

    // Inicia el polling cada 30 segundos si el badge existe en la página
    if (BADGE) {
        pollNotificaciones();
        pollTimer = setInterval(pollNotificaciones, 30000);
    }

    // Limpia el intervalo al salir (SPA virtual)
    window.addEventListener('beforeunload', function() {
        if (pollTimer) clearInterval(pollTimer);
    });
})();
