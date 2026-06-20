{{-- expedientes.blade.php - Sección de expedientes digitales: listado con búsqueda, detalle con documentos, subida/aprobación/rechazo y notas de administrador. --}}
<div id="expedientes-lista">
    <header class="section-header">
        <div class="header-info">
            <h1>Gestión de <span class="text-blue-accent">Expedientes</span></h1>
            <p>Administre los expedientes digitales de los trabajadores.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-primary-dark" id="btnCrearExpediente">
                <i class="fas fa-folder-plus" size="20"></i> Crear Expediente
            </button>
        </div>
    </header>

    <div class="search-filter-bar">
        <div class="search-wrapper">
            <i class="fas fa-search" size="16"></i>
            <input type="text" id="buscadorExpedientes" placeholder="Buscar por nombre o cédula...">
        </div>
        <div class="filter-group-sm">
            <select id="filtroEstadoExpediente">
                <option value="">Todos los estados</option>
                <option value="0">Sin iniciar</option>
                <option value="50">En proceso</option>
                <option value="100">Completado</option>
            </select>
        </div>
    </div>

    <div class="expedientes-grid" id="expedientesGrid">
        <p class="empty-state">Cargando expedientes...</p>
    </div>
</div>

<div id="expediente-detalle" class="hidden">
    <div class="detalle-header">
        <button class="btn-volver" onclick="volverALista()">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a Expedientes</span>
        </button>
        <div class="detalle-header-right">
            <div class="global-progress-mini" id="globalProgressMini">
                <span class="progress-label">EXPEDIENTE</span>
                <span class="progress-value" id="progressValueText">0%</span>
                <div class="progress-track-mini">
                    <div class="progress-fill-mini" id="progressFillMini" style="width: 0%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="detalle-grid">
        <aside class="detalle-sidebar">
            <div class="perfil-expediente">
                <div class="foto-expediente" id="fotoExpediente">
                    <img src="" alt="Foto" id="imgFotoCarnet">
                    <div class="foto-placeholder" id="fotoPlaceholder">
                        <i class="fas fa-user" size="48"></i>
                    </div>
                </div>
                <h2 id="detalleNombre"></h2>
                <span class="detalle-cedula" id="detalleCedula"></span>
                <span class="detalle-cargo" id="detalleCargo"></span>
                <span class="detalle-unidad" id="detalleUnidad"></span>
            </div>

            <div class="info-detalle-card">
                <div class="info-row"><span>Solicitud</span><strong id="detalleSolicitud">—</strong></div>
                <div class="info-row"><span>Años Servicio</span><strong id="detalleAnos">—</strong></div>
                <div class="info-row"><span>Edad</span><strong id="detalleEdad">—</strong></div>
            </div>

            <div class="notas-admin-card">
                <h4><i class="fas fa-pen" size="14"></i> Notas del Admin</h4>
                <textarea id="notasAdminInput" placeholder="Agregar nota..."></textarea>
                <button class="btn-guardar-notas" id="btnGuardarNotas">Guardar</button>
            </div>
        </aside>

        <main class="detalle-content">
            <div class="estado-global-card">
                <div class="eg-header">
                    <h3>Estado Global del Expediente</h3>
                    <span class="eg-porcentaje" id="egPorcentaje">0%</span>
                </div>
                <div class="eg-bar">
                    <div class="eg-fill" id="egFill" style="width: 0%;"></div>
                </div>
                <p class="eg-desc" id="egDesc">Sin documentos cargados</p>
            </div>

            <div class="documentos-header">
                <h3><i class="fas fa-file-lines" size="18"></i> Documentos</h3>
                <button class="btn-subir-doc" id="btnSubirDoc"><i class="fas fa-upload" size="16"></i> Subir Documento</button>
            </div>

            <div class="documentos-list" id="documentosList">
                <p class="empty-state" style="padding: 30px;">No hay documentos cargados.</p>
            </div>

            <div class="historial-card">
                <h4><i class="fas fa-wave-square" size="16"></i> Actividad del Expediente</h4>
                <div id="expedienteActividad">
                    <p class="empty-state" style="padding:15px;text-align:center;color:#94a3b8;">Sin actividad registrada</p>
                </div>
            </div>
        </main>
    </div>
