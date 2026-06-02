// ============================================
// profile.js — Perfil de usuario y administración
// Ubicación: /perfil (página de perfil del usuario)
// Responsabilidades:
//   - Navegación por pestañas del perfil (información, seguridad, actividad, configuración, admin)
//   - Toast flotante para feedback visual
//   - Helper CSRF para peticiones fetch
//   - Edición de perfil (PUT /perfil/actualizar)
//   - Foto de perfil con Cropper.js (recorte cuadrado 400×400)
//   - Cambio de contraseña (PUT /perfil/password)
//   - Autenticación de doble factor (2FA)
//   - Gestión de sesiones activas (listar, cerrar, cerrar otras)
//   - Configuración de tema, idioma, color de acento
//   - Notificaciones y privacidad
//   - Log de actividad del usuario
//   - Panel de administración (usuarios, roles, actividad global, configuración global)
// ============================================

// ============================================
// INICIALIZACIÓN DEL PERFIL
// ============================================
function inicializarPerfil() {
    // Aplica tema oscuro si está configurado en la sesión
    if (window.SIGEJUB_PROFILE_THEME === 'dark') {
        document.body.classList.add('dark-mode');
    }
    // Define el color de acento desde la variable global inyectada por el backend
    document.documentElement.style.setProperty('--accent', window.SIGEJUB_ACCENT_COLOR || '#1a365d');
    // Si la pestaña de actividad está activa por defecto, carga el log
    if (document.getElementById('tab-actividad')?.classList.contains('active')) cargarActividad();
}

document.addEventListener('DOMContentLoaded', inicializarPerfil);

// Refrescar datos activos al volver con el botón Atrás del navegador (sin recarga completa)
window.addEventListener('pageshow', function(e) {
    if (e.persisted && document.querySelector('.profile-nav-item.active')) {
        var tabId = document.querySelector('.profile-nav-item.active').dataset.tab;
        if (tabId === 'tab-actividad' && typeof cargarActividad === 'function') cargarActividad();
        if (tabId === 'tab-configuracion' && typeof cargarConfigExtra === 'function') cargarConfigExtra();
    }
});

// ============================================
// NAVEGACIÓN POR PESTAÑAS DEL PERFIL
// ============================================
document.querySelectorAll('.profile-nav-item[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        // Remueve la clase activa de TODOS los botones y paneles
        document.querySelectorAll('.profile-nav-item').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.profile-tab').forEach(function(t) { t.classList.remove('active'); });
        // Activa solo el botón clickeado y su panel correspondiente
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
        // Carga dinámica de datos según la pestaña seleccionada
        if (btn.dataset.tab === 'tab-actividad') cargarActividad();
        if (btn.dataset.tab === 'tab-seguridad') cargarSesiones();
        if (btn.dataset.tab === 'tab-admin') cargarAdminUsuarios();
    });
});

// ============================================
// TOAST FLOTANTE — Feedback visual no obstructivo
// ============================================
function mostrarToast(msg, tipo) {
    // Evita duplicados: si ya hay un toast visible, lo remueve antes de crear otro
    var exists = document.querySelector('.toast-float');
    if (exists) exists.remove();
    // Mapa de colores según el tipo de mensaje
    var colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb' };
    var div = document.createElement('div');
    div.className = 'toast-float';
    // Estilos inline del toast fijo en la esquina inferior derecha
    div.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:14px 22px;border-radius:12px;color:white;font-weight:600;font-size:0.85rem;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:99999;animation:slideUp 0.3s ease;max-width:400px;';
    div.style.background = colors[tipo] || colors.info;
    div.textContent = msg;
    document.body.appendChild(div);
    // Auto-destrucción después de 3 segundos con transición de opacidad
    setTimeout(function() { div.style.opacity = '0'; div.style.transition = 'opacity 0.3s'; setTimeout(function() { div.remove(); }, 300); }, 3000);
}
// Inyecta la animación CSS de entrada del toast
var styleToast = document.createElement('style');
styleToast.textContent = '@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }';
document.head.appendChild(styleToast);

// ============================================
// CSRF HELPER — Reutilizable para todas las peticiones fetch
// ============================================
function csrfToken() { return document.querySelector('meta[name="csrf-token"]').content; }
function api(url, opts) {
    opts = opts || {};
    // Combina headers por defecto (CSRF + JSON) con los que envíe el llamante
    return fetch(url, {
        headers: Object.assign({ 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }, opts.headers || {}),
        ...opts,
    });
}

