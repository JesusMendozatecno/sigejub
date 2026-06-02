// === PROFILE.JS - SIGEJUB ===

function inicializarPerfil() {
    if (window.SIGEJUB_PROFILE_THEME === 'dark') {
        document.body.classList.add('dark-mode');
    }
    document.documentElement.style.setProperty('--accent', window.SIGEJUB_ACCENT_COLOR || '#1a365d');
    if (document.getElementById('tab-actividad')?.classList.contains('active')) cargarActividad();
}

document.addEventListener('DOMContentLoaded', inicializarPerfil);

window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        window.location.reload();
    }
});

// === TAB NAVIGATION ===
document.querySelectorAll('.profile-nav-item[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.profile-nav-item').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.profile-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
        if (btn.dataset.tab === 'tab-actividad') cargarActividad();
        if (btn.dataset.tab === 'tab-seguridad') cargarSesiones();
        if (btn.dataset.tab === 'tab-admin') cargarAdminUsuarios();
    });
});

// === TOAST ===
function mostrarToast(msg, tipo) {
    var exists = document.querySelector('.toast-float');
    if (exists) exists.remove();
    var colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb' };
    var div = document.createElement('div');
    div.className = 'toast-float';
    div.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:14px 22px;border-radius:12px;color:white;font-weight:600;font-size:0.85rem;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:99999;animation:slideUp 0.3s ease;max-width:400px;';
    div.style.background = colors[tipo] || colors.info;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(function() { div.style.opacity = '0'; div.style.transition = 'opacity 0.3s'; setTimeout(function() { div.remove(); }, 300); }, 3000);
}
var styleToast = document.createElement('style');
styleToast.textContent = '@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }';
document.head.appendChild(styleToast);

// === CSRF HELPER ===
function csrfToken() { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; }
function api(url, opts) {
    opts = opts || {};
    return fetch(url, {
        headers: Object.assign({ 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }, opts.headers || {}),
        ...opts,
    });
}

// === TAB: PERFIL ===
document.getElementById('formProfile')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    fd.append('_method', 'PUT');
    try {
        var r = await api('/perfil/actualizar', { method: 'POST', body: fd });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error al guardar', 'error'); return; }
        mostrarToast(d.message, 'success');
        document.querySelector('.profile-name').textContent = fd.get('name');
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

// === AVATAR CROPPER ===
var cropper = null;

function onAvatarSelect(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        var container = document.getElementById('cropContainer');
        container.innerHTML = '<img id="cropImage" src="' + ev.target.result + '">';
        document.getElementById('cropModal').classList.add('show');
        if (cropper) cropper.destroy();
        setTimeout(function() {
            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                cropBoxResizable: true,
                cropBoxMovable: true,
            });
        }, 200);
    };
    reader.readAsDataURL(file);
    e.target.value = '';
}

function cerrarCrop() {
    document.getElementById('cropModal').classList.remove('show');
    if (cropper) { cropper.destroy(); cropper = null; }
}

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
            var d;
            try { d = JSON.parse(text); } catch (e) { console.error('Respuesta no JSON:', text.substring(0,200)); mostrarToast('Error del servidor', 'error'); return; }
            if (!r.ok) { mostrarToast(d.message || 'Error al subir', 'error'); return; }
            document.getElementById('profileAvatarImg').src = d.avatar + '?t=' + Date.now();
            mostrarToast(d.message, 'success');
            cerrarCrop();
        } catch (err) { console.error('Error avatar:', err); mostrarToast('Error de conexión', 'error'); }
    }, 'image/jpeg', 0.9);
}

