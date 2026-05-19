<style>
    #displayCedula:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
</style>
<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
        <p>Administre y procese nuevas peticiones de retiro institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="abrirModalSolicitud()">
            <i data-lucide="plus-circle" size="20"></i> Nueva Solicitud
        </button>
    </div>
</header>

<div class="list-header">
    <div class="list-title-area">
        <h2 style="font-size: 1.5rem; color: #0f172a;">Listado de Solicitudes</h2>
        <div class="tab-filters" id="statusFilters">
            <button class="active" data-status="all">Todas</button>
            <button data-status="pending">Pendientes</button>
            <button data-status="approved">Aprobadas</button>
            <button data-status="rejected">Rechazadas</button>
        </div>
    </div>
    <div class="list-actions">
        <button class="btn-outline" onclick="exportarSolicitudesPDF()">
            <i data-lucide="download" size="16"></i> Exportar PDF
        </button>
    </div>
</div>

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

<div class="table-footer">
    <span id="solicitudesCounter">Mostrando 0 solicitudes</span>
    <div class="pagination" id="solicitudesPagination">
    </div>
</div>

<section class="metrics-row" style="margin-top: 20px;">
    <div class="metric-card">
        <div class="metric-icon orange"><i data-lucide="hourglass"></i></div>
        <div class="metric-data">
            <h3 id="metricPendientes">0</h3>
            <p>ESPERANDO REVISIÓN</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon green"><i data-lucide="check-circle-2"></i></div>
        <div class="metric-data">
            <h3 id="metricAprobadas">0</h3>
            <p>TOTAL APROBADAS</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon blue"><i data-lucide="timer"></i></div>
        <div class="metric-data">
            <h3 id="metricTotal">0</h3>
            <p>TOTAL SOLICITUDES</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon red"><i data-lucide="x-circle"></i></div>
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
                    <i data-lucide="info"></i>
                    <span>Los documentos PDF deben ser legibles y estar actualizados.</span>
                </div>
                <div class="info-item">
                    <i data-lucide="shield-check"></i>
                    <span>Este proceso cumple con la normativa de seguridad de datos institucionales.</span>
                </div>
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalSolicitud" type="button">&times;</button>
            <form id="formSolicitud" data-action="{{ route('solicitudes.store') }}">
                @csrf
                <section class="form-section">
                    <h3><i data-lucide="file-text"></i> Información General</h3>
                    <div class="form-row-3">
                        <div class="input-group">
                            <label>TRABAJADOR</label>
                            <select name="trabajador_id" id="selectTrabajador" required>
                                <option value="">Seleccione un trabajador...</option>
                            </select>
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
                            <option value="Edad Avanzada">Edad Avanzada</option>
                            <option value="Antigüedad">Antigüedad</option>
                            <option value="Invalidez">Invalidez</option>
                            <option value="Especial">Especial</option>
                        </select>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos del Trabajador</h3>
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
                    <h3><i data-lucide="align-left"></i> Observaciones</h3>
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
                    <h3><i data-lucide="file-text"></i> Solicitud</h3>
                    <p style="margin: 8px 0; font-size: 0.9rem; color: #475569;">
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
    <div class="modal-container" style="max-width: 560px;">
        <aside class="modal-sidebar">
            <span class="badge-new">DETALLE</span>
            <h1>Solicitud<br>de Jubilación</h1>
            <p>Información completa de la solicitud seleccionada.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalVer" type="button">&times;</button>
            <section class="form-section">
                <h3><i data-lucide="file-text"></i> Información General</h3>
                <div class="detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="detail-item"><span>Folio</span> <strong id="verFolio">—</strong></div>
                    <div class="detail-item"><span>Estatus</span> <strong id="verEstatus">—</strong></div>
                    <div class="detail-item"><span>Fecha Solicitud</span> <strong id="verFecha">—</strong></div>
                    <div class="detail-item"><span>Período</span> <strong id="verPeriodo">—</strong></div>
                    <div class="detail-item"><span>Tipo Jubilación</span> <strong id="verTipo">—</strong></div>
                </div>
            </section>
            <section class="form-section" style="margin-top: 16px;">
                <h3><i data-lucide="user"></i> Datos del Trabajador</h3>
                <div class="detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="detail-item"><span>Nombre</span> <strong id="verNombre">—</strong></div>
                    <div class="detail-item"><span>Cédula</span> <strong id="verCedula">—</strong></div>
                    <div class="detail-item"><span>Cargo</span> <strong id="verCargo">—</strong></div>
                    <div class="detail-item"><span>Unidad/Depto</span> <strong id="verUnidad">—</strong></div>
                    <div class="detail-item"><span>Edad</span> <strong id="verEdad">—</strong></div>
                    <div class="detail-item"><span>Años Servicio</span> <strong id="verAnosServicio">—</strong></div>
                </div>
            </section>
            <section class="form-section" style="margin-top: 16px;">
                <h3><i data-lucide="align-left"></i> Observaciones</h3>
                <p id="verObservaciones" style="color: #475569; font-size: 0.9rem; line-height: 1.5; background: #f8fafc; padding: 12px; border-radius: 8px;">—</p>
            </section>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="btnCerrarVer">Cerrar</button>
            </div>
        </main>
    </div>
