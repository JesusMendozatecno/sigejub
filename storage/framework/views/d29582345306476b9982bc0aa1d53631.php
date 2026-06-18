<header class="section-header">
    <div class="header-info">
        <h1><i class="fas fa-hard-drive" size="22"></i> Historial</h1>
        <p>Registro inmutable de auditoría — todas las acciones del sistema.</p>
    </div>
    <div class="header-actions">
        <button class="cn-btn-action cn-btn-primary" onclick="exportarCajaNegra()">
            <i class="fas fa-download" size="16"></i>
            <span>Exportar</span>
        </button>
    </div>
</header>

<!-- Filter Toolbar -->
<div class="cn-toolbar">
    <div class="cn-search-wrap">
        <i class="fas fa-search" size="16" class="cn-search-icon"></i>
        <input type="text" id="cnSearch" placeholder="Buscar en descripción...">
    </div>
    <div class="cn-filters">
        <select id="cnUsuario" onchange="cargarCajaNegra()">
            <option value="">Usuario</option>
        </select>
        <select id="cnAccion" onchange="cargarCajaNegra()">
            <option value="">Acción</option>
            <option value="created">Creado</option>
            <option value="updated">Actualizado</option>
            <option value="deleted">Eliminado</option>
        </select>
        <select id="cnTipo" onchange="cargarCajaNegra()">
            <option value="">Tipo</option>
            <option value="trabajador">Trabajador</option>
            <option value="solicitud">Solicitud</option>
            <option value="expediente">Expediente</option>
            <option value="documento">Documento</option>
            <option value="usuario">Usuario</option>
            <option value="notificacion">Notificación</option>
        </select>
        <div class="cn-date-range">
            <input type="date" id="cnDesde" onchange="cargarCajaNegra()" title="Desde">
            <span class="cn-date-sep">→</span>
            <input type="date" id="cnHasta" onchange="cargarCajaNegra()" title="Hasta">
        </div>
    </div>
</div>

<!-- Stats -->
<div class="cn-stats" id="cnStats">
    <div class="cn-stat-card cn-stat-total">
        <i class="fas fa-database" size="22"></i>
        <div><h3 id="cnTotal">0</h3><p>Total registros</p></div>
    </div>
    <div class="cn-stat-card cn-stat-today">
        <i class="fas fa-calendar-check" size="22"></i>
        <div><h3 id="cnHoy">0</h3><p>Registros hoy</p></div>
    </div>
    <div class="cn-stat-card cn-stat-week">
        <i class="fas fa-wave-square" size="22"></i>
        <div><h3 id="cnSemana">0</h3><p>Últimos 7 días</p></div>
    </div>
</div>

<!-- Table -->
<div class="cn-table-wrap">
    <table class="cn-table" id="cnTable">
        <thead>
            <tr>
                <th>FECHA/HORA</th>
                <th>USUARIO</th>
                <th>ACCIÓN</th>
                <th>TIPO</th>
                <th>DESCRIPCIÓN</th>
                <th>IP</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="cnBody">
            <tr><td colspan="7" class="cn-empty">Cargando registros...</td></tr>
        </tbody>
    </table>
</div>

<div class="cn-footer">
    <span id="cnCounter" class="cn-counter">Mostrando 0 registros</span>
    <div class="cn-pagination" id="cnPagination"></div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="cnDetailModal">
    <div class="modal-container" style="max-width: 820px;">
        <aside class="modal-sidebar">
            <span class="badge-new">AUDITORÍA</span>
            <h1>Detalle del<br>Registro</h1>
            <p>Información completa del registro de auditoría seleccionado.</p>
            <div class="sidebar-actions" style="margin-top: auto;">
                <button type="button" class="btn-sidebar-cancel" id="btnCerrarCnDetalle">Cerrar</button>
            </div>
        </aside>
        <main class="modal-form-content" id="cnDetailContent">
            <p class="cn-modal-loading">Cargando detalle...</p>
        </main>
    </div>
