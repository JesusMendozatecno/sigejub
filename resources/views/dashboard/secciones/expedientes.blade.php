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

<script src="{{ asset('js/secciones/expedientes.js') }}"></script>
