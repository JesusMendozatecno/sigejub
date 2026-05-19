/**
 * SIGEJUB - Sistema de Gestión de Jubilaciones
 * Módulo Único: Directorio de Trabajadores, Modales Dinámicos y Alertas de Confirmación
 */
(function() {
    let trabajadorSeleccionadoId = null;
    let filaSeleccionadaDOM = null;

    // Elementos del Modal Principal
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

        // Configurar Modal para registro nuevo de trabajador
        if (btnRegistrar) {
            btnRegistrar.addEventListener('click', (e) => {
                e.preventDefault();
                form.reset();
                
                // Restaurar ruta original de registro
                form.setAttribute('data-action', form.getAttribute('original-action') || form.getAttribute('data-action'));
                form.removeAttribute('data-method');
                
                modalTitle.innerHTML = "Registrar<br>Nuevo<br>Trabajador";
                modalDescription.textContent = "Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.";
                
                setFormReadOnly(false);
                btnSubmit.style.display = 'block';
                btnSubmit.textContent = 'Registrar Trabajador';
                btnHabilitarEdicion.style.display = 'none';
                
                abrirModal(modal);
            });
        }

        // Eventos de cierre de ventanas modales
        if (btnCerrar) btnCerrar.addEventListener('click', () => cerrarModal(modal));
        if (btnCancelar) btnCancelar.addEventListener('click', () => cerrarModal(modal));
        if (btnNoEliminar) btnNoEliminar.addEventListener('click', () => cerrarModal(modalEliminar));
        
        // Confirmación de la eliminación
        if (btnSiEliminar) btnSiEliminar.addEventListener('click', ejecutarBajaAjax);

        // Cierre al hacer click fuera del contenedor del modal
        window.addEventListener('click', (e) => {
            if (e.target === modal) cerrarModal(modal);
            if (e.target === modalEliminar) cerrarModal(modalEliminar);
        });

        // Botón de transformación "Editar" interno del Modal
        if (btnHabilitarEdicion) {
            btnHabilitarEdicion.addEventListener('click', (e) => {
                e.preventDefault();
                modalTitle.innerHTML = "Editar<br>Datos del<br>Trabajador";
                modalDescription.textContent = "Modifique los campos necesarios del expediente digital. Al finalizar guarde los cambios.";
                
                setFormReadOnly(false);
                btnHabilitarEdicion.style.display = 'none';
                btnSubmit.style.display = 'block';
                
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        // Envío controlado del formulario vía AJAX
        if (form) {
            form.addEventListener('submit', async function(ev) {
                ev.preventDefault();
                const url = this.getAttribute('data-action');
                const method = this.getAttribute('data-method') || 'POST';
                const formData = new FormData(this);

                if (btnSubmit) { 
                    btnSubmit.disabled = true; 
                    btnSubmit.textContent = 'Procesando...'; 
                }

                try {
                    const resp = await fetch(url, {
                        method : method,
                        body   : formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await resp.json();
                    if (!resp.ok) throw data;

                    alert(data.message || 'Datos procesados con éxito en la auditoría digital.');
                    this.reset();
                    cerrarModal(modal);
                    
                    // Recargar los trabajadores de la tabla asíncronamente
                    cargarTrabajadores();

                } catch (err) {
                    if (err.errors) {
                        alert(Object.values(err.errors).flat().join('\n'));
                    } else {
                        alert(err.message || 'Error interno en el sistema.');
                    }
                } finally {
                    if (btnSubmit) { 
                        btnSubmit.disabled = false; 
                        btnSubmit.textContent = method === 'PUT' ? 'Guardar Cambios' : 'Registrar Trabajador'; 
                    }
                }
            });
        }

        // Filtro de Estatus de la Tabla
        const selEstatus = document.getElementById('filtroEstatus');
        if (selEstatus) {
            selEstatus.addEventListener('change', () => cargarTrabajadores(selEstatus.value));
        }
    });

    // Helpers de Control de Visibilidad
    function abrirModal(targetModal) {
        if(targetModal) targetModal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function cerrarModal(targetModal) {
        if(targetModal) targetModal.style.display = 'none';
    }

    function setFormReadOnly(status) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(el => {
            if (el.name !== '_token') el.disabled = status;
        });
    }

    // Formateador seguro para inputs de tipo Date (YYYY-MM-DD)
    function formatearFechaISO(fechaStr) {
        if (!fechaStr || fechaStr.trim() === '' || fechaStr === 'null' || fechaStr === 'undefined') return '';
        // Si ya viene con el formato correcto YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}$/.test(fechaStr)) return fechaStr;
        
        // Si viene con hora integrada (ej: YYYY-MM-DD HH:MM:SS), picar el string
        if (fechaStr.includes(' ')) {
            return fechaStr.split(' ')[0];
        }
        return '';
    }

    // Carga Asíncrona de Filas Dinámicas de la Tabla
    async function cargarTrabajadores(estatus = '') {
        const params = estatus ? `?estatus=${estatus}` : '';
        try {
            const resp = await fetch('/trabajadores' + params);
            const data = await resp.json();
            const tbody = document.getElementById('tbodyTrabajadores');
            const badgetTotal = document.getElementById('totalTrabajadores');

            if (!tbody) return;
            tbody.innerHTML = '';

            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem; color: #888;">No hay trabajadores registrados</td></tr>';
                if(badgetTotal) badgetTotal.textContent = '0';
                return;
            }

            data.data.forEach(t => {
                const esJubilado = (t.total_anos_servicio >= 25 || t.edad >= 60);
                const estatusTxt = esJubilado ? 'jubilado' : 'activo';
                const iniciales = (t.nombres.charAt(0) + t.apellidos.charAt(0)).toUpperCase();
                const colores = ['blue', 'purple', 'green', 'orange', 'red', 'teal', 'indigo', 'pink', 'amber', 'cyan'];
                const color = colores[t.id % colores.length];

                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>TR-${String(t.id).padStart(4, '0')}</td>
                    <td>
                        <div class="user-cell">
                            <span class="avatar ${color}">${iniciales}</span>
                            <strong>${t.nombres} ${t.apellidos}</strong>
                        </div>
                    </td>
                    <td>${t.cedula}</td>
                    <td>${t.cargo}</td>
                    <td><span class="badge-type doc">INSTITUCIONAL</span></td>
                    <td><span class="dot ${estatusTxt}"></span> ${estatusTxt.charAt(0).toUpperCase() + estatusTxt.slice(1)}</td>
                    <td class="acciones-cell">
                        <i data-lucide="folder-open" class="btn-icon btn-ver" title="Ver Expediente" 
                           data-id="${t.id}" data-nombres="${t.nombres}" data-apellidos="${t.apellidos}" 
                           data-cedula="${t.cedula}" data-cargo="${t.cargo}" data-unidad="${t.unidad_departamento}" 
                           data-grado="${t.grado_nivel}" data-fecha-ingreso="${t.fecha_ingreso || ''}" 
                           data-fecha-nacimiento="${t.fecha_nacimiento || ''}" data-genero="${t.genero}" 
                           data-nivel="${t.nivel_instruccion}" data-externo="${t.anos_servicio_externo}" 
                           data-porc-antig="${t.porcentaje_antiguedad}" data-cuenta="${t.cuenta_bancaria || ''}"></i>
                        
                        <i data-lucide="trash-2" class="btn-icon btn-eliminar" title="Eliminar" data-id="${t.id}" data-nombre="${t.nombres} ${t.apellidos}"></i>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            if(badgetTotal) badgetTotal.textContent = data.total || data.data.length;
            if (typeof lucide !== 'undefined') lucide.createIcons();

        } catch (err) {
            console.error('Error al cargar trabajadores:', err);
        }
    }

    // Captura del Botón de Lectura "Ver"
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-ver');
        if (!btn) return;

        e.stopPropagation(); // Evita burbujeos de clicks hacia el panel de navegación

        if(!form.getAttribute('original-action')) {
            form.setAttribute('original-action', form.getAttribute('data-action'));
        }

        form.setAttribute('data-action', `/trabajadores/${btn.dataset.id}`);
        form.setAttribute('data-method', 'PUT');
        
        modalTitle.innerHTML = "Visualizar<br>Expediente<br>Digital";
        modalDescription.textContent = "Usted se encuentra en modo de lectura institucional. Para realizar mutaciones pulse el botón inferior.";

        // Asignación segura de textos con validación de existencia de inputs
        const txtCedula    = form.querySelector('[name="cedula"]');
        const txtNombres   = form.querySelector('[name="nombres"]');
        const txtApellidos = form.querySelector('[name="apellidos"]');
        const txtCargo     = form.querySelector('[name="cargo"]');
        const txtUnidad    = form.querySelector('[name="unidad_departamento"]');
        const txtGrado     = form.querySelector('[name="grado_nivel"]');
        const selGenero    = form.querySelector('[name="genero"]');
        const selNivel     = form.querySelector('[name="nivel_instruccion"]');
        const numExterno   = form.querySelector('[name="anos_servicio_externo"]');
        const numPorc      = form.querySelector('[name="porcentaje_antiguedad"]');
        const txtCuenta    = form.querySelector('[name="cuenta_bancaria"]');

        if(txtCedula)    txtCedula.value    = btn.dataset.cedula || '';
        if(txtNombres)   txtNombres.value   = btn.dataset.nombres || '';
        if(txtApellidos) txtApellidos.value = btn.dataset.apellidos || '';
        if(txtCargo)     txtCargo.value     = btn.dataset.cargo || '';
        if(txtUnidad)    txtUnidad.value    = btn.dataset.unidad || '';
        if(txtGrado)     txtGrado.value     = btn.dataset.grado || '';
        if(selGenero)    selGenero.value    = btn.dataset.genero || '';
        if(selNivel)     selNivel.value     = btn.dataset.nivel || '';
        if(numExterno)   numExterno.value   = btn.dataset.externo || '0';
        if(numPorc)      numPorc.value      = btn.dataset.porcAntig || '0';
        if(txtCuenta)    txtCuenta.value    = btn.dataset.cuenta || '';

        // PROTECCIÓN Y FORMATEO SEGURO DE FECHAS (Previene la caída de ejecución capturada en video)
        const dateIngreso    = form.querySelector('[name="fecha_ingreso"]');
        const dateNacimiento = form.querySelector('[name="fecha_nacimiento"]');

        if(dateIngreso) {
            const rawIngreso = btn.dataset.fechaIngreso || btn.dataset['fecha-ingreso'];
            dateIngreso.value = formatearFechaISO(rawIngreso);
        }
        if(dateNacimiento) {
            const rawNacimiento = btn.dataset.fechaNacimiento || btn.dataset['fecha-nacimiento'];
            dateNacimiento.value = formatearFechaISO(rawNacimiento);
        }

        setFormReadOnly(true);
        btnSubmit.style.display = 'none';
        btnHabilitarEdicion.style.display = 'inline-flex';

        abrirModal(modal);
    });

    // Captura del Botón de Baja "Eliminar"
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;

        trabajadorSeleccionadoId = btn.dataset.id;
        deleteWorkerName.textContent = btn.dataset.nombre;
        filaSeleccionadaDOM = btn.closest('tr');

        abrirModal(modalEliminar);
    });

    // Petición asíncrona de eliminación por AJAX
    function ejecutarBajaAjax() {
        if (!trabajadorSeleccionadoId) return;

        fetch(`/trabajadores/${trabajadorSeleccionadoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                cerrarModal(modalEliminar);
                
                if(filaSeleccionadaDOM) {
                    filaSeleccionadaDOM.style.transition = 'opacity 0.3s';
                    filaSeleccionadaDOM.style.opacity = '0';
                    setTimeout(() => filaSeleccionadaDOM.remove(), 300);
                }
                
                const badgetTotal = document.getElementById('totalTrabajadores');
                if(badgetTotal) {
                    badgetTotal.textContent = Math.max(0, parseInt(badgetTotal.textContent || '0') - 1);
                }
                alert(data.message);
            } else {
                alert(data.message || 'Error al procesar la baja institucional.');
            }
        })
        .catch(() => alert('Error de red al intentar conectar con el servidor.'))
        .finally(() => {
            trabajadorSeleccionadoId = null;
            filaSeleccionadaDOM = null;
        });
    }

    // Mutation Observer controlado para prevenir el parpadeo e inicialización forzada
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