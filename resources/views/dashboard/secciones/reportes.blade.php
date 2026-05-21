<header class="section-header-flex">
    <div class="header-info">
        <h1>Centro de Reportes</h1>
        <p>Visualización y análisis de datos del sistema institucional</p>
    </div>
    <div class="header-actions">
        <div class="actions-group">
            <button class="btn-export-excel" type="button">
                <i class="fas fa-file-excel"></i>
                <span>Exportar a Excel</span>
            </button>

            <button class="btn-export-pdf" type="button">
                <i class="fas fa-file-lines"></i>
                <span>Exportar a PDF</span>
            </button>
        </div>
    </div>
</header>

<div class="filters-bar-card" style="margin-top: 20px;">
    <div class="filter-group">
        <label>RANGO DE FECHAS</label>
        <div class="filter-input">
            <i class="fas fa-calendar"></i>
            <span>01/01/2023 - 31/12/2023</span>
        </div>
    </div>
    <div class="filter-group">
        <label>ESTATUS DEL TRÁMITE</label>
        <select><option>Todos los estatus</option></select>
    </div>
    <div class="filter-group">
        <label>DEPARTAMENTO</label>
        <select><option>Todas las facultades</option></select>
    </div>
    <button class="btn-filter-apply" type="button"><i class="fas fa-filter"></i> Aplicar Filtros</button>
</div>

<div class="metrics-grid-3" style="margin-top: 20px;">
    <div class="metric-card-simple">
        <div class="metric-icon-box blue-light"><i class="fas fa-clock"></i></div>
        <span class="metric-label">TIEMPO PROMEDIO</span>
        <div class="metric-value-row">
            <h2>42 <span>días</span></h2>
        </div>
        <span class="trend down"><i class="fas fa-arrow-trend-down"></i> -5.2% respecto al mes anterior</span>
    </div>

    <div class="metric-card-simple">
        <div class="metric-icon-box green-light"><i class="fas fa-square-check"></i></div>
        <span class="metric-label">TRÁMITES FINALIZADOS</span>
        <div class="metric-value-row">
            <h2>1,284</h2>
        </div>
        <span class="trend up"><i class="fas fa-arrow-trend-up"></i> +12% en el último trimestre</span>
    </div>

    <div class="metric-card-wide">
        <div class="metric-content-left">
            <div class="metric-icon-box indigo-light"><i class="fas fa-chart-bar"></i></div>
            <span class="metric-label">TOTAL PRESTACIONES PAGADAS</span>
            <div class="metric-value-row">
                <h2>$84,295,000 <span class="currency-tag">MXN</span></h2>
            </div>
            <div class="legend-mini">
                <span class="dot-indigo"></span> Docentes
                <span class="dot-teal"></span> Administrativos
            </div>
        </div>
        <div class="metric-visual-right">
            <i class="fas fa-landmark bg-icon-watermark"></i>
        </div>
    </div>
</div>

<div class="charts-grid-2" style="margin-top: 20px;">
    <div class="chart-card">
        <div class="chart-header">
            <h3>Trabajadores Jubilados por Año</h3>
            <div class="toggle-group">
                <span>LINEAL</span>
                <span class="active">BARRAS</span>
            </div>
        </div>
        <div class="chart-placeholder-bars">
            <div class="bar-container"><div class="bar" style="height: 20%;"></div><span>2019</span></div>
            <div class="bar-container"><div class="bar" style="height: 35%;"></div><span>2020</span></div>
            <div class="bar-container"><div class="bar" style="height: 25%;"></div><span>2021</span></div>
            <div class="bar-container"><div class="bar" style="height: 45%;"></div><span>2022</span></div>
            <div class="bar-container active"><div class="bar" style="height: 80%;"></div><span>2023</span><small>284</small></div>
        </div>
    </div>

    <div class="chart-card">
        <h3>Solicitudes por Estatus Actual</h3>
        <div class="donut-chart-area">
            <div class="donut-visual">
                <div class="inner-text">
                    <strong>452</strong>
                    <span>TOTAL ACTIVAS</span>
                </div>
            </div>
            <ul class="chart-legend-list">
                <li><span class="dot blue"></span> En Trámite <strong>60%</strong></li>
                <li><span class="dot green"></span> Aprobadas <strong>25%</strong></li>
                <li><span class="dot red"></span> Rechazadas <strong>15%</strong></li>
            </ul>
        </div>
    </div>
</div>

<div class="table-card-full" style="margin-top: 20px;">
    <div class="table-header">
        <h3>Resumen Estadístico por Departamento</h3>
        <a href="#" class="view-all">Ver desglose completo <i class="fas fa-arrow-right"></i></a>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th>DEPARTAMENTO / FACULTAD</th>
                <th>PLANTILLA ACTIVA</th>
                <th>JUBILACIONES 2023</th>
                <th>TIEMPO PROMEDIO</th>
                <th>MONTO TOTAL ESTIMADO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="dept-cell">
                        <div class="dept-icon blue"><i class="fas fa-microscope"></i></div>
                        <div><strong>Facultad de Ingeniería</strong><br><small>División de Ciencias Básicas</small></div>
                    </div>
                </td>
                <td>450</td>
                <td><strong class="text-indigo">32</strong></td>
                <td><span class="badge-time green">38 días</span></td>
                <td>$12,450,000</td>
            </tr>
            <tr>
                <td>
                    <div class="dept-cell">
                        <div class="dept-icon green"><i class="fas fa-stethoscope"></i></div>
                        <div><strong>Facultad de Medicina</strong><br><small>Unidad de Especialidades</small></div>
                    </div>
                </td>
                <td>620</td>
                <td><strong class="text-indigo">48</strong></td>
                <td><span class="badge-time green">45 días</span></td>
                <td>$18,220,000</td>
            </tr>
            <tr>
                <td>
                    <div class="dept-cell">
                        <div class="dept-icon orange"><i class="fas fa-gavel"></i></div>
                        <div><strong>Facultad de Derecho</strong><br><small>Ciencias Sociales</small></div>
                    </div>
                </td>
                <td>280</td>
                <td><strong class="text-indigo">15</strong></td>
                <td><span class="badge-time blue">52 días</span></td>
                <td>$6,840,000</td>
            </tr>
        </tbody>
    </table>
</div>