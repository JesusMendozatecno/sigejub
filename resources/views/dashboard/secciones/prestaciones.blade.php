<div class="breadcrumb-area">
    <span class="breadcrumb">Panel de Control > Nómina > <strong>Cálculo de Prestaciones</strong></span>
</div>

<header class="section-header">
    <div class="header-info">
        <h1>Cálculo Técnico de Prestaciones</h1>
        <p>Determine el salario integral y las acumulaciones de ley para el personal universitario.</p>
    </div>
</header>

<div class="prestaciones-grid">
    <div class="calculation-main">
        <div class="worker-selector-card">
            <div class="worker-avatar-box">
                <i data-lucide="user"></i>
            </div>
            <div class="worker-details">
                <h3>Dr. Ricardo J. Villasmil</h3>
                <p>C.I. 12.456.789 • Profesor Titular • 28 años de servicio</p>
                <div class="badge-row">
                    <span class="badge-status active-bg">ACTIVO</span>
                    <span class="badge-status info-bg">DEDICACIÓN EXCLUSIVA</span>
                </div>
            </div>
            <button class="btn-ghost-blue" type="button">Cambiar Trabajador</button>
        </div>

        <div class="income-structure-card">
            <div class="card-title-row">
                <div class="title-with-icon">
                    <i data-lucide="banknote"></i>
                    <h3>Estructura de Ingresos Mensuales</h3>
                </div>
                <span class="disclaimer">Valores expresados en divisas según tasa BCV</span>
            </div>

            <div class="inputs-grid-2x2">
                <div class="calc-input-group">
                    <label>SUELDO BASE</label>
                    <div class="currency-input"><span>$</span><input type="text" value="1250.00"></div>
                </div>
                <div class="calc-input-group">
                    <label>PRIMA PROFESIONALIZACIÓN (20%)</label>
                    <div class="currency-input"><span>$</span><input type="text" value="250.00"></div>
                </div>
                <div class="calc-input-group">
                    <label>PRIMA POR HIJOS (CANT. 2)</label>
                    <div class="currency-input"><span>$</span><input type="text" value="85.50"></div>
                </div>
                <div class="calc-input-group">
                    <label>PRIMA DE ANTIGÜEDAD (FIJA)</label>
                    <div class="currency-input"><span>$</span><input type="text" value="312.00"></div>
                </div>
            </div>

            <div class="jubilation-percent-area">
                <div class="percent-info">
                    <strong>Porcentaje de Jubilación Aplicable</strong>
                    <p>Según el artículo 42 de la Ley Orgánica de Universidades.</p>
                </div>
                <div class="percent-toggle">
                    <button class="active" type="button">100% <span>Jubilación</span></button>
                    <button type="button">82.5% <span>Incapacidad</span></button>
                </div>
            </div>
        </div>

        <div class="bottom-metrics">
            <div class="metric-box-white border-blue">
                <div class="metric-icon-small blue-bg"><i data-lucide="calculator"></i></div>
                <div class="metric-content">
                    <span class="tag-top">BASE MENSUAL</span>
                    <p>Salario Integral Estimado</p>
                    <h2>$ 1.897,50</h2>
                </div>
            </div>
            <div class="metric-box-white border-green">
                <div class="metric-icon-small green-bg"><i data-lucide="history"></i></div>
                <div class="metric-content">
                    <span class="tag-top">VIGENCIA: 2024</span>
                    <p>Factor de Antigüedad (Años)</p>
                    <h2>28,4</h2>
                </div>
            </div>
        </div>
    </div>

    <aside class="calculation-sidebar">
        <div class="consolidated-card">
            <p class="card-label">RESUMEN CONSOLIDADO</p>
            <span class="total-subtitle">Total Prestaciones Acumuladas</span>
            <h1 class="total-amount">$ 53.889,00</h1>
            
            <div class="sub-amounts">
                <div class="sub-item">
                    <span>Monto Mensual</span>
                    <strong>$ 1.897,50</strong>
                </div>
                <div class="sub-item">
                    <span>Ahorro Caja</span>
                    <strong>$ 4.560,12</strong>
                </div>
            </div>

            <div class="liquidation-status">
                <div class="status-header">
                    <span>Estado de Liquidación</span>
                    <span class="status-tag-green">PRE-APROBADO</span>
                </div>
                <div class="progress-bar-thin">
                    <div class="fill" style="width: 80%;"></div>
                </div>
            </div>
        </div>

        <div class="action-buttons-stack">
            <button class="btn-dark-full" type="button"><i data-lucide="printer"></i> Generar Comprobante</button>
            <button class="btn-outline-full" type="button"><i data-lucide="refresh-cw"></i> Actualizar Historial</button>
            <p class="helper-text">Al procesar, se enviará una notificación al Departamento de Finanzas y al trabajador.</p>
        </div>

        <div class="doc-checklist-card">
            <h3>DOCUMENTOS REQUERIDOS</h3>
            <ul class="checklist">
                <li class="done"><i data-lucide="check-circle-2"></i> Certificación de Cargos (Actualizado)</li>
                <li class="done"><i data-lucide="check-circle-2"></i> Constancia de Años de Servicio</li>
                <li class="pending"><i data-lucide="circle"></i> Acta de Cese de Funciones</li>
            </ul>
        </div>
    </aside>
</div>