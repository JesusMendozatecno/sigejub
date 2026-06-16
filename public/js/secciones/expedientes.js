/* ============================================================
   expedientes.js — JavaScript para la sección "Expedientes"
   Funcionalidad: CRUD de expedientes digitales, carga de lista
   con tarjetas, detalle de expediente con documentos, subida
   y gestión de documentos (aprobar/rechazar/re-subir), notas
   de administrador, búsqueda por cédula y observador de
   recarga al cambiar de pestaña.
   ============================================================ */

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

    /* === Obtener icono según nombre del documento === */
    function getDocIcon(nombre) {
        return ICONOS_DOC[nombre] || 'fa-file';
    }

    /* === Cargar lista de expedientes === */
    async function cargarExpedientes() {
        const grid = document.getElementById('expedientesGrid');
        if (!grid) return;
        try {
            const resp = await fetch('/expedientes');
            const data = await resp.json();
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
                        <strong>${t.nombres || ''} ${t.apellidos || ''}</strong>
                        <span>${t.cedula || '—'}</span>
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

    /* === Abrir detalle de expediente === */
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

    /* === Actualizar indicadores de progreso global === */
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

    /* === Renderizar lista de documentos === */
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

    /* === Cargar actividad del expediente (placeholder) === */
    function cargarActividadExpediente() {
        // Placeholder para actividad del expediente
    }

    /* === Volver a la lista de expedientes === */
    window.volverALista = function() {
        document.getElementById('expediente-detalle').classList.add('hidden');
        document.getElementById('expedientes-lista').classList.remove('hidden');
        expedienteActualId = null;
        cargarExpedientes();
    };

    /* === Inicialización al cargar el DOM === */
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
                cargarExpedientes();
            } catch (err) {
                mostrarToast(err.mensaje || 'Error al crear expediente', 'error');
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
                mostrarToast(err.mensaje || 'Error al subir', 'error');
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
                mostrarToast(err.mensaje || 'Error al guardar notas', 'error');
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
                    mostrarToast(err.mensaje || 'Error', 'error');
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
                    mostrarToast(err.mensaje || 'Error', 'error');
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
                        mostrarToast(err.mensaje || 'Error al reemplazar', 'error');
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
                    cargarExpedientes();
                }
            });
        });
        const seccion = document.getElementById('expedientes');
        if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    });
})();
