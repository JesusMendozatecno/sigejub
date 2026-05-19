=/**
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
            btnHabilitarEdicion.addEventListener('click', (e) => {
                e.preventDefault();
                modoEdicionActivo = true;
                habilitarCamposFormulario(true);
                modalTitle.innerHTML = "Modificar<br>Expediente";
                btnHabilitarEdicion.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.style.display = 'block';
                    btnSubmit.textContent = 'Guardar Cambios';
                }
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
                    url = `/trabajadores/${trabajadorSeleccionadoId}/update`;
                    formData.append('_method', 'PUT');
                }

                try {
                    const tokenElement = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': tokenElement ? tokenElement.value : '',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok) throw data;

                    alert(data.message || 'Operación completada con éxito.');
                    form.reset();
                    cerrarTodoModal();
                    cargarTrabajadores();

                } catch (err) {
                    console.error(err);
                    if (err.errors) {
                        alert(Object.values(err.errors).flat().join('\n'));
                    } else {
                        alert(err.message || 'Error interno de comunicación.');
                    }
                } finally {
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
            const url = `/api/trabajadores?estatus=${estatus}&nomina=${nomina}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al consultar servidores');
            const data = await response.json();

            tbody.innerHTML = '';
            document.getElementById('totalTrabajadores').textContent = data.length || 0;

            data.forEach(t => {
                const tr = document.createElement('tr');
                tr.dataset.id = t.id;
                tr.innerHTML = `
                    <td>${t.id}</td>
                    <td><strong>${t.nombres} ${t.apellidos}</strong></td>
                    <td>${t.cedula}</td>
                    <td>${t.cargo}</td>
                    <td><span class="type-tag">${t.unidad_departamento}</span></td>
                    <td><span class="status-pill ${t.estatus === 'activo' ? 'active' : 'retired'}">${t.estatus.toUpperCase()}</span></td>
                    <td>
                        <div class="actions-cell-group">
                            <button class="btn-action view" onclick="window.sigejubVerTrabajador(${t.id}, this)" title="Ver Expediente"><i data-lucide="folder"></i></button>
                            <button class="btn-action delete" onclick="window.sigejubEliminarTrabajador(${t.id}, '${t.nombres} ${t.apellidos}', this)" title="Dar de Baja"><i data-lucide="trash-2"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();

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
            if (btnHabilitarEdicion) btnHabilitarEdicion.style.display = 'inline-flex';
            if (btnSubmit) btnSubmit.style.display = 'none';

            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();

        } catch (err) {
            alert(err.message);
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
            const tokenElement = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
            const res = await fetch(`/trabajadores/${trabajadorSeleccionadoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': tokenElement ? tokenElement.value : '',
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
                alert(data.message || 'Baja procesada.');
            } else {
                alert(data.message || 'Error al procesar la baja.');
            }
        } catch (err) {
            alert('Error de red al intentar conectar.');
        }
    }

    function habilitarCamposFormulario(condicion) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(i => i.disabled = !condicion);
    }

    // Observer para la inicialización al cambiar de pestaña
    let estabaActivo = false;
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'trabajadores') {
                const actualmenteActivo = m.target.classList.contains('active');
                if (actualmenteActivo && !estabaActivo) {
                    cargarTrabajadores();
                }
                estabaActivo = actualmenteActivo;
            }
        });
    });

    const seccion = document.getElementById('trabajadores');
    if (seccion) {
        estabaActivo = seccion.classList.contains('active');
        observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    }
})();