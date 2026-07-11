/**
 * solicitudes.js — Script de la sección solicitudes.
 * CRUD completo con DataTable, filtros, exportación PDF y aprobación/rechazo.
 */

(function() {
    let currentStatus = 'all';

    /* === 1. AUTOCOMPLETE DE TRABAJADORES === */
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
                <span class="auto-name">${t.nombres} ${t.apellidos}</span>
                <span class="auto-cedula">${t.cedula}</span>
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

    /* === Input: buscar mientras escribe === */
    let timeoutBusqueda = null;
    document.addEventListener('input', function(e) {
        if (e.target.id !== 'inputBusquedaTrabajador') return;
        clearTimeout(timeoutBusqueda);

        const hiddenId = document.getElementById('hiddenTrabajadorId').value;
        const nombreActual = document.getElementById('displayNombreCompleto').value;
        if (hiddenId && e.target.value !== nombreActual) {
            limpiarSeleccion();
        }

        const val = e.target.value.trim();
        if (val.length === 0) {
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

    /* === Click en un item del autocomplete === */
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.autocomplete-item');
        if (!item) return;
        const idx = parseInt(item.dataset.index);
        const t = trabajadoresCache[idx];
        if (t) seleccionarTrabajador(t);
    });

    /* === Navegación por teclado === */
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

    /* === Cerrar autocomplete al hacer clic fuera === */
    document.addEventListener('click', function(e) {
        const input = document.getElementById('inputBusquedaTrabajador');
        const lista = document.getElementById('autocompleteList');
        if (!input || !lista) return;
        if (!input.contains(e.target) && !lista.contains(e.target)) {
            lista.style.display = 'none';
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
            const [resp, respPend, respAprob, respRech] = await Promise.all([
                fetch('/solicitudes?per_page=1'),
                fetch('/solicitudes?estado=pending&per_page=1'),
                fetch('/solicitudes?estado=approved&per_page=1'),
                fetch('/solicitudes?estado=rejected&per_page=1'),
            ]);
            const data = await resp.json();
            const dataPend = await respPend.json();
            const dataAprob = await respAprob.json();
            const dataRech = await respRech.json();
            document.getElementById('metricTotal').textContent = data.total || 0;
            document.getElementById('metricPendientes').textContent = dataPend.total || 0;
            document.getElementById('metricAprobadas').textContent = dataAprob.total || 0;
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

                mostrarToast(data.mensaje || 'Estatus actualizado correctamente.', 'success');
                cerrarModalEditar();
                cargarSolicitudes(currentStatus);
                cargarMetricas();

            } catch (err) {
                if (err.errores) {
                    mostrarToast(Object.values(err.errores).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.mensaje || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar Cambios'; }
            }
        });
    });

    /* === 11. Envío del formulario de creación (AJAX, sin recarga) === */
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formSolicitud');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
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
                form.reset();
                document.getElementById('displayNombreCompleto').value = '';
                document.getElementById('displayCedula').value = '';
                document.getElementById('hiddenTrabajadorId').value = '';
                document.getElementById('inputBusquedaTrabajador').value = '';
                cargarSolicitudes(currentStatus);
                cargarMetricas();

            } catch (err) {
                if (err.errores) {
                    mostrarToast(Object.values(err.errores).flat().join('\n'), 'error');
                } else {
                    mostrarToast(err.mensaje || 'Error en el sistema.', 'error');
                }
            } finally {
                ocultarCargando();
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Registrar Solicitud'; }
            }
        });
    });

    /* === 12. Exportar solicitudes a PDF === */
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
