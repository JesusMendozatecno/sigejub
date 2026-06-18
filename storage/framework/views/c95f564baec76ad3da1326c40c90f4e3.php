
<style>
.prestaciones-grid-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}
.prestacion-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.prestacion-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,35,102,0.1);
    border-color: #dbeafe;
}
.prestacion-card .pc-foto {
    height: 120px;
    background: linear-gradient(135deg, #f8fafc, #eef2f6);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}
.prestacion-card .pc-foto::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 30px;
    background: linear-gradient(transparent, white);
}
.prestacion-card .pc-foto img { width: 100%; height: 100%; object-fit: cover; }
.prestacion-card .pc-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
}
.prestacion-card .pc-info { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 4px; }
.prestacion-card .pc-info strong { font-size: 0.92rem; color: #0f172a; line-height: 1.3; }
.prestacion-card .pc-info .pc-cedula { font-size: 0.78rem; color: #64748b; }
.prestacion-card .pc-info .pc-row { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
.prestacion-card .pc-anios {
    font-size: 0.75rem; font-weight: 700; color: #1e3a8a;
    background: #eff6ff; padding: 3px 10px; border-radius: 10px;
}
.prestacion-card .pc-badge {
    font-size: 0.68rem; font-weight: 700; color: #1e40af;
    background: #dbeafe; padding: 3px 10px; border-radius: 10px;
}
@media (max-width: 767px) {
    .prestaciones-grid-cards { grid-template-columns: 1fr; }
}
body.dark-mode .prestacion-card { background: #1e293b; border-color: #334155; }
body.dark-mode .prestacion-card:hover { border-color: #3b82f6; }
body.dark-mode .prestacion-card .pc-foto { background: linear-gradient(135deg, #1a2332, #1e293b); }
body.dark-mode .prestacion-card .pc-foto::after { background: linear-gradient(transparent, #1e293b); }
body.dark-mode .prestacion-card .pc-avatar { background: #334155; color: #64748b; }
body.dark-mode .prestacion-card .pc-info strong { color: #f1f5f9; }
body.dark-mode .prestacion-card .pc-cedula { color: #94a3b8; }
body.dark-mode .prestacion-card .pc-anios { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .prestacion-card .pc-badge { background: #1e3a5f; color: #93c5fd; }

/* === DETALLE DE PRESTACIÓN (panel derecho) === */
.prestacion-detalle-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 20px; }
.detalle-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
.btn-volver { display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; color: #475569; font-size: 0.83rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-volver:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1a365d; }
.detalle-header-right strong { font-size: 1.1rem; color: #0f172a; }

.prestaciones-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; }
.calculation-main, .calculo-sidebar { background: white; border-radius: 14px; padding: 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

.worker-selector-card { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
.worker-avatar-box { width: 56px; height: 56px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1e40af; flex-shrink: 0; }
.worker-details h3 { font-size: 1.05rem; color: #0f172a; margin: 0 0 4px; }
.worker-details p { font-size: 0.82rem; color: #64748b; margin: 0; }
.badge-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.badge-row .badge { font-size: 0.65rem; padding: 3px 10px; border-radius: 10px; font-weight: 700; text-transform: uppercase; }
.badge-blue { background: #eff6ff; color: #1e40af; }
.badge-green { background: #ecfdf5; color: #059669; }
.badge-gray { background: #f1f5f9; color: #64748b; }

.calculo-form { display: flex; flex-direction: column; gap: 14px; }
.calculo-form .form-group { display: flex; flex-direction: column; gap: 4px; }
.calculo-form label { font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px; }
.calculo-form input { padding: 10px 13px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.88rem; background: #f8fafc; color: #0f172a; outline: none; transition: border 0.2s; }
.calculo-form input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.calculo-form input:disabled { background: #f1f5f9; color: #94a3b8; }
.calculo-form input[readonly] { background: #f1f5f9; color: #334155; font-weight: 600; }
.calculo-form .btn-calc { padding: 10px 20px; background: #1a365d; color: white; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.calculo-form .btn-calc:hover { background: #1e3a8a; box-shadow: 0 4px 12px rgba(26,54,93,0.25); }

/* === SIDEBAR DE CÁLCULO === */
.calculo-sidebar h3 { font-size: 0.95rem; color: #0f172a; margin: 0 0 12px; display: flex; align-items: center; gap: 6px; }
.calculo-sidebar h3 i { width: 18px; height: 18px; }
.result-item { padding: 10px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
.result-item:last-child { border-bottom: none; }
.result-item .label { font-size: 0.78rem; color: #64748b; }
.result-item .value { font-size: 0.88rem; font-weight: 700; color: #0f172a; }
.result-item .value.highlight { color: #1a365d; font-size: 1rem; }
.result-item .sub-etiqueta { font-size: 0.65rem; color: #94a3b8; display: block; font-weight: 400; }

/* === CHECKLIST === */
.calculo-checklist { margin-top: 16px; }
.checklist-item { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #475569; }
.checklist-item:last-child { border-bottom: none; }
.checklist-item i { width: 16px; height: 16px; flex-shrink: 0; }
.checklist-item .fa-circle-check { color: #22c55e; }
.checklist-item .fa-circle { color: #d1d5db; }

/* === HISTORIAL === */
.historial-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.historial-item:last-child { border-bottom: none; }
.historial-item .h-icon { width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0; }
.historial-item .h-text { font-size: 0.82rem; color: #334155; }
.historial-item .h-date { font-size: 0.7rem; color: #94a3b8; }
.hidden { display: none !important; }

@media (max-width: 1024px) {
    .prestaciones-grid { grid-template-columns: 1fr; }
    .prestacion-detalle-wrap { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
    .detalle-header { flex-direction: column; align-items: flex-start; }
}

body.dark-mode .calculation-main,
body.dark-mode .calculo-sidebar { background: #1e293b; border-color: #334155; }
body.dark-mode .detalle-header-right strong { color: #f1f5f9; }
body.dark-mode .worker-selector-card { border-bottom-color: #334155; }
body.dark-mode .worker-details h3 { color: #f1f5f9; }
body.dark-mode .worker-details p { color: #94a3b8; }
body.dark-mode .calculo-form input { background: #1a2332; border-color: #334155; color: #f1f5f9; }
body.dark-mode .calculo-form input:focus { background: #1e293b; border-color: #3b82f6; }
body.dark-mode .result-item { border-bottom-color: #334155; }
body.dark-mode .result-item .label { color: #94a3b8; }
body.dark-mode .result-item .value { color: #f1f5f9; }
body.dark-mode .checklist-item { border-bottom-color: #334155; color: #94a3b8; }
body.dark-mode .historial-item { border-bottom-color: #334155; }
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Cálculo de Prestaciones</h1>
        <p>Seleccione un trabajador para ver y calcular sus prestaciones sociales.</p>
    </div>
</header>

<div id="prestaciones-lista">
    <p style="color:#94a3b8;font-size:0.9rem;margin-bottom:16px;">Trabajadores con solicitud aprobada y expediente registrado</p>
    <div class="prestaciones-grid-cards" id="prestacionesGrid">
        <p class="empty-state">Cargando...</p>
    </div>
</div>

<div id="prestacion-detalle" class="hidden">
    <div class="detalle-header">
        <button class="btn-volver" onclick="volverAListaPrestaciones()">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a Trabajadores</span>
        </button>
        <div class="detalle-header-right">
            <strong id="detalleHeaderNombre" style="font-size:1.1rem;color:#0f172a;"></strong>
        </div>
    </div>

    <div class="prestaciones-grid">
        <div class="calculation-main">
            <div class="worker-selector-card" id="detalleWorkerCard">
                <div class="worker-avatar-box" id="detalleFoto">
                    <i class="fas fa-user"></i>
                </div>
                <div class="worker-details">
                    <h3 id="detalleNombre"></h3>
                    <p id="detalleInfo"></p>
                    <div class="badge-row" id="detalleBadges"></div>
                </div>
            </div>

            <div class="income-structure-card">
                <div class="card-title-row">
                    <div class="title-with-icon">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3>Estructura de Ingresos Mensuales</h3>
                    </div>
                    <span class="disclaimer">Valores en USD según tasa BCV</span>
                </div>

                <div class="inputs-grid-2x2">
                    <div class="calc-input-group">
                        <label>SUELDO BASE</label>
                        <div class="currency-input"><span>$</span><input type="text" id="inputSueldoBase" value="0.00"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA PROFESIONALIZACIÓN</label>
                        <div class="currency-input"><span>$</span><input type="text" id="inputPrimaProf" value="0.00"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA POR HIJOS</label>
                        <div class="currency-input"><span>$</span><input type="text" id="inputPrimaHijos" value="0.00"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA DE ANTIGÜEDAD</label>
                        <div class="currency-input"><span>$</span><input type="text" id="inputPrimaAntiguedad" value="0.00"></div>
                    </div>
                </div>

                <div class="jubilation-percent-area">
                    <div class="percent-info">
                        <strong>Porcentaje de Jubilación Aplicable</strong>
                        <p id="jubilacionTexto">Según años de servicio del trabajador.</p>
                    </div>
                    <div class="percent-toggle">
                        <button class="active" type="button" onclick="cambiarPorcentaje(this, 100)">100% <span>Jubilación</span></button>
                        <button type="button" onclick="cambiarPorcentaje(this, 82.5)">82.5% <span>Incapacidad</span></button>
                    </div>
                </div>

                <button class="btn-primary" id="btnCalcularPrestacion" style="margin-top:20px;width:100%;justify-content:center;" onclick="calcularPrestacion()">
                    <i class="fas fa-calculator"></i> Calcular Prestaciones
                </button>
            </div>

            <div class="bottom-metrics" id="resultadosCalculo" style="display:none;">
                <div class="metric-box-white border-blue">
                    <div class="metric-icon-small blue-bg"><i class="fas fa-calculator"></i></div>
                    <div class="metric-content">
                        <span class="tag-top">BASE MENSUAL</span>
                        <p>Salario Integral Estimado</p>
                        <h2 id="resultadoSalario">$ 0.00</h2>
                    </div>
                </div>
                <div class="metric-box-white border-green">
                    <div class="metric-icon-small green-bg"><i class="fas fa-clock-rotate-left"></i></div>
                    <div class="metric-content">
                        <span class="tag-top">TOTAL PRESTACIONES</span>
                        <p>Acumulado según años de servicio</p>
                        <h2 id="resultadoTotal">$ 0.00</h2>
                    </div>
                </div>
            </div>
        </div>

        <aside class="calculation-sidebar">
            <div class="consolidated-card">
                <p class="card-label">DATOS DEL TRABAJADOR</p>
                <span class="total-subtitle">Edad y Servicio</span>
                <h1 class="total-amount" id="sidebarEdad">—</h1>
                <div class="sub-amounts">
                    <div class="sub-item">
                        <span>Años de Servicio</span>
                        <strong id="sidebarAnios">0</strong>
                    </div>
                    <div class="sub-item">
                        <span>Edad Jubilación</span>
                        <strong id="sidebarTipo">—</strong>
                    </div>
                </div>
                <div class="liquidation-status">
                    <div class="status-header">
                        <span>Estado</span>
                        <span class="status-tag-green" id="sidebarEstatus">APROBADO</span>
                    </div>
                </div>
            </div>

            <div class="action-buttons-stack">
                <button class="btn-dark-full" type="button" onclick="generarComprobante()"><i class="fas fa-print"></i> Generar Comprobante</button>
            </div>

            <div class="doc-checklist-card">
                <h3>DOCUMENTOS REQUERIDOS</h3>
                <ul class="checklist">
                    <li class="done"><i class="fas fa-circle-check"></i> Certificación de Cargos</li>
                    <li class="done"><i class="fas fa-circle-check"></i> Constancia de Años de Servicio</li>
                    <li class="pending"><i class="fas fa-circle"></i> Acta de Cese de Funciones</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<script>
(function() {
    let trabajadorActualId = null;
    let porcentajeActual = 100;

    window.cambiarPorcentaje = function(btn, pct) {
        document.querySelectorAll('.percent-toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        porcentajeActual = pct;
    };

    async function cargarPrestaciones() {
        const grid = document.getElementById('prestacionesGrid');
        if (!grid) return;
        try {
            const result = await cachedFetch('/prestaciones', { ttl: 60000 });
            const data = result.data;
            grid.innerHTML = '';
            if (!data.length) {
                grid.innerHTML = '<p class="empty-state">No hay trabajadores disponibles. Deben tener solicitud aprobada y expediente registrado.</p>';
                return;
            }
            data.forEach(t => {
                const foto = t.foto_carnet ? '/storage/' + t.foto_carnet : '';
                const card = document.createElement('div');
                card.className = 'prestacion-card';
                card.onclick = () => abrirDetalle(t.id);
                card.innerHTML = `
                    <div class="pc-foto">
                        ${foto ? `<img src="${foto}" alt="">` : `<div class="pc-avatar"><i class="fas fa-user" size="28"></i></div>`}
                    </div>
                    <div class="pc-info">
                        <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                        <span class="pc-cedula">${escaparHTML(t.cedula)} · ${escaparHTML(t.cargo)}</span>
                        <div class="pc-row">
                            <span class="pc-anios">${escaparHTML(t.total_anos_servicio) || 0} años</span>
                            <span class="pc-badge">${escaparHTML(t.tipo_jubilacion)}</span>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        } catch (err) {
            console.error('Error al cargar prestaciones:', err);
            grid.innerHTML = '<p class="empty-state">Error al cargar datos.</p>';
        }
    }

    window.abrirDetalle = async function(id) {
        trabajadorActualId = id;
        try {
            mostrarCargando('Cargando datos del trabajador...');
            const resp = await fetch('/prestaciones/' + id);
            const data = await resp.json();
            const t = data.trabajador;
            const exp = data.expediente;
            const sol = data.solicitud;

            document.getElementById('prestaciones-lista').classList.add('hidden');
            document.getElementById('prestacion-detalle').classList.remove('hidden');

            // Header name
            document.getElementById('detalleHeaderNombre').textContent = `${t.nombres} ${t.apellidos}`;

            // Worker card
            const foto = exp.foto_carnet ? `/storage/${exp.foto_carnet}` : '';
            document.getElementById('detalleFoto').innerHTML = foto
                ? `<img src="${foto}" style="width:60px;height:60px;border-radius:8px;object-fit:cover;">`
                : `<i class="fas fa-user"></i>`;
            document.getElementById('detalleNombre').textContent = `${t.nombres} ${t.apellidos}`;
            document.getElementById('detalleInfo').textContent = `${t.cedula} • ${t.cargo} • ${t.total_anos_servicio || 0} años`;
            document.getElementById('detalleBadges').innerHTML = `
                <span class="badge-status active-bg">${escaparHTML(t.edad || '—')} AÑOS</span>
                <span class="badge-status info-bg">${escaparHTML(t.unidad_departamento)}</span>
            `;

            // Sidebar
            document.getElementById('sidebarEdad').textContent = t.edad ? `${t.edad} Años` : '—';
            document.getElementById('sidebarAnios').textContent = t.total_anos_servicio || 0;
            document.getElementById('sidebarTipo').textContent = sol.tipo_jubilacion || '—';

            // Hide previous results
            document.getElementById('resultadosCalculo').style.display = 'none';

            // Pre-fill form fields if prestacion exists
            if (data.prestacion) {
                document.getElementById('inputSueldoBase').value = (data.prestacion.monto * 0.5).toFixed(2);
            }
        } catch (err) {
            console.error('Error al cargar detalle:', err);
            mostrarToast('Error al cargar datos del trabajador.', 'error');
        } finally {
            ocultarCargando();
        }
    };

    window.volverAListaPrestaciones = function() {
        document.getElementById('prestacion-detalle').classList.add('hidden');
        document.getElementById('prestaciones-lista').classList.remove('hidden');
        trabajadorActualId = null;
        cargarPrestaciones();
    };

    window.calcularPrestacion = function() {
        const sueldo = parseFloat(document.getElementById('inputSueldoBase').value) || 0;
        const primaProf = parseFloat(document.getElementById('inputPrimaProf').value) || 0;
        const primaHijos = parseFloat(document.getElementById('inputPrimaHijos').value) || 0;
        const primaAnti = parseFloat(document.getElementById('inputPrimaAntiguedad').value) || 0;
        const salarioIntegral = sueldo + primaProf + primaHijos + primaAnti;
        const totalPrestaciones = salarioIntegral * (porcentajeActual / 100) * 12;

        document.getElementById('resultadoSalario').textContent = `$ ${salarioIntegral.toFixed(2)}`;
        document.getElementById('resultadoTotal').textContent = `$ ${totalPrestaciones.toFixed(2)}`;
        document.getElementById('resultadosCalculo').style.display = 'flex';
    };

    window.generarComprobante = function() {
        mostrarToast('Función de comprobante próximamente.', 'info');
    };

    // Observer
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'prestaciones' && m.target.classList.contains('active')) {
                if (document.getElementById('prestacion-detalle').classList.contains('hidden')) {
                    cargarPrestaciones();
                }
            }
        });
    });
    const seccion = document.getElementById('prestaciones');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });

    document.addEventListener('DOMContentLoaded', cargarPrestaciones);
    if (document.getElementById('prestaciones')?.classList.contains('active')) cargarPrestaciones();
})();
</script>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views\dashboard\secciones\prestaciones.blade.php ENDPATH**/ ?>