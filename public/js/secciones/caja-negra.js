/* ============================================================
   caja-negra.js — JavaScript para la sección "Caja Negra"
   (Historial / Registro de Auditoría)
   Funcionalidad: carga de estadísticas, carga de usuarios
   para el filtro, búsqueda con debounce, paginación,
   visualización de detalle en modal y exportación.
   ============================================================ */

(function() {
    let cnPagina = 1;

    /* === Cargar estadísticas (total, hoy, últ. 7 días) === */
    async function cargarStats() {
        try {
            const r = await fetch('/caja-negra-data/estadisticas');
            const d = await r.json();
            document.getElementById('cnTotal').textContent = d.total || 0;
            document.getElementById('cnHoy').textContent = d.today || 0;
            document.getElementById('cnSemana').textContent = d.lastWeek || 0;
        } catch (e) { console.error('Error stats:', e); }
    }

    /* === Cargar usuarios para el filtro desplegable === */
    async function cargarUsuarios() {
        try {
            const r = await fetch('/caja-negra-data/usuarios');
            const users = await r.json();
            const sel = document.getElementById('cnUsuario');
            sel.innerHTML = '<option value="">Todos los usuarios</option>' +
                users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
        } catch (e) { console.error('Error usuarios:', e); }
    }

    /* === Cargar registros de la caja negra con filtros y paginación === */
    window.cargarCajaNegra = async function(pagina) {
        if (pagina) cnPagina = pagina;
        const params = new URLSearchParams();
        const search = document.getElementById('cnSearch').value;
        const usuario = document.getElementById('cnUsuario').value;
        const accion = document.getElementById('cnAccion').value;
        const tipo = document.getElementById('cnTipo').value;
        const desde = document.getElementById('cnDesde').value;
        const hasta = document.getElementById('cnHasta').value;
        if (search) params.set('search', search);
        if (usuario) params.set('user_id', usuario);
        if (accion) params.set('action', accion);
        if (tipo) params.set('subject_type', tipo);
        if (desde) params.set('from', desde);
        if (hasta) params.set('to', hasta);
        params.set('page', cnPagina);

        const tbody = document.getElementById('cnBody');
        tbody.innerHTML = '<tr><td colspan="7" class="cn-empty">Buscando registros...</td></tr>';

        try {
            const r = await fetch('/caja-negra?' + params.toString());
            const data = await r.json();
            if (!data.data || !data.data.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="cn-empty">Sin registros que coincidan con los filtros</td></tr>';
                document.getElementById('cnCounter').textContent = 'Mostrando 0 registros';
                document.getElementById('cnPagination').innerHTML = '';
                return;
            }
            tbody.innerHTML = data.data.map(a => {
                const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                const user = a.user ? a.user.name : '<em>Sistema</em>';
                const badgeAction = { created:'Creado', updated:'Actualizado', deleted:'Eliminado' }[a.action] || a.action;
                const badgeClass = 'badge-' + a.action;
                const tipoLabel = a.subject_type ? a.subject_type.charAt(0).toUpperCase() + a.subject_type.slice(1) : '—';
                const ip = a.ip_address || '—';
                return `<tr>
                    <td style="font-size:0.78rem;color:#64748b;white-space:nowrap;">${fecha}</td>
                    <td style="font-weight:600;font-size:0.85rem;">${user}</td>
                    <td><span class="badge-action ${badgeClass}">${badgeAction}</span></td>
                    <td><span class="badge-type">${tipoLabel}</span></td>
                    <td style="font-size:0.83rem;color:#334155;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${a.description}</td>
                    <td style="font-size:0.75rem;color:#94a3b8;font-family:monospace;">${ip}</td>
                    <td><button class="cn-btn-ghost" onclick="verDetalleCajaNegra(${a.id})" title="Ver detalle"><i class="fas fa-eye" size="15"></i> Ver</button></td>
                </tr>`;
            }).join('');

            document.getElementById('cnCounter').textContent = `Mostrando ${data.data.length} de ${data.total} registros`;

            const pag = document.getElementById('cnPagination');
            if (data.last_page <= 1) { pag.innerHTML = ''; return; }
            let html = '';
            if (data.current_page > 1) {
                html += `<button class="cn-btn-pag" onclick="cargarCajaNegra(${data.current_page - 1})" title="Anterior">‹</button>`;
            }
            for (let i = 1; i <= data.last_page; i++) {
                if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                    html += `<button class="cn-btn-pag ${i === data.current_page ? 'active' : ''}" onclick="cargarCajaNegra(${i})">${i}</button>`;
                } else if (html.slice(-9) !== '<span>…</span>') {
                    html += `<span style="padding:0 6px;color:#94a3b8;font-weight:600;align-self:center;">…</span>`;
                }
            }
            if (data.current_page < data.last_page) {
                html += `<button class="cn-btn-pag" onclick="cargarCajaNegra(${data.current_page + 1})" title="Siguiente">›</button>`;
            }
            pag.innerHTML = html;
        } catch (e) {
            console.error('Error caja negra:', e);
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2.5rem;color:#ef4444;font-size:0.9rem;">⚠ Error al cargar los registros</td></tr>';
        }
    };

    /* === Ver detalle de un registro en modal === */
    window.verDetalleCajaNegra = async function(id) {
        const modal = document.getElementById('cnDetailModal');
        const content = document.getElementById('cnDetailContent');
        modal.classList.add('show');
        content.innerHTML = '<p class="cn-modal-loading">Cargando detalle...</p>';
        try {
            const r = await fetch('/caja-negra/' + id);
            const a = await r.json();
            const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
            const user = a.user ? a.user.name : 'Sistema';
            const badgeAction = { created:'Creado', updated:'Actualizado', deleted:'Eliminado' }[a.action] || a.action;
            const oldHtml = a.old_values ? `<pre>${JSON.stringify(a.old_values, null, 2)}</pre>` : '<span class="text-muted">—</span>';
            const newHtml = a.new_values ? `<pre>${JSON.stringify(a.new_values, null, 2)}</pre>` : '<span class="text-muted">—</span>';
            const reqHtml = a.request_data ? `<pre>${JSON.stringify(a.request_data, null, 2)}</pre>` : '<span class="text-muted">—</span>';

            content.innerHTML = `
                <div class="cn-detail-grid">
                    <div class="cn-detail-field"><label>Fecha/Hora</label><div class="value">${fecha}</div></div>
                    <div class="cn-detail-field"><label>Usuario</label><div class="value">${user}</div></div>
                    <div class="cn-detail-field"><label>Acción</label><div class="value"><span class="badge-action ${'badge-'+a.action}">${badgeAction}</span></div></div>
                    <div class="cn-detail-field"><label>Tipo</label><div class="value"><span class="badge-type">${a.subject_type||'—'}</span></div></div>
                    <div class="cn-detail-field"><label>ID del registro</label><div class="value">${a.subject_id ?? '—'}</div></div>
                    <div class="cn-detail-field"><label>IP</label><div class="value" style="font-family:monospace;">${a.ip_address||'—'}</div></div>
                </div>
                <div class="cn-detail-field" style="margin-bottom:8px;"><label>Descripción</label><div class="value">${a.description}</div></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                    <div class="cn-detail-full"><label>Valores Anteriores</label>${oldHtml}</div>
                    <div class="cn-detail-full"><label>Valores Nuevos</label>${newHtml}</div>
                </div>
                <div class="cn-detail-full" style="margin-top:12px;"><label>Datos de la Petición</label>${reqHtml}</div>
            `;
        } catch (e) {
            content.innerHTML = '<p class="cn-modal-loading" style="color:#ef4444;">Error al cargar detalle</p>';
        }
    };

    /* === Exportar registros a PDF === */
    window.exportarCajaNegra = function() {
        const params = new URLSearchParams();
        const desde = document.getElementById('cnDesde').value;
        const hasta = document.getElementById('cnHasta').value;
        if (desde) params.set('from', desde);
        if (hasta) params.set('to', hasta);
        const url = '/caja-negra/exportar' + (params.toString() ? '?' + params.toString() : '');
        window.open(url, '_blank', 'width=1000,height=700');
    };

    /* === Búsqueda con debounce al escribir === */
    let cnTimeout = null;
    document.getElementById('cnSearch').addEventListener('input', function() {
        clearTimeout(cnTimeout);
        cnTimeout = setTimeout(() => { cnPagina = 1; cargarCajaNegra(); }, 400);
    });

    /* === Inicialización al cargar el DOM === */
    document.addEventListener('DOMContentLoaded', () => {
        cargarStats();
        cargarUsuarios();
        cargarCajaNegra();
    });

    /* === Observador para recargar al cambiar a la pestaña === */
    const observer = new MutationObserver(m => {
        m.forEach(mm => {
            if (mm.target.id === 'caja-negra' && mm.target.classList.contains('active')) {
                cargarStats();
                cargarCajaNegra();
            }
        });
    });
    const sec = document.getElementById('caja-negra');
    if (sec) observer.observe(sec, { attributes: true, attributeFilter: ['class'] });
})();
