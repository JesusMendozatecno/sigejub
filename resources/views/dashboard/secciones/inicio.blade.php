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

<script>
(function() {
    const ICON_MAP = {
        created: { 'trabajador': ['fa-users', 'green'], 'solicitud': ['fa-file-lines', 'blue'], 'usuario': ['fa-user-plus', 'purple'] },
        updated: { 'trabajador': ['fa-pen', 'orange'], 'solicitud': ['fa-pen-to-square', 'orange'] },
        deleted: { 'trabajador': ['fa-trash-can', 'red'], 'solicitud': ['fa-circle-xmark', 'red'] },
    };

    function getActivityIcon(action, subjectType) {
        const fallback = ['fa-circle', 'blue'];
        return (ICON_MAP[action] && ICON_MAP[action][subjectType]) || fallback;
    }

    function timeAgo(dateStr) {
        const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
        if (seconds < 60) return 'Ahora';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `Hace ${minutes} min`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `Hace ${hours} h`;
        const days = Math.floor(hours / 24);
        return `Hace ${days} día${days > 1 ? 's' : ''}`;
    }

    async function cargarActividades() {
        const container = document.getElementById('actividadRecienteLista');
        if (!container) return;
        try {
            const resp = await fetch('/actividades');
            const actividades = await resp.json();
            container.innerHTML = '';
            if (!actividades.length) {
                container.innerHTML = '<div class="activity-item"><div class="activity-text" style="text-align:center;color:#94a3b8;padding:20px;"><p>Sin actividades recientes</p></div></div>';
                return;
            }
            actividades.forEach(a => {
                const [icon, color] = getActivityIcon(a.action, a.subject_type);
                const user = a.user ? a.user.name : 'Sistema';
                const div = document.createElement('div');
                div.className = 'activity-item';
                div.innerHTML = `
                    <div class="activity-icon ${color}"><i class="fas ${icon}"></i></div>
                    <div class="activity-text">
                        <p>${a.description}</p>
                        <span>${user} • ${timeAgo(a.created_at)}</span>
                    </div>
                `;
                container.appendChild(div);
            });
        } catch (err) {
            console.error('Error al cargar actividades:', err);
        }
    }

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

        } catch (err) {
            console.error('Error al cargar estadísticas de inicio:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarEstadisticasInicio();
        cargarActividades();
    });

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