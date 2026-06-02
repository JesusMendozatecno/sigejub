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

    /* === Ver detalle de un registro en modal (sidebar style) === */
    window.verDetalleCajaNegra = async function(id) {
        const modal = document.getElementById('cnDetailModal');
        const content = document.getElementById('cnDetailContent');
        modal.style.display = 'flex';
        content.innerHTML = '<p class="cn-modal-loading" style="text-align:center;padding:2rem;color:#94a3b8;">Cargando detalle...</p>';
        try {
            const r = await fetch('/caja-negra/' + id);
            const a = await r.json();
            const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
            const diff = Math.floor((Date.now() - new Date(a.created_at).getTime()) / 1000);
            const tiempoAgo = diff < 60 ? 'hace unos segundos' : diff < 3600 ? `hace ${Math.floor(diff/60)} min` : diff < 86400 ? `hace ${Math.floor(diff/3600)} h` : `hace ${Math.floor(diff/86400)} días`;
            const user = a.user ? a.user.name : 'Sistema';
            const userEmail = a.user ? a.user.email : '—';
            const userRole = a.user ? (a.user.role === 'admin' ? 'Administrador' : 'Analista') : '—';
            const badgeAction = { created:'Creado', updated:'Actualizado', deleted:'Eliminado' }[a.action] || a.action;
            const tipoLabel = a.subject_type ? a.subject_type.charAt(0).toUpperCase() + a.subject_type.slice(1) : '—';
            const oldHtml = a.old_values ? `<pre>${JSON.stringify(a.old_values, null, 2)}</pre>` : '<span class="text-muted" style="color:#94a3b8;">—</span>';
            const newHtml = a.new_values ? `<pre>${JSON.stringify(a.new_values, null, 2)}</pre>` : '<span class="text-muted" style="color:#94a3b8;">—</span>';
            const reqHtml = a.request_data ? `<pre>${JSON.stringify(a.request_data, null, 2)}</pre>` : '<span class="text-muted" style="color:#94a3b8;">—</span>';
            const uaHtml = a.user_agent ? `<div style="font-size:0.73rem;color:#64748b;word-break:break-all;font-family:monospace;background:#f1f5f9;padding:8px 10px;border-radius:6px;margin-top:4px;">${a.user_agent}</div>` : '<span class="text-muted" style="color:#94a3b8;">—</span>';
            const iconAccion = { created:'fa-plus-circle', updated:'fa-pen', deleted:'fa-trash-can' }[a.action] || 'fa-circle';
            const iconTipo = { trabajador:'fa-user', solicitud:'fa-file-lines', expediente:'fa-folder', documento:'fa-file-pdf', usuario:'fa-user-shield', notificacion:'fa-bell' }[a.subject_type] || 'fa-circle';

            content.innerHTML = `
                <section class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Información General</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA/HORA</label>
                            <input type="text" value="${fecha}" readonly>
                        </div>
                        <div class="input-group">
                            <label>TIEMPO TRANSCURRIDO</label>
                            <input type="text" value="${tiempoAgo}" readonly>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>ACCIÓN</label>
                            <input type="text" value="${badgeAction}" readonly>
                        </div>
                        <div class="input-group">
                            <label>TIPO</label>
                            <input type="text" value="${tipoLabel}" readonly>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>ID DEL REGISTRO</label>
                            <input type="text" value="${a.subject_id ?? '—'}" readonly>
                        </div>
                        <div class="input-group">
                            <label>DIRECCIÓN IP</label>
                            <input type="text" value="${a.ip_address || '—'}" readonly style="font-family:monospace;">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Usuario</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRE</label>
                            <input type="text" value="${user}" readonly>
                        </div>
                        <div class="input-group">
                            <label>CORREO</label>
                            <input type="text" value="${userEmail}" readonly>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>ROL</label>
                            <input type="text" value="${userRole}" readonly>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-align-left"></i> Descripción</h3>
                    <textarea class="form-textarea" readonly style="resize:none;">${a.description}</textarea>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-globe"></i> Navegador / Dispositivo</h3>
                    <div style="margin-top:4px;">${uaHtml}</div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-code-branch"></i> Valores Anteriores</h3>
                    ${oldHtml}
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-code-branch"></i> Valores Nuevos</h3>
                    ${newHtml}
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-paper-plane"></i> Datos de la Petición</h3>
                    ${reqHtml}
                </section>
            `;
        } catch (e) {
            content.innerHTML = '<p class="cn-modal-loading" style="text-align:center;padding:2rem;color:#ef4444;">Error al cargar detalle</p>';
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

    function cerrarCnDetalle() {
        document.getElementById('cnDetailModal').style.display = 'none';
    }

    /* === Inicialización al cargar el DOM === */
    document.addEventListener('DOMContentLoaded', () => {
        cargarStats();
        cargarUsuarios();
        cargarCajaNegra();
        document.getElementById('btnCerrarCnDetalle')?.addEventListener('click', cerrarCnDetalle);
        window.addEventListener('click', function(e) {
            if (e.target === document.getElementById('cnDetailModal')) cerrarCnDetalle();
        });
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
