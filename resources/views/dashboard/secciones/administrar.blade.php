{{-- administrar.blade.php - Sección de administración: gestión de usuarios (CRUD), tabla de actividades, gráfica de actividad y modales para editar/enviar notificaciones. --}}
<header class="section-header">
    <div class="header-info">
        <h1>Panel de <span class="text-blue-accent">Administración</span></h1>
        <p>Gestión de usuarios, permisos y monitoreo de actividades.</p>
    </div>
</header>

<div class="tab-filters" style="margin-bottom:20px;">
    <button class="admin-tab active" data-tab="usuarios"><i class="fas fa-users" size="15"></i> Usuarios</button>
    <button class="admin-tab" data-tab="actividades"><i class="fas fa-wave-square" size="15"></i> Actividades</button>
</div>

<!-- ===== TAB USUARIOS ===== -->
<div id="tabUsuarios" class="admin-tab-content">
    <div class="search-filter-bar" style="margin-bottom:16px;">
        <div class="search-wrapper">
            <i class="fas fa-search" size="16"></i>
            <input type="text" id="buscadorUsuarios" placeholder="Buscar por nombre o email...">
        </div>
        <div class="filter-group-sm">
            <select id="filtroRol">
                <option value="">Todos los roles</option>
                <option value="analista">Analista</option>
                <option value="admin">Admin</option>
            </select>
        </div>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOMBRE</th>
                <th>EMAIL</th>
                <th>ROL</th>
                <th>REGISTRO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyUsuarios"></tbody>
    </table>

    <div class="table-footer">
        <span id="usuariosCounter">Mostrando 0 usuarios</span>
    </div>
</div>

<!-- ===== TAB ACTIVIDADES ===== -->
<div id="tabActividades" class="admin-tab-content" style="display:none;">
    <div class="search-filter-bar" style="margin-bottom:16px;">
        <div class="filter-group-sm">
            <select id="filtroTipoActividad">
                <option value="">Todos los tipos</option>
                <option value="solicitud">Solicitudes</option>
                <option value="trabajador">Trabajadores</option>
                <option value="expediente">Expedientes</option>
                <option value="usuario">Usuarios</option>
                <option value="notificacion">Notificaciones</option>
            </select>
        </div>
        <div class="filter-group-sm">
            <select id="filtroDiasActividad">
                <option value="7">Últimos 7 días</option>
                <option value="30">Últimos 30 días</option>
                <option value="90">Últimos 90 días</option>
            </select>
        </div>
    </div>

    <div style="background:white;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);margin-bottom:20px;">
        <canvas id="actividadChart" height="200"></canvas>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>FECHA</th>
                <th>USUARIO</th>
                <th>ACCIÓN</th>
                <th>DESCRIPCIÓN</th>
            </tr>
        </thead>
        <tbody id="tbodyActividades"></tbody>
    </table>
</div>

<!-- ===== MODAL EDITAR USUARIO ===== -->
<div id="modalEditarUsuario" class="modal-overlay">
    <div class="modal-container" style="max-width:480px;">
        <aside class="modal-sidebar" style="max-width:160px;">
            <span class="badge-new">PERMISOS</span>
            <h1>Editar<br>Usuario</h1>
            <p>Modifique los permisos del usuario seleccionado.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalEditarUsuario" type="button">&times;</button>
            <form id="formEditarUsuario">
                @csrf
                <input type="hidden" name="user_id" id="editUserId">
                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Datos del Usuario</h3>
                    <p style="margin:8px 0;font-size:0.9rem;">
                        <strong id="editUserName">—</strong><br>
                        <span id="editUserEmail">—</span>
                    </p>
                </section>
                <section class="form-section">
                    <h3><i class="fas fa-shield"></i> Rol / Permisos</h3>
                    <div class="input-group">
                        <label>ROL DEL USUARIO</label>
                        <select name="rol" id="editUserRole" required>
                            <option value="analista">Analista</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </section>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarEditarUsuario">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- ===== MODAL ENVIAR NOTIFICACIÓN ===== -->
<div id="modalEnviarNotificacion" class="modal-overlay">
    <div class="modal-container" style="max-width:480px;">
        <aside class="modal-sidebar" style="max-width:160px;">
            <span class="badge-new">NOTIFICAR</span>
            <h1>Enviar<br>Notificación</h1>
            <p>Envie un mensaje al usuario seleccionado.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalNotificacion" type="button">&times;</button>
            <form id="formEnviarNotificacion">
                @csrf
                <input type="hidden" name="user_id" id="notifUserId">
                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Para:</h3>
                    <p style="margin:8px 0;font-size:0.9rem;">
                        <strong id="notifUserName">—</strong>
                    </p>
                </section>
                <section class="form-section">
                    <h3><i class="fas fa-bell"></i> Mensaje</h3>
                    <div class="input-group">
                        <label>TÍTULO</label>
                        <input type="text" name="titulo" required placeholder="Ej: Actualización de expediente">
                    </div>
                    <div class="input-group" style="margin-top:12px;">
                        <label>MENSAJE</label>
                        <textarea class="form-textarea" name="mensaje" required placeholder="Escriba el mensaje..."></textarea>
                    </div>
                </section>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarNotificacion">Cancelar</button>
                    <button type="submit" class="btn-submit">Enviar</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="{{ asset('js/chartjs/chart.umd.min.js') }}"></script>
