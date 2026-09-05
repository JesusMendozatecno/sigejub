{{-- caja-negra.blade.php - Módulo Historial (Caja Negra + Copias de Seguridad).
     Registro inmutable de auditoría: filtros, estadísticas, paginación, detalle y exportación.
     Copias de Seguridad: generar, listar, verificar (SHA-256), descargar, eliminar y restaurar (superadmin). --}}
<header class="section-header">
    <div class="header-info">
        <h1><i class="fas fa-hard-drive" size="22"></i> Historial</h1>
        <p>Registro inmutable de auditoría — todas las acciones del sistema.</p>
    </div>
    <div class="header-actions">
        <button class="cn-btn-action cn-btn-primary" onclick="exportarCajaNegra()" title="Exportar como PDF (respeta filtros)">
            <i class="fas fa-file-pdf" size="16"></i>
            <span>PDF</span>
        </button>
        <button class="cn-btn-action cn-btn-primary" onclick="exportarCajaNegraCsv()" title="Exportar como CSV (respeta filtros)">
            <i class="fas fa-file-csv" size="16"></i>
            <span>CSV</span>
        </button>
    </div>
</header>

<!-- Filter Toolbar -->
<div class="cn-toolbar">
    <div class="cn-search-wrap">
        <i class="fas fa-search" size="16" class="cn-search-icon"></i>
        <input type="text" id="cnSearch" placeholder="Buscar en descripción, IP o ruta...">
    </div>
    <div class="cn-filters">
        <select id="cnUsuario" onchange="cargarCajaNegra()">
            <option value="">Usuario</option>
        </select>
        <select id="cnAccion" onchange="cargarCajaNegra()">
            <option value="">Acción</option>
            @foreach(\App\Services\AuditService::ACCION_ETIQUETAS as $clave => $etiqueta)
            <option value="{{ $clave }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
        <select id="cnTipo" onchange="cargarCajaNegra()">
            <option value="">Tipo</option>
            @foreach(\App\Services\AuditService::ENTIDAD_ETIQUETAS as $clave => $etiqueta)
            <option value="{{ $clave }}">{{ $etiqueta }}</option>
            @endforeach
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
                <th>ROL</th>
                <th>ACCIÓN</th>
                <th>TIPO</th>
                <th>DESCRIPCIÓN</th>
                <th>IP</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="cnBody">
            <tr><td colspan="8" class="cn-empty">Cargando registros...</td></tr>
        </tbody>
    </table>
</div>

<div class="cn-footer">
    <span id="cnCounter" class="cn-counter">Mostrando 0 registros</span>
    <div class="cn-pagination" id="cnPagination"></div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="cnDetailModal">
    <div class="modal-container" style="max-width: 860px;">
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

<!-- ============================================================
     COPIAS DE SEGURIDAD
     ============================================================ -->
<div style="margin-top: 24px; background: white; border-radius: 14px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border: 1px solid #f1f5f9;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
        <div>
            <h3 style="margin: 0; font-size: 1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-shield-halved"></i> Copias de Seguridad
            </h3>
            <p style="margin: 4px 0 0; font-size: 0.78rem; color: #64748b;">
                Respaldo completo: base de datos + archivos del sistema. Cada copia se verifica con SHA-256 y todas las operaciones quedan registradas en la Caja Negra.
            </p>
        </div>
        <button class="cn-btn-action cn-btn-primary" id="btnGenerarBackup" onclick="generarBackup()">
            <i class="fas fa-hard-drive" size="16"></i>
            <span>Generar copia de seguridad</span>
        </button>
    </div>
    <div id="backupList" style="min-height: 40px;">
        <p style="color: #94a3b8; font-size: 0.85rem; text-align: center; padding: 16px;">Cargando copias de seguridad...</p>
    </div>
</div>