async function eliminarAvatar() {
    if (!confirm('¿Eliminar foto de perfil?')) return;
    try {
        var r = await fetch('/perfil/avatar', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        location.reload();
    } catch (err) { console.error('Error eliminar avatar:', err); mostrarToast('Error de conexión', 'error'); }
}

// === TAB: SEGURIDAD ===
document.getElementById('formPassword')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    fd.append('_method', 'PUT');
    try {
        var r = await api('/perfil/password', { method: 'POST', body: fd });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error', 'error'); return; }
        mostrarToast(d.message, 'success');
        e.target.reset();
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

async function toggleFA() {
    var tog = document.getElementById('toggle2FA');
    var nuevo = !tog.classList.contains('active');
    try {
        var r = await api('/perfil/2fa', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ enabled: nuevo }) });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        tog.classList.toggle('active', d.enabled);
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

async function cargarSesiones() {
    var el = document.getElementById('sessionList');
    try {
        var r = await api('/perfil/sesiones');
        var sessions = await r.json();
        if (!sessions.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin sesiones activas</p>'; return; }
        el.innerHTML = sessions.map(function(s) {
            return '<div class="session-item' + (s.is_current ? ' current' : '') + '"><div class="session-device"><i class="fas ' + (s.is_current ? 'fa-mobile-screen-button' : 'fa-laptop') + '"></i><div class="session-info"><p>' + (s.user_agent ? s.user_agent.substring(0, 60) + '...' : 'Dispositivo desconocido') + (s.is_current ? ' <span class="session-badge current">Actual</span>' : '') + '</p><span>' + s.ip_address + ' — ' + (s.last_activity_humans || 'desconocido') + '</span></div></div>' + (!s.is_current ? '<button class="btn btn-danger btn-sm" onclick="eliminarSesion(\'' + s.id + '\')">Cerrar</button>' : '') + '</div>';
        }).join('');
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar sesiones</p>'; }
}

async function eliminarSesion(id) {
    try {
        var r = await api('/perfil/sesiones/' + id, { method: 'DELETE' });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarSesiones();
    } catch (err) { mostrarToast('Error', 'error'); }
}

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

// === TAB: CONFIGURACIÓN ===
async function cambiarTema(tema) {
    document.querySelectorAll('.theme-option').forEach(function(el) { el.classList.remove('selected'); });
    document.querySelector('.theme-option[data-theme="' + tema + '"]')?.classList.add('selected');
    document.body.classList.toggle('dark-mode', tema === 'dark');
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ theme: tema }) });
    } catch (err) {}
}

async function cambiarIdioma(lang) {
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ language: lang }) });
        mostrarToast('Idioma actualizado', 'success');
    } catch (err) {}
}

async function cambiarColor(color) {
    document.querySelectorAll('.color-preset').forEach(function(el) { el.classList.remove('selected'); });
    document.querySelector('.color-preset[data-color="' + color + '"]')?.classList.add('selected');
    document.documentElement.style.setProperty('--accent', color);
    try {
        await api('/perfil/configuracion', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ accent_color: color }) });
        mostrarToast('Color actualizado', 'success');
    } catch (err) {}
}

async function guardarNotificaciones() {
    try {
        await api('/perfil/notificaciones', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ notification_email: document.getElementById('notifEmail').value, notification_system: document.getElementById('notifSystem').value }) });
    } catch (err) {}
}

async function togglePrivacidad() {
    var tog = document.getElementById('togglePrivacy');
    var nuevo = !tog.classList.contains('active');
    tog.classList.toggle('active', nuevo);
    try {
        await api('/perfil/notificaciones', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ profile_public: nuevo }) });
        mostrarToast(nuevo ? 'Perfil público' : 'Perfil privado', 'success');
    } catch (err) { tog.classList.toggle('active', !nuevo); }
}