// ============================================
// TAB: PERFIL — Edición de datos personales
// ============================================
document.getElementById('formProfile')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    fd.append('_method', 'PUT');  // Laravel spoofing: POST → PUT
    try {
        var r = await api('/perfil/actualizar', { method: 'POST', body: fd });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error al guardar', 'error'); return; }
        mostrarToast(d.message, 'success');
        // Actualiza el nombre mostrado en el encabezado del perfil sin recargar la página
        document.querySelector('.profile-name').textContent = fd.get('name');
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

// ============================================
// AVATAR / CROPPER.JS — Recorte y carga de foto de perfil
// ============================================
var cropper = null;  // Instancia activa de Cropper.js

// Al seleccionar un archivo, lo carga en el modal de recorte
function onAvatarSelect(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        var container = document.getElementById('cropContainer');
        container.innerHTML = '<img id="cropImage" src="' + ev.target.result + '">';
        document.getElementById('cropModal').classList.add('show');
        // Destruye instancia previa antes de crear una nueva
        if (cropper) cropper.destroy();
        // Espera a que la imagen se renderice antes de inicializar Cropper.js
        setTimeout(function() {
            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,       // Cuadrado perfecto
                viewMode: 1,          // No permite arrastrar la imagen fuera del canvas
                dragMode: 'move',     // Arrastrar para reposicionar, no para seleccionar
                cropBoxResizable: true,
                cropBoxMovable: true,
            });
        }, 200);
    };
    reader.readAsDataURL(file);
    e.target.value = '';  // Resetea el input file para permitir seleccionar la misma imagen otra vez
}

// Cierra el modal de recorte y limpia la instancia de Cropper
function cerrarCrop() {
    document.getElementById('cropModal').classList.remove('show');
    if (cropper) { cropper.destroy(); cropper = null; }
}

// POST /perfil/avatar — Recorta la imagen (400×400) y la sube al servidor
async function confirmarCrop() {
    if (!cropper) return;
    var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
    if (!canvas) { mostrarToast('Error al procesar la imagen', 'error'); return; }
    canvas.toBlob(async function(blob) {
        var fd = new FormData();
        fd.append('avatar', blob, 'avatar.jpg');
        fd.append('_token', csrfToken());
        try {
            var r = await fetch('/perfil/avatar', { method: 'POST', body: fd });
            var text = await r.text();
            // Intenta parsear la respuesta como JSON; si falla, muestra el error crudo
            var d;
            try { d = JSON.parse(text); } catch (e) { console.error('Respuesta no JSON:', text.substring(0,200)); mostrarToast('Error del servidor', 'error'); return; }
            if (!r.ok) { mostrarToast(d.message || 'Error al subir', 'error'); return; }
            // Refresca el avatar agregando un timestamp para evitar caché del navegador
            document.getElementById('profileAvatarImg').src = d.avatar + '?t=' + Date.now();
            mostrarToast(d.message, 'success');
            cerrarCrop();
        } catch (err) { console.error('Error avatar:', err); mostrarToast('Error de conexión', 'error'); }
    }, 'image/jpeg', 0.9);
}

