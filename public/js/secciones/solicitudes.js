/* ============================================================
   solicitudes.js — JavaScript para la sección "Solicitudes"
   Funcionalidad: CRUD de solicitudes de jubilación, búsqueda
   por cédula, filtros por estado (pendiente/aprobado/rechazado),
   métricas, modales de creación/edición/visualización y
   exportación a PDF.
   ============================================================ */

(function() {
    let currentStatus = 'all';

    /* === 1. Cargar trabajadores en el selector desplegable === */
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

    /* === 2. Buscar trabajador por cédula al escribir === */
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

    /* === 3. Mostrar datos del trabajador al seleccionar del dropdown === */
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

    /* === 4. Cargar solicitudes desde la API === */
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
                        <i class="fas fa-eye btn-icon btn-ver-solicitud" title="Ver Detalle" data-id="${s.id}"></i>
                        <i class="fas fa-pen btn-icon btn-editar-solicitud" title="Editar" data-id="${s.id}"></i>
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

    /* === 5. Cargar métricas (pendientes, aprobadas, total, rechazadas) === */
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

    /* === 6. Filtros por estado (tabs) === */
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#statusFilters button');
        if (!btn) return;

        document.querySelectorAll('#statusFilters button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentStatus = btn.dataset.status;
        cargarSolicitudes(currentStatus);
    });

    /* === 7. Modal de nueva solicitud === */
    window.abrirModalSolicitud = function() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) modal.style.display = 'flex';
    };

    function cerrarModalSolicitud() {
        const modal = document.getElementById('modalSolicitud');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnCerrar = document.getElementById('btnCancelarSolicitud');
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalSolicitud);

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('modalSolicitud');
            if (e.target === modal) cerrarModalSolicitud();
        });
    });

    /* === 8. Ver detalle de solicitud (modal informativo) === */
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ver-solicitud');
        if (!btn) return;

        const id = btn.dataset.id;
        const folio = '#SOL-' + String(id).padStart(4, '0');

        mostrarCargando('Cargando detalle...');
        fetch('/solicitudes/' + id)
            .then(r => r.json())
            .then(s => {
                ocultarCargando();
                document.getElementById('verFolio').value = folio;
                var t = s.trabajador || {};
                document.getElementById('verNombre').value = [t.nombres, t.apellidos].filter(Boolean).join(' ') || '—';
                document.getElementById('verCedula').value = t.cedula || '—';
                document.getElementById('verUnidad').value = t.unidad_departamento || '—';
                document.getElementById('verCargo').value = t.cargo || '—';
                document.getElementById('verEdad').value = t.edad || '—';
                document.getElementById('verAnosServicio').value = t.anos_servicio || '—';
                document.getElementById('verTipo').value = s.tipo_jubilacion || '—';
                document.getElementById('verFecha').value = s.fecha_solicitud ? new Date(s.fecha_solicitud + 'T12:00:00').toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
                document.getElementById('verPeriodo').value = s.periodo || '—';
                document.getElementById('verEstatus').value = s.estado ? s.estado.charAt(0).toUpperCase() + s.estado.slice(1) : '—';
                document.getElementById('verObservaciones').value = s.observaciones || '—';

                var modal = document.getElementById('modalVerSolicitud');
                if (modal) modal.style.display = 'flex';
            })
            .catch(function(err) {
                ocultarCargando();
                console.error('Error al cargar detalle:', err);
                mostrarToast('Error al cargar el detalle de la solicitud.', 'error');
            });
    });

    function cerrarModalVer() {
        const modal = document.getElementById('modalVerSolicitud');
        if (modal) modal.style.display = 'none';
    }

    /* === 9. Editar solicitud (cambiar estatus) === */
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

    /* === 10. Envío del formulario de edición (cambio de estatus) === */
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

    /* === 11. Exportar solicitudes a PDF === */
    window.exportarSolicitudesPDF = function() {
        const params = currentStatus !== 'all' ? `?estado=${currentStatus}` : '';
        window.open('/solicitudes/exportar' + params, '_blank', 'width=1000,height=700');
    };

    /* === Observador para recargar al cambiar a la pestaña === */
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