</div>

<div id="modalCrearExpediente" class="modal-overlay">
    <div class="modal-container" style="max-width: 720px; height: auto; max-height: 90vh;">
        <aside class="modal-sidebar">
            <span class="badge-new">NUEVO EXPEDIENTE</span>
            <h1>Crear<br>Expediente<br>Digital</h1>
            <p>Busque al trabajador por su cédula. Debe tener una solicitud registrada.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalExpediente" type="button">&times;</button>
            <form id="formCrearExpediente">
                @csrf
                <section class="form-section">
                    <h3><i class="fas fa-search"></i> Buscar Trabajador</h3>
                    <div class="busqueda-cedula-row">
                        <input type="text" id="inputBuscarCedula" placeholder="Ingrese la cédula (V-00000000)" required>
                        <button type="button" class="btn-submit" id="btnBuscarTrabajador">Buscar</button>
                    </div>
                    <div id="resultadoBusqueda" class="hidden">
                        <div class="trabajador-encontrado">
                            <div class="te-avatar"><i class="fas fa-user-check" size="32"></i></div>
                            <div class="te-info">
                                <strong id="teNombre"></strong>
                                <span id="teCedula"></span>
                                <span id="teSolicitud"></span>
                            </div>
                        </div>
                        <input type="hidden" name="trabajador_id" id="inputTrabajadorId">
                        <input type="hidden" name="solicitud_id" id="inputSolicitudId">
                    </div>
                    <div id="errorBusqueda" class="hidden" style="color:#dc2626;margin-top:10px;"></div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-camera"></i> Foto Carnet</h3>
                    <div class="upload-photo-area">
                        <input type="file" name="foto_carnet" id="inputFotoCarnet" accept="image/*" class="hidden">
                        <div class="photo-preview" id="photoPreview">
                            <i class="fas fa-image" size="40"></i>
                            <p>Haga clic para subir la foto carnet del trabajador</p>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-upload"></i> Documentos</h3>
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:15px;">Seleccione los archivos PDF o imágenes para adjuntar al expediente.</p>
                    <input type="file" name="documentos[]" id="inputDocumentos" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                    <div class="dropzone-docs" id="dropzoneDocs">
                        <i class="fas fa-cloud-arrow-up" size="36"></i>
                        <p>Arrastre los archivos aquí o haga clic para seleccionar</p>
                        <span>PDF, DOC, JPG hasta 5MB</span>
                    </div>
                    <ul class="file-preview-list" id="filePreviewList"></ul>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarExpediente">Cancelar</button>
                    <button type="submit" class="btn-submit" id="btnSubmitExpediente">Crear Expediente</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalSubirDocumento" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:500px;">
        <h3 style="text-align:center;">Subir Documento</h3>
        <form id="formSubirDocumento">
            @csrf
            <div class="input-group">
                <label>NOMBRE DEL DOCUMENTO</label>
                <select name="nombre" id="selectNombreDocumento" required>
                    <option value="">Seleccione...</option>
                    <option value="Cédula de Identidad">Cédula de Identidad</option>
                    <option value="Oficio de Solicitud">Oficio de Solicitud</option>
                    <option value="Punto de Cuenta">Punto de Cuenta</option>
                    <option value="Constancia de Trabajo">Constancia de Trabajo</option>
                    <option value="Recibo de Nómina">Recibo de Nómina</option>
                    <option value="Informe Médico">Informe Médico</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="input-group">
                <label>ARCHIVO</label>
                <input type="file" name="archivo" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarSubirDoc">Cancelar</button>
                <button type="submit" class="btn-submit">Subir</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    let expedienteActualId = null;

    const ICONOS_DOC = {
        'Cédula de Identidad': 'fa-user-check',
        'Oficio de Solicitud': 'fa-file-lines',
        'Punto de Cuenta': 'fa-file-lines',
        'Constancia de Trabajo': 'fa-clipboard',
        'Recibo de Nómina': 'fa-dollar-sign',
        'Informe Médico': 'fa-heart-pulse',
    };

    function getDocIcon(nombre) {
        return ICONOS_DOC[nombre] || 'fa-file';
    }

    // === CARGAR LISTA DE EXPEDIENTES ===
    async function cargarExpedientes() {
        const grid = document.getElementById('expedientesGrid');
        if (!grid) return;
        try {
            const result = await cachedFetch('/expedientes', { ttl: 60000 });
            const data = result.data;
            grid.innerHTML = '';
            if (!data.length) {
                grid.innerHTML = '<p class="empty-state">No hay expedientes registrados. Cree el primero.</p>';
                return;
            }
            data.forEach(exp => {
                const t = exp.trabajador || {};
                const estado = exp.estado_global || 0;
                const claseEstado = estado === 100 ? 'completado' : (estado > 0 ? 'progreso' : 'pendiente');
                const textoEstado = estado === 100 ? 'Completado' : (estado > 0 ? `En Proceso (${estado}%)` : 'Pendiente');
                const foto = exp.foto_carnet ? `/storage/${exp.foto_carnet}` : '';
                const card = document.createElement('div');
                card.className = 'expediente-card';
                card.onclick = () => abrirDetalle(exp.id);
                card.innerHTML = `
                    <div class="ec-foto">
                        ${foto ? `<img src="${foto}" alt="">` : `<div class="ec-avatar"><i class="fas fa-user" size="28"></i></div>`}
                    </div>
                    <div class="ec-info">
                        <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                        <span>${escaparHTML(t.cedula || '—')}</span>
                        <span class="ec-badge ${claseEstado}">${textoEstado}</span>
                    </div>
                `;
                grid.appendChild(card);
            });
        } catch (err) {
            console.error('Error al cargar expedientes:', err);
            grid.innerHTML = '<p class="empty-state">Error al cargar expedientes.</p>';
        }
    }

    // === ABRIR DETALLE ===
    async function abrirDetalle(id) {
        expedienteActualId = id;
        try {
            mostrarCargando('Cargando expediente...');
            const resp = await fetch(`/expedientes/${id}`);
            const exp = await resp.json();
            const t = exp.trabajador || {};
            const sol = exp.solicitud || {};

            document.getElementById('expedientes-lista').classList.add('hidden');
            document.getElementById('expediente-detalle').classList.remove('hidden');

            document.getElementById('detalleNombre').textContent = `${t.nombres || ''} ${t.apellidos || ''}`;
            document.getElementById('detalleCedula').textContent = t.cedula || '—';
            document.getElementById('detalleCargo').textContent = t.cargo || '';
            document.getElementById('detalleUnidad').textContent = t.unidad_departamento || '';
            document.getElementById('detalleSolicitud').textContent = sol.tipo_jubilacion || '—';
            document.getElementById('detalleAnos').textContent = t.total_anos_servicio ? `${t.total_anos_servicio} Años` : '—';
            document.getElementById('detalleEdad').textContent = t.edad ? `${t.edad} Años` : '—';

            const fotoImg = document.getElementById('imgFotoCarnet');
            const fotoPlaceholder = document.getElementById('fotoPlaceholder');
            if (exp.foto_carnet) {
                fotoImg.src = `/storage/${exp.foto_carnet}`;
                fotoImg.classList.remove('hidden');
                fotoPlaceholder.classList.add('hidden');
            } else {
                fotoImg.classList.add('hidden');
                fotoPlaceholder.classList.remove('hidden');
            }

            document.getElementById('notasAdminInput').value = exp.notas_admin || '';

            actualizarProgresoGlobal(exp.estado_global || 0);
            renderDocumentos(exp.documentos || []);
            cargarActividadExpediente();
        } catch (err) {
            console.error('Error al abrir detalle:', err);
        } finally {
            ocultarCargando();
        }
    }

    function actualizarProgresoGlobal(porcentaje) {
        document.getElementById('progressValueText').textContent = `${porcentaje}%`;
        document.getElementById('progressFillMini').style.width = `${porcentaje}%`;
        document.getElementById('egPorcentaje').textContent = `${porcentaje}%`;
        document.getElementById('egFill').style.width = `${porcentaje}%`;

        let desc = 'Sin documentos cargados';
        if (porcentaje === 100) desc = 'Expediente completo';
        else if (porcentaje > 0) desc = `Documentos en revisión (${porcentaje}%)`;
        document.getElementById('egDesc').textContent = desc;
    }

    function renderDocumentos(documentos) {
        const container = document.getElementById('documentosList');
        container.innerHTML = '';
        if (!documentos.length) {
            container.innerHTML = '<p class="empty-state" style="padding:30px;">No hay documentos cargados.</p>';
            return;
        }
        documentos.forEach(doc => {
            const icono = getDocIcon(doc.nombre);
            const estados = { en_revision: { cls: 'pending', txt: 'En Revisión' }, aprobado: { cls: 'success', txt: 'Aprobado' }, rechazado: { cls: 'danger', txt: 'Rechazado' } };
            const est = estados[doc.estado] || estados.en_revision;
            const div = document.createElement('div');
            div.className = `doc-item-row ${doc.estado === 'rechazado' ? 'error' : ''}`;
            div.innerHTML = `
                <div class="doc-icon ${est.cls}"><i class="fas ${icono}"></i></div>
                <div class="doc-info">
                    <h4>${doc.nombre}</h4>
                    <span class="status-tag ${est.cls}">● ${est.txt}</span>
                    ${doc.nota_rechazo ? `<p class="rechazo-nota">"${doc.nota_rechazo}"</p>` : ''}
                </div>
                <div class="doc-btns">
                    <button class="btn-view" onclick="window.open('/storage/${doc.archivo}','_blank')" title="Ver"><i class="fas fa-eye"></i></button>
                    ${doc.estado === 'rechazado' ? `<button class="btn-action-primary btn-reupload" data-id="${doc.id}" title="Re-subir">${'<i class="fas fa-rotate"></i>'} Corregir</button>` : ''}
                    ${doc.estado === 'en_revision' ? `
                        <div class="doc-actions-inline">
                            <button class="btn-approve" data-id="${doc.id}" title="Aprobar"><i class="fas fa-check"></i></button>
                            <button class="btn-reject" data-id="${doc.id}" title="Rechazar"><i class="fas fa-xmark"></i></button>
                        </div>
                    ` : ''}
                </div>
            `;
            container.appendChild(div);
        });
    }

    function cargarActividadExpediente() {
        // Placeholder para actividad del expediente
    }

    window.volverALista = function() {
        document.getElementById('expediente-detalle').classList.add('hidden');
        document.getElementById('expedientes-lista').classList.remove('hidden');
        expedienteActualId = null;
        cargarExpedientes();
    };

    // === INICIALIZAR ===
    document.addEventListener('DOMContentLoaded', () => {
        cargarExpedientes();

        // Botón crear expediente
        document.getElementById('btnCrearExpediente')?.addEventListener('click', () => {
            document.getElementById('modalCrearExpediente').style.display = 'flex';
            document.getElementById('inputBuscarCedula').value = '';
            document.getElementById('resultadoBusqueda').classList.add('hidden');
            document.getElementById('errorBusqueda').classList.add('hidden');
            document.getElementById('formCrearExpediente').reset();
            document.getElementById('filePreviewList').innerHTML = '';
        });

        // Cerrar modal crear
        const cerrarCrear = () => document.getElementById('modalCrearExpediente').style.display = 'none';
        document.getElementById('closeModalExpediente')?.addEventListener('click', cerrarCrear);
        document.getElementById('btnCancelarExpediente')?.addEventListener('click', cerrarCrear);

        // Búsqueda de trabajador por cédula
        document.getElementById('btnBuscarTrabajador')?.addEventListener('click', async () => {
            const cedula = document.getElementById('inputBuscarCedula').value.trim();
            if (!cedula) return;
            const errorDiv = document.getElementById('errorBusqueda');
            const resultado = document.getElementById('resultadoBusqueda');
            errorDiv.classList.add('hidden');
            try {
                mostrarCargando('Buscando trabajador...');
                const resp = await fetch(`/expedientes/buscar-trabajador?cedula=${encodeURIComponent(cedula)}`);
                const data = await resp.json();
                if (!resp.ok) {
                    errorDiv.textContent = data.error || 'Error al buscar';
                    errorDiv.classList.remove('hidden');
                    resultado.classList.add('hidden');
                    return;
                }
                const t = data.trabajador;
                const s = data.solicitud;
                document.getElementById('teNombre').textContent = `${t.nombres} ${t.apellidos}`;
                document.getElementById('teCedula').textContent = `Cédula: ${t.cedula}`;
                document.getElementById('teSolicitud').textContent = `Solicitud: ${s.tipo_jubilacion || '—'} (${s.estado})`;
                document.getElementById('inputTrabajadorId').value = t.id;
                document.getElementById('inputSolicitudId').value = s.id;
                resultado.classList.remove('hidden');
            } catch (err) {
                errorDiv.textContent = 'Error de conexión';
                errorDiv.classList.remove('hidden');
            } finally {
                ocultarCargando();
            }
        });

        // Enter en búsqueda
        document.getElementById('inputBuscarCedula')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btnBuscarTrabajador').click();
            }
        });

        // Preview foto carnet
        document.getElementById('photoPreview')?.addEventListener('click', () => {
            document.getElementById('inputFotoCarnet').click();
        });
        document.getElementById('inputFotoCarnet')?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.innerHTML = `<img src="${ev.target.result}" style="max-width:100%;max-height:120px;border-radius:8px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `<i class="fas fa-image" size="40"></i><p>Haga clic para subir la foto carnet del trabajador</p>`;
            }
        });

        // Dropzone documentos
        const dropzone = document.getElementById('dropzoneDocs');
        const inputDocs = document.getElementById('inputDocumentos');
        const fileList = document.getElementById('filePreviewList');
        dropzone?.addEventListener('click', () => inputDocs.click());
        dropzone?.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
        dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
        dropzone?.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            inputDocs.files = e.dataTransfer.files;
            inputDocs.dispatchEvent(new Event('change'));
        });
        inputDocs?.addEventListener('change', () => {
            fileList.innerHTML = '';
            Array.from(inputDocs.files).forEach(f => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="fas fa-file"></i> ${f.name} <span>(${(f.size/1024).toFixed(1)} KB)</span>`;
                fileList.appendChild(li);
            });
        });

        // Submit crear expediente
        document.getElementById('formCrearExpediente')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const fotoInput = document.getElementById('inputFotoCarnet');
            if (fotoInput.files[0]) formData.append('foto_carnet', fotoInput.files[0]);

            const btn = document.getElementById('btnSubmitExpediente');
            btn.disabled = true; btn.textContent = 'Creando...';
            try {
                mostrarCargando('Creando expediente...');
                const resp = await fetch('/expedientes', {
                    method: 'POST', body: formData,
                    headers: { 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje, 'success');
                document.getElementById('modalCrearExpediente').style.display = 'none';
                Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/expedientes')).forEach(k => localStorage.removeItem(k));
                cargarExpedientes();
            } catch (err) {
                mostrarToast(err.mensaje || err.message || 'Error al crear expediente', 'error');
            } finally {
                ocultarCargando();
                btn.disabled = false; btn.textContent = 'Crear Expediente';
            }
        });

        // Subir documento desde detalle
        document.getElementById('btnSubirDoc')?.addEventListener('click', () => {
            if (!expedienteActualId) return;
            document.getElementById('modalSubirDocumento').style.display = 'flex';
            document.getElementById('formSubirDocumento').reset();
        });
        document.getElementById('btnCancelarSubirDoc')?.addEventListener('click', () => {
            document.getElementById('modalSubirDocumento').style.display = 'none';
        });

        document.getElementById('formSubirDocumento')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('.btn-submit');
            btn.disabled = true; btn.textContent = 'Subiendo...';
            try {
                mostrarCargando('Subiendo documento...');
                const resp = await fetch(`/expedientes/${expedienteActualId}/documentos`, {
                    method: 'POST', body: formData,
                    headers: { 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje, 'success');
                document.getElementById('modalSubirDocumento').style.display = 'none';
                abrirDetalle(expedienteActualId);
            } catch (err) {
                mostrarToast(err.mensaje || err.message || 'Error al subir', 'error');
            } finally {
                ocultarCargando();
                btn.disabled = false; btn.textContent = 'Subir';
            }
        });

        // Guardar notas admin
        document.getElementById('btnGuardarNotas')?.addEventListener('click', async () => {
            if (!expedienteActualId) return;
            const notas = document.getElementById('notasAdminInput').value;
            try {
                mostrarCargando('Guardando notas...');
                const resp = await fetch(`/expedientes/${expedienteActualId}/notas`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({ notas_admin: notas })
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje, 'success');
            } catch (err) {
                mostrarToast(err.mensaje || err.message || 'Error al guardar notas', 'error');
            } finally {
                ocultarCargando();
            }
        });

        // Delegación de eventos para aprobar/rechazar/reemplazar documentos
        document.getElementById('documentosList')?.addEventListener('click', async (e) => {
            const btnAprobar = e.target.closest('.btn-approve');
            const btnRechazar = e.target.closest('.btn-reject');
            const btnReupload = e.target.closest('.btn-reupload');

            if (btnAprobar) {
                const id = btnAprobar.dataset.id;
                try {
                    mostrarCargando('Aprobando documento...');
                    const resp = await fetch(`/documentos/${id}/estado`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                        },
                        body: JSON.stringify({ estado: 'aprobado' })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw data;
                    mostrarToast('Documento aprobado', 'success');
                    abrirDetalle(expedienteActualId);
                } catch (err) {
                    mostrarToast(err.mensaje || err.message || 'Error', 'error');
                } finally {
                    ocultarCargando();
                }
            }

            if (btnRechazar) {
                const id = btnRechazar.dataset.id;
                const razon = prompt('Motivo del rechazo:');
                if (!razon) return;
                try {
                    mostrarCargando('Rechazando documento...');
                    const resp = await fetch(`/documentos/${id}/estado`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                        },
                        body: JSON.stringify({ estado: 'rechazado', nota_rechazo: razon })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw data;
                    mostrarToast('Documento rechazado', 'warning');
                    abrirDetalle(expedienteActualId);
                } catch (err) {
                    mostrarToast(err.mensaje || err.message || 'Error', 'error');
                } finally {
                    ocultarCargando();
                }
            }

            if (btnReupload) {
                const id = btnReupload.dataset.id;
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('archivo', file);
                    try {
                        mostrarCargando('Reemplazando documento...');
                        const resp = await fetch(`/documentos/${id}/reemplazar`, {
                            method: 'POST', body: formData,
                            headers: { 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                            }
                        });
                        const data = await resp.json();
                        if (!resp.ok) throw data;
                        mostrarToast(data.mensaje, 'success');
                        abrirDetalle(expedienteActualId);
                    } catch (err) {
                        mostrarToast(err.mensaje || err.message || 'Error al reemplazar', 'error');
                    } finally {
                        ocultarCargando();
                    }
                };
                input.click();
            }
        });

        // Observador para recargar al cambiar de pestaña
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(m => {
                if (m.target.id === 'expedientes' && m.target.classList.contains('active')) {
                    if (!document.getElementById('expediente-detalle').classList.contains('hidden')) return;
                    Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/expedientes')).forEach(k => localStorage.removeItem(k));
                    cargarExpedientes();
                }
            });
        });
        const seccion = document.getElementById('expedientes');
        if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    });
})();
</script>
