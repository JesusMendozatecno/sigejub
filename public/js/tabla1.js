document.addEventListener('DOMContentLoaded', () => {

    /*
    |======================================================
    | 1. LISTADO DE TRABAJADORES — Cargar desde BD al entrar
    |======================================================
    */
    async function cargarTrabajadores() {
        try {
            const resp = await fetch('/trabajadores');
            const data = await resp.json();
            const tbody = document.querySelector('.custom-table tbody');

            if (!tbody || !data.data) return;

            tbody.innerHTML = '';

            data.data.forEach(t => {
                const estatus = (t.total_anos_servicio >= 25 || t.edad >= 60) ? 'jubilado' : 'activo';
                const iniciales = t.nombres.charAt(0) + t.apellidos.charAt(0);

                const fila = document.createElement('tr');
                fila.dataset.id = t.id;

                // Determinar clase color avatar según inicial
                const colores = ['blue', 'purple', 'green', 'orange', 'red', 'teal', 'indigo', 'pink'];
                const color = colores[t.id % colores.length];

                fila.innerHTML = `
                    <td>TR-${String(t.id).padStart(4, '0')}</td>
                    <td>
                        <div class="user-cell">
                            <span class="avatar ${color}">${iniciales.toUpperCase()}</span>
                            <strong>${t.nombres} ${t.apellidos}</strong>
                        </div>
                    </td>
                    <td>${t.cedula}</td>
                    <td>${t.cargo}</td>
                    <td><span class="badge-type doc">DOCENTE</span></td>
                    <td><span class="dot ${estatus}"></span> ${estatus.charAt(0).toUpperCase() + estatus.slice(1)}</td>
                    <td class="actions">
                        <i data-lucide="folder-open" class="btn-icon btn-ver" title="Ver Expediente" data-id="${t.id}"></i>
                        <i data-lucide="edit-3" class="btn-icon btn-editar" title="Editar" data-id="${t.id}" data-nombres="${t.nombres}" data-apellidos="${t.apellidos}" data-cedula="${t.cedula}" data-cargo="${t.cargo}" data-unidad="${t.unidad_departamento}" data-grado="${t.grado_nivel}" data-fecha-ingreso="${t.fecha_ingreso}" data-fecha-nacimiento="${t.fecha_nacimiento}" data-genero="${t.genero}" data-nivel="${t.nivel_instruccion}" data-externo="${t.anos_servicio_externo}" data-porc-antig="${t.porcentaje_antiguedad}" data-cuenta="${t.cuenta_bancaria}"></i>
                        <i data-lucide="trash-2" class="btn-icon btn-eliminar" title="Eliminar" data-id="${t.id}" data-nombre="${t.nombres} ${t.apellidos}"></i>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            // Refrescar íconos Lucide
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Actualizar contador
            const counter = document.querySelector('.total-badge-card h2');
            if (counter) counter.textContent = data.total || data.data.length;
            document.querySelector('.table-footer span')?.classList.add('text-muted');

        } catch (error) {
            console.error('Error al cargar trabajadores:', error);
        }
    }

    cargarTrabajadores();

    /*
    |======================================================
    | 2. VER EXPEDIENTE — Redirige al detalle del trabajador
    |======================================================
    */
    document.addEventListener('click', (e) => {
        const btnVer = e.target.closest('.btn-ver');
        if (btnVer) {
            const id = btnVer.dataset.id;
            // Llamar al endpoint show para traer datos completos y guardarlos en sesión
            fetch(`/trabajadores/${id}`)
                .then(r => r.json())
                .then(data => {
                    sessionStorage.setItem('trabajadorSeleccionado', JSON.stringify(data));
                    window.location.href = '/dashboard?tab=expedientes';
                });
        }
    });

    /*
    |======================================================
    | 3. EDITAR — Abre el modal con los datos cargados
    |======================================================
    */
    document.addEventListener('click', (e) => {
        const btnEditar = e.target.closest('.btn-editar');
        if (!btnEditar) return;

        // Cargar datos en el formulario
        const f = document.getElementById('formTrabajador');
        f.setAttribute('data-action', `/trabajadores/${btnEditar.dataset.id}`);
        // Cambiar a PUT explícitamente
        f.setAttribute('data-method', 'PUT');
        f.querySelector('.btn-submit').textContent = 'Guardar Cambios';

        f.querySelector('[name="cedula"]').value = btnEditar.dataset.cedula;
        f.querySelector('[name="nombres"]').value = btnEditar.dataset.nombres;
        f.querySelector('[name="apellidos"]').value = btnEditar.dataset.apellidos;
        f.querySelector('[name="cargo"]').value = btnEditar.dataset.cargo;
        f.querySelector('[name="unidad_departamento"]').value = btnEditar.dataset.unidad;
        f.querySelector('[name="grado_nivel"]').value = btnEditar.dataset.grado;
        f.querySelector('[name="fecha_ingreso"]').value = btnEditar.dataset['fecha-ingreso'];
        f.querySelector('[name="fecha_nacimiento"]').value = btnEditar.dataset['fecha-nacimiento'];
        f.querySelector('[name="genero"]').value = btnEditar.dataset.genero;
        f.querySelector('[name="nivel_instruccion"]').value = btnEditar.dataset.nivel;
        f.querySelector('[name="anos_servicio_externo"]').value = btnEditar.dataset.externo;
        f.querySelector('[name="porcentaje_antiguedad"]').value = btnEditar.dataset.porcAntig;
        f.querySelector('[name="cuenta_bancaria"]').value = btnEditar.dataset.cuenta || '';

        // Limpiar campos no editables del formulario
        f.querySelector('[name="numero_hijos"]').value = '';
        f.querySelector('[name="hijos_discapacidad"]').value = '';
        f.querySelector('[name="porcentaje_caja_ahorro"]').value = '';

        // Abrir modal
        document.getElementById('modalTrabajador').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    /*
    |======================================================
    | 4. ELIMINAR — Confirmación y eliminación suave
    |======================================================
    */
    document.addEventListener('click', (e) => {
        const btnEliminar = e.target.closest('.btn-eliminar');
        if (!btnEliminar) return;

        const nombre = btnEliminar.dataset.nombre;
        const id = btnEliminar.dataset.id;

        if (!confirm(`¿Eliminar a "${nombre}"? Esta acción se puede deshacer (soft delete).`)) return;

        fetch(`/trabajadores/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                // Eliminar la fila visualmente
                const fila = btnEliminar.closest('tr');
                fila.style.transition = 'opacity 0.3s';
                fila.style.opacity = '0';
                setTimeout(() => fila.remove(), 300);
                alert(data.message);
            } else {
                alert(data.message || 'Error al eliminar');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error de conexión al intentar eliminar.');
        });
    });

    /*
    |======================================================
    | 5. FORMULARIO — Envío AJAX (CREAR o EDITAR según method)
    |======================================================
    */
    const formTrabajador = document.getElementById('formTrabajador');
    if (formTrabajador) {
        formTrabajador.addEventListener('submit', async function(e) {
            e.preventDefault();

            const actionUrl = this.getAttribute('data-action');
            const method = this.getAttribute('data-method') || 'POST';
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerText = 'Guardando...';
            }

            try {
                const options = {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                };

                const resp = await fetch(actionUrl, options);
                const data = await resp.json();

                if (!resp.ok) throw data;

                alert(data.message || 'Operación exitosa.');
                formTrabajador.reset();
                document.getElementById('closeModal')?.click();
                // Si era edición, recargar la tabla
                if (method === 'PUT') {
                    location.reload();
                } else {
                    await cargarTrabajadores();
                }

            } catch (error) {
                if (error.errors) {
                    let mensajes = '';
                    Object.values(error.errors).forEach(err => { mensajes += err[0] + '\n'; });
                    alert(mensajes);
                } else {
                    alert(error.message || 'Error interno.');
                }
            } finally {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = method === 'PUT' ? 'Guardar Cambios' : 'Registrar Trabajador';
                }
            }
        });
    }

});