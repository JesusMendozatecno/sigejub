{{-- reportes.blade.php - Centro de reportes y estadísticas.
     Todos los números y gráficos se alimentan de /estadisticas/dashboard,
     que los calcula directamente de la base de datos. --}}
<style>
.rep-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 20px; }
.rep-kpi { position: relative; overflow: hidden; background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.rep-kpi::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 4px; background: linear-gradient(90deg, #2563eb, #38bdf8); opacity: .9; }
.rep-kpi.green::after { background: linear-gradient(90deg, #16a34a, #4ade80); }
.rep-kpi.violet::after { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.rep-kpi.amber::after { background: linear-gradient(90deg, #d97706, #fbbf24); }
.rep-kpi .kpi-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.05rem; background: linear-gradient(135deg, #2563eb, #3b82f6); }
.rep-kpi.green .kpi-icon { background: linear-gradient(135deg, #16a34a, #22c55e); }
.rep-kpi.violet .kpi-icon { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
.rep-kpi.amber .kpi-icon { background: linear-gradient(135deg, #d97706, #f59e0b); }
.rep-kpi .kpi-label { font-size: 0.68rem; font-weight: 800; letter-spacing: .6px; color: #64748b; margin-top: 6px; }
.rep-kpi .kpi-value { font-size: 1.9rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
.rep-kpi .kpi-detail { font-size: 0.72rem; color: #94a3b8; font-weight: 600; }
.rep-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; }
.rep-card-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; }
.rep-card-header h3 { margin: 0; font-size: 0.92rem; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.rep-card-header h3 i { color: #2563eb; }
.rep-canvas-box { position: relative; height: 280px; padding: 14px 16px 18px; }
.rep-toggle { display: inline-flex; background: #f1f5f9; border-radius: 8px; padding: 3px; gap: 3px; }
.rep-toggle button { border: none; background: transparent; padding: 6px 14px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all .18s; }
.rep-toggle button.active { background: white; color: #1e3a8a; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.rep-table td, .rep-table th { vertical-align: middle; }
.rep-empty { text-align: center; color: #94a3b8; font-size: 0.8rem; padding: 26px 10px; }
body.dark-mode .rep-kpi, body.dark-mode .rep-card { background: #1e293b; border-color: #334155; }
body.dark-mode .rep-kpi .kpi-value, body.dark-mode .rep-card-header h3 { color: #f1f5f9; }
body.dark-mode .rep-card-header { border-bottom-color: #334155; }
body.dark-mode .rep-toggle { background: #0f172a; }
body.dark-mode .rep-toggle button.active { background: #1e293b; color: #93c5fd; }
</style>

<header class="section-header-flex">
    <div class="header-info">
        <h1>Centro de <span class="text-blue-accent">Reportes y Estadísticas</span></h1>
        <p>Visualización y análisis de los datos reales del sistema institucional.</p>
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

<!-- Reporte R5: Próximos Jubilados -->
<div style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px 18px;display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
    <div style="font-weight:700;color:#0f172a;font-size:0.92rem;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-hourglass-half" style="color:#2563eb;"></i> Reporte: Próximos Jubilados
    </div>
    <label style="font-size:0.72rem;color:#64748b;font-weight:700;">DESDE
        <input type="date" id="proxJubDesde" value="{{ now()->format('Y-m-d') }}" style="display:block;margin-top:3px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.82rem;">
    </label>
    <label style="font-size:0.72rem;color:#64748b;font-weight:700;">HASTA
        <input type="date" id="proxJubHasta" value="{{ now()->addMonths(24)->format('Y-m-d') }}" style="display:block;margin-top:3px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.82rem;">
    </label>
    <button class="btn-export-pdf" type="button" onclick="abrirProximosJubilados()" style="margin-top:14px;">
        <i class="fas fa-print"></i>
        <span>Abrir Reporte</span>
    </button>
    <p style="font-size:0.72rem;color:#64748b;flex-basis:100%;margin:0;">Muestra a los trabajadores cuya fecha estimada de retiro (60 años de edad o 25 años de servicio, el primer requisito que se cumpla) cae dentro del período consultado.</p>
</div>
<script>
function abrirProximosJubilados() {
    const desde = document.getElementById('proxJubDesde').value;
    const hasta = document.getElementById('proxJubHasta').value;
    if (!desde || !hasta) { mostrarToast('Seleccione el período del reporte.', 'warning'); return; }
    window.open('/reportes/proximos-jubilados?desde=' + encodeURIComponent(desde) + '&hasta=' + encodeURIComponent(hasta), '_blank');
}
</script>

<div class="rep-kpis">
    <div class="rep-kpi">
        <div class="kpi-icon"><i class="fas fa-users"></i></div>
        <span class="kpi-label">TRABAJADORES REGISTRADOS</span>
        <span class="kpi-value" id="repTrabajadores">0</span>
        <span class="kpi-detail" id="repTrabajadoresDetalle">—</span>
    </div>
    <div class="rep-kpi green">
        <div class="kpi-icon"><i class="fas fa-file-lines"></i></div>
        <span class="kpi-label">SOLICITUDES TOTALES</span>
        <span class="kpi-value" id="repSolicitudes">0</span>
        <span class="kpi-detail" id="repSolicitudesDetalle">—</span>
    </div>
    <div class="rep-kpi amber">
        <div class="kpi-icon"><i class="fas fa-folder-open"></i></div>
        <span class="kpi-label">EXPEDIENTES DIGITALES</span>
        <span class="kpi-value" id="repExpedientes">0</span>
        <span class="kpi-detail" id="repExpedientesDetalle">—</span>
    </div>
    <div class="rep-kpi violet">
        <div class="kpi-icon"><i class="fas fa-user-gear"></i></div>
        <span class="kpi-label">USUARIOS ACTIVOS</span>
        <span class="kpi-value" id="repUsuarios">0</span>
        <span class="kpi-detail">Analistas y administradores</span>
    </div>
</div>

<div class="charts-grid-2" style="margin-top: 20px;">
    <div class="rep-card">
        <div class="rep-card-header">
            <h3><i class="fas fa-chart-column"></i> Trabajadores por Tipo de Nómina</h3>
        </div>
        <div class="rep-canvas-box"><canvas id="chartNominaCanvas"></canvas></div>
    </div>
    <div class="rep-card">
        <div class="rep-card-header">
            <h3><i class="fas fa-chart-pie"></i> Solicitudes por Estatus</h3>
        </div>
        <div class="rep-canvas-box"><canvas id="chartSolicitudesCanvas"></canvas></div>
    </div>
</div>

<div class="rep-card" style="margin-top: 20px;">
    <div class="rep-card-header">
        <h3><i class="fas fa-chart-line"></i> Solicitudes por Mes</h3>
        <div class="rep-toggle" id="toggleSolicitudes">
            <button type="button" class="active" data-vista="general">General</button>
            <button type="button" data-vista="especifico">Por tipo de jubilación</button>
        </div>
    </div>
    <div class="rep-canvas-box" style="height:320px;"><canvas id="chartSolicitudesMesCanvas"></canvas></div>
</div>

<div class="table-card-full" style="margin-top: 20px;">
    <div class="table-header">
        <h3>Resumen General del Sistema</h3>
    </div>
    <table class="report-table rep-table" id="tablaResumen">
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
var chartSolicitudesMesInstance = null;
var statsReportes = null;
var vistaSolicitudesMes = 'general';

var MESES_REPORTE = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
var COLORES_TIPOS = ['#2563eb', '#f59e0b', '#16a34a', '#7c3aed', '#dc2626', '#0891b2'];

function montoBsRep(v) {
    var n = parseFloat(v) || 0;
    return 'Bs. ' + n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function cargarStats() {
    var cont = document.getElementById('tbodyResumen');
    try {
        var resp = await fetch('/estadisticas/dashboard');
        var d = await resp.json();
        statsReportes = d;

        var trab = d.trabajadores || {};
        var sol = d.solicitudes || {};
        var exp = d.expedientes || {};
        var nom = d.nomina || {};
        var pres = d.prestaciones || {};

        document.getElementById('repTrabajadores').textContent = trab.total || 0;
        document.getElementById('repTrabajadoresDetalle').textContent =
            (trab.activos || 0) + ' activos · ' + (trab.jubilados || 0) + ' jubilados';
        document.getElementById('repSolicitudes').textContent = sol.total || 0;
        document.getElementById('repSolicitudesDetalle').textContent =
            (sol.aprobado || 0) + ' aprobadas · ' + (sol.pendiente || 0) + ' pendientes';
        document.getElementById('repExpedientes').textContent = exp.total || 0;
        document.getElementById('repExpedientesDetalle').textContent =
            (exp.porcentaje_completos || 0) + '% completos · ' + (exp.sin_solicitud || 0) + ' sin solicitud';
        document.getElementById('repUsuarios').textContent = d.usuarios_activos || 0;

        var filas = [
            ['Trabajadores registrados', trab.total || 0, 'Total en el sistema (activos + jubilados)'],
            ['Trabajadores activos', trab.activos || 0, 'Personal vigente en nómina'],
            ['Trabajadores jubilados', trab.jubilados || 0, 'Con proceso de jubilación concluido'],
            ['Solicitudes totales', sol.total || 0, 'Incluye todos los estatus'],
            ['Solicitudes aprobadas', sol.aprobado || 0, 'Aprobadas por el consejo'],
            ['Solicitudes pendientes', (sol.pendiente || 0) + (sol.revision || 0), 'En revisión o esperando aprobación'],
            ['Tasa de aprobación', (sol.tasa_aprobacion || 0) + '%', 'Aprobadas sobre solicitudes resueltas'],
            ['Expedientes digitales', exp.total || 0, (exp.completos || 0) + ' completos · ' + (exp.sin_solicitud || 0) + ' sin solicitud'],
            ['Última nómina', nom.periodo ? (nom.mes_nombre || '') + ' ' + nom.periodo : '—', (nom.trabajadores || 0) + ' trabajadores · ' + montoBsRep(nom.total_general)],
            ['Prestaciones calculadas', pres.calculadas || 0, 'Monto total: ' + montoBsRep(pres.monto_total)],
            ['Usuarios activos', d.usuarios_activos || 0, 'Analistas y administradores'],
        ];
        if (cont) {
            cont.innerHTML = filas.map(function(f) {
                return '<tr><td><strong>' + f[0] + '</strong></td><td>' + f[1] + '</td>' +
                    '<td style="color:#64748b;">' + f[2] + '</td></tr>';
            }).join('');
        }

        pintarGraficos(d);
    } catch (e) {
        console.error('Error al cargar estadísticas:', e);
        if (cont) cont.innerHTML = '<tr><td colspan="3" class="rep-empty">No se pudieron cargar las estadísticas.</td></tr>';
    }
}

function pintarGraficos(d) {
    if (typeof Chart === 'undefined') return;
    var trab = d.trabajadores || {};
    var sol = d.solicitudes || {};

    // Trabajadores por tipo de nómina (dato real: trabajadores.tipo_nomina)
    var porTipo = trab.por_tipo_nomina || {};
    if (chartNominaInstance) chartNominaInstance.destroy();
    var ctxNom = document.getElementById('chartNominaCanvas');
    if (ctxNom) {
        chartNominaInstance = new Chart(ctxNom, {
            type: 'bar',
            data: {
                labels: ['Administrativo', 'Docente', 'Obrero', 'Sin tipo'],
                datasets: [{
                    label: 'Trabajadores',
                    data: [porTipo.ADM || 0, porTipo.DOC || 0, porTipo.OBREROS || 0, porTipo['SIN TIPO'] || 0],
                    backgroundColor: ['#2563eb', '#0ea5e9', '#f59e0b', '#94a3b8'],
                    borderRadius: 8,
                    maxBarThickness: 64
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Solicitudes por estatus
    if (chartSolicitudesInstance) chartSolicitudesInstance.destroy();
    var ctxSol = document.getElementById('chartSolicitudesCanvas');
    if (ctxSol) {
        chartSolicitudesInstance = new Chart(ctxSol, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                datasets: [{
                    data: [sol.pendiente || 0, sol.aprobado || 0, sol.rechazado || 0],
                    backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } } }
            }
        });
    }

    pintarSolicitudesMes();
}

// Gráfico de solicitudes por mes con dos lecturas:
//  - General: total de solicitudes de cada mes.
//  - Específico: una serie por tipo de jubilación.
function pintarSolicitudesMes() {
    if (typeof Chart === 'undefined' || !statsReportes) return;
    var sol = statsReportes.solicitudes || {};
    var porMes = sol.por_mes || [];
    var porTipoMes = sol.por_tipo_mes || {};

    var datasets = [];
    if (vistaSolicitudesMes === 'general') {
        datasets = [{
            label: 'Solicitudes',
            data: porMes,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.14)',
            fill: true,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#2563eb'
        }];
    } else {
        var tipos = Object.keys(porTipoMes);
        if (!tipos.length) {
            datasets = [{ label: 'Sin tipos registrados', data: porMes, borderColor: '#94a3b8', backgroundColor: 'rgba(148,163,184,0.12)', fill: false, tension: 0.35 }];
        } else {
            datasets = tipos.map(function(tipo, i) {
                return {
                    label: tipo,
                    data: porTipoMes[tipo] || [],
                    borderColor: COLORES_TIPOS[i % COLORES_TIPOS.length],
                    backgroundColor: COLORES_TIPOS[i % COLORES_TIPOS.length],
                    fill: false,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3
                };
            });
        }
    }

    if (chartSolicitudesMesInstance) chartSolicitudesMesInstance.destroy();
    var ctx = document.getElementById('chartSolicitudesMesCanvas');
    if (!ctx) return;
    chartSolicitudesMesInstance = new Chart(ctx, {
        type: 'line',
        data: { labels: MESES_REPORTE, datasets: datasets },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } } }
        }
    });
}

document.getElementById('toggleSolicitudes')?.addEventListener('click', function(e) {
    var btn = e.target.closest('button[data-vista]');
    if (!btn) return;
    vistaSolicitudesMes = btn.dataset.vista;
    this.querySelectorAll('button').forEach(function(b) { b.classList.toggle('active', b === btn); });
    pintarSolicitudesMes();
});

(function() {
    cargarStats();

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.target.id === 'reportes' && m.target.classList.contains('active')) {
                cargarStats();
            }
        });
    });
    var seccion = document.getElementById('reportes');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    if (seccion && seccion.classList.contains('active')) cargarStats();
})();
</script>