<script>
(function() {
    let chartInstancia = null;

    // ===== TABS =====
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.admin-tab-content').forEach(c => c.style.display = 'none');
            document.getElementById('tab' + tab.dataset.tab.charAt(0).toUpperCase() + tab.dataset.tab.slice(1)).style.display = 'block';
            if (tab.dataset.tab === 'actividades') {
                cargarActividades();
                cargarGrafica();
            }
        });
    });

    // ===== USUARIOS =====
    async function cargarUsuarios() {
        const rol = document.getElementById('filtroRol').value;
        const search = document.getElementById('buscadorUsuarios').value;
        const params = new URLSearchParams();
        if (rol) params.set('rol', rol);
        if (search) params.set('search', search);

        try {
            const resp = await fetch('/usuarios?' + params.toString());
            const data = await resp.json();
            const tbody = document.getElementById('tbodyUsuarios');
            tbody.innerHTML = '';

            if (!data.data || !data.data.length) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#888;">No hay usuarios registrados</td></tr>';
                document.getElementById('usuariosCounter').textContent = 'Mostrando 0 usuarios';
                return;
            }

            data.data.forEach(u => {
                const rolClass = u.rol === 'admin' ? 'status-pill active' : 'status-pill retired';
                const fecha = new Date(u.created_at + 'T12:00:00').toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${u.id}</td>
                    <td><strong>${escaparHTML(u.nombre)}</strong></td>
                    <td>${escaparHTML(u.correo)}</td>
                    <td><span class="${rolClass}">${escaparHTML(u.rol.toUpperCase())}</span></td>
                    <td>${escaparHTML(fecha)}</td>
                    <td class="actions">
                        <i class="fas fa-shield btn-icon btn-editar-usuario" title="Editar Permisos" data-id="${u.id}" data-nombre="${escaparHTML(u.nombre)}" data-correo="${escaparHTML(u.correo)}" data-rol="${escaparHTML(u.rol)}"></i>
                        <i class="fas fa-bell btn-icon btn-notificar-usuario" title="Enviar Notificación" data-id="${u.id}" data-nombre="${escaparHTML(u.nombre)}"></i>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            const total = data.total || data.data.length;
            document.getElementById('usuariosCounter').textContent = `Mostrando ${data.data.length} de ${total} usuarios`;
        } catch (err) {
            console.error('Error al cargar usuarios:', err);
            mostrarToast('Error al cargar usuarios: ' + (err.mensaje || err.message || 'desconocido'), 'error');
        }
    }

    // ===== FILTROS USUARIOS =====
    document.getElementById('filtroRol')?.addEventListener('change', cargarUsuarios);
    let timeoutBusqueda = null;
    document.getElementById('buscadorUsuarios')?.addEventListener('input', () => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(cargarUsuarios, 400);
    });

    // ===== MODAL EDITAR USUARIO =====
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-editar-usuario');
        if (!btn) return;
        document.getElementById('editUserId').value = btn.dataset.id;
        document.getElementById('editUserName').textContent = btn.dataset.nombre;
        document.getElementById('editUserEmail').textContent = btn.dataset.correo;
        document.getElementById('editUserRole').value = btn.dataset.rol;
        document.getElementById('modalEditarUsuario').style.display = 'flex';
    });

    function cerrarModalEditarUsuario() {
        document.getElementById('modalEditarUsuario').style.display = 'none';
    }

    document.getElementById('closeModalEditarUsuario')?.addEventListener('click', cerrarModalEditarUsuario);
    document.getElementById('btnCancelarEditarUsuario')?.addEventListener('click', cerrarModalEditarUsuario);
    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalEditarUsuario')) cerrarModalEditarUsuario();
    });

    document.getElementById('formEditarUsuario')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('editUserId').value;
        const formData = new FormData(this);
        const btn = this.querySelector('.btn-submit');
        btn.disabled = true; btn.textContent = 'Guardando...';

        try {
            mostrarCargando('Actualizando permisos...');
            formData.append('_method', 'PUT');
            const resp = await fetch(`/usuarios/${id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                }
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje, 'success');
            cerrarModalEditarUsuario();
            cargarUsuarios();
        } catch (err) {
            const msg = err.errors ? Object.values(err.errors).flat().join('\n') : (err.mensaje || err.message || 'Error al actualizar');
            mostrarToast(msg, 'error');
        } finally {
            ocultarCargando();
            btn.disabled = false; btn.textContent = 'Guardar Cambios';
        }
    });

    // ===== MODAL ENVIAR NOTIFICACIÓN =====
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-notificar-usuario');
        if (!btn) return;
        document.getElementById('notifUserName').textContent = btn.dataset.nombre;
        document.getElementById('formEnviarNotificacion').reset();
        document.getElementById('notifUserId').value = btn.dataset.id;
        document.getElementById('modalEnviarNotificacion').style.display = 'flex';
    });

    function cerrarModalNotificacion() {
        document.getElementById('modalEnviarNotificacion').style.display = 'none';
    }

    document.getElementById('closeModalNotificacion')?.addEventListener('click', cerrarModalNotificacion);
    document.getElementById('btnCancelarNotificacion')?.addEventListener('click', cerrarModalNotificacion);
    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalEnviarNotificacion')) cerrarModalNotificacion();
    });

    document.getElementById('formEnviarNotificacion')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('.btn-submit');
        btn.disabled = true; btn.textContent = 'Enviando...';

        try {
            mostrarCargando('Enviando notificación...');
            const resp = await fetch('/notificaciones', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                }
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje, 'success');
            cerrarModalNotificacion();
        } catch (err) {
            mostrarToast(err.mensaje || err.message || 'Error al enviar', 'error');
        } finally {
            ocultarCargando();
            btn.disabled = false; btn.textContent = 'Enviar';
        }
    });

    // ===== ACTIVIDADES =====
    async function cargarActividades() {
        const type = document.getElementById('filtroTipoActividad').value;
        const days = document.getElementById('filtroDiasActividad').value;
        const params = new URLSearchParams({ days });
        if (type) params.set('type', type);

        try {
            const resp = await fetch('/actividades-detalle?' + params.toString());
            const data = await resp.json();
            const tbody = document.getElementById('tbodyActividades');
            tbody.innerHTML = '';

            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;color:#888;">No hay actividades registradas</td></tr>';
                return;
            }

            data.forEach(a => {
                const u = a.user || {};
                const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                const accionMap = { created: 'Creó', updated: 'Modificó', deleted: 'Eliminó' };
                const accion = accionMap[a.accion] || a.accion;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="white-space:nowrap;font-size:0.8rem;color:#64748b;">${fecha}</td>
                    <td><strong>${u.nombre || 'Sistema'}</strong></td>
                    <td><span class="badge-status ${a.accion === 'created' ? 'approved' : a.accion === 'deleted' ? 'rejected' : 'pending'}">${accion}</span></td>
                    <td style="color:#475569;font-size:0.85rem;">${a.descripcion}</td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            console.error('Error al cargar actividades:', err);
        }
    }

    // ===== GRÁFICA =====
    async function cargarGrafica() {
        const days = document.getElementById('filtroDiasActividad').value;
        const type = document.getElementById('filtroTipoActividad').value;
        const params = new URLSearchParams({ days });
        if (type) params.set('type', type);
        try {
            const resp = await fetch('/actividades-resumen?' + params.toString());
            const data = await resp.json();

            const labels = [...new Set(data.map(d => d.fecha))].sort();
            const tipos = [...new Set(data.map(d => d.tipo_entidad))];

            const datasets = tipos.map((tipo, i) => {
                const colores = ['#2563eb', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'];
                const bg = colores[i % colores.length];
                const valores = labels.map(l => {
                    const item = data.find(d => d.fecha === l && d.tipo_entidad === tipo);
                    return item ? item.total : 0;
                });
                return {
                    label: tipo.charAt(0).toUpperCase() + tipo.slice(1),
                    data: valores,
                    backgroundColor: bg + '33',
                    borderColor: bg,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                };
            });

            const ctx = document.getElementById('actividadChart').getContext('2d');
            if (chartInstancia) chartInstancia.destroy();

            chartInstancia = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (err) {
            console.error('Error al cargar gráfica:', err);
        }
    }

    // ===== FILTROS ACTIVIDAD =====
    document.getElementById('filtroTipoActividad')?.addEventListener('change', () => {
        cargarActividades();
        cargarGrafica();
    });
    document.getElementById('filtroDiasActividad')?.addEventListener('change', () => {
        cargarActividades();
        cargarGrafica();
    });

    // ===== INICIALIZAR AL ENTRAR A LA PESTAÑA =====
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'administrar' && m.target.classList.contains('active')) {
                cargarUsuarios();
            }
        });
    });

    const seccion = document.getElementById('administrar');
    if (seccion) {
        observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    }

    // Cargar usuarios inmediatamente si ya está activa
    if (document.getElementById('administrar')?.classList.contains('active')) {
        cargarUsuarios();
    }
})();
</script>
