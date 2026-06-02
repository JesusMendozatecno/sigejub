<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
        <p>Administre y procese nuevas peticiones de retiro institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="abrirModalSolicitud()">
            <i class="fas fa-circle-plus" size="20"></i> Nueva Solicitud
        </button>
    </div>
</header>

<div class="list-header">
    <div class="list-title-area">
        <h2>Listado de Solicitudes</h2>
        <div class="tab-filters" id="statusFilters">
            <button class="active" data-status="all">Todas</button>
            <button data-status="pending">Pendientes</button>
            <button data-status="approved">Aprobadas</button>
            <button data-status="rejected">Rechazadas</button>
        </div>
    </div>
    <div class="list-actions">
        <button class="btn-outline" onclick="exportarSolicitudesPDF()">
            <i class="fas fa-download" size="16"></i> Exportar PDF
        </button>
    </div>
</div>

<table class="custom-table">
    <thead>
        <tr>
            <th>FOLIO</th>
            <th>TRABAJADOR</th>
            <th>TIPO DE RETIRO</th>
            <th>FECHA APERTURA</th>
            <th>ESTATUS</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody id="tbodySolicitudes">
        </tbody>
</table>

<div class="table-footer">
    <span id="solicitudesCounter">Mostrando 0 solicitudes</span>
    <div class="pagination" id="solicitudesPagination">
    </div>
</div>

<section class="metrics-row" style="margin-top: 20px;">
    <div class="metric-card">
        <div class="metric-icon orange"><i class="fas fa-hourglass"></i></div>
        <div class="metric-data">
            <h3 id="metricPendientes">0</h3>
            <p>ESPERANDO REVISIÓN</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon green"><i class="fas fa-circle-check"></i></div>
        <div class="metric-data">
            <h3 id="metricAprobadas">0</h3>
            <p>TOTAL APROBADAS</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon blue"><i class="fas fa-stopwatch"></i></div>
        <div class="metric-data">
            <h3 id="metricTotal">0</h3>
            <p>TOTAL SOLICITUDES</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon red"><i class="fas fa-circle-xmark"></i></div>
        <div class="metric-data">
            <h3 id="metricRechazadas">0</h3>
            <p>TOTAL RECHAZADAS</p>
        </div>
    </div>
</section>

