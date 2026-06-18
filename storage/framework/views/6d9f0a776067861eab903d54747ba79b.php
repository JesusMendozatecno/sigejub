
<header class="section-header-flex">
    <div class="header-info">
        <h1>Centro de Reportes y Estadísticas</h1>
        <p>Visualización y análisis de datos del sistema institucional</p>
    </div>
    <div class="header-actions">
        <div class="actions-group">
            <button class="btn-export-excel" type="button" onclick="exportarReporteExcel()">
                <i class="fas fa-file-excel"></i>
                <span>Exportar a Excel</span>
            </button>
            <button class="btn-export-pdf" type="button" onclick="exportarReportePDF()">
                <i class="fas fa-file-lines"></i>
                <span>Exportar a PDF</span>
            </button>
        </div>
    </div>
</header>

<div class="metrics-grid-3" style="margin-top: 20px;">
    <div class="metric-card-simple" id="statTrabajadores">
        <div class="metric-icon-box blue-light"><i class="fas fa-users"></i></div>
        <span class="metric-label">TRABAJADORES REGISTRADOS</span>
        <div class="metric-value-row">
            <h2 id="totalTrabajadores">0</h2>
        </div>
    </div>
    <div class="metric-card-simple" id="statSolicitudes">
        <div class="metric-icon-box green-light"><i class="fas fa-file-lines"></i></div>
        <span class="metric-label">SOLICITUDES TOTALES</span>
        <div class="metric-value-row">
            <h2 id="totalSolicitudes">0</h2>
        </div>
    </div>
    <div class="metric-card-wide" id="statUsuarios">
        <div class="metric-content-left">
            <div class="metric-icon-box indigo-light"><i class="fas fa-user-gear"></i></div>
            <span class="metric-label">USUARIOS ACTIVOS</span>
            <div class="metric-value-row">
                <h2 id="totalUsuarios">0</h2>
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
            <h3>Trabajadores por Tipo de Nómina</h3>
        </div>
        <div class="chart-container" style="height:250px;padding:10px;" id="chartNomina">
            <canvas id="chartNominaCanvas"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Solicitudes por Estatus</h3>
        <div class="donut-chart-area" id="chartSolicitudes">
            <canvas id="chartSolicitudesCanvas"></canvas>
        </div>
    </div>
</div>

<div class="table-card-full" style="margin-top: 20px;">
    <div class="table-header">
        <h3>Resumen General del Sistema</h3>
    </div>
    <table class="report-table" id="tablaResumen">
        <thead>
            <tr>
                <th>MÉTRICA</th>
                <th>VALOR</th>
                <th>DETALLE</th>
            </tr>
        </thead>
        <tbody id="tbodyResumen"></tbody>
    </table>
</div>

<script>
window.exportarReporteExcel = function() {
    window.open('/exportar/nomina', '_blank');
};

window.exportarReportePDF = function() {
    mostrarToast('Exportación PDF próximamente disponible', 'info');
};

var chartNominaInstance = null;
var chartSolicitudesInstance = null;

async function cargarStats() {
    try {
        var r = await fetch('/trabajadores?per_page=1');
        var d = await r.json();
        document.getElementById('totalTrabajadores').textContent = d.total || 0;
    } catch(e) {}

    try {
        var r = await fetch('/solicitudes?per_page=1');
        var d = await r.json();
        document.getElementById('totalSolicitudes').textContent = d.total || 0;
    } catch(e) {}

    try {
        var r = await fetch('/usuarios?per_page=1');
        var d = await r.json();
        document.getElementById('totalUsuarios').textContent = d.total || 0;
    } catch(e) {}

    // Tabla resumen
    var tbody = document.getElementById('tbodyResumen');
    tbody.innerHTML = '';
    var items = [
        ['Trabajadores registrados', document.getElementById('totalTrabajadores').textContent, 'Total en el sistema'],
        ['Solicitudes totales', document.getElementById('totalSolicitudes').textContent, 'Incluye todos los estatus'],
        ['Usuarios activos', document.getElementById('totalUsuarios').textContent, 'Analistas y administradores'],
    ];
    items.forEach(function(item) {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td><strong>' + item[0] + '</strong></td><td>' + item[1] + '</td><td style="color:#64748b;">' + item[2] + '</td>';
        tbody.appendChild(tr);
    });
}

async function cargarCharts() {
    if (typeof Chart === 'undefined') return;

    // Chart: Solicitudes por estatus
    try {
        var r = await fetch('/solicitudes?per_page=1&estado=approved');
        var aprobadas = (await r.json()).total || 0;
        r = await fetch('/solicitudes?per_page=1&estado=rejected');
        var rechazadas = (await r.json()).total || 0;
        r = await fetch('/solicitudes?per_page=1&estado=pending');
        var pendientes = (await r.json()).total || 0;

        if (chartSolicitudesInstance) chartSolicitudesInstance.destroy();
        var ctx = document.getElementById('chartSolicitudesCanvas');
        if (ctx) {
            chartSolicitudesInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                    datasets: [{
                        data: [pendientes, aprobadas, rechazadas],
                        backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
    } catch(e) {}

    // Chart: Trabajadores por tipo de nómina (distribución simulada con datos reales de cargo)
    try {
        var r = await fetch('/trabajadores?per_page=500');
        var data = await r.json();
        var rows = data.data || data;
        var docente = 0, admin = 0, obrero = 0, otros = 0;
        for (var i = 0; i < rows.length; i++) {
            var cargo = (rows[i].cargo || '').toLowerCase();
            var unidad = (rows[i].unidad_departamento || '').toLowerCase();
            if (cargo.indexOf('docente') !== -1 || unidad.indexOf('académico') !== -1 || unidad.indexOf('academico') !== -1) docente++;
            else if (cargo.indexOf('obrero') !== -1 || unidad.indexOf('obrero') !== -1) obrero++;
            else if (cargo.indexOf('admin') !== -1 || unidad.indexOf('admin') !== -1) admin++;
            else otros++;
        }

        if (chartNominaInstance) chartNominaInstance.destroy();
        var ctx2 = document.getElementById('chartNominaCanvas');
        if (ctx2) {
            chartNominaInstance = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Docente', 'Administrativo', 'Obrero', 'Otros'],
                    datasets: [{
                        label: 'Trabajadores',
                        data: [docente, admin, obrero, otros],
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#f59e0b', '#94a3b8'],
                        borderRadius: 6
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
            });
        }
    } catch(e) {}
}

(function() {
    cargarStats();
    cargarCharts();

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.target.id === 'reportes' && m.target.classList.contains('active')) {
                cargarStats();
                cargarCharts();
            }
        });
    });
    var seccion = document.getElementById('reportes');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    if (seccion?.classList.contains('active')) { cargarStats(); cargarCharts(); }
})();
</script>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views\dashboard\secciones\reportes.blade.php ENDPATH**/ ?>