<!-- Confirmar eliminación de copia -->
<div class="modal-overlay" id="bkDeleteModal">
    <div class="modal-container" style="max-width: 460px;">
        <aside class="modal-sidebar">
            <span class="badge-new">SEGURIDAD</span>
            <h1>Eliminar<br>Copia</h1>
            <p>Confirmación requerida.</p>
            <div style="margin-top: auto;"><button type="button" class="btn-sidebar-cancel" id="btnCancelarBorrarBk">Cancelar</button></div>
        </aside>
        <main class="modal-form-content">
            <h2 style="margin:0 0 10px;font-size:1rem;color:#0f172a;">¿Eliminar esta copia de seguridad?</h2>
            <p style="font-size:0.85rem;color:#64748b;margin:0 0 16px;">
                La eliminación es irreversible y quedará registrada permanentemente en la Caja Negra.
            </p>
            <div class="cn-detail-field"><label>Archivo</label><div class="value" id="bkDeleteNombre">—</div></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">
                <button class="cn-btn-ghost" id="btnCancelarBorrarBk2">Cancelar</button>
                <button class="cn-btn-action cn-danger" id="btnConfirmarBorrarBk">Eliminar</button>
            </div>
        </main>
    </div>
</div>

<!-- Restaurar copia (solo superadmin) -->
<div class="modal-overlay" id="bkRestoreModal">
    <div class="modal-container" style="max-width: 620px;">
        <aside class="modal-sidebar">
            <span class="badge-new">SUPERADMIN</span>
            <h1>Restaurar<br>Copia</h1>
            <p>Restauración segura con backup preventivo obligatorio.</p>
            <div style="margin-top: auto;"><button type="button" class="btn-sidebar-cancel" id="btnCerrarRestaurarBk">Cancelar</button></div>
        </aside>
        <main class="modal-form-content" id="bkRestoreContent">
            <p class="cn-modal-loading">Analizando la copia de seguridad...</p>
        </main>
    </div>
</div>

