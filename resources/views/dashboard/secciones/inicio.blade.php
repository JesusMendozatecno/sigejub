<style>
    /* Limita la altura del listado de actividad para que quede alineado con Datos Institucionales */
    .recent-activity .activity-list { max-height: 400px; overflow-y: auto; }
</style>
<header class="welcome-header">
    <div class="welcome-text">
        <p class="subtitle"></p>
        <h1>Panel de Gestión Integral</h1>
    </div>
    <button class="btn-primary" onclick="switchTab('solicitudes')" type="button">
        <i class="fas fa-circle-plus"></i> Nueva Solicitud
    </button>
</header>

<section class="stats-grid">
    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap blue"><i class="fas fa-users"></i></div>
        </div>
        <p>TOTAL TRABAJADORES</p>
        <h2 id="inicioTotalTrabajadores">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap orange"><i class="fas fa-clock"></i></div>
        </div>
        <p>PENDIENTES</p>
        <h2 id="inicioPendientes" class="text-orange">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap green"><i class="fas fa-circle-check"></i></div>
        </div>
        <p>APROBADAS</p>
        <h2 id="inicioAprobadas" class="text-green">—</h2>
    </div>

    <div class="stat-card">
        <div class="card-head">
            <div class="icon-wrap red"><i class="fas fa-circle-xmark"></i></div>
        </div>
        <p>RECHAZADAS</p>
        <h2 id="inicioRechazadas" class="text-red">—</h2>
    </div>
</section>

<div class="content-layout" style="align-items: start; grid-template-columns: 1.3fr 1fr;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <section class="chart-container">
            <div class="chart-header">
                <div>
                    <h3>Solicitudes por Mes</h3>
                    <p class="chart-subtitle">Volumen de tramitación - {{ date('Y') }}</p>
                </div>
            </div>
            <div class="bar-chart" id="barChartSolicitudes">
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>ENE</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>FEB</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>MAR</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>ABR</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>MAY</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>JUN</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>JUL</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>AGO</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>SEP</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>OCT</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>NOV</span></div>
                <div class="bar-group"><div class="bar" style="height: 10%;"></div><span>DIC</span></div>
            </div>
        </section>

        <div class="deadlines-card">
            <h3>Próximos Vencimientos</h3>
            <div id="deadlineItems">
                <div class="deadline-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">
                    Cargando...
                </div>
            </div>
        </div>

        <div class="info-banner-dark" id="datoInstitucional">
            <div class="info-head">
                <i class="fas fa-lightbulb"></i>
                <h4>Dato Institucional</h4>
            </div>
            <p id="datoInstitucionalTexto">Cargando estadísticas...</p>
        </div>
    </div>

    <section class="recent-activity" style="height: 100%;">
        <h3 class="section-title"><i class="fas fa-clock-rotate-left"></i> Actividad Reciente</h3>
        <div class="activity-list" id="actividadRecienteLista">
            <div class="activity-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">
                Cargando...
            </div>
        </div>
    </section>
</div>

<script src="{{ asset('js/secciones/inicio.js') }}"></script>