</div>

<script>
(function() {
    let currentStatus = 'all';

    // === 1. CARGAR TRABAJADORES EN EL SELECT ===
    async function cargarSelectTrabajadores() {
        const select = document.getElementById('selectTrabajador');
        if (!select) return;
        try {
            const resp = await fetch('/trabajadores?per_page=1000');
            const data = await resp.json();
            select.innerHTML = '<option value="">Seleccione un trabajador...</option>';
            (data.data || []).forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.nombres} ${t.apellidos} — ${t.cedula}`;
                opt.dataset.nombres = t.nombres;
                opt.dataset.apellidos = t.apellidos;
                opt.dataset.cedula = t.cedula;
                select.appendChild(opt);
            });
        } catch (err) {
            console.error('Error al cargar trabajadores:', err);
        }
    }

    // === 2. BUSCAR POR CÉDULA AL ESCRIBIR ===
    let timeoutCedula = null;
    document.addEventListener('input', function(e) {
        if (e.target.id !== 'displayCedula') return;
        clearTimeout(timeoutCedula);

        const cedula = e.target.value.trim();
        if (cedula.length < 3) return;

        timeoutCedula = setTimeout(async () => {
            try {
                const resp = await fetch(`/trabajadores?search=${encodeURIComponent(cedula)}&per_page=1`);
                const data = await resp.json();
                if (!data.data || data.data.length === 0) return;

                const t = data.data[0];
                if (!t.cedula.toLowerCase().includes(cedula.toLowerCase())) return;

                const select = document.getElementById('selectTrabajador');
                const nombreInput = document.getElementById('displayNombreCompleto');

                Array.from(select.options).forEach(opt => {
                    if (opt.value == t.id) opt.selected = true;
                });

                nombreInput.value = (t.nombres || '') + ' ' + (t.apellidos || '');
                e.target.value = t.cedula;
            } catch (err) {
                console.error('Error al buscar cédula:', err);
            }
        }, 400);
    });

    // === 3. MOSTRAR DATOS DEL TRABAJADOR SELECCIONADO (dropdown) ===
    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectTrabajador') {
            const selected = e.target.selectedOptions[0];
            const nombreInput = document.getElementById('displayNombreCompleto');
            const cedulaInput = document.getElementById('displayCedula');
            const busquedaInput = document.getElementById('inputBusquedaTrabajador');
            if (selected && selected.value) {
                nombreInput.value = (selected.dataset.nombres || '') + ' ' + (selected.dataset.apellidos || '');
                cedulaInput.value = selected.dataset.cedula || '';
                if (busquedaInput) busquedaInput.value = '';
            } else {
                nombreInput.value = '';
                cedulaInput.value = '';
            }
        }
    });

    // === 3. CARGAR SOLICITUDES DESDE LA API ===
    async function cargarSolicitudes(estado = 'all') {
        const params = estado !== 'all' ? `?estado=${estado}` : '';
        try {
            const resp = await fetch('/solicitudes' + params);
            const data = await resp.json();
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

                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td class="folio">${folio}</td>
                    <td>
                        <div class="worker-info">
                            <strong>${nombre}</strong>
                            <span>${unidad}</span>
                        </div>
                    </td>
                    <td>${fecha}</td>
                    <td>${periodo}</td>
                    <td><span class="badge-status ${badgeClass}">${badgeText}</span></td>
                    <td class="actions">
                        <i data-lucide="eye" class="btn-icon btn-ver-solicitud" title="Ver Detalle" data-id="${s.id}"></i>
                        <i data-lucide="edit-2" class="btn-icon btn-editar-solicitud" title="Editar" data-id="${s.id}"></i>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();

            const total = data.total || data.data.length;
            document.getElementById('solicitudesCounter').textContent = `Mostrando ${data.data.length} de ${total} solicitudes`;

        } catch (err) {
            console.error('Error al cargar solicitudes:', err);
        }
    }

    // === 4. CARGAR MÉTRICAS ===
    async function cargarMetricas() {
        try {
            const resp = await fetch('/solicitudes?per_page=1');
            const data = await resp.json();
            const total = data.total || 0;
            document.getElementById('metricTotal').textContent = total;

            const respPend = await fetch('/solicitudes?estado=pending&per_page=1');
            const dataPend = await respPend.json();
            document.getElementById('metricPendientes').textContent = dataPend.total || 0;

            const respAprob = await fetch('/solicitudes?estado=approved&per_page=1');
            const dataAprob = await respAprob.json();
            document.getElementById('metricAprobadas').textContent = dataAprob.total || 0;

            const respRech = await fetch('/solicitudes?estado=rejected&per_page=1');
            const dataRech = await respRech.json();
            document.getElementById('metricRechazadas').textContent = dataRech.total || 0;
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

    // === 6. MODAL ===
    window.abrirModalSolicitud = function() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) {
            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    function cerrarModalSolicitud() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnCerrar = document.getElementById('closeModalSolicitud');
        const btnCancelar = document.getElementById('btnCancelarSolicitud');
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalSolicitud);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalSolicitud);

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('modalSolicitud');
            if (e.target === modal) cerrarModalSolicitud();
        });
    });

    // === 7. ENVÍO DEL FORMULARIO VIA AJAX ===
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formSolicitud');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const url = this.getAttribute('data-action');
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');

            if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Guardando...'; }

            try {
                mostrarCargando('Registrando solicitud...');
                const resp = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;

                mostrarToast(data.message || 'Solicitud registrada exitosamente.', 'success');
                this.reset();
                cerrarModalSolicitud();
                cargarSolicitudes(currentStatus);
                cargarMetricas();

            } catch (err) {
                if (err.errors) {
                    mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.message || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Registrar Solicitud'; }
            }
        });
    });

    // === 8. INICIALIZAR AL ENTRAR A LA PESTAÑA ===
    document.addEventListener('DOMContentLoaded', function() {
        cargarSelectTrabajadores();
        cargarSolicitudes('all');
        cargarMetricas();
    });

    // === 8.5 VER DETALLE DE SOLICITUD ===
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ver-solicitud');
        if (!btn) return;

        const id = btn.dataset.id;
        const folio = `#SOL-${String(id).padStart(4, '0')}`;

        mostrarCargando('Cargando detalle...');
        fetch(`/solicitudes/${id}`)
            .then(r => r.json())
            .then(s => {
                ocultarCargando();
                const t = s.trabajador || {};
                const badgeClass = s.estado === 'pendiente' ? 'pending' : (s.estado === 'aprobado' ? 'approved' : 'rejected');
                const badgeText = s.estado.charAt(0).toUpperCase() + s.estado.slice(1);
                const fecha = s.fecha_solicitud ? new Date(s.fecha_solicitud + 'T12:00:00').toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';

                document.getElementById('verFolio').textContent = folio;
                document.getElementById('verEstatus').innerHTML = `<span class="badge-status ${badgeClass}">${badgeText}</span>`;
                document.getElementById('verFecha').textContent = fecha;
                document.getElementById('verPeriodo').textContent = s.periodo || '—';
                document.getElementById('verTipo').textContent = s.tipo_jubilacion || '—';
                document.getElementById('verNombre').textContent = [t.nombres, t.apellidos].filter(Boolean).join(' ') || '—';
                document.getElementById('verCedula').textContent = t.cedula || '—';
                document.getElementById('verCargo').textContent = t.cargo || '—';
                document.getElementById('verUnidad').textContent = t.unidad_departamento || '—';
                document.getElementById('verEdad').textContent = t.edad != null ? `${t.edad} años` : '—';
                document.getElementById('verAnosServicio').textContent = t.total_anos_servicio != null ? `${t.total_anos_servicio} años` : '—';
                document.getElementById('verObservaciones').textContent = s.observaciones || 'Sin observaciones.';

                const modal = document.getElementById('modalVerSolicitud');
                if (modal) {
                    modal.style.display = 'flex';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            })
            .catch(err => {
                ocultarCargando();
                console.error('Error al cargar detalle:', err);
                mostrarToast('Error al cargar el detalle de la solicitud.', 'error');
            });
    });

    function cerrarModalVer() {
        const modal = document.getElementById('modalVerSolicitud');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnCerrar = document.getElementById('closeModalVer');
        const btnCancelar = document.getElementById('btnCerrarVer');
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalVer);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalVer);

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('modalVerSolicitud');
            if (e.target === modal) cerrarModalVer();
        });
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
                    if (typeof lucide !== 'undefined') lucide.createIcons();
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

    document.addEventListener('DOMContentLoaded', function() {
        const btnCerrar = document.getElementById('closeModalEditar');
        const btnCancelar = document.getElementById('btnCancelarEditar');
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalEditar);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalEditar);

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('modalEditarSolicitud');
            if (e.target === modal) cerrarModalEditar();
        });
    });

    // === 10. ENVÍO DEL FORMULARIO DE EDICIÓN ===
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formEditarSolicitud');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
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

                mostrarToast(data.message || 'Estatus actualizado correctamente.', 'success');
                cerrarModalEditar();
                cargarSolicitudes(currentStatus);
                cargarMetricas();

            } catch (err) {
                if (err.errors) {
                    mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.message || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar Cambios'; }
            }
        });
    });

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'solicitudes' && m.target.classList.contains('active')) {
                cargarSolicitudes(currentStatus);
                cargarMetricas();
            }
        });
    });
    const seccion = document.getElementById('solicitudes');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });

})();
</script>
