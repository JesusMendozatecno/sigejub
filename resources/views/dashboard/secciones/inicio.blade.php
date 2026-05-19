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
                <i data-lucide="lightbulb"></i>
                <h4>Dato Institucional</h4>
            </div>
            <p id="datoInstitucionalTexto">Cargando estadísticas...</p>
        </div>
    </div>

    <section class="recent-activity" style="height: 100%;">
        <h3 class="section-title"><i data-lucide="history"></i> Actividad Reciente</h3>
        <div class="activity-list" id="actividadRecienteLista">
            <div class="activity-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">
                Cargando...
            </div>
        </div>
    </section>
</div>

<script>
(function() {
    async function cargarEstadisticasInicio() {
        try {
            const [respTrab, respPend, respAprob, respRech, respMes, respVenc] = await Promise.all([
                fetch('/trabajadores?per_page=1'),
                fetch('/solicitudes?estado=pending&per_page=1'),
                fetch('/solicitudes?estado=approved&per_page=1'),
                fetch('/solicitudes?estado=rejected&per_page=1'),
                fetch('/solicitudes/por-mes'),
                fetch('/solicitudes/vencimientos'),
            ]);

            const dataTrab = await respTrab.json();
            const dataPend = await respPend.json();
            const dataAprob = await respAprob.json();
            const dataRech = await respRech.json();
            const dataMes = await respMes.json();
            const dataVenc = await respVenc.json();

            const el = id => document.getElementById(id);
            if (el('inicioTotalTrabajadores')) el('inicioTotalTrabajadores').textContent = dataTrab.total || 0;
            if (el('inicioPendientes')) el('inicioPendientes').textContent = dataPend.total || 0;
            if (el('inicioAprobadas')) el('inicioAprobadas').textContent = dataAprob.total || 0;
            if (el('inicioRechazadas')) el('inicioRechazadas').textContent = dataRech.total || 0;

            const nombresMeses = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
            const barras = document.querySelectorAll('#barChartSolicitudes .bar-group .bar');
            if (barras.length && Array.isArray(dataMes)) {
                const max = Math.max(...dataMes, 1);
                dataMes.forEach((val, i) => {
                    if (barras[i]) {
                        const pct = Math.max((val / max) * 90, 5);
                        barras[i].style.height = pct + '%';
                        if (val > 0) {
                            barras[i].innerHTML = `<span class="val">${val}</span>`;
                        } else {
                            barras[i].innerHTML = '';
                        }
                    }
                });
            }

            const container = document.getElementById('deadlineItems');
            if (container && dataVenc) {
                let html = '';
                const urgentes = dataVenc.proximos || [];
                const pendientes = dataVenc.pendientes || [];

                if (urgentes.length === 0 && pendientes.length === 0) {
                    html = '<div class="deadline-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">Sin vencimientos próximos</div>';
                } else {
                    urgentes.forEach(t => {
                        const nombre = [t.nombres, t.apellidos].filter(Boolean).join(' ');
                        const edadRestante = Math.max(60 - (t.edad || 0), 0);
                        html += `
                            <div class="deadline-item urgent">
                                <span class="tag">Urgente</span>
                                <p>${nombre}</p>
                                <span>${edadRestante > 0 ? edadRestante + ' años para edad de jubilación' : 'Edad de jubilación cumplida'} · ${t.total_anos_servicio || 0} años servicio</span>
                            </div>
                        `;
                    });
                    pendientes.forEach(s => {
                        const nombre = s.trabajador ? [s.trabajador.nombres, s.trabajador.apellidos].filter(Boolean).join(' ') : '—';
                        const dias = Math.ceil((new Date() - new Date(s.created_at)) / (1000 * 60 * 60 * 24));
                        html += `
                            <div class="deadline-item warning">
                                <span class="tag">${dias > 7 ? 'Pendiente' : 'Mañana'}</span>
                                <p>Solicitud de ${nombre}</p>
                                <span>${dias} días en espera · #SOL-${String(s.id).padStart(4, '0')}</span>
                            </div>
                        `;
                    });
                }
                container.innerHTML = html;
            }

            const datoTexto = document.getElementById('datoInstitucionalTexto');
            if (datoTexto && dataVenc) {
                const total = dataVenc.total_solicitudes || 0;
                const tasa = dataVenc.tasa_aprobacion || 0;
                if (total === 0) {
                    datoTexto.textContent = 'Aún no hay solicitudes registradas en el sistema.';
                } else {
                    datoTexto.textContent = `Se han procesado ${total} solicitudes de jubilación, con una tasa de aprobación del ${tasa}%.`;
                }
            }

            const listaActividad = document.getElementById('actividadRecienteLista');
            if (listaActividad && dataVenc && dataVenc.recientes) {
                const items = dataVenc.recientes;
                if (items.length === 0) {
                    listaActividad.innerHTML = '<div class="activity-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">Sin actividad reciente</div>';
                } else {
                    listaActividad.innerHTML = items.map(s => {
                        const nombre = s.trabajador ? [s.trabajador.nombres, s.trabajador.apellidos].filter(Boolean).join(' ') : '—';
                        const badge = s.estado === 'aprobado' ? 'green' : (s.estado === 'rechazado' ? 'red' : 'blue');
                        const icono = s.estado === 'aprobado' ? 'check-circle' : (s.estado === 'rechazado' ? 'x-circle' : 'file-up');
                        const hace = Math.ceil((new Date() - new Date(s.created_at)) / (1000 * 60));
                        const tiempo = hace < 60 ? `Hace ${hace} min` : `Hace ${Math.floor(hace / 60)} horas`;
                        return `
                            <div class="activity-item">
                                <div class="activity-icon ${badge}"><i data-lucide="${icono}"></i></div>
                                <div class="activity-text">
                                    <p>Solicitud de ${nombre}</p>
                                    <span>#SOL-${String(s.id).padStart(4, '0')} · ${tiempo}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
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