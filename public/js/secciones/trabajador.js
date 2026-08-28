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
    let paginaActualTrabajadores = 1;

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

    // Referencias al modal de agregar catálogo
    const modalCatalogo = document.getElementById('modalAgregarCatalogo');
    let catalogoTipoActual = '';  // 'cargo' o 'grado'
    let catalogoSelectRef = null;

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

                // Cargar opciones de catálogos
                cargarOpcionesCatalogo('cargo', 'selectCargo');
                cargarOpcionesCatalogo('grado', 'selectGradoNivel');

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

                // Combinar tipo_documento + cédula en un solo campo
                const tipoDoc = document.getElementById('selectTipoDocumento');
                const inputCed = document.getElementById('inputCedula');
                if (tipoDoc && inputCed && inputCed.value) {
                    formData.delete('cedula');
                    formData.append('cedula', tipoDoc.value + '-' + inputCed.value);
                }

                // Manejar checkbox actividad_universitaria (unchecked = no enviado)
                const checkAct = document.getElementById('checkActividadUniversitaria');
                formData.delete('actividad_universitaria');
                formData.append('actividad_universitaria', checkAct && checkAct.checked ? '1' : '0');

                // Resolver cargo_id desde el select
                const selectCargo = document.getElementById('selectCargo');
                if (selectCargo && selectCargo.value) {
                    formData.delete('cargo_id');
                    formData.append('cargo_id', selectCargo.value);
                } else {
                    formData.delete('cargo_id');
                }

                // Asegurar numero_hijos y hijos_discapacidad si los grupos están ocultos
                if (!document.getElementById('checkTieneHijos')?.checked) {
                    formData.set('numero_hijos', '0');
                }
                if (!document.getElementById('checkHijosDiscapacidad')?.checked) {
                    formData.set('hijos_discapacidad', '0');
                }

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
                    if (window.limpiarCacheSigejub) window.limpiarCacheSigejub();
                    form.reset();
                    // Resetear checkboxes manualmente (form.reset no resetea checkboxes personalizados)
                    document.getElementById('checkTieneHijos').checked = false;
                    toggleHijosFields();
                    document.getElementById('checkHijosDiscapacidad').checked = false;
                    toggleHijosDiscapacidadFields();
                    document.getElementById('checkActividadUniversitaria').checked = false;
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
        if (selEstatus) selEstatus.addEventListener('change', () => cargarTrabajadores(selEstatus.value, selNomina?.value || '', selAsignacion?.value || ''));

        const selNomina = document.getElementById('filtroNomina');
        if (selNomina) selNomina.addEventListener('change', () => cargarTrabajadores(selEstatus?.value || '', selNomina.value, selAsignacion?.value || ''));

        const selAsignacion = document.getElementById('filtroAsignacion');
        if (selAsignacion) selAsignacion.addEventListener('change', () => cargarTrabajadores(selEstatus?.value || '', selNomina?.value || '', selAsignacion.value));

        // ============================================
        // CONFIRMACIÓN DE ELIMINACIÓN — Botón "Sí, eliminar"
        // ============================================
        if (btnSiEliminar) {
            btnSiEliminar.addEventListener('click', ejecutarBajaTrabajador);
        }

        // ============================================
        // MODAL CATÁLOGO — Eventos de cancelar y guardar
        // ============================================
        const btnCancelarCatalogo = document.getElementById('btnCancelarCatalogo');
        const btnGuardarCatalogo = document.getElementById('btnGuardarCatalogo');
        if (btnCancelarCatalogo) btnCancelarCatalogo.addEventListener('click', cerrarModalCatalogo);
        if (btnGuardarCatalogo) btnGuardarCatalogo.addEventListener('click', guardarCatalogo);
        if (modalCatalogo) {
            window.addEventListener('click', (e) => { if (e.target === modalCatalogo) cerrarModalCatalogo(); });
        }

    });

    // ============================================
    // CARGAR OPCS DE CATÁLOGO — Carga opciones desde /master/{tipo} y agrega "Agregar nuevo"
    // ============================================
    async function cargarOpcionesCatalogo(tipo, selectId, selectedValue) {
        const select = document.getElementById(selectId);
        if (!select) return;
        try {
            const res = await fetch(`/master/${tipo}?solo_activos=1&per_page=100`);
            const data = await res.json();
            const items = data.data || [];
            select.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            items.forEach(item => {
                const opt = document.createElement('option');
                if (tipo === 'cargo') {
                    opt.value = item.id;
                    opt.textContent = item.codigo + ' — ' + item.nombre;
                    opt.dataset.nombre = item.nombre;
                    if (selectedValue && item.nombre === selectedValue) opt.selected = true;
                } else {
                    opt.value = item.nombre;
                    opt.textContent = item.codigo + ' — ' + item.nombre;
                    if (selectedValue && item.nombre === selectedValue) opt.selected = true;
                }
                select.appendChild(opt);
            });
        } catch (err) {
            console.error('Error cargando catálogo ' + tipo + ':', err);
            select.innerHTML = '<option value="" disabled>Error al cargar</option>';
        }
    }

    function seleccionarValorCatalogo(selectId, valor) {
        const select = document.getElementById(selectId);
        if (!select || !valor) return;
        const opciones = Array.from(select.options);
        const match = opciones.find(o => o.dataset && o.dataset.nombre === valor) || opciones.find(o => o.value === valor);
        if (match) {
            select.value = match.value;
        } else {
            // Si no existe en el catálogo, agregarlo temporalmente y seleccionarlo
            const opt = document.createElement('option');
            opt.value = valor;
            opt.textContent = valor;
            opt.selected = true;
            select.insertBefore(opt, select.options[select.options.length - 1]); // antes de "Agregar nuevo"
        }
    }

    // ============================================
    // MODAL AGREGAR CATÁLOGO — Abrir, guardar, cerrar
    // ============================================
    function abrirModalCatalogo(tipo, selectRef) {
        catalogoTipoActual = tipo;
        catalogoSelectRef = selectRef;
        const titulo = document.getElementById('tituloModalCatalogo');
        if (titulo) titulo.innerHTML = `<i class="fas fa-plus-circle"></i> Agregar Nuevo ${tipo === 'cargo' ? 'Cargo' : 'Grado'}`;
        document.getElementById('inputNombreCatalogo').value = '';
        document.getElementById('inputCodigoCatalogo').value = '';
        if (modalCatalogo) modalCatalogo.style.display = 'flex';
    }

    function cerrarModalCatalogo() {
        if (modalCatalogo) modalCatalogo.style.display = 'none';
        // Restaurar el select al valor anterior
        if (catalogoSelectRef) {
            catalogoSelectRef.value = catalogoSelectRef.options[0]?.value || '';
        }
    }

    async function guardarCatalogo() {
        const nombre = document.getElementById('inputNombreCatalogo').value.trim();
        const codigo = document.getElementById('inputCodigoCatalogo').value.trim();
        if (!nombre || !codigo) {
            mostrarToast('Nombre y código son obligatorios.', 'error');
            return;
        }
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(`/master/${catalogoTipoActual}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ nombre, codigo, activo: true })
            });
            const data = await res.json();
            if (!res.ok) throw data;
            mostrarToast(data.mensaje || 'Guardado.', 'success');
            cerrarModalCatalogo();
            // Recargar opciones y seleccionar la nueva
            await cargarOpcionesCatalogo(catalogoTipoActual, catalogoSelectRef?.id, nombre);
        } catch (err) {
            mostrarToast(err.mensaje || (err.errores ? Object.values(err.errores).flat().join(', ') : 'Error'), 'error');
        }
    }
    // GET /trabajadores?estatus=...&asignacion=...
    // ============================================
    async function cargarTrabajadores(estatus = '', nomina = '', asignacion = '', pagina) {
        const tbody = document.getElementById('tbodyTrabajadores');
        if (!tbody) return;

        if (pagina !== undefined) paginaActualTrabajadores = pagina;
        else paginaActualTrabajadores = 1;

        try {
            const url = `/trabajadores?estatus=${estatus}&asignacion=${asignacion}&page=${paginaActualTrabajadores}`;
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

                const tdNombre = document.createElement('td');
                const strong = document.createElement('strong'); strong.textContent = `${t.nombres} ${t.apellidos}`;
                tdNombre.appendChild(strong);
                const tdCedula = document.createElement('td');
                const cedulaParts = (t.cedula || '').split('-');
                const tipoDoc = cedulaParts.length > 1 ? cedulaParts[0] : '';
                const numDoc = cedulaParts.length > 1 ? cedulaParts.slice(1).join('-') : t.cedula;
                tdCedula.innerHTML = `<span style="color:#6366f1;font-weight:700;">${escaparHTML(tipoDoc)}</span> ${escaparHTML(numDoc)}`;
                const tdCargo = document.createElement('td'); tdCargo.textContent = t.cargo;
                const tdTipo = document.createElement('td');
                const typeTag = document.createElement('span'); typeTag.className = 'type-tag'; typeTag.textContent = t.unidad_departamento;
                tdTipo.appendChild(typeTag);
                const tdAsignacion = document.createElement('td');
                const asigTag = document.createElement('span'); asigTag.className = 'type-tag';
                asigTag.textContent = t.asignacion || '—';
                if (t.asignacion === 'Nomina') asigTag.style.background = '#dbeafe';
                else if (t.asignacion === 'Manual') asigTag.style.background = '#fef3c7';
                tdAsignacion.appendChild(asigTag);
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

                tr.append(tdNombre, tdCedula, tdCargo, tdTipo, tdAsignacion, tdEstatus, tdAcciones);
                tbody.appendChild(tr);
            });

            renderPaginacionTrabajadores(data);

        } catch (err) {
            console.error('Error render-table:', err);
        }
    }

    function renderPaginacionTrabajadores(data) {
        const container = document.querySelector('.table-footer .pagination');
        if (!container) return;
        if (data.last_page <= 1) { container.innerHTML = ''; return; }
        var html = '';
        if (data.current_page > 1) {
            html += '<button type="button" data-page="' + (data.current_page - 1) + '">&laquo;</button>';
        }
        for (var i = 1; i <= data.last_page; i++) {
            html += '<button type="button" data-page="' + i + '"' + (i === data.current_page ? ' class="active"' : '') + '>' + i + '</button>';
        }
        if (data.current_page < data.last_page) {
            html += '<button type="button" data-page="' + (data.current_page + 1) + '">&raquo;</button>';
        }
        container.innerHTML = html;
        container.querySelectorAll('button[data-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var f = document.getElementById('filtroEstatus');
                var a = document.getElementById('filtroAsignacion');
                cargarTrabajadores(f ? f.value : '', '', a ? a.value : '', parseInt(this.dataset.page));
            });
        });
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

            // Cargar opciones de catálogos antes de poblar
            await Promise.all([
                cargarOpcionesCatalogo('cargo', 'selectCargo', t.cargo),
                cargarOpcionesCatalogo('grado', 'selectGradoNivel', t.grado_nivel)
            ]);

            // Puebla todos los campos del formulario con los datos del servidor
            const cedulaParts = (t.cedula || '').split('-');
            const tipoDoc = cedulaParts.length > 1 ? cedulaParts[0] : 'V';
            const numDoc = cedulaParts.length > 1 ? cedulaParts.slice(1).join('-') : t.cedula;
            document.getElementById('selectTipoDocumento').value = tipoDoc;
            document.getElementById('inputCedula').value = numDoc;
            document.getElementById('selectGenero').value = t.genero;
            document.getElementById('inputNombres').value = t.nombres;
            document.getElementById('inputApellidos').value = t.apellidos;
            document.getElementById('inputFechaNacimiento').value = t.fecha_nacimiento;
            seleccionarValorCatalogo('selectCargo', t.cargo);
            document.getElementById('inputUnidadDepartamento').value = t.unidad_departamento;
            seleccionarValorCatalogo('selectGradoNivel', t.grado_nivel);
            document.getElementById('inputFechaIngreso').value = t.fecha_ingreso;
            document.getElementById('inputAnosExterno').value = t.anos_servicio_externo;
            document.getElementById('inputPorcentajeAntiguedad').value = t.porcentaje_antiguedad;
            document.getElementById('selectNivelInstruccion').value = t.nivel_instruccion;
            document.getElementById('inputCuentaBancaria').value = t.cuenta_bancaria || '';

            // Poblar campos de hijos y actividad universitaria
            const tieneHijos = parseInt(t.numero_hijos) > 0;
            document.getElementById('checkTieneHijos').checked = tieneHijos;
            toggleHijosFields();
            if (tieneHijos) document.getElementById('inputNumeroHijos').value = t.numero_hijos;

            const tieneHijosDisc = parseInt(t.hijos_discapacidad) > 0;
            document.getElementById('checkHijosDiscapacidad').checked = tieneHijosDisc;
            toggleHijosDiscapacidadFields();
            if (tieneHijosDisc) document.getElementById('inputHijosDiscapacidad').value = t.hijos_discapacidad;

            document.getElementById('checkActividadUniversitaria').checked = t.actividad_universitaria ? true : false;

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