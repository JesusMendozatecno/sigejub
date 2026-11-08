
<style>
.carta-indicator { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; transition: background .2s; }
.carta-indicator:hover { background: #e2e8f0; }
</style>
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

    <div id="expedientesCarpetas" class="carpetas-chips"></div>
    <div class="expedientes-grid" id="expedientesGrid">
        <p class="empty-state">Cargando expedientes...</p>
    </div>
    <div class="activity-pagination" id="expedientesPagination"></div>
</div>

<div id="expediente-detalle" class="hidden">
    <div class="detalle-header">
        <button class="btn-volver" onclick="volverALista()">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a Expedientes</span>
        </button>
        <div class="detalle-header-right">
            <div class="carta-indicator" id="cartaIndicator" title="Carta de aprobación del consejo" style="cursor:pointer;">
                <i class="fas fa-scroll" id="cartaIcon" style="font-size:1.3rem;color:#475569;"></i>
            </div>
        </div>
    </div>

    <div class="detalle-grid">
        <aside class="detalle-sidebar">
            <div class="perfil-expediente">
                <div class="foto-expediente" id="fotoExpediente" style="cursor:pointer;" title="Cambiar foto carnet">
                    <img src="" alt="Foto" id="imgFotoCarnet">
                    <div class="foto-placeholder" id="fotoPlaceholder">
                        <i class="fas fa-user" size="48"></i>
                    </div>
                    <input type="file" id="inputEditFotoCarnet" accept="image/*" class="hidden">
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

            <div class="info-detalle-card" id="clausulasCardExpediente">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;">
                    <h4 style="margin:0;font-size:0.8rem;color:#0f172a;"><i class="fas fa-file-contract" size="14"></i> Cláusulas de Convención</h4>
                    <button type="button" class="btn-guardar-notas" id="btnGestionarClausulas" style="padding:5px 10px;">Gestionar</button>
                </div>
                <div id="clausulasAsignadas">
                    <p style="font-size:0.78rem;color:#94a3b8;margin:0;">Sin cláusulas asignadas</p>
                </div>
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

            <div id="docsRequeridos" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:16px;">
                <h4 style="font-size:0.85rem;color:#0f172a;margin:0 0 10px;display:flex;align-items:center;gap:6px;"><i class="fas fa-clipboard-check" style="color:#2563eb;"></i> Documentos Requeridos para Aprobación</h4>
                <div id="checklistRequeridos" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;"></div>
            </div>

            <div class="documentos-list" id="documentosList">
                <p class="empty-state" style="padding: 30px;">No hay documentos cargados.</p>
            </div>

            <div class="documentos-header" style="margin-top:18px;">
                <h3><i class="fas fa-money-check-dollar" size="18"></i> Recibos de Nómina</h3>
                <button class="btn-subir-doc" id="btnSubirRecibo"><i class="fas fa-upload" size="16"></i> Adjuntar Recibo</button>
            </div>
            <p style="font-size:0.78rem;color:#64748b;margin:-6px 0 10px;">Adjunte los comprobantes de nómina de cada período (PDF, JPG o PNG). El monto en dólares es opcional.</p>
            <div class="documentos-list" id="recibosList">
                <p class="empty-state" style="padding: 30px;">No hay recibos de nómina adjuntos.</p>
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

<div id="modalClausulasExpediente" class="modal-overlay" data-no-close-outside="true">
    <div class="modal-delete-box" style="text-align:left;max-width:680px;">
        <h3 style="text-align:center;"><i class="fas fa-file-contract"></i> Cláusulas de la Convención Colectiva</h3>
        <p style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:14px;">
            Marque las cláusulas que aplican al trabajador. Su beneficio se suma automáticamente
            en el cálculo de prestaciones.
        </p>
        <div id="listaClausulasExpediente" style="max-height:360px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;padding:6px;">
            <p style="padding:16px;color:#94a3b8;font-size:0.82rem;margin:0;">Cargando cláusulas...</p>
        </div>
        <div class="modal-actions" style="justify-content:center;">
            <button type="button" class="btn-cancel" id="btnCancelarClausulasExpediente">Cancelar</button>
            <button type="button" class="btn-submit" id="btnGuardarClausulasExpediente">Guardar cláusulas</button>
        </div>
    </div>
</div>

<div id="modalCrearExpediente" class="modal-overlay">
    <div class="modal-container" style="max-width: 720px; height: auto; max-height: 90vh;">
        <aside class="modal-sidebar">
            <span class="badge-new">NUEVO EXPEDIENTE</span>
            <h1>Crear<br>Expediente<br>Digital</h1>
            <p>Busque al trabajador por su cédula. Puede crear el expediente con o sin una solicitud registrada.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalExpediente" type="button">&times;</button>
            <form id="formCrearExpediente">
                <?php echo csrf_field(); ?>
                <section class="form-section">
                    <h3><i class="fas fa-search"></i> Buscar Trabajador</h3>
                    <div class="busqueda-cedula-row">
                        <input type="text" id="inputBuscarCedula" placeholder="Ingrese la cédula (V-00000000)" required pattern="[VEJPGvejpg]-?\d{5,10}" title="Formato: V-12345678" onkeypress="if(!/[VEJPGvejpg0-9\-]/.test(event.key))event.preventDefault()">
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
        <form id="formSubirDocumento" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
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
                </select>
            </div>
            <div class="input-group">
                <label>ARCHIVO</label>
                <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarSubirDoc">Cancelar</button>
                <button type="submit" class="btn-submit">Subir</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para adjuntar recibo de nómina (R3) -->
<div id="modalSubirRecibo" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:520px;">
        <h3 style="text-align:center;"><i class="fas fa-money-check-dollar"></i> Adjuntar Recibo de Nómina</h3>
        <form id="formSubirRecibo" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="input-group" style="margin-bottom:4px;">
                    <label>PERÍODO</label>
                    <input type="month" name="periodo" placeholder="AAAA-MM">
                </div>
                <div class="input-group" style="margin-bottom:4px;">
                    <label>MONTO (USD, OPCIONAL)</label>
                    <input type="number" step="0.01" min="0" name="monto_usd" placeholder="Ej: 250.00" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
                </div>
            </div>
            <div class="input-group">
                <label>CONCEPTO</label>
                <input type="text" name="concepto" maxlength="150" placeholder="Ej: Nómina enero 2026">
            </div>
            <div class="input-group">
                <label>OBSERVACIONES</label>
                <input type="text" name="observaciones" maxlength="2000" placeholder="Referencia del comprobante">
            </div>
            <div class="input-group">
                <label>ARCHIVO (PDF, JPG o PNG · máx. 100 MB)</label>
                <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarSubirRecibo">Cancelar</button>
                <button type="submit" class="btn-submit">Adjuntar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para subir/modificar carta de aprobación -->
<div id="modalCartaAprobacion" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:500px;">
        <h3 style="text-align:center;"><i class="fas fa-scroll"></i> Carta de Aprobación</h3>
        <p id="cartaModalInfo" style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:12px;"></p>
        <div id="cartaActualVista" style="display:none;text-align:center;margin-bottom:14px;padding:10px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;">
            <button type="button" id="btnVerCartaActual" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1px solid #2563eb;background:#eff6ff;color:#1d4ed8;border-radius:8px;font-weight:600;cursor:pointer;">
                <i class="fas fa-eye" size="14"></i> Ver carta actual
            </button>
            <p style="margin:8px 0 0;font-size:0.78rem;color:#64748b;">Verifíquela y, si hay un error, cámbiela abajo.</p>
        </div>
        <form id="formCartaAprobacion">
            <?php echo csrf_field(); ?>
            <div class="input-group">
                <label>ARCHIVO (PDF)</label>
                <input type="file" name="carta" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarCarta">Cancelar</button>
                <button type="submit" class="btn-submit">Subir/Reemplazar Carta</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    let expedienteActualId = null;
    let expedienteDocsActuales = [];
    let clausulasExpediente = [];

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
    let expedientesPaginaActual = 1;
    let expedientesCache = [];
    let expedientesTotalPaginas = 1;

    // Devuelve la letra (A-Z) de agrupación a partir del APELLIDO del trabajador
    // (el orden alfabético de archivo se hace por apellido).
    function letraCarpetaExpediente(exp) {
        const t = (exp && exp.trabajador) || {};
        const base = (t.apellidos || t.nombres || '').trim();
        const ch = base.charAt(0).toUpperCase();
        if (!ch) return '#';
        return /[A-ZÁÉÍÓÚÑ]/.test(ch) ? ch.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : '#';
    }

    function renderPaginacionExpedientes() {
        const cont = document.getElementById('expedientesPagination');
        if (!cont) return;
        if (expedientesTotalPaginas <= 1) { cont.innerHTML = ''; return; }
        const cur = expedientesPaginaActual;
        const last = expedientesTotalPaginas;
        let html = '<button class="pag-btn" data-p="' + (cur - 1) + '"' + (cur <= 1 ? ' disabled' : '') + '>&lsaquo;</button>';
        for (let i = 1; i <= last; i++) {
            if (i === 1 || i === last || (i >= cur - 2 && i <= cur + 2)) {
                html += '<button class="pag-btn' + (i === cur ? ' active' : '') + '" data-p="' + i + '">' + i + '</button>';
            } else if (i === cur - 3 || i === cur + 3) {
                html += '<span class="pag-info">&hellip;</span>';
            }
        }
        html += '<button class="pag-btn" data-p="' + (cur + 1) + '"' + (cur >= last ? ' disabled' : '') + '>&rsaquo;</button>';
        html += '<span class="pag-info">' + cur + ' de ' + last + '</span>';
        cont.innerHTML = html;
        cont.querySelectorAll('.pag-btn[data-p]').forEach(function(b) {
            b.addEventListener('click', function() {
                const p = parseInt(this.dataset.p, 10);
                if (p >= 1 && p <= last && p !== cur) cargarExpedientes(p);
            });
        });
    }

    // Filtra en cliente por texto (nombre/cédula) y estado. Devuelve la lista filtrada.
    function filtrarExpedientes(lista) {
        const q = (document.getElementById('buscadorExpedientes')?.value || '').trim().toLowerCase();
        const estadoF = document.getElementById('filtroEstadoExpediente')?.value || '';
        return lista.filter(exp => {
            const t = exp.trabajador || {};
            if (estadoF !== '') {
                const est = String(exp.estado_global || 0);
                if (estadoF === '0' ? est !== '0' : est !== estadoF) return false;
            }
            if (q) {
                const texto = ((t.nombres || '') + ' ' + (t.apellidos || '') + ' ' + (t.cedula || '')).toLowerCase();
                if (texto.indexOf(q) === -1) return false;
            }
            return true;
        });
    }

    // Pinta una lista de expedientes agrupada por carpeta alfabética (vista drilldown).
    function pintarExpedientes(lista, grid, carpetasCont) {
        const grupos = {};
        lista.forEach(exp => {
            const letra = letraCarpetaExpediente(exp);
            (grupos[letra] = grupos[letra] || []).push(exp);
        });
        const letras = Object.keys(grupos).sort();
        grid.innerHTML = '';
        let htmlGrid = '';
        let htmlCarpetas = '';

        htmlGrid += '<button type="button" class="btn-volver-carpetas hidden" id="btnVolverCarpetasExp"><i class="fas fa-arrow-left"></i> Volver a carpetas</button>';

        letras.forEach(letra => {
            htmlCarpetas += '<button type="button" class="carpeta-chip" data-letra="' + letra + '">' + letra + '<span class="carpeta-chip-count">' + grupos[letra].length + '</span></button>';
            htmlGrid += '<div class="carpeta-grupo carpeta-drilldown hidden" data-letra="' + letra + '">';
            htmlGrid += '<div class="carpeta-header"><i class="fas fa-folder-open"></i><span class="carpeta-letra">' + letra + '</span><span class="carpeta-total">' + grupos[letra].length + ' expediente' + (grupos[letra].length === 1 ? '' : 's') + '</span></div>';
            htmlGrid += '<div class="expedientes-grid">';
            grupos[letra].forEach(exp => {
                const t = exp.trabajador || {};
                const estado = exp.estado_global || 0;
                const claseEstado = estado === 100 ? 'completado' : (estado > 0 ? 'progreso' : 'pendiente');
                const textoEstado = estado === 100 ? 'Completado' : (estado > 0 ? `En Proceso (${estado}%)` : 'Pendiente');
                const foto = exp.foto_carnet ? `/storage/${exp.foto_carnet}` : '';
                htmlGrid += `
                    <div class="expediente-card" data-id="${exp.id}">
                        <div class="ec-foto">
                            ${foto ? `<img src="${foto}" alt="">` : `<div class="ec-avatar"><i class="fas fa-user"></i></div>`}
                        </div>
                        <div class="ec-info">
                            <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                            <span>${escaparHTML(t.cedula || '—')}</span>
                            <span class="ec-badge ${claseEstado}">${textoEstado}</span>
                        </div>
                    </div>
                `;
            });
            htmlGrid += '</div></div>';
        });
        grid.innerHTML = htmlGrid;
        if (carpetasCont) carpetasCont.innerHTML = htmlCarpetas;

        grid.querySelectorAll('.expediente-card').forEach(card => {
            card.addEventListener('click', function() { abrirDetalle(parseInt(this.dataset.id, 10)); });
        });

        const btnVolver = document.getElementById('btnVolverCarpetasExp');

        function abrirCarpeta(letra) {
            if (!carpetasCont) return;
            carpetasCont.classList.add('hidden');
            carpetasCont.style.display = 'none';
            grid.querySelectorAll('.carpeta-grupo').forEach(function(g) {
                const activa = g.dataset.letra === letra;
                g.classList.toggle('carpeta-activa', activa);
                g.classList.toggle('hidden', !activa);
                // El grupo se oculta/muestra también en línea: si no, una vez
                // que se vuelve a las carpetas la letra no se puede abrir más.
                g.style.display = activa ? '' : 'none';
            });
            if (btnVolver) {
                btnVolver.classList.remove('hidden');
                btnVolver.onclick = function() {
                    volverACarpetasExp();
                };
            }
            // Subir al inicio de la sección
            const sec = document.getElementById('seccion-expedientes');
            if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

    function volverACarpetasExp() {
        if (carpetasCont) {
            carpetasCont.classList.remove('hidden');
            carpetasCont.style.display = '';
        }
        grid.querySelectorAll('.carpeta-grupo').forEach(function(g) {
            g.classList.remove('carpeta-activa');
            g.classList.add('hidden');
            g.style.display = 'none';
        });
        const btnVolver = document.getElementById('btnVolverCarpetasExp');
        if (btnVolver) {
            btnVolver.classList.add('hidden');
            btnVolver.style.display = 'none';
        }
    }

        if (carpetasCont) {
            carpetasCont.querySelectorAll('.carpeta-chip').forEach(chip => {
                chip.addEventListener('click', function() {
                    abrirCarpeta(this.dataset.letra);
                });
            });
        }
        grid.querySelectorAll('.carpeta-header').forEach(function(header) {
            const grupo = header.closest('.carpeta-grupo');
            header.style.cursor = 'pointer';
            header.addEventListener('click', function(e) {
                // Evitar que al hacer clic en tarjeta se dispare
                if (e.target.closest('.expediente-card')) return;
                abrirCarpeta(grupo.dataset.letra);
            });
        });
    }

    async function cargarExpedientes(page) {
        const grid = document.getElementById('expedientesGrid');
        const carpetasCont = document.getElementById('expedientesCarpetas');
        if (!grid) return;
        expedientesPaginaActual = page || 1;
        try {
            const result = await cachedFetch('/expedientes?page=' + expedientesPaginaActual + '&per_page=12', { ttl: 60000 });
            const raw = result.data;
            const data = Array.isArray(raw) ? raw : (raw.data || []);
            expedientesCache = data;
            expedientesTotalPaginas = Array.isArray(raw) ? 1 : (raw.last_page || 1);
            if (carpetasCont) carpetasCont.innerHTML = '';
            const filtrados = filtrarExpedientes(data);
            if (!filtrados.length) {
                grid.innerHTML = '<p class="empty-state">' + (data.length ? 'No hay expedientes que coincidan con el filtro.' : 'No hay expedientes registrados. Cree el primero.') + '</p>';
                renderPaginacionExpedientes();
                return;
            }
            pintarExpedientes(filtrados, grid, carpetasCont);
            renderPaginacionExpedientes();
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
            expedienteDocsActuales = (exp.documentos || []).filter(d => d.estado !== 'rechazado');
            renderChecklistRequeridos(exp.documentos || []);
            renderDocumentos(exp.documentos || []);
            renderRecibos(exp.recibos || []);
            clausulasExpediente = exp.clausulas || [];
            renderClausulasExpediente();
            renderCartaAprobacion(exp);
            cargarActividadExpediente();
        } catch (err) {
            console.error('Error al abrir detalle:', err);
        } finally {
            ocultarCargando();
        }
    }

    function actualizarProgresoGlobal(porcentaje) {
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

    function renderCartaAprobacion(exp) {
        const indicator = document.getElementById('cartaIndicator');
        if (!indicator) return;

        const icon = document.getElementById('cartaIcon');
        const tieneCarta = !!exp.carta_aprobacion;

        if (exp.estado_global < 100) {
            indicator.style.display = 'none';
            return;
        }

        indicator.style.display = 'inline-flex';
        const userRol = '<?php echo e(Auth::user()->rol); ?>';
        indicator.title = tieneCarta
            ? (userRol === 'superadmin' ? 'Gestionar carta de aprobación' : 'Ver carta de aprobación')
            : 'Sin carta de aprobación';

        if (tieneCarta) {
            icon.style.color = '#16a34a';
            icon.className = 'fas fa-check-circle';
        } else {
            icon.style.color = '#475569';
            icon.className = 'fas fa-scroll';
        }
    }

    function cargarActividadExpediente() {
        // Placeholder para actividad del expediente
    }

    const DOCS_REQUERIDOS = [
        'Cédula de Identidad',
        'Oficio de Solicitud',
        'Punto de Cuenta',
        'Constancia de Trabajo',
        'Recibo de Nómina',
        'Informe Médico',
    ];

    function renderChecklistRequeridos(documentos) {
        const container = document.getElementById('checklistRequeridos');
        if (!container) return;
        let html = '';
        DOCS_REQUERIDOS.forEach(nombre => {
            const doc = documentos.find(d => d.nombre === nombre);
            const tiene = !!doc;
            let icono = '<i class="fas fa-circle" style="color:#d1d5db;"></i>';
            let color = '#64748b';
            let etiqueta = '';
            if (doc && doc.estado === 'aprobado') {
                icono = '<i class="fas fa-check-circle" style="color:#16a34a;"></i>';
                color = '#16a34a';
            } else if (tiene) {
                icono = '<i class="fas fa-circle-notch fa-spin" style="color:#f59e0b;"></i>';
                color = '#f59e0b';
                etiqueta = ' (en revisión)';
            }
            html += '<div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:' + color + ';">' + icono + ' ' + nombre + etiqueta + '</div>';
        });
        container.innerHTML = html;
    }

    window.volverALista = function() {
        document.getElementById('expediente-detalle').classList.add('hidden');
        document.getElementById('expedientes-lista').classList.remove('hidden');
        expedienteActualId = null;
        cargarExpedientes();
    };

    // === CLÁUSULAS DE CONVENCIÓN COLECTIVA ===
    function renderClausulasExpediente() {
        const cont = document.getElementById('clausulasAsignadas');
        if (!cont) return;
        if (!clausulasExpediente.length) {
            cont.innerHTML = '<p style="font-size:0.78rem;color:#94a3b8;margin:0;">Sin cláusulas asignadas</p>';
            return;
        }
        cont.innerHTML = clausulasExpediente.map(c =>
            '<div style="font-size:0.78rem;line-height:1.45;padding:4px 0;border-bottom:1px solid #f1f5f9;">' +
                '<strong>Cláusula ' + escaparHTML(c.numero) + '</strong> · ' + escaparHTML(c.nombre) +
                '<div style="font-size:0.72rem;color:#64748b;">' + escaparHTML(descripcionClausula(c)) + '</div>' +
            '</div>'
        ).join('');
    }

    function descripcionClausula(c) {
        const valor = parseFloat(c.valor) || 0;
        let texto;
        switch (c.tipo_calculo) {
            case 'porcentaje_sueldo_base': texto = valor + '% del sueldo base'; break;
            case 'porcentaje_sueldo_integral': texto = valor + '% del sueldo integral'; break;
            case 'dias_sueldo_integral': texto = valor + ' días de salario integral'; break;
            case 'monto_fijo': texto = 'Bs. ' + valor.toFixed(2) + ' fijos'; break;
            default: texto = 'Informativa (sin monto adicional)'; break;
        }
        if (c.por_anio) texto += ' por año de servicio';
        return texto;
    }

    async function abrirModalClausulas() {
        if (!expedienteActualId) return;
        const cont = document.getElementById('listaClausulasExpediente');
        cont.innerHTML = '<p style="padding:16px;color:#94a3b8;font-size:0.82rem;margin:0;">Cargando cláusulas...</p>';
        document.getElementById('modalClausulasExpediente').style.display = 'flex';
        try {
            const resp = await fetch('/expedientes/' + expedienteActualId + '/clausulas');
            const data = await resp.json();
            const disponibles = data.disponibles || [];
            const marcadas = new Set((data.seleccionadas || []).map(c => parseInt(c.id, 10)));
            if (!disponibles.length) {
                cont.innerHTML = '<p style="padding:16px;color:#94a3b8;font-size:0.82rem;margin:0;">No hay cláusulas registradas. Créelas en Cargos y Grados → Cláusulas.</p>';
                return;
            }
            cont.innerHTML = disponibles.map(c =>
                '<label style="display:flex;gap:10px;align-items:flex-start;padding:10px;border-bottom:1px solid #f1f5f9;cursor:pointer;">' +
                    '<input type="checkbox" value="' + c.id + '"' + (marcadas.has(parseInt(c.id, 10)) ? ' checked' : '') + ' style="margin-top:3px;">' +
                    '<span style="font-size:0.82rem;line-height:1.45;">' +
                        '<strong>Cláusula ' + escaparHTML(c.numero) + '</strong> · ' + escaparHTML(c.nombre) +
                        '<div style="font-size:0.75rem;color:#64748b;">' + escaparHTML(descripcionClausula(c)) + '</div>' +
                        (c.descripcion ? '<div style="font-size:0.72rem;color:#94a3b8;margin-top:2px;">' + escaparHTML(c.descripcion) + '</div>' : '') +
                    '</span>' +
                '</label>'
            ).join('');
        } catch (err) {
            cont.innerHTML = '<p style="padding:16px;color:#dc2626;font-size:0.82rem;margin:0;">Error al cargar las cláusulas.</p>';
        }
    }

    async function guardarClausulas() {
        if (!expedienteActualId) return;
        const btn = document.getElementById('btnGuardarClausulasExpediente');
        const ids = Array.from(document.querySelectorAll('#listaClausulasExpediente input[type="checkbox"]:checked'))
            .map(i => parseInt(i.value, 10));
        btn.disabled = true; btn.textContent = 'Guardando...';
        try {
            const resp = await fetch('/expedientes/' + expedienteActualId + '/clausulas', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ clausulas: ids })
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Cláusulas actualizadas.', 'success');
            document.getElementById('modalClausulasExpediente').style.display = 'none';
            abrirDetalle(expedienteActualId);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al guardar las cláusulas.', 'error');
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar cláusulas';
        }
    }

    document.getElementById('btnGestionarClausulas')?.addEventListener('click', abrirModalClausulas);
    document.getElementById('btnCancelarClausulasExpediente')?.addEventListener('click', function() {
        document.getElementById('modalClausulasExpediente').style.display = 'none';
    });
    document.getElementById('btnGuardarClausulasExpediente')?.addEventListener('click', guardarClausulas);

    // === RECIBOS DE NÓMINA ===
    // Estas funciones viven en el ámbito del módulo porque `abrirDetalle` las
    // invoca al abrir el expediente (antes estaban dentro de DOMContentLoaded
    // y provocaban un ReferenceError).
    function renderRecibos(recibos) {
        const container = document.getElementById('recibosList');
        if (!container) return;
        if (!recibos || !recibos.length) {
            container.innerHTML = '<p class="empty-state" style="padding:30px;">No hay recibos de nómina adjuntos.</p>';
            return;
        }
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
        container.innerHTML = recibos.map(r => `
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="font-size:0.82rem;line-height:1.55;">
                    <strong>${esc(r.periodo || '—')}</strong>${r.concepto ? ' · ' + esc(r.concepto) : ''}
                    ${r.monto_usd ? '<span style="color:#16a34a;font-weight:700;margin-left:8px;">$ ' + Number(r.monto_usd).toFixed(2) + '</span>' : ''}
                    ${r.observaciones ? '<div style="font-size:0.75rem;color:#64748b;">' + esc(r.observaciones) + '</div>' : ''}
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    ${r.archivo ? `<a href="/storage/${esc(r.archivo)}" target="_blank" class="btn-icon btn-ver" title="Ver recibo"><i class="fas fa-eye"></i></a>` : ''}
                    <button type="button" class="btn-icon btn-eliminar" title="Eliminar recibo" data-recibo-id="${r.id}"><i class="fas fa-trash-can"></i></button>
                </div>
            </div>`).join('');
        container.querySelectorAll('[data-recibo-id]').forEach(btn => {
            btn.addEventListener('click', () => eliminarRecibo(parseInt(btn.dataset.reciboId, 10)));
        });
    }

    async function eliminarRecibo(id) {
        if (!id) return;
        if (!confirm('¿Eliminar este recibo de nómina?')) return;
        try {
            mostrarCargando('Eliminando recibo...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            const resp = await fetch(`/recibos-pago/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Recibo eliminado.', 'success');
            abrirDetalle(expedienteActualId);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al eliminar el recibo.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    // === INICIALIZAR ===
    document.addEventListener('DOMContentLoaded', () => {
        cargarExpedientes();

        // Buscador y filtro de estado: refiltran la lista ya cargada.
        const repintarFiltro = () => {
            const grid = document.getElementById('expedientesGrid');
            const carpetasCont = document.getElementById('expedientesCarpetas');
            if (!grid) return;
            const filtrados = filtrarExpedientes(expedientesCache);
            if (!filtrados.length) {
                grid.innerHTML = '<p class="empty-state">No hay expedientes que coincidan con el filtro.</p>';
                if (carpetasCont) carpetasCont.innerHTML = '';
                return;
            }
            pintarExpedientes(filtrados, grid, carpetasCont);
        };
        document.getElementById('buscadorExpedientes')?.addEventListener('input', repintarFiltro);
        document.getElementById('filtroEstadoExpediente')?.addEventListener('change', repintarFiltro);

        // Botón crear expediente
        document.getElementById('btnCrearExpediente')?.addEventListener('click', () => {
            document.getElementById('modalCrearExpediente').style.display = 'flex';
            document.getElementById('inputBuscarCedula').value = '';
            document.getElementById('resultadoBusqueda').classList.add('hidden');
            document.getElementById('errorBusqueda').classList.add('hidden');
            document.getElementById('formCrearExpediente').reset();
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
                document.getElementById('teSolicitud').textContent = s
                    ? `Solicitud: ${s.tipo_jubilacion || '—'} (${s.estado})`
                    : 'Sin solicitud asociada (se puede crear el expediente igual).';
                document.getElementById('inputTrabajadorId').value = t.id;
                document.getElementById('inputSolicitudId').value = s ? s.id : '';
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

        // Editar foto carnet desde el detalle
        document.getElementById('fotoExpediente')?.addEventListener('click', () => {
            document.getElementById('inputEditFotoCarnet').click();
        });
        document.getElementById('inputEditFotoCarnet')?.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file || !expedienteActualId) return;
            const formData = new FormData();
            formData.append('foto_carnet', file);
            try {
                mostrarCargando('Actualizando foto carnet...');
                const resp = await fetch(`/expedientes/${expedienteActualId}/foto-carnet`, {
                    method: 'POST', body: formData,
                    headers: { 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                document.getElementById('imgFotoCarnet').src = '/storage/' + data.foto_carnet;
                document.getElementById('imgFotoCarnet').classList.remove('hidden');
                document.getElementById('fotoPlaceholder').classList.add('hidden');
                mostrarToast(data.mensaje, 'success');
            } catch (err) {
                mostrarToast(err.mensaje || 'Error al actualizar foto', 'error');
            } finally {
                ocultarCargando();
                e.target.value = '';
            }
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
                cargarExpedientes(1);
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
            const select = document.getElementById('selectNombreDocumento');
            const ocupados = expedienteDocsActuales.map(d => d.nombre);
            Array.from(select.options).forEach(opt => {
                opt.disabled = ocupados.includes(opt.value);
            });
        });
        document.getElementById('btnCancelarSubirDoc')?.addEventListener('click', () => {
            document.getElementById('modalSubirDocumento').style.display = 'none';
        });


        document.getElementById('btnSubirRecibo')?.addEventListener('click', () => {
            if (!expedienteActualId) return;
            document.getElementById('formSubirRecibo').reset();
            document.getElementById('modalSubirRecibo').style.display = 'flex';
        });
        document.getElementById('btnCancelarSubirRecibo')?.addEventListener('click', () => {
            document.getElementById('modalSubirRecibo').style.display = 'none';
        });
        document.getElementById('modalSubirRecibo')?.addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('formSubirRecibo')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!expedienteActualId) return;
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('.btn-submit');
            btn.disabled = true; btn.textContent = 'Subiendo...';
            try {
                mostrarCargando('Subiendo recibo...');
                const resp = await fetch(`/expedientes/${expedienteActualId}/recibos`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje, 'success');
                document.getElementById('modalSubirRecibo').style.display = 'none';
                abrirDetalle(expedienteActualId);
            } catch (err) {
                mostrarToast(err.mensaje || err.message || 'Error al subir el recibo', 'error');
            } finally {
                ocultarCargando();
                btn.disabled = false; btn.textContent = 'Adjuntar';
            }
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
                input.accept = '.pdf,.jpg,.jpeg,.png';
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

        // Carta de aprobación: click en indicador
        const CURRENT_USER_ROL = '<?php echo e(Auth::user()->rol); ?>';
        document.getElementById('cartaIndicator')?.addEventListener('click', async function() {
            if (!expedienteActualId) return;
            try {
                const resp = await fetch(`/expedientes/${expedienteActualId}`);
                const exp = await resp.json();
                if (CURRENT_USER_ROL === 'superadmin') {
                    const info = document.getElementById('cartaModalInfo');
                    const vistaCarta = document.getElementById('cartaActualVista');
                    const btnVerCarta = document.getElementById('btnVerCartaActual');
                    info.textContent = exp.carta_aprobacion ? 'Ya existe una carta. Puede verla y, si hay un error, reemplazarla.' : 'Suba la carta de aprobación del consejo.';
                    if (exp.carta_aprobacion) {
                        vistaCarta.style.display = 'block';
                        btnVerCarta.onclick = () => window.open(`/storage/${exp.carta_aprobacion}`, '_blank');
                    } else {
                        vistaCarta.style.display = 'none';
                    }
                    document.getElementById('modalCartaAprobacion').style.display = 'flex';
                } else if (exp.carta_aprobacion) {
                    window.open(`/storage/${exp.carta_aprobacion}`, '_blank');
                } else {
                    mostrarToast('El expediente no tiene carta de aprobación aún.', 'info');
                }
            } catch (err) {
                mostrarToast('Error al consultar expediente', 'error');
            }
        });

        // Carta de aprobación: submit del formulario (superadmin)
        document.getElementById('formCartaAprobacion')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!expedienteActualId) return;
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('.btn-submit');
            const origText = btn.textContent;
            btn.disabled = true; btn.textContent = 'Subiendo...';
            try {
                mostrarCargando('Subiendo carta de aprobación...');
                const resp = await fetch(`/expedientes/${expedienteActualId}/carta-aprobacion`, {
                    method: 'POST', body: formData,
                    headers: { 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    }
                });
                const data = await resp.json();
                if (!resp.ok) throw data;
                mostrarToast(data.mensaje, 'success');
                document.getElementById('modalCartaAprobacion').style.display = 'none';
                e.target.reset();
                if (data.archivo) {
                    window.open(`/storage/${data.archivo}`, '_blank');
                }
                abrirDetalle(expedienteActualId);
            } catch (err) {
                mostrarToast(err.mensaje || err.message || 'Error al subir carta', 'error');
            } finally {
                ocultarCargando();
                btn.disabled = false; btn.textContent = origText;
            }
        });

        // Cerrar modal carta
        const cerrarCarta = () => document.getElementById('modalCartaAprobacion').style.display = 'none';
        document.getElementById('btnCancelarCarta')?.addEventListener('click', cerrarCarta);
        document.getElementById('modalCartaAprobacion')?.addEventListener('click', function(e) {
            if (e.target === this) cerrarCarta();
        });

        // Observador para recargar al cambiar de pestaña
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(m => {
                if (m.target.id === 'expedientes' && m.target.classList.contains('active')) {
                    if (!document.getElementById('expediente-detalle').classList.contains('hidden')) return;
                    Object.keys(localStorage).filter(k => k.startsWith('sigejub_cache_/expedientes')).forEach(k => localStorage.removeItem(k));
                    cargarExpedientes(1);
                }
            });
        });
        const seccion = document.getElementById('expedientes');
        if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    });
})();
</script>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views/dashboard/secciones/expedientes.blade.php ENDPATH**/ ?>