{{-- solicitudes.blade.php - Sección de gestión de solicitudes de jubilación: CRUD completo con filtros por estado, métricas, modales de creación/edición/detalle y exportación PDF. --}}
<style>
    #displayCedula:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .autocomplete-list {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
        max-height: 280px; overflow-y: auto; display: none;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12); margin-top: 4px;
    }
    .autocomplete-list .autocomplete-item {
        padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s; display: flex; flex-direction: column;
    }
    .autocomplete-list .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-list .autocomplete-item:hover,
    .autocomplete-list .autocomplete-item.highlighted { background: #eff6ff; }
    .autocomplete-list .autocomplete-item .auto-name { font-weight: 600; color: #1e293b; font-size: 0.9rem; }
    .autocomplete-list .autocomplete-item .auto-cedula { font-size: 0.78rem; color: #64748b; }
    .autocomplete-list .autocomplete-empty {
        padding: 16px; text-align: center; color: #94a3b8; font-size: 0.85rem;
    }
</style>
<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
        <p>Administre y procese nuevas peticiones de retiro institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="abrirModalSolicitud()">
            <i class="fas fa-circle-plus" size="20"></i> Nueva Solicitud
        </button>
    </div>
</header>

<div class="list-header">
    <div class="list-title-area">
        <h2>Listado de Solicitudes</h2>
        <div class="tab-filters" id="statusFilters">
            <button class="active" data-status="all">Todas</button>
            <button data-status="pending">Pendientes</button>
            <button data-status="approved">Aprobadas</button>
            <button data-status="rejected">Rechazadas</button>
        </div>
    </div>
    <div class="list-actions">
        <button class="btn-outline" onclick="exportarSolicitudesPDF()">
            <i class="fas fa-download" size="16"></i> Exportar PDF
        </button>
    </div>
</div>

<div style="overflow-x:auto;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>FOLIO</th>
                <th>TRABAJADOR</th>
                <th>TIPO DE RETIRO</th>
                <th>FECHA APERTURA</th>
                <th>ESTATUS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodySolicitudes">
            </tbody>
    </table>
</div>

<div class="table-footer">
    <span id="solicitudesCounter">Mostrando 0 solicitudes</span>
    <div class="pagination" id="solicitudesPagination">
    </div>
</div>

<section class="metrics-row" style="margin-top: 20px;">
    <div class="metric-card">
        <div class="metric-icon orange"><i class="fas fa-hourglass"></i></div>
        <div class="metric-data">
            <h3 id="metricPendientes">0</h3>
            <p>ESPERANDO REVISIÓN</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="metric-data">
            <h3 id="metricAprobadas">0</h3>
            <p>TOTAL APROBADAS</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon blue"><i class="fas fa-stopwatch"></i></div>
        <div class="metric-data">
            <h3 id="metricTotal">0</h3>
            <p>TOTAL SOLICITUDES</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon red"><i class="fas fa-circle-xmark"></i></div>
        <div class="metric-data">
            <h3 id="metricRechazadas">0</h3>
            <p>TOTAL RECHAZADAS</p>
        </div>
    </div>
</section>

<div id="modalSolicitud" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">NUEVO REGISTRO</span>
            <h1>Registro de Solicitud de Jubilación</h1>
            <p>Complete cuidadosamente todos los campos requeridos para iniciar el proceso de retiro administrativo del trabajador.</p>
            <div class="modal-info-list">
                <div class="info-item">
                    <i class="fas fa-circle-info"></i>
                    <span>Los documentos PDF deben ser legibles y estar actualizados.</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-shield-check"></i>
                    <span>Este proceso cumple con la normativa de seguridad de datos institucionales.</span>
                </div>
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalSolicitud" type="button">&times;</button>
            <form id="formSolicitud" data-action="{{ route('solicitudes.store') }}">
                @csrf
                <section class="form-section">
                    <h3><i class="fas fa-file-lines"></i> Información General</h3>
                    <div class="form-row-3">
                        <div class="input-group" style="position:relative;">
                            <label>TRABAJADOR</label>
                            <input type="text" id="inputBusquedaTrabajador" placeholder="Escriba nombre o cédula..." autocomplete="off" required>
                            <input type="hidden" name="trabajador_id" id="hiddenTrabajadorId">
                            <div id="autocompleteList" class="autocomplete-list"></div>
                        </div>
                        <div class="input-group">
                            <label>FECHA SOLICITUD</label>
                            <input type="date" name="fecha_solicitud" required>
                        </div>
                        <div class="input-group">
                            <label>PERIODO</label>
                            <select name="periodo">
                                <option value="">Seleccione...</option>
                                <option value="2024-A">2024 - A</option>
                                <option value="2024-B">2024 - B</option>
                                <option value="2025-A">2025 - A</option>
                                <option value="2025-B">2025 - B</option>
                                <option value="2026-A">2026 - A</option>
                                <option value="2026-B">2026 - B</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>TIPO DE JUBILACIÓN</label>
                        <select name="tipo_jubilacion">
                            <option value="">Seleccione...</option>
                            <option value="Antigüedad">Antigüedad</option>
                            <option value="Invalidez">Invalidez</option>
                            <option value="Especial">Especial</option>
                        </select>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Datos del Trabajador</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" id="displayNombreCompleto" readonly placeholder="Seleccione un trabajador...">
                        </div>
                        <div class="input-group">
                            <label>CÉDULA</label>
                            <input type="text" id="displayCedula" placeholder="Escriba la cédula y presione Enter o TAB">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-align-left"></i> Observaciones</h3>
                    <textarea class="form-textarea" name="observaciones" placeholder="Indique cualquier detalle adicional relevante para el trámite..."></textarea>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarSolicitud">Cancelar</button>
                    <button type="submit" class="btn-submit">Registrar Solicitud</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalEditarSolicitud" class="modal-overlay">
    <div class="modal-container" style="max-width: 480px;">
        <aside class="modal-sidebar" style="max-width: 160px;">
            <span class="badge-new">ACTUALIZAR</span>
            <h1>Cambiar<br>Estatus</h1>
            <p>Actualice el estado de la solicitud de jubilación.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalEditar" type="button">&times;</button>
            <form id="formEditarSolicitud">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="solicitud_id" id="editSolicitudId">
                <section class="form-section">
                    <h3><i class="fas fa-file-lines"></i> Solicitud</h3>
                    <p style="margin: 8px 0; font-size: 0.9rem;">
                        <strong id="editFolio">—</strong> —
                        <span id="editTrabajadorNombre">—</span>
                    </p>
                    <div class="input-group">
                        <label>NUEVO ESTATUS</label>
                        <select name="estado" id="editEstado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="revision">En Revisión</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div class="input-group" style="margin-top: 12px;">
                        <label>OBSERVACIONES</label>
                        <textarea class="form-textarea" name="observaciones" placeholder="Motivo del cambio de estatus..."></textarea>
                    </div>
                </section>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarEditar">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalVerSolicitud" class="modal-overlay">
    <div class="modal-container" style="max-width: 820px;">
        <aside class="modal-sidebar">
            <span class="badge-new">DETALLE</span>
            <h1>Solicitud<br>de Jubilación</h1>
            <p>Información completa de la solicitud seleccionada.</p>
            <div class="sidebar-actions" style="margin-top: auto;">
                <button type="button" class="btn-sidebar-cancel" id="btnCerrarVer">Cerrar</button>
            </div>
        </aside>
        <main class="modal-form-content">
            <section class="form-section">
                <h3><i class="fas fa-file-lines"></i> Información General</h3>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>FOLIO</label>
                        <input type="text" id="verFolio" readonly>
                    </div>
                    <div class="input-group">
                        <label>ESTATUS</label>
                        <input type="text" id="verEstatus" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>FECHA SOLICITUD</label>
                        <input type="text" id="verFecha" readonly>
                    </div>
                    <div class="input-group">
                        <label>PERÍODO</label>
                        <input type="text" id="verPeriodo" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>TIPO JUBILACIÓN</label>
                        <input type="text" id="verTipo" readonly>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3><i class="fas fa-user"></i> Datos del Trabajador</h3>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>NOMBRE COMPLETO</label>
                        <input type="text" id="verNombre" readonly>
                    </div>
                    <div class="input-group">
                        <label>CÉDULA</label>
                        <input type="text" id="verCedula" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>CARGO</label>
                        <input type="text" id="verCargo" readonly>
                    </div>
                    <div class="input-group">
                        <label>UNIDAD / DEPARTAMENTO</label>
                        <input type="text" id="verUnidad" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>EDAD</label>
                        <input type="text" id="verEdad" readonly>
                    </div>
                    <div class="input-group">
                        <label>AÑOS DE SERVICIO</label>
                        <input type="text" id="verAnosServicio" readonly>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3><i class="fas fa-align-left"></i> Observaciones</h3>
                <textarea class="form-textarea" id="verObservaciones" readonly style="resize: none;">—</textarea>
            </section>
        </main>
    </div>
</div>

<script>
function escaparHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

(function() {
    let currentStatus = 'all';

    // === 1. AUTOCOMPLETE DE TRABAJADORES ===
    let autocompleteIndex = -1;
    let trabajadoresCache = [];

    async function cargarAutocomplete(search = '') {
        const url = search
            ? `/trabajadores/autocomplete?search=${encodeURIComponent(search)}`
            : '/trabajadores/autocomplete';
        try {
            const resp = await fetch(url);
            return await resp.json();
        } catch (err) {
            console.error('Error en autocomplete:', err);
            return [];
        }
    }

    function mostrarAutocomplete(resultados) {
        const lista = document.getElementById('autocompleteList');
        if (!lista) return;
        trabajadoresCache = resultados;
        autocompleteIndex = -1;

        if (!resultados || resultados.length === 0) {
            lista.innerHTML = '<div class="autocomplete-empty">Sin resultados</div>';
            lista.style.display = 'block';
            return;
        }

        lista.innerHTML = resultados.map((t, i) =>
            `<div class="autocomplete-item" data-index="${i}" data-id="${t.id}">
                <span class="auto-name">${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</span>
                <span class="auto-cedula">${escaparHTML(t.cedula)}</span>
            </div>`
        ).join('');
        lista.style.display = 'block';
    }

    function seleccionarTrabajador(trabajador) {
        document.getElementById('hiddenTrabajadorId').value = trabajador.id;
        document.getElementById('displayNombreCompleto').value = `${trabajador.nombres} ${trabajador.apellidos}`;
        document.getElementById('displayCedula').value = trabajador.cedula;
        document.getElementById('inputBusquedaTrabajador').value = `${trabajador.nombres} ${trabajador.apellidos}`;
        document.getElementById('autocompleteList').style.display = 'none';
    }

    function limpiarSeleccion() {
        document.getElementById('hiddenTrabajadorId').value = '';
        document.getElementById('displayNombreCompleto').value = '';
        document.getElementById('displayCedula').value = '';
    }

    // Input: buscar mientras escribe
    let timeoutBusqueda = null;
    document.addEventListener('input', function(e) {
        if (e.target.id !== 'inputBusquedaTrabajador') return;
        clearTimeout(timeoutBusqueda);

        // Si el usuario borró y no coincide con el seleccionado, limpiar
        const hiddenId = document.getElementById('hiddenTrabajadorId').value;
        const nombreActual = document.getElementById('displayNombreCompleto').value;
        if (hiddenId && e.target.value !== nombreActual) {
            limpiarSeleccion();
        }

        const val = e.target.value.trim();
        if (val.length === 0) {
            // Sin búsqueda: cargar últimos 10
            timeoutBusqueda = setTimeout(async () => {
                const r = await cargarAutocomplete();
                mostrarAutocomplete(r);
            }, 200);
            return;
        }
        if (val.length < 2) return;

        timeoutBusqueda = setTimeout(async () => {
            const r = await cargarAutocomplete(val);
            mostrarAutocomplete(r);
        }, 300);
    });

    // Click en un item del autocomplete
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.autocomplete-item');
        if (!item) return;
        const idx = parseInt(item.dataset.index);
        const t = trabajadoresCache[idx];
        if (t) seleccionarTrabajador(t);
    });

    // Navegación por teclado (flechas + enter)
    document.addEventListener('keydown', function(e) {
        if (e.target.id !== 'inputBusquedaTrabajador') return;
        const lista = document.getElementById('autocompleteList');
        if (!lista || lista.style.display !== 'block') return;

        const items = lista.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (autocompleteIndex < items.length - 1) autocompleteIndex++;
            items.forEach((el, i) => el.classList.toggle('highlighted', i === autocompleteIndex));
            if (autocompleteIndex >= 0) items[autocompleteIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (autocompleteIndex > 0) autocompleteIndex--;
            items.forEach((el, i) => el.classList.toggle('highlighted', i === autocompleteIndex));
            if (autocompleteIndex >= 0) items[autocompleteIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            if (autocompleteIndex >= 0 && autocompleteIndex < items.length) {
                e.preventDefault();
                const t = trabajadoresCache[autocompleteIndex];
                if (t) seleccionarTrabajador(t);
            }
        } else if (e.key === 'Escape') {
            lista.style.display = 'none';
        }
    });

    // Cerrar autocomplete al hacer clic fuera
    document.addEventListener('click', function(e) {
        const input = document.getElementById('inputBusquedaTrabajador');
        const lista = document.getElementById('autocompleteList');
        if (!input || !lista) return;
        if (!input.contains(e.target) && !lista.contains(e.target)) {
            lista.style.display = 'none';
        }
    });

    // === 3. CARGAR SOLICITUDES DESDE LA API ===
    async function cargarSolicitudes(estado = 'all') {
        const params = estado !== 'all' ? `?estado=${estado}` : '';
        try {
            const result = await cachedFetch('/solicitudes' + params, { ttl: 60000 });
            const data = result.data;
            const tbody = document.getElementById('tbodySolicitudes');
            if (!tbody) return;

            tbody.innerHTML = '';

            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 2rem; color: #888;">No hay solicitudes registradas</td></tr>';
                document.getElementById('solicitudesCounter').textContent = 'Mostrando 0 solicitudes';
                return;
            }

            data.data.forEach(s => {
                const t = s.trabajador || {};
                const nombre = [t.nombres, t.apellidos].filter(Boolean).join(' ') || '—';
                const unidad = t.unidad_departamento || '—';
                const folio = `#SOL-${String(s.id).padStart(4, '0')}`;
                const fecha = s.fecha_solicitud ? new Date(s.fecha_solicitud + 'T12:00:00').toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
                const periodo = s.periodo || '—';

                const badgeClass = s.estado === 'pendiente' ? 'pending' : (s.estado === 'aprobado' ? 'approved' : 'rejected');
                const badgeText = s.estado.charAt(0).toUpperCase() + s.estado.slice(1);

                const tipoJub = s.tipo_jubilacion || '—';
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td class="folio">${escaparHTML(folio)}</td>
                    <td>
                        <div class="worker-info">
                            <strong>${escaparHTML(nombre)}</strong>
                            <span>${escaparHTML(unidad)}</span>
                        </div>
                    </td>
                    <td>${escaparHTML(tipoJub)}</td>
                    <td>${escaparHTML(fecha)}</td>
                    <td><span class="badge-status ${escaparHTML(badgeClass)}">${escaparHTML(badgeText)}</span></td>
                    <td class="actions">
                        <i class="fas fa-eye btn-icon btn-ver-solicitud" title="Ver Detalle" data-id="${escaparHTML(String(s.id))}"></i>
                        <i class="fas fa-pen btn-icon btn-editar-solicitud" title="Editar" data-id="${escaparHTML(String(s.id))}"></i>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            const total = data.total || data.data.length;
            document.getElementById('solicitudesCounter').textContent = `Mostrando ${data.data.length} de ${total} solicitudes`;

        } catch (err) {
            console.error('Error al cargar solicitudes:', err);
        }
    }

    // === 4. CARGAR MÉTRICAS ===
    async function cargarMetricas() {
        try {
            const [cTotal, cStats] = await Promise.all([
                cachedFetch('/solicitudes?per_page=1', { ttl: 60000 }),
                cachedFetch('/solicitudes/estadisticas', { ttl: 60000 }),
            ]);
            const stats = cStats.data;
            document.getElementById('metricTotal').textContent = cTotal.data.total || 0;
            document.getElementById('metricPendientes').textContent = stats.pendiente || 0;
            document.getElementById('metricAprobadas').textContent = stats.aprobado || 0;
            document.getElementById('metricRechazadas').textContent = stats.rechazado || 0;
        } catch (err) {
            console.error('Error al cargar métricas:', err);
        }
    }

    // === 5. FILTROS POR ESTADO ===
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#statusFilters button');
        if (!btn) return;

        document.querySelectorAll('#statusFilters button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentStatus = btn.dataset.status;
        cargarSolicitudes(currentStatus);
    });

    // === 6. MODAL NUEVA SOLICITUD ===
    window.abrirModalSolicitud = async function() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) modal.style.display = 'flex';
        const input = document.getElementById('inputBusquedaTrabajador');
        if (input) {
            input.value = '';
            input.focus();
        }
        limpiarSeleccion();
        const r = await cargarAutocomplete();
        mostrarAutocomplete(r);
    };

    function cerrarModalSolicitud() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) modal.style.display = 'none';
        document.getElementById('autocompleteList').style.display = 'none';
    }

    function cerrarModalVer() {
        const modal = document.getElementById('modalVerSolicitud');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('closeModalSolicitud')?.addEventListener('click', cerrarModalSolicitud);
    document.getElementById('btnCancelarSolicitud')?.addEventListener('click', cerrarModalSolicitud);
    window.addEventListener('click', (e) => {
        const m = document.getElementById('modalSolicitud');
        if (e.target === m) cerrarModalSolicitud();
    });

    document.getElementById('btnCerrarVer')?.addEventListener('click', cerrarModalVer);
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('modalVerSolicitud');
        if (e.target === modal) cerrarModalVer();
    });

    // === 9. EDITAR SOLICITUD (Cambiar estatus) ===
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-editar-solicitud');
        if (!btn) return;

        const id = btn.dataset.id;
        const folio = `#SOL-${String(id).padStart(4, '0')}`;

        mostrarCargando('Cargando solicitud...');
        fetch(`/solicitudes/${id}`)
            .then(r => r.json())
            .then(s => {
                ocultarCargando();
                document.getElementById('editSolicitudId').value = s.id;
                document.getElementById('editFolio').textContent = folio;
                const t = s.trabajador || {};
                document.getElementById('editTrabajadorNombre').textContent = [t.nombres, t.apellidos].filter(Boolean).join(' ') || '—';
                document.getElementById('editEstado').value = s.estado;
                document.getElementById('formEditarSolicitud').querySelector('[name="observaciones"]').value = s.observaciones || '';

                const modal = document.getElementById('modalEditarSolicitud');
                if (modal) {
                    modal.style.display = 'flex';
                }
            })
            .catch(err => {
                ocultarCargando();
                console.error('Error al cargar solicitud:', err);
                mostrarToast('Error al cargar los datos de la solicitud.', 'error');
            });
    });

    function cerrarModalEditar() {
        const modal = document.getElementById('modalEditarSolicitud');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('closeModalEditar')?.addEventListener('click', cerrarModalEditar);
    document.getElementById('btnCancelarEditar')?.addEventListener('click', cerrarModalEditar);
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('modalEditarSolicitud');
        if (e.target === modal) cerrarModalEditar();
    });

    // === 10. ENVÍO DEL FORMULARIO DE EDICIÓN ===
    const formEdit = document.getElementById('formEditarSolicitud');
    if (formEdit) {

        formEdit.addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = document.getElementById('editSolicitudId').value;
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');

            if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Guardando...'; }

            try {
                mostrarCargando('Actualizando estatus...');
                const resp = await fetch(`/solicitudes/${id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;

                mostrarToast(data.mensaje || 'Estatus actualizado correctamente.', 'success');
                cerrarModalEditar();
                Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/solicitudes')).forEach(k => localStorage.removeItem(k));
                cargarSolicitudes(currentStatus);
                cargarMetricas();

            } catch (err) {
                if (err.errors) {
                    mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.mensaje || err.message || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar Cambios'; }
            }
        });
    }

    // === 11. ENVÍO DEL FORMULARIO DE CREACIÓN ===
    const formCreate = document.getElementById('formSolicitud');
    if (formCreate) {
        formCreate.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');
            if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Registrando...'; }
            try {
                const hiddenId = document.getElementById('hiddenTrabajadorId').value;
                if (!hiddenId) {
                    mostrarToast('Debe seleccionar un trabajador de la lista.', 'error');
                    if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Registrar Solicitud'; }
                    return;
                }
                mostrarCargando('Registrando solicitud...');
                const resp = await fetch('/solicitudes', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje || 'Solicitud registrada exitosamente.', 'success');
                cerrarModalSolicitud();
                formCreate.reset();
                document.getElementById('displayNombreCompleto').value = '';
                document.getElementById('displayCedula').value = '';
                document.getElementById('hiddenTrabajadorId').value = '';
                document.getElementById('inputBusquedaTrabajador').value = '';
                Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/solicitudes')).forEach(k => localStorage.removeItem(k));
                cargarSolicitudes(currentStatus);
                cargarMetricas();
            } catch (err) {
                if (err.errors) {
                    mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.mensaje || err.message || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Registrar Solicitud'; }
            }
        });
    }

    // === EXPORTAR PDF ===
    window.exportarSolicitudesPDF = function() {
        const params = currentStatus !== 'all' ? `?estado=${currentStatus}` : '';
        window.open('/solicitudes/exportar' + params, '_blank', 'width=1000,height=700');
    };

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'solicitudes' && m.target.classList.contains('active')) {
                Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/solicitudes')).forEach(k => localStorage.removeItem(k));
                cargarSolicitudes(currentStatus);
                cargarMetricas();
            }
        });
    });
    const seccion = document.getElementById('solicitudes');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });

})();
</script>
