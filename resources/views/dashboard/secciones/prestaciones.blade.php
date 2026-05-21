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
            <i data-lucide="arrow-left"></i>
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
                    <i data-lucide="user"></i>
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
                        <i data-lucide="banknote"></i>
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
                    <i data-lucide="calculator"></i> Calcular Prestaciones
                </button>
            </div>

            <div class="bottom-metrics" id="resultadosCalculo" style="display:none;">
                <div class="metric-box-white border-blue">
                    <div class="metric-icon-small blue-bg"><i data-lucide="calculator"></i></div>
                    <div class="metric-content">
                        <span class="tag-top">BASE MENSUAL</span>
                        <p>Salario Integral Estimado</p>
                        <h2 id="resultadoSalario">$ 0.00</h2>
                    </div>
                </div>
                <div class="metric-box-white border-green">
                    <div class="metric-icon-small green-bg"><i data-lucide="history"></i></div>
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
                <button class="btn-dark-full" type="button" onclick="generarComprobante()"><i data-lucide="printer"></i> Generar Comprobante</button>
            </div>

            <div class="doc-checklist-card">
                <h3>DOCUMENTOS REQUERIDOS</h3>
                <ul class="checklist">
                    <li class="done"><i data-lucide="check-circle-2"></i> Certificación de Cargos</li>
                    <li class="done"><i data-lucide="check-circle-2"></i> Constancia de Años de Servicio</li>
                    <li class="pending"><i data-lucide="circle"></i> Acta de Cese de Funciones</li>
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
            const resp = await fetch('/prestaciones');
            const data = await resp.json();
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
                        ${foto ? `<img src="${foto}" alt="">` : `<div class="pc-avatar"><i data-lucide="user" size="28"></i></div>`}
                    </div>
                    <div class="pc-info">
                        <strong>${t.nombres} ${t.apellidos}</strong>
                        <span>${t.cedula}</span>
                        <span class="pc-anios">${t.total_anos_servicio || 0} años de servicio</span>
                        <span class="pc-badge">${t.tipo_jubilacion}</span>
                    </div>
                `;
                grid.appendChild(card);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
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
                : `<i data-lucide="user"></i>`;
            document.getElementById('detalleNombre').textContent = `${t.nombres} ${t.apellidos}`;
            document.getElementById('detalleInfo').textContent = `${t.cedula} • ${t.cargo} • ${t.total_anos_servicio || 0} años`;
            document.getElementById('detalleBadges').innerHTML = `
                <span class="badge-status active-bg">${t.edad || '—'} AÑOS</span>
                <span class="badge-status info-bg">${t.unidad_departamento || ''}</span>
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

            if (typeof lucide !== 'undefined') lucide.createIcons();
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