<div id="modalSolicitud" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">NUEVO REGISTRO</span>
            <h1>Registro de Solicitud de Jubilación</h1>
            <p>Complete cuidadosamente todos los campos requeridos para iniciar el proceso de retiro administrativo del trabajador.</p>
            <div class="modal-info-list">
                <div class="info-item">
                    <i class="fas fa-circle-info"></i>
                    <span>Los documentos PDF deben ser legibles y estar actualizados.</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-shield-check"></i>
                    <span>Este proceso cumple con la normativa de seguridad de datos institucionales.</span>
                </div>
            </div>
            <div class="sidebar-actions">
                <button type="button" class="btn-sidebar-cancel" id="btnCancelarSolicitud">Cerrar</button>
            </div>
        </aside>

        <main class="modal-form-content">
            <form id="formSolicitud" data-action="{{ route('solicitudes.store') }}">
                @csrf
                <section class="form-section">
                    <h3><i class="fas fa-file-lines"></i> Información General</h3>
                    <div class="form-row-3">
                        <div class="input-group">
                            <label>TRABAJADOR</label>
                            <select name="trabajador_id" id="selectTrabajador" required>
                                <option value="">Seleccione un trabajador...</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>FECHA SOLICITUD</label>
                            <input type="date" name="fecha_solicitud" required>
                        </div>
                        <div class="input-group">
                            <label>PERIODO</label>
                            <select name="periodo">
                                <option value="">Seleccione...</option>
                                <option value="2024-A">2024 - A</option>
                                <option value="2024-B">2024 - B</option>
                                <option value="2025-A">2025 - A</option>
                                <option value="2025-B">2025 - B</option>
                                <option value="2026-A">2026 - A</option>
                                <option value="2026-B">2026 - B</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>TIPO DE JUBILACIÓN</label>
                        <select name="tipo_jubilacion">
                            <option value="">Seleccione...</option>
                            <option value="Edad Avanzada">Edad Avanzada</option>
                            <option value="Antigüedad">Antigüedad</option>
                            <option value="Invalidez">Invalidez</option>
                            <option value="Especial">Especial</option>
                        </select>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Datos del Trabajador</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" id="displayNombreCompleto" readonly placeholder="Seleccione un trabajador...">
                        </div>
                        <div class="input-group">
                            <label>CÉDULA</label>
                            <input type="text" id="displayCedula" placeholder="Escriba la cédula y presione Enter o TAB">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-align-left"></i> Observaciones</h3>
                    <textarea class="form-textarea" name="observaciones" placeholder="Indique cualquier detalle adicional relevante para el trámite..."></textarea>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarSolicitud">Cancelar</button>
                    <button type="submit" class="btn-submit">Registrar Solicitud</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalEditarSolicitud" class="modal-overlay">
    <div class="modal-container" style="max-width: 480px;">
        <aside class="modal-sidebar" style="max-width: 160px;">
            <span class="badge-new">ACTUALIZAR</span>
            <h1>Cambiar<br>Estatus</h1>
            <p>Actualice el estado de la solicitud de jubilación.</p>
        </aside>
        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModalEditar" type="button">&times;</button>
            <form id="formEditarSolicitud">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="solicitud_id" id="editSolicitudId">
                <section class="form-section">
                    <h3><i class="fas fa-file-lines"></i> Solicitud</h3>
                    <p style="margin: 8px 0; font-size: 0.9rem;">
                        <strong id="editFolio">—</strong> —
                        <span id="editTrabajadorNombre">—</span>
                    </p>
                    <div class="input-group">
                        <label>NUEVO ESTATUS</label>
                        <select name="estado" id="editEstado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="revision">En Revisión</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div class="input-group" style="margin-top: 12px;">
                        <label>OBSERVACIONES</label>
                        <textarea class="form-textarea" name="observaciones" placeholder="Motivo del cambio de estatus..."></textarea>
                    </div>
                </section>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarEditar">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalVerSolicitud" class="modal-overlay">
    <div class="modal-container" style="max-width: 820px;">
        <aside class="modal-sidebar">
            <span class="badge-new">DETALLE</span>
            <h1>Solicitud<br>de Jubilación</h1>
            <p>Información completa de la solicitud seleccionada.</p>
            <div class="sidebar-actions" style="margin-top: auto;">
                <button type="button" class="btn-sidebar-cancel" id="btnCerrarVer">Cerrar</button>
            </div>
        </aside>
        <main class="modal-form-content">
            <section class="form-section">
                <h3><i class="fas fa-file-lines"></i> Información General</h3>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>FOLIO</label>
                        <input type="text" id="verFolio" readonly>
                    </div>
                    <div class="input-group">
                        <label>ESTATUS</label>
                        <input type="text" id="verEstatus" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>FECHA SOLICITUD</label>
                        <input type="text" id="verFecha" readonly>
                    </div>
                    <div class="input-group">
                        <label>PERÍODO</label>
                        <input type="text" id="verPeriodo" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>TIPO JUBILACIÓN</label>
                        <input type="text" id="verTipo" readonly>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3><i class="fas fa-user"></i> Datos del Trabajador</h3>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>NOMBRE COMPLETO</label>
                        <input type="text" id="verNombre" readonly>
                    </div>
                    <div class="input-group">
                        <label>CÉDULA</label>
                        <input type="text" id="verCedula" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>CARGO</label>
                        <input type="text" id="verCargo" readonly>
                    </div>
                    <div class="input-group">
                        <label>UNIDAD / DEPARTAMENTO</label>
                        <input type="text" id="verUnidad" readonly>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>EDAD</label>
                        <input type="text" id="verEdad" readonly>
                    </div>
                    <div class="input-group">
                        <label>AÑOS DE SERVICIO</label>
                        <input type="text" id="verAnosServicio" readonly>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3><i class="fas fa-align-left"></i> Observaciones</h3>
                <textarea class="form-textarea" id="verObservaciones" readonly style="resize: none;">—</textarea>
            </section>
        </main>
    </div>
</div>

<script src="{{ asset('js/secciones/solicitudes.js') }}"></script>
