// ============================================
// tabla1.js — Tabla dinámica de trabajadores con CRUD completo
// Ubicación: Sección "Trabajadores" del dashboard (vista con tabla .custom-table)
// Responsabilidades:
//   - Carga asíncrona del listado de trabajadores desde GET /trabajadores
//   - Renderizado de filas con avatar, estatus (activo/jubilado) y acciones
//   - Vista rápida de expediente (guarda en sessionStorage y redirige)
//   - Edición: carga datos en el modal y envía PUT
//   - Eliminación: soft delete con confirmación y animación de salida
//   - Envío del formulario (creación/edición) vía AJAX
// ============================================

document.addEventListener('DOMContentLoaded', () => {

    // ============================================
    // 1. LISTADO DE TRABAJADORES — GET /trabajadores y renderizado en tabla
    // ============================================
    async function cargarTrabajadores() {
        try {
            const resp = await fetch('/trabajadores');
            const data = await resp.json();
            const tbody = document.querySelector('.custom-table tbody');

            if (!tbody || !data.data) return;

            tbody.innerHTML = '';  // Limpia la tabla antes de recargar

            data.data.forEach(t => {
                // Determina estatus según reglas de jubilación
                const estatus = (t.total_anos_servicio >= 25 || t.edad >= 60) ? 'jubilado' : 'activo';
                const iniciales = t.nombres.charAt(0) + t.apellidos.charAt(0);

                const fila = document.createElement('tr');
                fila.dataset.id = t.id;

                // Asigna un color de avatar basado en el ID (consistente por trabajador)
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
                        <i class="fas fa-folder-open btn-icon btn-ver" title="Ver Expediente" data-id="${t.id}"></i>
                        <i class="fas fa-pen btn-icon btn-editar" title="Editar" data-id="${t.id}" data-nombres="${t.nombres}" data-apellidos="${t.apellidos}" data-cedula="${t.cedula}" data-cargo="${t.cargo}" data-unidad="${t.unidad_departamento}" data-grado="${t.grado_nivel}" data-fecha-ingreso="${t.fecha_ingreso}" data-fecha-nacimiento="${t.fecha_nacimiento}" data-genero="${t.genero}" data-nivel="${t.nivel_instruccion}" data-externo="${t.anos_servicio_externo}" data-porc-antig="${t.porcentaje_antiguedad}" data-cuenta="${t.cuenta_bancaria}"></i>
                        <i class="fas fa-trash-can btn-icon btn-eliminar" title="Eliminar" data-id="${t.id}" data-nombre="${t.nombres} ${t.apellidos}"></i>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            // Actualiza el contador total de trabajadores en la cabecera de la tabla
            const counter = document.querySelector('.total-badge-card h2');
            if (counter) counter.textContent = data.total || data.data.length;
            document.querySelector('.table-footer span')?.classList.add('text-muted');

        } catch (error) {
            console.error('Error al cargar trabajadores:', error);
        }
    }

    // Carga inicial al cargar la página
    cargarTrabajadores();

    // ============================================
    // 2. VER EXPEDIENTE — GET /trabajadores/{id}, guarda en sessionStorage y redirige
    // ============================================
    document.addEventListener('click', (e) => {
        const btnVer = e.target.closest('.btn-ver');
        if (btnVer) {
            const id = btnVer.dataset.id;
            fetch(`/trabajadores/${id}`)
                .then(r => r.json())
                .then(data => {
                    // Guarda los datos completos en sessionStorage para la vista de expediente
                    sessionStorage.setItem('trabajadorSeleccionado', JSON.stringify(data));
                    window.location.href = '/dashboard?tab=expedientes';
                });
        }
    });

    // ============================================
    // 3. EDITAR — Carga datos del trabajador en el modal y prepara PUT
    // ============================================
    document.addEventListener('click', (e) => {
        const btnEditar = e.target.closest('.btn-editar');
        if (!btnEditar) return;

        const f = document.getElementById('formTrabajador');
        // Cambia la URL del formulario a la ruta de actualización con el ID
        f.setAttribute('data-action', `/trabajadores/${btnEditar.dataset.id}`);
        f.setAttribute('data-method', 'PUT');  // Laravel method spoofing
        f.querySelector('.btn-submit').textContent = 'Guardar Cambios';

        // Puebla todos los campos del formulario con los datos del trabajador
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

        // Limpia campos NO editables (se calculan automáticamente en el backend)
        f.querySelector('[name="numero_hijos"]').value = '';
        f.querySelector('[name="hijos_discapacidad"]').value = '';
        f.querySelector('[name="porcentaje_caja_ahorro"]').value = '';

        // Abre el modal
        document.getElementById('modalTrabajador').style.display = 'flex';
    });

    // ============================================
    // 4. ELIMINAR — Confirmación y DELETE con animación visual
    // ============================================
    document.addEventListener('click', (e) => {
        const btnEliminar = e.target.closest('.btn-eliminar');
        if (!btnEliminar) return;

        const nombre = btnEliminar.dataset.nombre;
        const id = btnEliminar.dataset.id;

        if (!confirm(`¿Eliminar a "${nombre}"? Esta acción se puede deshacer (soft delete).`)) return;

        fetch(`/trabajadores/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 'success') {
                // Animación de desvanecimiento antes de remover la fila del DOM
                const fila = btnEliminar.closest('tr');
                fila.style.transition = 'opacity 0.3s';
                fila.style.opacity = '0';
                setTimeout(() => fila.remove(), 300);
                alert(data.mensaje);
            } else {
                alert(data.mensaje || 'Error al eliminar');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error de conexión al intentar eliminar.');
        });
    });

    // ============================================
    // 5. FORMULARIO — Envío AJAX (POST para crear, PUT para editar)
    // ============================================
    const formTrabajador = document.getElementById('formTrabajador');
    if (formTrabajador) {
        formTrabajador.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Lee data-action y data-method para decidir si es creación o edición
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

                alert(data.mensaje || 'Operación exitosa.');
                formTrabajador.reset();
                document.getElementById('closeModal')?.click();  // Cierra el modal

                // En edición recarga la página; en creación refresca solo la tabla
                if (method === 'PUT') {
                    location.reload();
                } else {
                    await cargarTrabajadores();
                }

            } catch (error) {
                // Errores de validación de Laravel: muestra el primer mensaje de cada campo
                if (error.errors) {
                    let mensajes = '';
                    Object.values(error.errors).forEach(err => { mensajes += err[0] + '\n'; });
                    alert(mensajes);
                } else {
                    alert(error.mensaje || 'Error interno.');
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