<script>
(function() {
    const ES_ES_RELOAD = true;
    window.CN_ES_SUPERADMIN = (typeof window.SIGEJUB_ROL !== 'undefined' && window.SIGEJUB_ROL === 'superadmin');

    /* ===== CAJA NEGRA ===== */
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
                users.map(u => `<option value="${u.id}">${escaparHTML(u.nombre + ' ' + (u.apellido || ''))}</option>`).join('');
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
        if (accion) params.set('accion', accion);
        if (tipo) params.set('tipo_entidad', tipo);
        if (desde) params.set('from', desde);
        if (hasta) params.set('to', hasta);
        params.set('page', cnPagina);

        const tbody = document.getElementById('cnBody');
        tbody.innerHTML = '<tr><td colspan="8" class="cn-empty">Buscando registros...</td></tr>';

        try {
            const r = await fetch('/caja-negra?' + params.toString());
            const data = await r.json();
            if (!data.data || !data.data.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="cn-empty">Sin registros que coincidan con los filtros</td></tr>';
                document.getElementById('cnCounter').textContent = 'Mostrando 0 registros';
                document.getElementById('cnPagination').innerHTML = '';
                return;
            }
            tbody.innerHTML = data.data.map(a => {
                const fecha = new Date(a.created_at).toLocaleString('es-ES', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                const userEscaped = a.usuario_nombre ? escaparHTML(a.usuario_nombre) : 'Sistema';
                const badgeClass = 'badge-' + String(a.accion).replace(/[^a-zA-Z0-9]/g, '_');
                const ip = a.direccion_ip || '—';
                return `<tr>
                    <td style="font-size:0.78rem;color:#64748b;white-space:nowrap;">${fecha}</td>
                    <td style="font-weight:600;font-size:0.85rem;">${userEscaped}</td>
                    <td style="font-size:0.75rem;color:#64748b;"><span class="cn-rol-chip">${escaparHTML(a.usuario_rol || '—')}</span></td>
                    <td><span class="badge-action ${badgeClass}">${escaparHTML(a.accion_humana)}</span></td>
                    <td><span class="badge-type">${escaparHTML(a.tipo_entidad_humana)}</span></td>
                    <td style="font-size:0.83rem;color:#334155;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escaparHTML(a.descripcion)}</td>
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
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2.5rem;color:#ef4444;font-size:0.9rem;">⚠ Error al cargar los registros</td></tr>';
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
            const fecha = new Date(a.created_at).toLocaleString('es-ES', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
            const badgeClass = 'badge-' + String(a.accion).replace(/[^a-zA-Z0-9]/g, '_');

            function tablaCambios(obj) {
                if (!obj || typeof obj !== 'object' || Array.isArray(obj)) {
                    return '<pre>' + escaparHTML(JSON.stringify(obj, null, 2)) + '</pre>';
                }
                const filas = Object.keys(obj).map(k =>
                    '<tr><td>' + escaparHTML(k) + '</td><td><pre>' + escaparHTML(JSON.stringify(obj[k], null, 2)) + '</pre></td></tr>'
                ).join('');
                return '<table class="cn-diff-table"><thead><tr><th>Campo</th><th>Valor</th></tr></thead><tbody>' + filas + '</tbody></table>';
            }

            content.innerHTML = `
                <div class="cn-detail-grid">
                    <div class="cn-detail-field"><label>Fecha/Hora</label><div class="value">${fecha}</div></div>
                    <div class="cn-detail-field"><label>Usuario</label><div class="value">${escaparHTML(a.usuario_nombre || 'Sistema')}</div></div>
                    <div class="cn-detail-field"><label>Rol</label><div class="value"><span class="cn-rol-chip">${escaparHTML(a.usuario_rol || '—')}</span></div></div>
                    <div class="cn-detail-field"><label>Acción</label><div class="value"><span class="badge-action ${badgeClass}">${escaparHTML(a.accion_humana)}</span></div></div>
                    <div class="cn-detail-field"><label>Tipo de entidad</label><div class="value"><span class="badge-type">${escaparHTML(a.tipo_entidad_humana)}</span></div></div>
                    <div class="cn-detail-field"><label>ID del registro</label><div class="value">${a.entidad_id ?? '—'}</div></div>
                </div>
                <div class="cn-detail-field" style="margin-top:10px;"><label>Descripción</label><div class="value">${escaparHTML(a.descripcion)}</div></div>
                <div class="cn-detail-origen">
                    <div class="cn-detail-field"><label>Dirección IP</label><div class="value" style="font-family:monospace;">${escaparHTML(a.direccion_ip || '—')}</div></div>
                    <div class="cn-detail-field"><label>Método HTTP</label><div class="value" style="font-family:monospace;">${escaparHTML(a.metodo || '—')}</div></div>
                    <div class="cn-detail-field"><label>Ruta</label><div class="value" style="font-family:monospace;">${escaparHTML(a.ruta || '—')}</div></div>
                    <div class="cn-detail-field"><label>Navegador / User Agent</label><div class="value">${escaparHTML(a.navegador || '—')}</div></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                    <div class="cn-detail-full"><label>Valores Anteriores (antes)</label>${a.valores_anteriores ? tablaCambios(a.valores_anteriores) : '<span class="text-muted">—</span>'}</div>
                    <div class="cn-detail-full"><label>Valores Nuevos (después)</label>${a.valores_nuevos ? tablaCambios(a.valores_nuevos) : '<span class="text-muted">—</span>'}</div>
                </div>
                <div class="cn-detail-full" style="margin-top:12px;"><label>Datos de la Petición (sanitizados)</label>${a.datos_peticion ? '<pre>' + escaparHTML(JSON.stringify(a.datos_peticion, null, 2)) + '</pre>' : '<span class="text-muted">—</span>'}</div>
            `;
        } catch (e) {
            content.innerHTML = '<p class="cn-modal-loading" style="color:#ef4444;">Error al cargar detalle</p>';
        }
    };

    window.exportarCajaNegra = function() {
        const params = filtrosCajaNegra();
        const url = '/caja-negra/exportar' + (params ? '?' + params : '');
        window.open(url, '_blank', 'width=1000,height=700');
    };

    window.exportarCajaNegraCsv = function() {
        const params = filtrosCajaNegra();
        const url = '/caja-negra/exportar-csv' + (params ? '?' + params : '');
        window.open(url, '_blank');
    };

    function filtrosCajaNegra() {
        const params = new URLSearchParams();
        const usuario = document.getElementById('cnUsuario').value;
        const accion = document.getElementById('cnAccion').value;
        const tipo = document.getElementById('cnTipo').value;
        const desde = document.getElementById('cnDesde').value;
        const hasta = document.getElementById('cnHasta').value;
        const search = document.getElementById('cnSearch').value;
        if (search) params.set('search', search);
        if (usuario) params.set('user_id', usuario);
        if (accion) params.set('accion', accion);
        if (tipo) params.set('tipo_entidad', tipo);
        if (desde) params.set('from', desde);
        if (hasta) params.set('to', hasta);
        return params.toString();
    }

    // Search on enter with debounce
    let cnTimeout = null;
    document.getElementById('cnSearch').addEventListener('input', function() {
        clearTimeout(cnTimeout);
        cnTimeout = setTimeout(() => { cnPagina = 1; cargarCajaNegra(); }, 400);
    });

    /* ===== COPIAS DE SEGURIDAD ===== */
    function escaparBk(s) { return escaparHTML(s); }
    function formatearFecha(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        if (isNaN(d)) return iso;
        return d.toLocaleString('es-ES', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }
    function mostrarHashCorto(hash) {
        if (!hash) return '<span class="text-muted">—</span>';
        return '<span class="bk-hash" title="' + escaparBk(hash) + '">' + escaparBk(hash.slice(0, 16)) + '…</span>';
    }

    async function cargarBackups() {
        const el = document.getElementById('backupList');
        try {
            const r = await fetch('/backups');
            const data = await r.json();
            if (!data.backups || !data.backups.length) {
                el.innerHTML = '<div class="backup-empty"><i class="fas fa-database" style="font-size:2rem;display:block;margin-bottom:8px;color:#cbd5e1;"></i>No hay copias de seguridad disponibles</div>';
                return;
            }
            el.innerHTML = data.backups.map(function(b) {
                const estado = b.estado === 'Verificado'
                    ? '<span class="bk-badge bk-badge-ok"><i class="fas fa-circle-check"></i> Verificado</span>'
                    : '<span class="bk-badge bk-badge-bad"><i class="fas fa-circle-exclamation"></i> Inválido</span>';
                const restaurar = window.CN_ES_SUPERADMIN
                    ? '<button class="btn-bk-restore" onclick="restaurarBackup(\'' + escaparJS(b.nombre) + '\')" title="Restaurar (solo superadmin)"><i class="fas fa-rotate-left"></i> Restaurar</button>'
                    : '';
                return '<div class="backup-item">' +
                    '<div class="backup-info">' +
                        '<div class="backup-icon"><i class="fas fa-file-zipper"></i></div>' +
                        '<div style="flex:1;min-width:0;">' +
                            '<div class="backup-name">' + escaparBk(b.nombre) + '</div>' +
                            '<div class="backup-meta">' + formatearFecha(b.fecha) + ' — ' + escaparBk(b.tamano) + ' — ' + b.tipo + '</div>' +
                            '<div class="backup-meta" style="margin-top:5px;">SHA-256: ' + mostrarHashCorto(b.hash) + ' ' + estado + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="backup-actions">' +
                        '<button class="btn-bk-download" onclick="descargarBackup(\'' + escaparJS(b.nombre) + '\')"><i class="fas fa-download"></i> Descargar</button>' +
                        '<button class="btn-bk-verify" onclick="verificarBackup(\'' + escaparJS(b.nombre) + '\')"><i class="fas fa-shield-halved"></i> Verificar</button>' +
                        restaurar +
                        '<button class="btn-bk-delete" onclick="solicitarBorrarBackup(\'' + escaparJS(b.nombre) + '\')" title="Eliminar"><i class="fas fa-trash-can"></i></button>' +
                    '</div>' +
                '</div>';
            }).join('');
        } catch (e) {
            el.innerHTML = '<div class="backup-error">Error al cargar las copias de seguridad</div>';
        }
    }

    function escaparJS(s) {
        return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    window.generarBackup = async function() {
        const btn = document.getElementById('btnGenerarBackup');
        const el = document.getElementById('backupList');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
        el.innerHTML = '<div class="backup-loading"><i class="fas fa-spinner"></i> Generando copia de seguridad, esto puede tomar varios minutos...</div>';
        try {
            const r = await fetch('/backups/generar', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
            const data = await r.json();
            if (data.estado === 'error') { throw new Error(data.mensaje); }
            await cargarBackups();
            if (typeof mostrarToast === 'function') mostrarToast(data.mensaje || 'Copia generada', 'success');
        } catch (e) {
            el.innerHTML = '<div class="backup-error">Error: ' + escaparBk(e.message) + '</div>';
            if (typeof mostrarToast === 'function') mostrarToast('Error al generar copia', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-hard-drive"></i> Generar copia de seguridad';
        }
    };

    window.descargarBackup = function(nombre) {
        window.open('/backups/' + encodeURIComponent(nombre) + '/descargar', '_blank');
        if (typeof mostrarToast === 'function') setTimeout(() => mostrarToast('Descarga iniciada', 'info'), 600);
    };

    window.verificarBackup = async function(nombre) {
        if (typeof mostrarToast === 'function') mostrarToast('Verificando integridad (SHA-256)...', 'info');
        try {
            const r = await fetch('/backups/' + encodeURIComponent(nombre) + '/verificar');
            const data = await r.json();
            if (data.estado === 'error') { throw new Error(data.mensaje); }
            const ok = data.integridad === 'Integridad válida';
            if (typeof mostrarToast === 'function') mostrarToast(data.integridad || data.verificacion, ok ? 'success' : 'error');
            await cargarBackups();
        } catch (e) {
            if (typeof mostrarToast === 'function') mostrarToast('Error al verificar: ' + e.message, 'error');
        }
    };

    // === Eliminación con confirmación ===
    let bkABorrar = null;
    window.solicitarBorrarBackup = function(nombre) {
        bkABorrar = nombre;
        document.getElementById('bkDeleteNombre').textContent = nombre;
        document.getElementById('bkDeleteModal').classList.add('show');
    };
    window.cerrarModalBorrarBackup = function() { document.getElementById('bkDeleteModal').classList.remove('show'); };
    window.confirmarBorrarBackup = async function() {
        if (!bkABorrar) return;
        const btn = document.getElementById('btnConfirmarBorrarBk');
        btn.disabled = true;
        try {
            const r = await fetch('/backups/' + encodeURIComponent(bkABorrar), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
            const data = await r.json();
            if (data.estado === 'error') { throw new Error(data.mensaje); }
            if (typeof mostrarToast === 'function') mostrarToast('Copia eliminada y registrada en Caja Negra', 'success');
            cerrarModalBorrarBackup();
            await cargarBackups();
        } catch (e) {
            if (typeof mostrarToast === 'function') mostrarToast('Error al eliminar: ' + e.message, 'error');
        } finally {
            btn.disabled = false;
        }
    };
    document.getElementById('btnConfirmarBorrarBk').addEventListener('click', confirmarBorrarBackup);
    document.getElementById('btnCancelarBorrarBk').addEventListener('click', cerrarModalBorrarBackup);
    document.getElementById('btnCancelarBorrarBk2').addEventListener('click', cerrarModalBorrarBackup);

    // === Restauración segura (superadmin): flujo obligatorio ===
    window.restaurarBackup = function(nombre) {
        const modal = document.getElementById('bkRestoreModal');
        const content = document.getElementById('bkRestoreContent');
        modal.classList.add('show');
        content.innerHTML = '<p class="cn-modal-loading">Verificando integridad de la copia...</p>';
        pasoRestaurar1(nombre, content);
    };

    function pasoRestaurar1(nombre, content) {
        // 1) Verificar integridad
        fetch('/backups/' + encodeURIComponent(nombre) + '/verificar').then(r => r.json()).then(data => {
            if (data.estado === 'error') { throw new Error(data.mensaje); }
            const integridad = data.integridad === 'Integridad válida';
            content.innerHTML = `
                <h2 style="margin:0 0 12px;font-size:1rem;color:#0f172a;">Paso 1 de 3 — Verificación de integridad</h2>
                <div class="cn-detail-field"><label>Archivo</label><div class="value" style="word-break:break-all;">${escaparBk(nombre)}</div></div>
                <div class="cn-detail-field"><label>SHA-256 actual</label><div class="value" style="font-family:monospace;font-size:0.7rem;word-break:break-all;">${escaparBk(data.hash_actual)}</div></div>
                <div class="cn-detail-field"><label>SHA-256 registrado</label><div class="value" style="font-family:monospace;font-size:0.7rem;word-break:break-all;">${escaparBk(data.hash_registrado || '—')}</div></div>
                <div style="margin-top:16px;">${integridad
                    ? '<div class="bk-alert bk-alert-ok"><i class="fas fa-circle-check"></i> Integridad válida — la copia es segura para restaurar.</div>'
                    : '<div class="bk-alert bk-alert-bad"><i class="fas fa-circle-exclamation"></i> Integridad no válida — NO se puede restaurar esta copia.</div>'}</div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">
                    <button class="cn-btn-ghost" onclick="cerrarModalRestaurarBk()">Cancelar</button>
                    <button class="cn-btn-action cn-btn-primary" id="bkRestorePaso2" ${integridad ? '' : 'disabled'}>Continuar</button>
                </div>
            `;
            const btn2 = document.getElementById('bkRestorePaso2');
            if (btn2) btn2.addEventListener('click', () => pasoRestaurar2(nombre, content));
        }).catch(e => {
            content.innerHTML = '<p class="cn-modal-loading" style="color:#ef4444;">Error al verificar: ' + escaparBk(e.message) + '</p>';
        });
    }

    function pasoRestaurar2(nombre, content) {
        // 2) Advertencia + confirmación explícita
        content.innerHTML = `
            <h2 style="margin:0 0 12px;font-size:1rem;color:#0f172a;">Paso 2 de 3 — Confirmación</h2>
            <div class="bk-alert bk-alert-warn"><i class="fas fa-triangle-exclamation"></i>
                ADVERTENCIA: La restauración reemplazará la información actual de la base de datos con el contenido de esta copia.
                Se creará un <strong>backup preventivo</strong> automáticamente antes de restaurar.
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">
                <button class="cn-btn-ghost" onclick="cerrarModalRestaurarBk()">Cancelar</button>
                <button class="cn-btn-action cn-danger" id="bkRestorePaso3">Sí, continuar con la restauración</button>
            </div>
        `;
        document.getElementById('bkRestorePaso3').addEventListener('click', () => pasoRestaurar3(nombre, content));
    }

    function pasoRestaurar3(nombre, content) {
        // 3) Ejecutar restauración (backup preventivo + restaurar)
        content.innerHTML = '<p class="cn-modal-loading"><i class="fas fa-spinner fa-spin"></i> Creando backup preventivo y restaurando... Este proceso puede tardar varios minutos.</p>';
        fetch('/backups/' + encodeURIComponent(nombre) + '/restaurar', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
            .then(r => r.json()).then(data => {
                if (data.estado === 'error') {
                    content.innerHTML = `
                        <div class="bk-alert bk-alert-bad"><i class="fas fa-circle-xmark"></i> ${escaparBk(data.mensaje)}</div>
                        <div style="display:flex;justify-content:flex-end;margin-top:18px;">
                            <button class="cn-btn-ghost" onclick="cerrarModalRestaurarBk()">Cerrar</button>
                        </div>
                    `;
                    if (typeof mostrarToast === 'function') mostrarToast('Restauración fallida', 'error');
                    return;
                }
                content.innerHTML = `
                    <div class="bk-alert bk-alert-ok"><i class="fas fa-circle-check"></i> ${escaparBk(data.mensaje)}</div>
                    <div class="cn-detail-field"><label>Backup preventivo</label><div class="value">${escaparBk(data.preventivo || '—')}</div></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:18px;">
                        <button class="cn-btn-ghost" onclick="cerrarModalRestaurarBk()">Cerrar</button>
                    </div>
                `;
                if (typeof mostrarToast === 'function') mostrarToast('Restauración completada', 'success');
                cargarBackups();
            }).catch(e => {
                content.innerHTML = '<p class="cn-modal-loading" style="color:#ef4444;">Error: ' + escaparBk(e.message) + '</p>';
            });
    }

    window.cerrarModalRestaurarBk = function() { document.getElementById('bkRestoreModal').classList.remove('show'); };
    document.getElementById('btnCerrarRestaurarBk').addEventListener('click', cerrarModalRestaurarBk);

    // Cierre de modales al hacer clic fuera
    ['cnDetailModal', 'bkDeleteModal', 'bkRestoreModal'].forEach(idModal => {
        const m = document.getElementById(idModal);
        if (m) m.addEventListener('click', function(e) {
            if (e.target === m) m.classList.remove('show');
        });
    });
    const cerrarDetalleBtn = document.getElementById('btnCerrarCnDetalle');
    if (cerrarDetalleBtn) cerrarDetalleBtn.addEventListener('click', () => {
        document.getElementById('cnDetailModal').classList.remove('show');
    });

    /* ===== INIT ===== */
    document.addEventListener('DOMContentLoaded', () => {
        cargarStats();
        cargarUsuarios();
        cargarCajaNegra();
        cargarBackups();
    });

    const observer = new MutationObserver(m => {
        m.forEach(mm => {
            if (mm.target.id === 'caja-negra' && mm.target.classList.contains('active')) {
                cargarStats();
                cargarUsuarios();
                cargarCajaNegra();
                cargarBackups();
            }
        });
    });
    const sec = document.getElementById('caja-negra');
    if (sec) observer.observe(sec, { attributes: true, attributeFilter: ['class'] });
})();
</script>