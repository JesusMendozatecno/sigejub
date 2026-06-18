/**
 * secciones/trabajador.js — Script de la sección trabajador.
 * Gestiona CRUD con DataTable, filtros por estatus y búsqueda.
 */

(function() {
    // ============================================
    // ESTADO GLOBAL DEL MÓDULO
    // ============================================
    let trabajadorSeleccionadoId = null;   // ID del trabajador en edición/visualización
    let filaSeleccionadaDOM = null;        // Referencia a la fila <tr> para animaciones
    let modoEdicionActivo = false;         // true = modo edición, false = solo lectura

    // ============================================
    // REFERENCIAS AL DOM — Modal principal y formulario
    // ============================================
    const modal = document.getElementById('modalTrabajador');
    const form = document.getElementById('formTrabajador');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const btnSubmit = document.getElementById('btnSubmitTrabajador');
    const btnHabilitarEdicion = document.getElementById('btnHabilitarEdicion');

    // Referencias al modal de confirmación de eliminación
    const modalEliminar = document.getElementById('modalEliminarConfirm');
    const deleteWorkerName = document.getElementById('deleteWorkerName');

    // ============================================
    // INICIALIZACIÓN — Eventos al cargar el DOM
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        const btnRegistrar = document.getElementById('btnRegistrarTrabajador');
        const btnCerrar = document.getElementById('closeModal');
        const btnCancelar = document.getElementById('btnCancelar');
        const btnNoEliminar = document.getElementById('btnNoEliminar');
        const btnSiEliminar = document.getElementById('btnSiEliminar');

        // ============================================
        // REGISTRAR NUEVO TRABAJADOR — Abre el modal en modo creación
        // ============================================
        if (btnRegistrar) {
            btnRegistrar.addEventListener('click', (e) => {
                e.preventDefault();
                if (!modal) return;
                modoEdicionActivo = false;
                trabajadorSeleccionadoId = null;

                form.reset();
                habilitarCamposFormulario(true);  // Todos los campos editables

                modalTitle.innerHTML = "Registrar<br>Nuevo<br>Trabajador";
                modalDescription.textContent = "Complete el expediente institucional para iniciar el cálculo de antigüedad.";
                if (btnHabilitarEdicion) btnHabilitarEdicion.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.style.display = 'block';
                    btnSubmit.textContent = 'Registrar Trabajador';
                }

                modal.style.display = 'flex';
            });
        }

        // ============================================
        // CIERRE DE MODALES — Botones y clic fuera
        // ============================================
        const cerrarTodoModal = () => { if (modal) modal.style.display = 'none'; };
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarTodoModal);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrarTodoModal);
        if (btnNoEliminar) btnNoEliminar.addEventListener('click', () => { if (modalEliminar) modalEliminar.style.display = 'none'; });

        // Cualquier clic en el fondo oscuro cierra el modal correspondiente
        window.addEventListener('click', (e) => {
            if (e.target === modal) cerrarTodoModal();
            if (e.target === modalEliminar) modalEliminar.style.display = 'none';
        });

        // ============================================
        // BOTÓN DE EDICIÓN — Alterna entre vista y edición dentro del modal
        // ============================================
        if (btnHabilitarEdicion) {
            btnHabilitarEdicion.addEventListener('click', async function(e) {
                e.preventDefault();
                if (modoEdicionActivo) {
                    // Segundo clic: envía el formulario para guardar cambios
                    form.requestSubmit();
                    return;
                }
                // Primer clic: activa la edición de campos
                modoEdicionActivo = true;
                habilitarCamposFormulario(true);
                modalTitle.innerHTML = "Modificar<br>Expediente";
                btnHabilitarEdicion.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar Cambios';
                if (btnSubmit) btnSubmit.style.display = 'none';
            });
        }

        // ============================================
        // ENVÍO DEL FORMULARIO — AJAX (POST para crear, PUT con spoofing para editar)
        // ============================================
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (btnSubmit) btnSubmit.disabled = true;

                const formData = new FormData(this);
                let url = '/trabajadores';  // Ruta por defecto: creación

                // Si está en modo edición, cambia la URL y agrega spoofing de método PUT
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

                    mostrarToast(data.mensaje || 'Operación completada con éxito.', 'success');
                    form.reset();
                    cerrarTodoModal();
                    cargarTrabajadores();  // Refresca la tabla sin recargar la página

                } catch (err) {
                    console.error(err);
                    if (err.errores) {
                        // Errores de validación de Laravel: aplana el array y los muestra en un toast
                        mostrarToast(Object.values(err.errores).flat().join('\n'), 'error');
                    } else {
                        mostrarToast(err.mensaje || 'Error interno de comunicación.', 'error');
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

        // ============================================
        // FILTROS REACTIVOS — Cambian la consulta de la tabla en tiempo real
        // ============================================
        const selEstatus = document.getElementById('filtroEstatus');
        if (selEstatus) selEstatus.addEventListener('change', () => cargarTrabajadores(selEstatus.value));

        const selNomina = document.getElementById('filtroNomina');
        if (selNomina) selNomina.addEventListener('change', () => cargarTrabajadores(selEstatus?.value || '', selNomina.value));

        // ============================================
        // CONFIRMACIÓN DE ELIMINACIÓN — Botón "Sí, eliminar"
        // ============================================
        if (btnSiEliminar) {
            btnSiEliminar.addEventListener('click', ejecutarBajaTrabajador);
        }
    });

    // ============================================
    // CARGAR TRABAJADORES — Tabla dinámica con filtros
    // GET /trabajadores?estatus=...&nomina=...
    // ============================================
    async function cargarTrabajadores(estatus = '', nomina = '') {
        const tbody = document.getElementById('tbodyTrabajadores');
        if (!tbody) return;

        try {
            const url = `/trabajadores?estatus=${estatus}&nomina=${nomina}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al consultar servidores');
            const data = await response.json();

            const items = data.data || [];
            tbody.innerHTML = '';  // Limpia la tabla
            document.getElementById('totalTrabajadores').textContent = data.total || items.length || 0;

            // Renderiza cada trabajador como una fila <tr> con celdas creadas manualmente
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

                // Columna de acciones: botones Ver y Eliminar
                const tdAcciones = document.createElement('td');
                const div = document.createElement('div'); div.className = 'acciones-cell';
                const btnVer = document.createElement('button');
                btnVer.className = 'btn-icon btn-ver';
                btnVer.title = 'Ver Expediente';
                btnVer.onclick = () => window.sigejubVerTrabajador(t.id, btnVer);
                const iconEye = document.createElement('i'); iconEye.className = 'fas fa-eye';
                btnVer.appendChild(iconEye);
                const btnEliminar = document.createElement('button');
                btnEliminar.className = 'btn-icon btn-eliminar';
                btnEliminar.title = 'Dar de Baja';
                btnEliminar.onclick = () => window.sigejubEliminarTrabajador(t.id, `${t.nombres} ${t.apellidos}`, btnEliminar);
                const iconTrash = document.createElement('i'); iconTrash.className = 'fas fa-trash-can';
                btnEliminar.appendChild(iconTrash);
                div.appendChild(btnVer);
                div.appendChild(btnEliminar);
                tdAcciones.appendChild(div);

                tr.append(tdId, tdNombre, tdCedula, tdCargo, tdTipo, tdEstatus, tdAcciones);
                tbody.appendChild(tr);

            });

        } catch (err) {
            console.error('Error render-table:', err);
        }
    }

    // ============================================
    // VER EXPEDIENTE — GET /trabajadores/{id} y carga en modal (solo lectura)
    // ============================================
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

            // Puebla todos los campos del formulario con los datos del servidor
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

            habilitarCamposFormulario(false);  // Deshabilita todos los campos (solo lectura)

            modalTitle.innerHTML = "Expediente<br>Laboral";
            modalDescription.textContent = "Modo de lectura institucional. Use el botón inferior para editar.";
            if (btnHabilitarEdicion) {
                btnHabilitarEdicion.innerHTML = '<i class="fas fa-pen"></i> Editar Expediente';
                btnHabilitarEdicion.style.display = 'flex';
            }
            if (btnSubmit) btnSubmit.style.display = 'none';

            modal.style.display = 'flex';

        } catch (err) {
            mostrarToast(err.message, 'error');
        } finally {
            ocultarCargando();
        }
    };

    // ============================================
    // ELIMINAR TRABAJADOR — Abre modal de confirmación de baja
    // ============================================
    window.sigejubEliminarTrabajador = function(id, nombre, elemento) {
        trabajadorSeleccionadoId = id;
        filaSeleccionadaDOM = elemento.closest('tr');
        if (deleteWorkerName) deleteWorkerName.textContent = nombre;
        if (modalEliminar) modalEliminar.style.display = 'flex';
    };

    // ============================================
    // EJECUTAR BAJA — DELETE /trabajadores/{id} con animación de salida
    // ============================================
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
                    // Animación de desvanecimiento y luego remoción del DOM
                    filaSeleccionadaDOM.style.opacity = '0';
                    setTimeout(() => {
                        filaSeleccionadaDOM.remove();
                        // Decrementa el contador de total de trabajadores
                        const badgeTotal = document.getElementById('totalTrabajadores');
                        if (badgeTotal) badgeTotal.textContent = Math.max(0, parseInt(badgeTotal.textContent) - 1);
                    }, 300);
                }
                mostrarToast(data.mensaje || 'Baja procesada.', 'success');
            } else {
                mostrarToast(data.mensaje || 'Error al procesar la baja.', 'error');
            }
        } catch (err) {
            mostrarToast('Error de red al intentar conectar.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    // ============================================
    // HABILITAR/DESHABILITAR CAMPOS — Controla la edición del formulario
    // ============================================
    function habilitarCamposFormulario(condicion) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(i => i.disabled = !condicion);  // true = habilitado, false = deshabilitado
    }

    // ============================================
    // ESTADÍSTICAS DEL DASHBOARD — Próximas jubilaciones y % expedientes digitales
    // GET /trabajadores-stats/dashboard
    // ============================================
    async function cargarTrabajadoresStats() {
        try {
            const result = await cachedFetch('/trabajadores-stats/dashboard', { ttl: 120000 });
            const data = result.data;

            // ============================================
            // LISTA DE PRÓXIMAS JUBILACIONES — Top 5
            // ============================================
            const lista = document.getElementById('proximasJubilacionesList');
            if (lista && data.proximas) {
                if (!data.proximas.length) {
                    lista.innerHTML = '<p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">No hay trabajadores próximos a jubilarse.</p>';
                } else {
                    // Renderiza los primeros 5 trabajadores con su edad y años de servicio
                    lista.innerHTML = '<p style="color:rgba(255,255,255,0.85);font-size:0.85rem;margin-bottom:10px;">' +
                        data.proximas.length + ' trabajadores próximos a cumplir requisitos:</p>' +
                        data.proximas.slice(0, 5).map(t =>
                            `<div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.1);font-size:0.82rem;">
                                <span>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</span>
                                <span style="color:rgba(255,255,255,0.6);font-size:0.75rem;">${escaparHTML(t.edad) || '—'} años · ${escaparHTML(t.total_anos_servicio) || 0} años serv.</span>
                            </div>`
                        ).join('');
                }
            }

            // ============================================
            // BARRA DE ESTATUS — % de expedientes digitalizados
            // ============================================
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

    // ============================================
    // MUTATION OBSERVER — Detecta cambios de pestaña para cargar datos bajo demanda
    // ============================================
    let estabaActivo = false;  // Estado anterior de la pestaña
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            // Solo nos interesa la sección #trabajadores
            if (m.target.id === 'trabajadores') {
                const actualmenteActivo = m.target.classList.contains('active');
                // Si se activó ahora y antes no lo estaba → carga datos
                if (actualmenteActivo && !estabaActivo) {
                    cargarTrabajadores();
                    cargarTrabajadoresStats();
                }
                estabaActivo = actualmenteActivo;
            }
        });
    });

    // Inicia la observación si la sección existe
    const seccion = document.getElementById('trabajadores');
    if (seccion) {
        estabaActivo = seccion.classList.contains('active');
        // Si ya está visible, carga las stats inmediatamente
        if (estabaActivo) cargarTrabajadoresStats();
        // Observa cambios en el atributo class de la sección
        observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    }
})();