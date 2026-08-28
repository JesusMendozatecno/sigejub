{{-- inicio.blade.php - Página de inicio del dashboard: tarjetas de estadísticas, gráfico de solicitudes por mes, vencimientos próximos y feed de actividad reciente. --}}
<style>
    .tasa-dolar-card{display:flex;align-items:center;gap:20px;background:linear-gradient(135deg,#1a365d,#1e3a8a);border-radius:16px;padding:24px;color:#fff;flex-wrap:wrap;}
    .tasa-dolar-icon{font-size:2.2rem;opacity:0.75;}
    .tasa-dolar-info{flex:1;min-width:220px;}
    .tasa-dolar-label{font-size:0.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;opacity:0.7;margin:0 0 4px;}
    .tasa-dolar-info h2{font-size:2rem;font-weight:800;margin:0;line-height:1;}
    .tasa-dolar-meta{font-size:0.72rem;opacity:0.65;margin-top:6px;}
    .tasa-dolar-status{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:20px;font-size:0.75rem;font-weight:700;}
    body.dark-mode .tasa-dolar-card{background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid #334155;}
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

<div id="tasaDolarSection" style="display:none; margin-top:16px;">
    <div class="tasa-dolar-card">
        <div class="tasa-dolar-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="tasa-dolar-info">
            <p class="tasa-dolar-label">TASA DEL DÓLAR</p>
            <h2 id="tasaDolarValor">—</h2>
            <p class="tasa-dolar-meta" id="tasaDolarMeta"></p>
        </div>
        <div class="tasa-dolar-status" id="tasaDolarBadge">
            <span id="tasaDolarDot" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#94a3b8;"></span>
            <span id="tasaDolarEstado">—</span>
        </div>
    </div>
</div>

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
            const result = await cachedFetch('/actividades', { ttl: 60000 });
            const actividades = result.data;
            container.innerHTML = '';
            if (!actividades.length) {
                container.innerHTML = '<div class="activity-item"><div class="activity-text" style="text-align:center;color:#94a3b8;padding:20px;"><p>Sin actividades recientes</p></div></div>';
                return;
            }
            actividades.forEach(a => {
                const [icon, color] = getActivityIcon(a.accion, a.tipo_entidad);
                const user = a.user ? escaparHTML(a.user.nombre) : 'Sistema';
                const div = document.createElement('div');
                div.className = 'activity-item';
                div.innerHTML = `
                    <div class="activity-icon ${color}"><i class="fas ${icon}"></i></div>
                    <div class="activity-text">
                        <p>${escaparHTML(a.descripcion)}</p>
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
        const ttlStats = 120000;
        const statsKey = 'sigejub_cache_stats_inicio';
        var cached = localStorage.getItem(statsKey);
        var skipRender = false;
        if (cached) {
            try {
                var p = JSON.parse(cached);
                if (Date.now() - p.ts < ttlStats) {
                    renderEstadisticasInicio(p.data);
                    skipRender = true;
                }
            } catch (e) {}
        }

        try {
            const [cTrab, cStats, cMes, cVenc] = await Promise.all([
                cachedFetch('/trabajadores?per_page=1'),
                cachedFetch('/solicitudes/estadisticas'),
                cachedFetch('/solicitudes/por-mes'),
                cachedFetch('/solicitudes/vencimientos'),
            ]);

            const stats = cStats.data;
            const data = {
                totalTrab: cTrab.data.total || 0,
                totalPend: stats.pendiente || 0,
                totalAprob: stats.aprobado || 0,
                totalRech: stats.rechazado || 0,
                porMes: cMes.data,
                vencimientos: cVenc.data,
            };

            localStorage.setItem(statsKey, JSON.stringify({ ts: Date.now(), data: data }));

            if (!skipRender) renderEstadisticasInicio(data);
        } catch (err) {
            console.error('Error al cargar estadísticas:', err);
        }
    }

    function renderEstadisticasInicio(data) {
        const el = id => document.getElementById(id);
        if (el('inicioTotalTrabajadores')) el('inicioTotalTrabajadores').textContent = data.totalTrab;
        if (el('inicioPendientes')) el('inicioPendientes').textContent = data.totalPend;
        if (el('inicioAprobadas')) el('inicioAprobadas').textContent = data.totalAprob;
        if (el('inicioRechazadas')) el('inicioRechazadas').textContent = data.totalRech;

        const nombresMeses = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
        const barras = document.querySelectorAll('#barChartSolicitudes .bar-group .bar');
        if (barras.length && Array.isArray(data.porMes)) {
            const max = Math.max(...data.porMes, 1);
            data.porMes.forEach((val, i) => {
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
        if (container && data.vencimientos) {
            let html = '';
            const venc = data.vencimientos;
            const urgentes = venc.proximos || [];
            const pendientes = venc.pendientes || [];

            if (urgentes.length === 0 && pendientes.length === 0) {
                html = '<div class="deadline-item" style="padding: 16px; color: #94a3b8; text-align: center; font-size: 0.85rem;">Sin vencimientos próximos</div>';
            } else {
                urgentes.forEach(t => {
                    const nombre = [t.nombres, t.apellidos].filter(Boolean).join(' ');
                    const edadRestante = Math.max(60 - (t.edad || 0), 0);
                    html += `
                        <div class="deadline-item urgent">
                            <span class="tag">Urgente</span>
                            <p>${escaparHTML(nombre)}</p>
                            <span>${edadRestante > 0 ? edadRestante + ' años para edad de jubilación' : 'Edad de jubilación cumplida'} · ${escaparHTML(t.total_anos_servicio) || 0} años servicio</span>
                        </div>
                    `;
                });
                pendientes.forEach(s => {
                    const nombre = s.trabajador ? [s.trabajador.nombres, s.trabajador.apellidos].filter(Boolean).join(' ') : '—';
                    const dias = Math.ceil((new Date() - new Date(s.created_at)) / (1000 * 60 * 60 * 24));
                    html += `
                        <div class="deadline-item warning">
                            <span class="tag">${dias > 7 ? 'Pendiente' : 'Mañana'}</span>
                            <p>Solicitud de ${escaparHTML(nombre)}</p>
                            <span>${dias} días en espera · #SOL-${String(s.id).padStart(4, '0')}</span>
                        </div>
                    `;
                });
            }
            container.innerHTML = html;
        }

        const datoTexto = document.getElementById('datoInstitucionalTexto');
        if (datoTexto && data.vencimientos) {
            const venc = data.vencimientos;
            const total = venc.total_solicitudes || 0;
            const tasa = venc.tasa_aprobacion || 0;
            if (total === 0) {
                datoTexto.textContent = 'Aún no hay solicitudes registradas en el sistema.';
            } else {
                datoTexto.textContent = `Se han procesado ${total} solicitudes de jubilación, con una tasa de aprobación del ${tasa}%.`;
            }
        }
    }

    async function cargarTasaDolar() {
        const section = document.getElementById('tasaDolarSection');
        if (!section) return;
        try {
            const resp = await fetch('/tasas-cambio/estado');
            const data = await resp.json();

            if (!data.disponible || !data.tasa) {
                section.style.display = 'none';
                return;
            }

            section.style.display = 'block';
            const t = data.tasa;

            const valorEl = document.getElementById('tasaDolarValor');
            if (valorEl) {
                valorEl.textContent = 'Bs. ' + parseFloat(t.valor).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            const metaEl = document.getElementById('tasaDolarMeta');
            if (metaEl) metaEl.textContent = (t.fuente || '') + ' · ' + (t.fecha || '');

            const dot = document.getElementById('tasaDolarDot');
            const badge = document.getElementById('tasaDolarBadge');
            const estado = document.getElementById('tasaDolarEstado');
            if (!dot || !badge || !estado) return;

            switch (data.estado) {
                case 'actualizada':
                    dot.style.background = '#4ade80';
                    badge.style.background = 'rgba(74,222,128,0.2)';
                    badge.style.color = '#166534';
                    estado.textContent = 'Actualizada';
                    break;
                case 'disponible':
                    dot.style.background = '#facc15';
                    badge.style.background = 'rgba(250,204,21,0.2)';
                    badge.style.color = '#92400e';
                    estado.textContent = 'Última disponible';
                    break;
                case 'desactualizada':
                    dot.style.background = '#f87171';
                    badge.style.background = 'rgba(248,113,113,0.2)';
                    badge.style.color = '#991b1b';
                    estado.textContent = 'Desactualizada';
                    break;
                default:
                    dot.style.background = '#94a3b8';
                    badge.style.background = 'rgba(148,163,184,0.2)';
                    badge.style.color = '#475569';
                    estado.textContent = data.etiqueta || '—';
            }
        } catch (err) {
            console.error('Error al cargar tasa del dólar:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarEstadisticasInicio();
        cargarActividades();
        cargarTasaDolar();
    });

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'inicio' && m.target.classList.contains('active')) {
                cargarEstadisticasInicio();
                cargarTasaDolar();
            }
        });
    });
    const seccion = document.getElementById('inicio');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
})();
</script>