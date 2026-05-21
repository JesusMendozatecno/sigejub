/**
 * SIGEJUB - Sistema de Gestión de Jubilaciones
 * Módulo Único Integrado: Directorio de Trabajadores, Modales Dinámicos y AJAX seguro
 */
(function() {
    let trabajadorSeleccionadoId = null;
    let filaSeleccionadaDOM = null;
    let modoEdicionActivo = false;

    // Elementos del DOM del Modal Principal
    const modal = document.getElementById('modalTrabajador');
    const form = document.getElementById('formTrabajador');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const btnSubmit = document.getElementById('btnSubmitTrabajador');
    const btnHabilitarEdicion = document.getElementById('btnHabilitarEdicion');

    // Elementos del Modal Eliminar
    const modalEliminar = document.getElementById('modalEliminarConfirm');
    const deleteWorkerName = document.getElementById('deleteWorkerName');

    document.addEventListener('DOMContentLoaded', () => {
        const btnRegistrar = document.getElementById('btnRegistrarTrabajador');
        const btnCerrar = document.getElementById('closeModal');
        const btnCancelar = document.getElementById('btnCancelar');
        const btnNoEliminar = document.getElementById('btnNoEliminar');
        const btnSiEliminar = document.getElementById('btnSiEliminar');

        // Configurar para registrar nuevo trabajador
        if (btnRegistrar) {
            btnRegistrar.addEventListener('click', (e) => {
                e.preventDefault();
                if (!modal) return;
                modoEdicionActivo = false;
                trabajadorSeleccionadoId = null;
                
                form.reset();
                habilitarCamposFormulario(true);

                modalTitle.innerHTML = "Registrar<br>Nuevo<br>Trabajador";
                modalDescription.textContent = "Complete el expediente institucional para iniciar el cálculo de antigüedad.";
                if (btnHabilitarEdicion) btnHabilitarEdicion.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.style.display = 'block';
                    btnSubmit.textContent = 'Registrar Trabajador';
                }

                modal.style.display = 'flex';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        // Manejadores para cerrar modales de forma segura
        const cerrarTodoModal = () => { if (modal) modal.style.display = 'none'; };
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarTodoModal);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrarTodoModal);
        if (btnNoEliminar) btnNoEliminar.addEventListener('click', () => { if (modalEliminar) modalEliminar.style.display = 'none'; });

        window.addEventListener('click', (e) => {
            if (e.target === modal) cerrarTodoModal();
            if (e.target === modalEliminar) modalEliminar.style.display = 'none';
        });

        // Alternar modo visualización a edición dentro del modal
        if (btnHabilitarEdicion) {
            btnHabilitarEdicion.addEventListener('click', async function(e) {
                e.preventDefault();
                if (modoEdicionActivo) {
                    // Ya estamos en edición → este clic envía el formulario
                    form.requestSubmit();
                    return;
                }
                modoEdicionActivo = true;
                habilitarCamposFormulario(true);
                modalTitle.innerHTML = "Modificar<br>Expediente";
                btnHabilitarEdicion.innerHTML = '<i data-lucide="save"></i> Guardar Cambios';
                if (btnSubmit) btnSubmit.style.display = 'none';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        // Procesamiento del formulario vía AJAX
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (btnSubmit) btnSubmit.disabled = true;

                const formData = new FormData(this);
                let url = '/trabajadores'; // Ruta de creación
                
                if (modoEdicionActivo && trabajadorSeleccionadoId) {
                    url = `/trabajadores/${trabajadorSeleccionadoId}`;
                    formData.append('_method', 'PUT');
                }

                try {
                    mostrarCargando(modoEdicionActivo ? 'Guardando cambios...' : 'Registrando trabajador...');
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok) throw data;

                    mostrarToast(data.message || 'Operación completada con éxito.', 'success');
                    form.reset();
                    cerrarTodoModal();
                    cargarTrabajadores();

                } catch (err) {
                    console.error(err);
                    if (err.errors) {
                        mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
                    } else {
                        mostrarToast(err.message || 'Error interno de comunicación.', 'error');
                    }
                } finally {
                    ocultarCargando();
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = modoEdicionActivo ? 'Guardar Cambios' : 'Registrar Trabajador';
                    }
                }
            });
        }

        // Filtros reactivos
        const selEstatus = document.getElementById('filtroEstatus');
        if (selEstatus) selEstatus.addEventListener('change', () => cargarTrabajadores(selEstatus.value));
        
        const selNomina = document.getElementById('filtroNomina');
        if (selNomina) selNomina.addEventListener('change', () => cargarTrabajadores(selEstatus?.value || '', selNomina.value));

        if (btnSiEliminar) {
            btnSiEliminar.addEventListener('click', ejecutarBajaTrabajador);
        }
    });

    // Función para renderizar la tabla asíncronamente
    async function cargarTrabajadores(estatus = '', nomina = '') {
        const tbody = document.getElementById('tbodyTrabajadores');
        if (!tbody) return;

        try {
            const url = `/trabajadores?estatus=${estatus}&nomina=${nomina}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al consultar servidores');
            const data = await response.json();

            const items = data.data || [];
            tbody.innerHTML = '';
            document.getElementById('totalTrabajadores').textContent = data.total || items.length || 0;

            items.forEach(t => {
                const tr = document.createElement('tr');
                tr.dataset.id = t.id;

                const tdId = document.createElement('td'); tdId.textContent = t.id;
                const tdNombre = document.createElement('td');
                const strong = document.createElement('strong'); strong.textContent = `${t.nombres} ${t.apellidos}`;
                tdNombre.appendChild(strong);
                const tdCedula = document.createElement('td'); tdCedula.textContent = t.cedula;
                const tdCargo = document.createElement('td'); tdCargo.textContent = t.cargo;
                const tdTipo = document.createElement('td');
                const typeTag = document.createElement('span'); typeTag.className = 'type-tag'; typeTag.textContent = t.unidad_departamento;
                tdTipo.appendChild(typeTag);
                const tdEstatus = document.createElement('td');
                const statusPill = document.createElement('span');
                const esActivo = t.estatus === 'activo';
                statusPill.className = `status-pill ${esActivo ? 'active' : 'retired'}`;
                statusPill.textContent = (t.estatus || 'SIN ESTATUS').toUpperCase();
                tdEstatus.appendChild(statusPill);

                const tdAcciones = document.createElement('td');
                const div = document.createElement('div'); div.className = 'acciones-cell';
                const btnVer = document.createElement('button');
                btnVer.className = 'btn-icon btn-ver';
                btnVer.title = 'Ver Expediente';
                btnVer.onclick = () => window.sigejubVerTrabajador(t.id, btnVer);
                const iconEye = document.createElement('i'); iconEye.setAttribute('data-lucide', 'eye');
                btnVer.appendChild(iconEye);
                const btnEliminar = document.createElement('button');
                btnEliminar.className = 'btn-icon btn-eliminar';
                btnEliminar.title = 'Dar de Baja';
                btnEliminar.onclick = () => window.sigejubEliminarTrabajador(t.id, `${t.nombres} ${t.apellidos}`, btnEliminar);
                const iconTrash = document.createElement('i'); iconTrash.setAttribute('data-lucide', 'trash-2');
                btnEliminar.appendChild(iconTrash);
                div.appendChild(btnVer);
                div.appendChild(btnEliminar);
                tdAcciones.appendChild(div);

                tr.append(tdId, tdNombre, tdCedula, tdCargo, tdTipo, tdEstatus, tdAcciones);
                tbody.appendChild(tr);

                if (typeof lucide !== 'undefined') lucide.createIcons();
            });

        } catch (err) {
            console.error('Error render-table:', err);
        }
    }

    window.sigejubVerTrabajador = async function(id, elemento) {
        if (!modal) return;
        filaSeleccionadaDOM = elemento.closest('tr');
        trabajadorSeleccionadoId = id;
        modoEdicionActivo = false;

        try {
            mostrarCargando('Cargando expediente...');
            const res = await fetch(`/trabajadores/${id}`);
            if (!res.ok) throw new Error('No se pudo obtener la información del trabajador');
            const t = await res.json();

            // Rellenar formulario
            document.getElementById('inputCedula').value = t.cedula;
            document.getElementById('selectGenero').value = t.genero;
            document.getElementById('inputNombres').value = t.nombres;
            document.getElementById('inputApellidos').value = t.apellidos;
            document.getElementById('inputFechaNacimiento').value = t.fecha_nacimiento;
            document.getElementById('inputCargo').value = t.cargo;
            document.getElementById('inputUnidadDepartamento').value = t.unidad_departamento;
            document.getElementById('inputGradoNivel').value = t.grado_nivel;
            document.getElementById('inputFechaIngreso').value = t.fecha_ingreso;
            document.getElementById('inputAnosExterno').value = t.anos_servicio_externo;
            document.getElementById('inputPorcentajeAntiguedad').value = t.porcentaje_antiguedad;
            document.getElementById('selectNivelInstruccion').value = t.nivel_instruccion;
            document.getElementById('inputCuentaBancaria').value = t.cuenta_bancaria || '';

            habilitarCamposFormulario(false);

            modalTitle.innerHTML = "Expediente<br>Laboral";
            modalDescription.textContent = "Modo de lectura institucional. Use el botón inferior para editar.";
            if (btnHabilitarEdicion) {
                btnHabilitarEdicion.innerHTML = '<i data-lucide="edit-3"></i> Editar Expediente';
                btnHabilitarEdicion.style.display = 'flex';
            }
            if (btnSubmit) btnSubmit.style.display = 'none';

            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();

        } catch (err) {
            mostrarToast(err.message, 'error');
        } finally {
            ocultarCargando();
        }
    };

    window.sigejubEliminarTrabajador = function(id, nombre, elemento) {
        trabajadorSeleccionadoId = id;
        filaSeleccionadaDOM = elemento.closest('tr');
        if (deleteWorkerName) deleteWorkerName.textContent = nombre;
        if (modalEliminar) modalEliminar.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    async function ejecutarBajaTrabajador() {
        if (!trabajadorSeleccionadoId) return;
        try {
            mostrarCargando('Procesando baja...');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
            const res = await fetch(`/trabajadores/${trabajadorSeleccionadoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            if (res.ok) {
                if (modalEliminar) modalEliminar.style.display = 'none';
                if (filaSeleccionadaDOM) {
                    filaSeleccionadaDOM.style.opacity = '0';
                    setTimeout(() => {
                        filaSeleccionadaDOM.remove();
                        const badgeTotal = document.getElementById('totalTrabajadores');
                        if (badgeTotal) badgeTotal.textContent = Math.max(0, parseInt(badgeTotal.textContent) - 1);
                    }, 300);
                }
                mostrarToast(data.message || 'Baja procesada.', 'success');
            } else {
                mostrarToast(data.message || 'Error al procesar la baja.', 'error');
            }
        } catch (err) {
            mostrarToast('Error de red al intentar conectar.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    function habilitarCamposFormulario(condicion) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(i => i.disabled = !condicion);
    }

    // Cargar estadísticas del dashboard de trabajadores
    async function cargarTrabajadoresStats() {
        try {
            const resp = await fetch('/trabajadores-stats/dashboard');
            const data = await resp.json();

            // Próximas jubilaciones
            const lista = document.getElementById('proximasJubilacionesList');
            if (lista && data.proximas) {
                if (!data.proximas.length) {
                    lista.innerHTML = '<p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">No hay trabajadores próximos a jubilarse.</p>';
                } else {
                    lista.innerHTML = '<p style="color:rgba(255,255,255,0.85);font-size:0.85rem;margin-bottom:10px;">' +
                        data.proximas.length + ' trabajadores próximos a cumplir requisitos:</p>' +
                        data.proximas.slice(0, 5).map(t =>
                            `<div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.1);font-size:0.82rem;">
                                <span>${t.nombres} ${t.apellidos}</span>
                                <span style="color:rgba(255,255,255,0.6);font-size:0.75rem;">${t.edad || '—'} años · ${t.total_anos_servicio || 0} años serv.</span>
                            </div>`
                        ).join('');
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            // Estatus de datos
            const texto = document.getElementById('estatusDatosTexto');
            const barra = document.getElementById('estatusDatosBarra');
            const pct = document.getElementById('estatusDatosPorcentaje');
            if (texto) {
                texto.textContent = `${data.porcentaje_expedientes}% de los trabajadores cuentan con expediente digital (${data.total_expedientes} de ${data.total_trabajadores}).`;
            }
            if (barra) barra.style.width = data.porcentaje_expedientes + '%';
            if (pct) pct.textContent = data.porcentaje_expedientes + '%';
        } catch (err) {
            console.error('Error al cargar stats de trabajadores:', err);
        }
    }

    // Observer para la inicialización al cambiar de pestaña
    let estabaActivo = false;
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'trabajadores') {
                const actualmenteActivo = m.target.classList.contains('active');
                if (actualmenteActivo && !estabaActivo) {
                    cargarTrabajadores();
                    cargarTrabajadoresStats();
                }
                estabaActivo = actualmenteActivo;
            }
        });
    });

    const seccion = document.getElementById('trabajadores');
    if (seccion) {
        estabaActivo = seccion.classList.contains('active');
        if (estabaActivo) cargarTrabajadoresStats();
        observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    }
})();