// DELETE /perfil/avatar — Elimina la foto de perfil y actualiza el DOM sin recargar
async function eliminarAvatar() {
    if (!confirm('¿Eliminar foto de perfil?')) return;
    try {
        var r = await fetch('/perfil/avatar', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        var img = document.getElementById('profileAvatarImg');
        if (img) {
            img.remove();
            var wrap = document.querySelector('.avatar-wrap');
            if (wrap) {
                var placeholder = document.createElement('i');
                placeholder.className = 'fas fa-user fa-3x avatar-placeholder';
                placeholder.id = 'profileAvatarPlaceholder';
                wrap.insertBefore(placeholder, wrap.querySelector('.avatar-overlay'));
            }
        }
        mostrarToast(d.message, 'success');
    } catch (err) { console.error('Error eliminar avatar:', err); mostrarToast('Error de conexión', 'error'); }
}

// ============================================
// TAB: SEGURIDAD — Contraseña, 2FA y sesiones activas
// ============================================

// PUT /perfil/password — Cambio de contraseña
document.getElementById('formPassword')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    fd.append('_method', 'PUT');
    try {
        var r = await api('/perfil/password', { method: 'POST', body: fd });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error', 'error'); return; }
        mostrarToast(d.message, 'success');
        e.target.reset();  // Limpia el formulario tras el éxito
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

// PUT /perfil/2fa — Activa/desactiva la autenticación de doble factor
async function toggleFA() {
    var tog = document.getElementById('toggle2FA');
    var nuevo = !tog.classList.contains('active');  // Calcula el estado contrario
    try {
        var r = await api('/perfil/2fa', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ enabled: nuevo }) });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        tog.classList.toggle('active', d.enabled);
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

// GET /perfil/sesiones — Obtiene y renderiza la lista de sesiones activas
async function cargarSesiones() {
    var el = document.getElementById('sessionList');
    try {
        var r = await api('/perfil/sesiones');
        var sessions = await r.json();
        if (!sessions.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin sesiones activas</p>'; return; }
        // Renderiza cada sesión; marca la actual con un badge y permite cerrar las demás
        el.innerHTML = sessions.map(function(s) {
            return '<div class="session-item' + (s.is_current ? ' current' : '') + '"><div class="session-device"><i class="fas ' + (s.is_current ? 'fa-mobile-screen-button' : 'fa-laptop') + '"></i><div class="session-info"><p>' + (s.user_agent ? s.user_agent.substring(0, 60) + '...' : 'Dispositivo desconocido') + (s.is_current ? ' <span class="session-badge current">Actual</span>' : '') + '</p><span>' + s.ip_address + ' — ' + (s.last_activity_humans || 'desconocido') + '</span></div></div>' + (!s.is_current ? '<button class="btn btn-danger btn-sm" onclick="eliminarSesion(\'' + s.id + '\')">Cerrar</button>' : '') + '</div>';
        }).join('');
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar sesiones</p>'; }
}

// DELETE /perfil/sesiones/{id} — Cierra una sesión específica
async function eliminarSesion(id) {
    try {
        var r = await api('/perfil/sesiones/' + id, { method: 'DELETE' });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarSesiones();  // Refresca el listado tras eliminar
    } catch (err) { mostrarToast('Error', 'error'); }
}

// POST /perfil/sesiones/cerrar-otras — Cierra todas las sesiones excepto la actual
async function cerrarOtrasSesiones() {
    if (!confirm('¿Cerrar sesión en todos los otros dispositivos?')) return;
    try {
        var r = await api('/perfil/sesiones/cerrar-otras', { method: 'POST' });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarSesiones();
    } catch (err) { mostrarToast('Error', 'error'); }
}

// ============================================
// TAB: CONFIGURACIÓN — Tema, idioma, color, notificaciones y privacidad
// ============================================

// PUT /perfil/configuracion — Cambia entre tema claro y oscuro
async function cambiarTema(tema) {
    document.querySelectorAll('.theme-option').forEach(function(el) { el.classList.remove('selected'); });
    document.querySelector('.theme-option[data-theme="' + tema + '"]')?.classList.add('selected');
    document.body.classList.toggle('dark-mode', tema === 'dark');
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ theme: tema }) });
    } catch (err) {}  // Error silencioso — la UI ya refleja el cambio
}

// PUT /perfil/configuracion — Cambia el idioma de la interfaz
async function cambiarIdioma(lang) {
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ language: lang }) });
        mostrarToast('Idioma actualizado', 'success');
    } catch (err) {}
}

// PUT /perfil/configuracion — Cambia el color de acento y lo persiste
async function cambiarColor(color) {
    document.querySelectorAll('.color-preset').forEach(function(el) { el.classList.remove('selected'); });
    document.querySelector('.color-preset[data-color="' + color + '"]')?.classList.add('selected');
    document.documentElement.style.setProperty('--accent', color);
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ accent_color: color }) });
        mostrarToast('Color actualizado', 'success');
    } catch (err) {}
}

// PUT /perfil/notificaciones — Guarda preferencias de notificaciones (email, sistema)
async function guardarNotificaciones() {
    try {
        await api('/perfil/notificaciones', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ notification_email: document.getElementById('notifEmail').value, notification_system: document.getElementById('notifSystem').value }) });
    } catch (err) {}
}

// PUT /perfil/notificaciones — Alterna la visibilidad pública del perfil
async function togglePrivacidad() {
    var tog = document.getElementById('togglePrivacy');
    var nuevo = !tog.classList.contains('active');
    tog.classList.toggle('active', nuevo);
    try {
        await api('/perfil/notificaciones', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ profile_public: nuevo }) });
        mostrarToast(nuevo ? 'Perfil público' : 'Perfil privado', 'success');
    } catch (err) { tog.classList.toggle('active', !nuevo); }  // Revierte el toggle si falla la petición
}

