<header class="welcome-header">
    <div class="welcome-text">
        <p class="subtitle"></p>
        <h1>Panel de Gestión Integral</h1>
    </div>
    <button class="btn-primary" onclick="switchTab('solicitudes')" type="button">
        <i data-lucide="plus-circle"></i> Nueva Solicitud
    </button>
</header>

<section class="stats-grid">
    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap blue"><i data-lucide="users"></i></div>
        </div>
        <p>TOTAL TRABAJADORES</p>
        <h2 id="inicioTotalTrabajadores">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap orange"><i data-lucide="clock"></i></div>
        </div>
        <p>PENDIENTES</p>
        <h2 id="inicioPendientes" class="text-orange">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap green"><i data-lucide="check-circle"></i></div>
        </div>
        <p>APROBADAS</p>
        <h2 id="inicioAprobadas" class="text-green">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap red"><i data-lucide="x-circle"></i></div>
        </div>
        <p>RECHAZADAS</p>
        <h2 id="inicioRechazadas" class="text-red">—</h2>
    </div>
</section>

<div class="content-layout">
    <section class="chart-container">
        <div class="chart-header">
            <div>
                <h3>Solicitudes por Mes</h3>
                <p class="chart-subtitle">Volumen de tramitación - Año Actual</p>
            </div>
            <div class="chart-toggle">
                <button class="active" type="button">Mensual</button>
                <button type="button">Anual</button>
            </div>
        </div>
        <div class="bar-chart">
            <div class="bar-group"><div class="bar" style="height: 40%;"></div><span>ENE</span></div>
            <div class="bar-group"><div class="bar" style="height: 60%;"></div><span>FEB</span></div>
            <div class="bar-group active"><div class="bar" style="height: 90%;"><span class="val">32</span></div><span>MAR</span></div>
            <div class="bar-group"><div class="bar" style="height: 70%;"></div><span>ABR</span></div>
            <div class="bar-group"><div class="bar" style="height: 80%;"></div><span>MAY</span></div>
            <div class="bar-group"><div class="bar" style="height: 50%;"></div><span>JUN</span></div>
        </div>
    </section>

    <section class="quick-access-dark">
        <h3>Accesos Rápidos</h3>
        <p>Gestione los procesos fundamentales de forma directa.</p>
        <div class="access-grid">
            <div class="access-box" onclick="switchTab('trabajadores')">
                <i data-lucide="users"></i>
                <p>TRABAJADORES</p>
            </div>
            <div class="access-box" onclick="switchTab('prestaciones')">
                <i data-lucide="wallet"></i>
                <p>PRESTACIONES</p>
            </div>
            <div class="access-box" onclick="switchTab('reportes')">
                <i data-lucide="bar-chart-3"></i>
                <p>REPORTES</p>
            </div>
            <div class="access-box">
                <i data-lucide="help-circle"></i>
                <p>GUÍAS</p>
            </div>
        </div>
    </section>
</div>

<div class="content-layout">
    <section class="recent-activity">
        <h3 class="section-title"><i data-lucide="history"></i> Actividad Reciente</h3>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon blue"><i data-lucide="file-up"></i></div>
                <div class="activity-text">
                    <p>Juan Pérez subió su cédula</p>
                    <span>Expediente #9923 • Hace 15 min</span>
                </div>
                <a href="#">Ver</a>
            </div>
            <div class="activity-item">
                <div class="activity-icon green"><i data-lucide="check-circle"></i></div>
                <div class="activity-text">
                    <p>Solicitud #452 aprobada</p>
                    <span>Admin. Maria • Hace 2 horas</span>
                </div>
                <a href="#">Detalle</a>
            </div>
        </div>
    </section>

    <section class="deadlines-container">
        <div class="deadlines-card">
            <h3>Próximos Vencimientos</h3>
            <div class="deadline-item urgent">
                <span class="tag">Urgente</span>
                <p>Revisión de Cálculo: Ana Belén</p>
                <span>Vence hoy, 16:00h</span>
            </div>
            <div class="deadline-item warning">
                <span class="tag">Mañana</span>
                <p>Firma de Dictamen #882</p>
                <span>Pendiente de firma digital</span>
            </div>
        </div>
        <div class="info-banner-dark">
            <div class="info-head">
                <i data-lucide="lightbulb"></i>
                <h4>Dato Institucional</h4>
            </div>
            <p>Este mes se han procesado un 15% más de jubilaciones que el promedio anterior.</p>
        </div>
    </section>
</div>

<script>
(function() {
    async function cargarEstadisticasInicio() {
        try {
            const [respTrab, respPend, respAprob, respRech] = await Promise.all([
                fetch('/trabajadores?per_page=1'),
                fetch('/solicitudes?estado=pending&per_page=1'),
                fetch('/solicitudes?estado=approved&per_page=1'),
                fetch('/solicitudes?estado=rejected&per_page=1'),
            ]);

            const dataTrab = await respTrab.json();
            const dataPend = await respPend.json();
            const dataAprob = await respAprob.json();
            const dataRech = await respRech.json();

            const el = id => document.getElementById(id);
            if (el('inicioTotalTrabajadores')) el('inicioTotalTrabajadores').textContent = dataTrab.total || 0;
            if (el('inicioPendientes')) el('inicioPendientes').textContent = dataPend.total || 0;
            if (el('inicioAprobadas')) el('inicioAprobadas').textContent = dataAprob.total || 0;
            if (el('inicioRechazadas')) el('inicioRechazadas').textContent = dataRech.total || 0;
        } catch (err) {
            console.error('Error al cargar estadísticas de inicio:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', cargarEstadisticasInicio);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'inicio' && m.target.classList.contains('active')) {
                cargarEstadisticasInicio();
            }
        });
    });
    const seccion = document.getElementById('inicio');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
})();
</script>