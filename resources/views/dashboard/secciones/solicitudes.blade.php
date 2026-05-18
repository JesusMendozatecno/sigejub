<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
        <p>Administre y procese nuevas peticiones de retiro institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="abrirModal()">
            <i data-lucide="plus-circle" size="20"></i> Nueva Solicitud
        </button>
    </div>
</header>

<div class="list-header">
    <div class="list-title-area">
        <h2 style="font-size: 1.5rem; color: #0f172a;">Listado de Solicitudes</h2>
        <div class="tab-filters" id="statusFilters">
            <button class="active" data-status="all">Todas</button>
            <button data-status="pending">Pendientes</button>
            <button data-status="approved">Aprobadas</button>
            <button data-status="rejected">Rechazadas</button>
        </div>
    </div>
    <div class="list-actions">
        <button class="btn-outline">
            <i data-lucide="sliders-horizontal" size="16"></i> Filtros Avanzados
        </button>
        <button class="btn-outline">
            <i data-lucide="download" size="16"></i> Exportar
        </button>
    </div>
</div>

<table class="custom-table">
    <thead>
        <tr>
            <th>FOLIO</th>
            <th>TRABAJADOR</th>
            <th>FECHA SOLICITUD</th>
            <th>PERÍODO</th>
            <th>ESTATUS</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="folio">#SOL-2024-001</td>
            <td>
                <div class="worker-info">
                    <strong>Dr. Roberto Hernández</strong>
                    <span>Facultad de Medicina</span>
                </div>
            </td>
            <td>15 Oct 2023</td>
            <td>Otoño 2024</td>
            <td><span class="badge-status pending">PENDIENTE</span></td>
            <td class="actions">
                <i data-lucide="eye"></i>
                <i data-lucide="edit-2"></i>
            </td>
        </tr>
    </tbody>
</table>

<div class="table-footer">
    <span>Mostrando 1 - 4 de 24 solicitudes</span>
    <div class="pagination">
        <button disabled>&lt;</button>
        <button class="active">1</button>
        <button>2</button>
        <button>3</button>
        <button>&gt;</button>
    </div>
</div>

<section class="metrics-row" style="margin-top: 20px;">
    <div class="metric-card">
        <div class="metric-icon orange"><i data-lucide="hourglass"></i></div>
        <div class="metric-data">
            <h3>12</h3>
            <p>ESPERANDO REVISIÓN</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon green"><i data-lucide="check-circle-2"></i></div>
        <div class="metric-data">
            <h3>158</h3>
            <p>TOTAL APROBADAS</p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon blue"><i data-lucide="timer"></i></div>
        <div class="metric-data">
            <h3>4.2 días</h3>
            <p>TIEMPO PROMEDIO</p>
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
                    <i data-lucide="info"></i>
                    <span>Los documentos PDF deben ser legibles y estar actualizados.</span>
                </div>
                <div class="info-item">
                    <i data-lucide="shield-check"></i>
                    <span>Este proceso cumple con la normativa de seguridad de datos institucionales.</span>
                </div>
            </div>
        </aside>

        <main class="modal-form-content">
            <form id="formNuevaJubilacion">
                <section class="form-section">
                    <h3><i data-lucide="file-text"></i> Información General</h3>
                    <div class="form-row-3">
                        <div class="input-group">
                            <label>INGRESE LA CÉDULA</label>
                            <select name="cedula"><option>Seleccione...</option></select>
                        </div>
                        <div class="input-group">
                            <label>FECHA SOLICITUD</label>
                            <input type="date">
                        </div>
                        <div class="input-group">
                            <label>PERIODO</label>
                            <select><option>2024 - A</option></select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>TIPO DE JUBILACIÓN</label>
                        <select><option>Edad Avanzada</option></select>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" placeholder="Ej: Carlos Eduardo Méndez">
                        </div>
                        <div class="input-group">
                            <label>CÉDULA</label>
                            <input type="text" placeholder="V-00.000.000">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="align-left"></i> Observaciones</h3>
                    <textarea class="form-textarea" placeholder="Indique cualquier detalle adicional relevante para el trámite..."></textarea>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Registrar Solicitud</button>
                </div>
            </form>
        </main>
    </div>
</div>