// ============================================
// TAB: ACTIVIDAD — Estadísticas y log de actividad del usuario
// ============================================
async function cargarActividad() {
    var list = document.getElementById('activityList');
    try {
        // Dos peticiones en paralelo: estadísticas generales + actividad reciente
        var resp = await Promise.all([api('/perfil/estadisticas'), api('/perfil/actividad')]);
        var stats = await resp[0].json();
        var acts = await resp[1].json();

        // Renderiza las tarjetas de estadísticas del encabezado
        document.getElementById('statTrabajadores').textContent = stats.total_trabajadores_capturados || 0;
        document.getElementById('statSolicitudes').textContent = stats.total_solicitudes_gestionadas || 0;
        document.getElementById('statExpedientes').textContent = stats.total_expedientes_movidos || 0;
        document.getElementById('statMiembro').textContent = stats.miembro_desde || '—';
        document.getElementById('statUltimoAcceso').textContent = stats.ultimo_acceso || '—';
        document.getElementById('statUltimaIP').textContent = stats.ultima_ip || '—';

        if (!acts.length) { list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">No hay actividad registrada</p>'; return; }

        // Mapa de iconos según la acción (created/updated/deleted) y el tipo de sujeto
        // Cada entrada: [icono FontAwesome, color CSS]
        var icons = {
            created: { trabajador: ['users','green'], solicitud: ['file-lines','blue'], usuario: ['user-plus','purple'], documento: ['file','blue'], notificacion: ['bell','purple'] },
            updated: { trabajador: ['pen','orange'], solicitud: ['pen-to-square','orange'], usuario: ['pen-to-square','orange'] },
            deleted: { trabajador: ['trash-can','red'], solicitud: ['circle-xmark','red'] }
        };
        var fallback = ['circle','blue'];

        // Renderiza cada ítem de actividad con su icono, descripción y fecha
        list.innerHTML = acts.map(function(a) {
            var iconData = (icons[a.action] && icons[a.action][a.subject_type]) || fallback;
            var fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            return '<div class="activity-item"><div class="activity-icon ' + iconData[1] + '"><i class="fas fa-' + iconData[0] + '"></i></div><div class="activity-text"><p>' + (a.description || a.action + ' ' + a.subject_type) + '</p><span>' + fecha + '</span></div></div>';
        }).join('');
    } catch (err) { list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar actividad</p>'; }
}

// ============================================
// TAB: ADMIN — Gestión de usuarios, roles y actividad global
// ============================================
var adminTabActual = 'admin-usuarios';

// Cambia entre sub-pestañas del panel de administración
function cambiarAdminTab(tab) {
    adminTabActual = tab;
    document.querySelectorAll('.admin-subtab').forEach(function(t) { t.style.display = 'none'; });
    document.querySelectorAll('.admin-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById(tab).style.display = 'block';
    document.querySelector('.admin-tab-btn[data-admin-tab="' + tab + '"]')?.classList.add('active');
    if (tab === 'admin-usuarios') cargarAdminUsuarios();
    if (tab === 'admin-actividad') cargarAdminActividad();
}

// GET /perfil/admin/usuarios — Lista usuarios con filtros de búsqueda y rol
async function cargarAdminUsuarios() {
    var search = document.getElementById('adminSearch').value;
    var role = document.getElementById('adminRoleFilter').value;
    var tbody = document.getElementById('adminUsersBody');
    try {
        var r = await api('/perfil/admin/usuarios?search=' + encodeURIComponent(search) + '&role=' + role);
        var data = await r.json();
        if (!data.data || !data.data.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Sin usuarios</td></tr>'; return; }
        // Renderiza cada fila: avatar inicial, nombre, email, selector de rol, fecha, acciones
        tbody.innerHTML = data.data.map(function(u) {
            return '<tr><td><div style="display:flex;align-items:center;gap:8px;"><div style="width:28px;height:28px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:#1e3a8a;overflow:hidden;">' + (u.avatar ? '<img src="' + window.SIGEJUB_STORAGE_URL + '/' + u.avatar + '" style="width:100%;height:100%;object-fit:cover;">' : u.name.charAt(0).toUpperCase()) + '</div>' + u.name + '</div></td><td>' + u.email + '</td><td><select class="form-input" style="width:auto;padding:4px 8px;font-size:0.75rem;" onchange="cambiarRolUsuario(' + u.id + ', this.value)"><option value="analista"' + (u.role === 'analista' ? ' selected' : '') + '>Analista</option><option value="admin"' + (u.role === 'admin' ? ' selected' : '') + '>Admin</option></select></td><td style="font-size:0.75rem;color:#94a3b8;">' + new Date(u.created_at).toLocaleDateString('es-ES') + '</td><td style="display:flex;gap:6px;"><button class="btn btn-primary btn-sm" onclick="abrirModalNotificacion(' + u.id + ', \'' + u.name.replace(/'/g, "\\'") + '\')" title="Enviar mensaje"><i class="fas fa-message"></i></button><button class="btn btn-danger btn-sm" onclick="eliminarUsuario(' + u.id + ')"' + (u.id === window.SIGEJUB_USER_ID ? ' disabled style="opacity:0.4;"' : '') + '>Eliminar</button></td></tr>';
        }).join('');
    } catch (err) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Error al cargar</td></tr>'; }
}

// PUT /perfil/admin/usuarios/{id} — Cambia el rol de un usuario
async function cambiarRolUsuario(id, role) {
    try {
        var r = await api('/perfil/admin/usuarios/' + id, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ role: role }) });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

// DELETE /perfil/admin/usuarios/{id} — Elimina un usuario permanentemente
async function eliminarUsuario(id) {
    if (!confirm('¿Eliminar este usuario permanentemente?')) return;
    try {
        var r = await api('/perfil/admin/usuarios/' + id, { method: 'DELETE' });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarAdminUsuarios();  // Refresca la tabla tras eliminar
    } catch (err) { mostrarToast('Error', 'error'); }
}

// ============================================
// MODAL DE NOTIFICACIONES (Admin) — Enviar notificación a un usuario
// ============================================
function abrirModalNotificacion(userId, userName) {
    document.getElementById('notifUserId').value = userId;
    document.getElementById('notifUserName').textContent = userName;
    document.getElementById('formEnviarNotificacion').reset();
    document.getElementById('modalEnviarNotificacion').style.display = 'flex';
}

function cerrarModalNotificacion() {
    document.getElementById('modalEnviarNotificacion').style.display = 'none';
}

// POST /notificaciones — Envía una notificación desde el admin a un usuario específico
document.getElementById('formEnviarNotificacion')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var btn = this.querySelector('.btn-primary');
    btn.disabled = true; btn.textContent = 'Enviando...';  // Feedback visual de carga
    try {
        var resp = await fetch('/notificaciones', { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
        var data = await resp.json();
        if (!resp.ok) throw data;
        mostrarToast(data.message, 'success');
        cerrarModalNotificacion();
    } catch (err) { mostrarToast(err.message || 'Error al enviar', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Enviar'; }  // Restaura el botón siempre
});

// Cierra el modal si se hace clic en el fondo oscuro
window.addEventListener('click', function(e) {
    var modal = document.getElementById('modalEnviarNotificacion');
    if (e.target === modal) cerrarModalNotificacion();
});

// ============================================
// ACTIVIDAD GLOBAL (Admin) — Log de acciones de todos los usuarios
// ============================================
async function cargarAdminActividad() {
    var el = document.getElementById('adminActivityList');
    try {
        var r = await api('/perfil/admin/actividad');
        var acts = await r.json();
        if (!acts.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin actividad global</p>'; return; }
        // Renderiza cada entrada: descripción + nombre del usuario + fecha formateada
        el.innerHTML = acts.map(function(a) {
            var fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            return '<div class="activity-item"><div class="activity-text"><p>' + a.description + '</p><span>' + (a.user ? a.user.name : 'Sistema') + ' — ' + fecha + '</span></div></div>';
        }).join('');
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar</p>'; }
}

// ============================================
// CONFIGURACIÓN GLOBAL (Admin) — Nombre app, tema por defecto, modo mantenimiento
// ============================================
document.getElementById('formGlobalConfig')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    var data = { app_name: fd.get('app_name'), default_theme: fd.get('default_theme'), maintenance_mode: fd.has('maintenance_mode') };
    try {
        var r = await api('/perfil/admin/config-global', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify(data) });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
});
