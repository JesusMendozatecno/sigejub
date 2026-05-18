<div id="expedientes-lista">
    <div class="section-header-flex">
        <h1 class="page-title">Gestión de Expedientes Digitales</h1>
        <div class="search-container">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Buscar expediente por nombre o cédula...">
        </div>
    </div>

    <div class="expediente-mini-card" onclick="verDetalleExpediente('Ricardo Mendoza')" style="cursor: pointer;">
        <div class="card-image-top">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Ricardo" alt="Foto">
        </div>
        
        <div class="card-info-bottom">
            <strong>Ricardo Mendoza</strong>
            <span>V-12.456.789</span>
            <div class="card-status-pill pending">En Revisión</div>
        </div>
    </div>
    </div>

<div id="expediente-detalle" style="display: none;">
    <header class="expediente-header">
        <div class="header-left">
            <nav class="breadcrumb">EXPEDIENTES > <span>GESTIÓN DE DOCUMENTOS</span></nav>
            <h1>Expediente Digital: <span id="nombre-empleado-header">Ricardo M.</span></h1>
        </div>
        <div class="header-right">
            <div class="global-status-card">
                <div class="status-info">
                    <span class="label">ESTADO GLOBAL</span>
                    <span class="value incomplete">Incompleto (3/5)</span>
                </div>
                <div class="status-chart">
                    <span class="pct">60%</span>
                </div>
            </div>
        </div>
    </header>

    <div class="gestion-container">
        <aside class="gestion-sidebar">
            <div class="profile-summary-card">
                <div class="user-main-info">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Ricardo" class="img-profile-sm">
                    <div class="user-texts">
                        <h3>Ricardo Mendoza</h3>
                        <span>V-12.456.789</span>
                        <span class="facultad-badge">FACULTAD DE INGENIERÍA</span>
                    </div>
                </div>
                <div class="user-details-list">
                    <div class="detail-item"><span>Años de Servicio</span> <strong>28 Años</strong></div>
                    <div class="detail-item"><span>Cargo Actual</span> <strong>Titular IV</strong></div>
                    <div class="detail-item"><span>Fecha de Solicitud</span> <strong>12 Oct 2023</strong></div>
                </div>
            </div>

            <div class="asistente-carga-card">
                <h4>Asistente de Carga</h4>
                <p>Arrastre aquí los archivos PDF para una clasificación automática por IA institucional.</p>
                <div class="drop-zone-blue">
                    <div class="drop-icon-box">
                        <i data-lucide="upload"></i>
                    </div>
                    <span>SOLTAR ARCHIVOS</span>
                </div>
            </div>
            
            <button class="btn-back-minimal" onclick="volverALista()" type="button">
                <div class="icon-circle">
                    <i data-lucide="chevron-left"></i>
                </div>
                <span>Volver al listado</span>
            </button>
        </aside>

        <main class="gestion-content">
            <div class="docs-card-container">
                <div class="docs-header-flex">
                    <h3>LISTADO DE DOCUMENTOS OBLIGATORIOS</h3>
                    <button class="btn-history" type="button"><i data-lucide="history"></i> Historial de Cambios</button>
                </div>

                <div class="docs-list">
                    <div class="doc-item-row">
                        <div class="doc-icon success"><i data-lucide="user-check"></i></div>
                        <div class="doc-info">
                            <h4>Cédula de Identidad</h4>
                            <span class="status-tag success">● CARGADO <small>Verificado hace 2 días</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-view" type="button"><i data-lucide="eye"></i></button>
                            <button class="btn-action-outline" type="button">REEMPLAZAR</button>
                        </div>
                    </div>

                    <div class="doc-item-row">
                        <div class="doc-icon gray"><i data-lucide="file-text"></i></div>
                        <div class="doc-info">
                            <h4>Oficio de Solicitud</h4>
                            <span class="status-tag pending">● PENDIENTE <small>Requerido para avanzar</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-action-primary" type="button"><i data-lucide="upload"></i> SUBIR ARCHIVO</button>
                        </div>
                    </div>

                    <div class="doc-item-row error">
                        <div class="doc-icon danger"><i data-lucide="file-x"></i></div>
                        <div class="doc-info">
                            <h4>Punto de Cuenta</h4>
                            <span class="status-tag danger">● RECHAZADO <small>"Firma del Rector ausente"</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-action-primary" type="button"><i data-lucide="upload"></i> CORREGIR</button>
                        </div>
                    </div>
                </div>

                <div class="docs-footer">
                    <div class="footer-text">
                        <strong>Resumen de Cumplimiento</strong>
                        <p>Faltan 2 documentos obligatorios para completar el expediente.</p>
                    </div>
                    <button class="btn-finalize" disabled type="button">FINALIZAR EXPEDIENTE</button>
                </div>
            </div>
        </main>
    </div>
</div>