</div>

<style>
/* === BADGES === */
.badge-action { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.4px; }
.badge-created { background:#ecfdf5;color:#059669;border:1px solid #a7f3d0; }
.badge-updated { background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe; }
.badge-deleted { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
.badge-type { font-size:0.68rem;color:#64748b;background:#f1f5f9;padding:3px 10px;border-radius:20px;font-weight:500; }

/* === BUTTONS === */
.cn-btn-action { display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:10px;font-size:0.83rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:none; }
.cn-btn-action i { width:16px;height:16px; }

.cn-btn-primary { background:#1a365d;color:white;box-shadow:0 2px 8px rgba(26,54,93,0.2); }
.cn-btn-primary:hover { background:#1e3a8a;box-shadow:0 4px 14px rgba(26,54,93,0.3);transform:translateY(-1px); }
.cn-btn-primary:active { transform:translateY(0);box-shadow:0 1px 4px rgba(26,54,93,0.2); }

.cn-btn-ghost { display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:1px solid transparent;background:transparent;color:#475569; }
.cn-btn-ghost:hover { background:#f1f5f9;border-color:#e2e8f0;color:#1a365d; }
.cn-btn-ghost i { width:15px;height:15px; }

.cn-btn-pag { display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:8px;font-size:0.8rem;font-weight:600;border:1px solid #e2e8f0;background:white;color:#475569;cursor:pointer;transition:all 0.15s;margin:0 2px; }
.cn-btn-pag:hover { background:#f1f5f9;border-color:#cbd5e1;color:#1a365d; }
.cn-btn-pag.active { background:#1a365d;border-color:#1a365d;color:white;box-shadow:0 2px 8px rgba(26,54,93,0.25); }
.cn-btn-pag.active:hover { background:#1e3a8a; }

/* === TOOLBAR === */
.cn-toolbar { background:white;border-radius:14px;padding:12px 16px;margin-bottom:18px;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-wrap:wrap;gap:12px;align-items:center;border:1px solid #f1f5f9; }
.cn-search-wrap { position:relative;flex:1;min-width:200px; }
.cn-search-icon { position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none; }
.cn-search-wrap input { width:100%;padding:9px 12px 9px 38px;border:1px solid #e2e8f0;border-radius:10px;font-size:0.83rem;background:#f8fafc;transition:all 0.2s;outline:none;color:#334155; }
.cn-search-wrap input:focus { border-color:#2563eb;background:white;box-shadow:0 0 0 3px rgba(37,99,235,0.08); }
.cn-search-wrap input::placeholder { color:#94a3b8; }

.cn-filters { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
.cn-filters select { padding:9px 30px 9px 12px;border:1px solid #e2e8f0;border-radius:10px;font-size:0.8rem;background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 10px center;transition:all 0.2s;outline:none;color:#475569;cursor:pointer;appearance:none;min-width:110px; }
.cn-filters select:focus { border-color:#2563eb;background-color:white;box-shadow:0 0 0 3px rgba(37,99,235,0.08); }

.cn-date-range { display:flex;align-items:center;gap:6px; }
.cn-date-range input[type=date] { padding:9px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:0.8rem;background:#f8fafc;transition:all 0.2s;outline:none;color:#475569;cursor:pointer;width:130px; }
.cn-date-range input[type=date]:focus { border-color:#2563eb;background:white;box-shadow:0 0 0 3px rgba(37,99,235,0.08); }
.cn-date-sep { color:#94a3b8;font-size:0.85rem;font-weight:600; }

/* === STATS === */
.cn-stats { display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px; }
@media (max-width: 600px) { .cn-stats { grid-template-columns:1fr; } }
.cn-stat-card { background:white;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #f1f5f9;transition:transform 0.2s; }
.cn-stat-card:hover { transform:translateY(-2px); }
.cn-stat-card i { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.cn-stat-card div h3 { font-size:1.4rem;font-weight:800;color:#0f172a;margin:0;line-height:1.2; }
.cn-stat-card div p { font-size:0.72rem;color:#64748b;margin:2px 0 0;text-transform:uppercase;font-weight:600;letter-spacing:0.3px; }
.cn-stat-total i { background:#eff6ff;color:#1a365d; }
.cn-stat-today i { background:#ecfdf5;color:#059669; }
.cn-stat-week i { background:#fff7ed;color:#ea580c; }

/* === TABLE === */
.cn-table-wrap { background:white;border-radius:14px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #f1f5f9; }
.cn-table { width:100%;border-collapse:collapse; }
.cn-table th { padding:14px 16px;font-size:0.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;text-align:left;background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.cn-table td { padding:12px 16px;font-size:0.83rem;color:#334155;border-bottom:1px solid #f1f5f9; }
.cn-table tbody tr:hover td { background:#fafbfc; }
.cn-table tbody tr:last-child td { border-bottom:none; }

/* === FOOTER === */
.cn-footer { display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:12px; }
.cn-counter { font-size:0.8rem;color:#64748b;font-weight:500; }
.cn-pagination { display:flex;flex-wrap:wrap;gap:4px; }

/* === DETAIL MODAL === */
.cn-detail-modal .modal-box { max-width:720px;padding:0;overflow:hidden; }
.cn-modal-head { display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f1f5f9; }
.cn-modal-head h3 { margin:0;font-size:1rem;color:#0f172a;display:flex;align-items:center;gap:8px; }
.cn-modal-close { background:none;border:none;font-size:1.5rem;color:#94a3b8;cursor:pointer;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:all 0.2s; }
.cn-modal-close:hover { background:#f1f5f9;color:#475569; }
#cnDetailContent { padding:20px 24px 24px; }
.cn-modal-loading { color:#94a3b8;text-align:center;padding:20px; }
.cn-detail-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.cn-detail-field { background:#f8fafc;border-radius:10px;padding:12px 14px; }
.cn-detail-field label { font-size:0.65rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:5px;letter-spacing:0.3px; }
.cn-detail-field .value { font-size:0.85rem;color:#0f172a;word-break:break-word;max-height:100px;overflow-y:auto; }
.cn-detail-field .value pre { font-size:0.73rem;background:#f1f5f9;padding:8px 10px;border-radius:6px;margin:4px 0 0;max-height:160px;overflow:auto;white-space:pre-wrap; }
.cn-detail-full { background:#f8fafc;border-radius:10px;padding:12px 14px;margin-top:10px; }
.cn-detail-full label { font-size:0.65rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:5px;letter-spacing:0.3px; }
.cn-detail-full pre { font-size:0.73rem;background:#f1f5f9;padding:10px;border-radius:6px;max-height:180px;overflow:auto;white-space:pre-wrap;margin:0; }

/* === EMPTY === */
.cn-empty { text-align:center;padding:3rem 1rem;color:#94a3b8;font-size:0.9rem; }
</style>

<script>
(function() {
    let cnPagina = 1;

    async function cargarStats() {
        try {
            const r = await fetch('/caja-negra-data/estadisticas');
            const d = await r.json();
            document.getElementById('cnTotal').textContent = d.total || 0;
            document.getElementById('cnHoy').textContent = d.today || 0;
            document.getElementById('cnSemana').textContent = d.lastWeek || 0;
        } catch (e) { console.error('Error stats:', e); }
    }

    async function cargarUsuarios() {
        try {
            const r = await fetch('/caja-negra-data/usuarios');
            const users = await r.json();
            const sel = document.getElementById('cnUsuario');
            sel.innerHTML = '<option value="">Todos los usuarios</option>' +
                users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
        } catch (e) { console.error('Error usuarios:', e); }
    }

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
                const userEscaped = a.user ? escaparHTML(a.user.name) : 'Sistema';
                const badgeAction = { created:'Creado', updated:'Actualizado', deleted:'Eliminado' }[a.action] || a.action;
                const badgeClass = 'badge-' + a.action;
                const tipoLabel = a.subject_type ? a.subject_type.charAt(0).toUpperCase() + a.subject_type.slice(1) : '—';
                const ip = a.ip_address || '—';
                return `<tr>
                    <td style="font-size:0.78rem;color:#64748b;white-space:nowrap;">${fecha}</td>
                    <td style="font-weight:600;font-size:0.85rem;">${userEscaped}</td>
                    <td><span class="badge-action ${badgeClass}">${badgeAction}</span></td>
                    <td><span class="badge-type">${escaparHTML(tipoLabel)}</span></td>
                    <td style="font-size:0.83rem;color:#334155;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escaparHTML(a.description)}</td>
                    <td style="font-size:0.75rem;color:#94a3b8;font-family:monospace;">${escaparHTML(ip)}</td>
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

    window.verDetalleCajaNegra = async function(id) {
        const modal = document.getElementById('cnDetailModal');
        const content = document.getElementById('cnDetailContent');
        modal.classList.add('show');
        content.innerHTML = '<p class="cn-modal-loading">Cargando detalle...</p>';
        try {
            const r = await fetch('/caja-negra/' + id);
            const a = await r.json();
            const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
            const userEscaped = a.user ? escaparHTML(a.user.name) : 'Sistema';
            const badgeAction = { created:'Creado', updated:'Actualizado', deleted:'Eliminado' }[a.action] || a.action;
            const oldHtml = a.old_values ? `<pre>${JSON.stringify(a.old_values, null, 2)}</pre>` : '<span class="text-muted">—</span>';
            const newHtml = a.new_values ? `<pre>${JSON.stringify(a.new_values, null, 2)}</pre>` : '<span class="text-muted">—</span>';
            const reqHtml = a.request_data ? `<pre>${JSON.stringify(a.request_data, null, 2)}</pre>` : '<span class="text-muted">—</span>';

            content.innerHTML = `
                <div class="cn-detail-grid">
                    <div class="cn-detail-field"><label>Fecha/Hora</label><div class="value">${fecha}</div></div>
                    <div class="cn-detail-field"><label>Usuario</label><div class="value">${userEscaped}</div></div>
                    <div class="cn-detail-field"><label>Acción</label><div class="value"><span class="badge-action ${'badge-'+a.action}">${badgeAction}</span></div></div>
                    <div class="cn-detail-field"><label>Tipo</label><div class="value"><span class="badge-type">${escaparHTML(a.subject_type) || '—'}</span></div></div>
                    <div class="cn-detail-field"><label>ID del registro</label><div class="value">${a.subject_id ?? '—'}</div></div>
                    <div class="cn-detail-field"><label>IP</label><div class="value" style="font-family:monospace;">${escaparHTML(a.ip_address) || '—'}</div></div>
                </div>
                <div class="cn-detail-field" style="margin-bottom:8px;"><label>Descripción</label><div class="value">${escaparHTML(a.description)}</div></div>
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

    window.exportarCajaNegra = function() {
        const params = new URLSearchParams();
        const desde = document.getElementById('cnDesde').value;
        const hasta = document.getElementById('cnHasta').value;
        if (desde) params.set('from', desde);
        if (hasta) params.set('to', hasta);
        const url = '/caja-negra/exportar' + (params.toString() ? '?' + params.toString() : '');
        window.open(url, '_blank', 'width=1000,height=700');
    };

    // Search on enter with debounce
    let cnTimeout = null;
    document.getElementById('cnSearch').addEventListener('input', function() {
        clearTimeout(cnTimeout);
        cnTimeout = setTimeout(() => { cnPagina = 1; cargarCajaNegra(); }, 400);
    });

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        cargarStats();
        cargarUsuarios();
        cargarCajaNegra();
    });

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
</script>
<?php /**PATH C:\Users\RickUltra\Documents\Programacion\Programacion\Proyecto con jesus\sigejub-2\resources\views/dashboard/secciones/caja-negra.blade.php ENDPATH**/ ?>