// === TAB: ACTIVIDAD ===
async function cargarActividad() {
    var list = document.getElementById('activityList');
    try {
        var resp = await Promise.all([api('/perfil/estadisticas'), api('/perfil/actividad')]);
        var stats = await resp[0].json();
        var acts = await resp[1].json();
        document.getElementById('statTrabajadores').textContent = stats.total_trabajadores_capturados || 0;
        document.getElementById('statSolicitudes').textContent = stats.total_solicitudes_gestionadas || 0;
        document.getElementById('statExpedientes').textContent = stats.total_expedientes_movidos || 0;
        document.getElementById('statMiembro').textContent = stats.miembro_desde || '—';
        document.getElementById('statUltimoAcceso').textContent = stats.ultimo_acceso || '—';
        document.getElementById('statUltimaIP').textContent = stats.ultima_ip || '—';
        if (!acts.length) { list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">No hay actividad registrada</p>'; return; }
        list.innerHTML = acts.map(function(a) {
            var icons = { created: { trabajador: ['users','green'], solicitud: ['file-lines','blue'], usuario: ['user-plus','purple'], documento: ['file','blue'], notificacion: ['bell','purple'] }, updated: { trabajador: ['pen','orange'], solicitud: ['pen-to-square','orange'], usuario: ['pen-to-square','orange'] }, deleted: { trabajador: ['trash-can','red'], solicitud: ['circle-xmark','red'] } };
            var fallback = ['circle','blue'];
            var iconData = (icons[a.action] && icons[a.action][a.subject_type]) || fallback;
            var fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            return '<div class="activity-item"><div class="activity-icon ' + iconData[1] + '"><i class="fas fa-' + iconData[0] + '"></i></div><div class="activity-text"><p>' + (a.description || a.action + ' ' + a.subject_type) + '</p><span>' + fecha + '</span></div></div>';
        }).join('');
    } catch (err) { list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar actividad</p>'; }
}

// === TAB: ADMIN ===
var adminTabActual = 'admin-usuarios';

function cambiarAdminTab(tab) {
    adminTabActual = tab;
    document.querySelectorAll('.admin-subtab').forEach(function(t) { t.style.display = 'none'; });
    document.querySelectorAll('.admin-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById(tab).style.display = 'block';
    document.querySelector('.admin-tab-btn[data-admin-tab="' + tab + '"]')?.classList.add('active');
    if (tab === 'admin-usuarios') cargarAdminUsuarios();
    if (tab === 'admin-actividad') cargarAdminActividad();
}

async function cargarAdminUsuarios() {
    var search = document.getElementById('adminSearch').value;
    var role = document.getElementById('adminRoleFilter').value;
    var tbody = document.getElementById('adminUsersBody');
    try {
        var r = await api('/perfil/admin/usuarios?search=' + encodeURIComponent(search) + '&role=' + role);
        var data = await r.json();
        if (!data.data || !data.data.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Sin usuarios</td></tr>'; return; }
        tbody.innerHTML = data.data.map(function(u) {
            return '<tr><td><div style="display:flex;align-items:center;gap:8px;"><div style="width:28px;height:28px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:#1e3a8a;overflow:hidden;">' + (u.avatar ? '<img src="' + window.SIGEJUB_STORAGE_URL + '/' + u.avatar + '" style="width:100%;height:100%;object-fit:cover;">' : u.name.charAt(0).toUpperCase()) + '</div>' + u.name + '</div></td><td>' + u.email + '</td><td><select class="form-input" style="width:auto;padding:4px 8px;font-size:0.75rem;" onchange="cambiarRolUsuario(' + u.id + ', this.value)"><option value="analista"' + (u.role === 'analista' ? ' selected' : '') + '>Analista</option><option value="admin"' + (u.role === 'admin' ? ' selected' : '') + '>Admin</option></select></td><td style="font-size:0.75rem;color:#94a3b8;">' + new Date(u.created_at).toLocaleDateString('es-ES') + '</td><td style="display:flex;gap:6px;"><button class="btn btn-primary btn-sm" onclick="abrirModalNotificacion(' + u.id + ', \'' + u.name.replace(/'/g, "\\'") + '\')" title="Enviar mensaje"><i class="fas fa-message"></i></button><button class="btn btn-danger btn-sm" onclick="eliminarUsuario(' + u.id + ')"' + (u.id === window.SIGEJUB_USER_ID ? ' disabled style="opacity:0.4;"' : '') + '>Eliminar</button></td></tr>';
        }).join('');
    } catch (err) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Error al cargar</td></tr>'; }
}

async function cambiarRolUsuario(id, role) {
    try {
        var r = await api('/perfil/admin/usuarios/' + id, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify({ role: role }) });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

async function eliminarUsuario(id) {
    if (!confirm('¿Eliminar este usuario permanentemente?')) return;
    try {
        var r = await api('/perfil/admin/usuarios/' + id, { method: 'DELETE' });
        var d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarAdminUsuarios();
    } catch (err) { mostrarToast('Error', 'error'); }
}

// === NOTIFICACIONES (MODAL) ===
function abrirModalNotificacion(userId, userName) {
    document.getElementById('notifUserId').value = userId;
    document.getElementById('notifUserName').textContent = userName;
    document.getElementById('formEnviarNotificacion').reset();
    document.getElementById('modalEnviarNotificacion').style.display = 'flex';
}

function cerrarModalNotificacion() {
    document.getElementById('modalEnviarNotificacion').style.display = 'none';
}

document.getElementById('formEnviarNotificacion')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var btn = this.querySelector('.btn-primary');
    btn.disabled = true; btn.textContent = 'Enviando...';
    try {
        var resp = await fetch('/notificaciones', { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
        var data = await resp.json();
        if (!resp.ok) throw data;
        mostrarToast(data.message, 'success');
        cerrarModalNotificacion();
    } catch (err) { mostrarToast(err.message || 'Error al enviar', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Enviar'; }
});

window.addEventListener('click', function(e) {
    var modal = document.getElementById('modalEnviarNotificacion');
    if (e.target === modal) cerrarModalNotificacion();
});

async function cargarAdminActividad() {
    var el = document.getElementById('adminActivityList');
    try {
        var r = await api('/perfil/admin/actividad');
        var acts = await r.json();
        if (!acts.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin actividad global</p>'; return; }
        el.innerHTML = acts.map(function(a) {
            var fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            return '<div class="activity-item"><div class="activity-text"><p>' + a.description + '</p><span>' + (a.user ? a.user.name : 'Sistema') + ' — ' + fecha + '</span></div></div>';
        }).join('');
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar</p>'; }
}

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
