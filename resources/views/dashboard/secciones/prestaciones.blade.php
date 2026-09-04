{{-- prestaciones.blade.php --}}
<style>
.prestaciones-grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; }
.prestacion-card { background: white; border-radius: 16px; overflow: hidden; cursor: pointer; transition: all 0.25s ease; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.prestacion-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,35,102,0.1); border-color: #dbeafe; }
.prestacion-card .pc-foto { height: 120px; background: linear-gradient(135deg, #f8fafc, #eef2f6); display: flex; align-items: center; justify-content: center; position: relative; }
.prestacion-card .pc-foto::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 30px; background: linear-gradient(transparent, white); }
.prestacion-card .pc-foto img { width: 100%; height: 100%; object-fit: cover; }
.prestacion-card .pc-avatar { width: 64px; height: 64px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; }
.prestacion-card .pc-info { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 4px; }
.prestacion-card .pc-info strong { font-size: 0.92rem; color: #0f172a; line-height: 1.3; }
.prestacion-card .pc-info .pc-cedula { font-size: 0.78rem; color: #64748b; }
.prestacion-card .pc-info .pc-row { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
.prestacion-card .pc-anios { font-size: 0.75rem; font-weight: 700; color: #1e3a8a; background: #eff6ff; padding: 3px 10px; border-radius: 10px; }
.prestacion-card .pc-badge { font-size: 0.68rem; font-weight: 700; color: #1e40af; background: #dbeafe; padding: 3px 10px; border-radius: 10px; }
.prestacion-card .pc-titulo { font-size: 0.7rem; font-weight: 700; color: #166534; background: #dcfce7; padding: 3px 10px; border-radius: 10px; display: inline-flex; align-items: center; gap: 5px; }
.prestacion-card .pc-monto { font-size: 0.75rem; font-weight: 700; color: #16a34a; background: #ecfdf5; padding: 3px 10px; border-radius: 10px; }

.prestacion-detalle-layout { display: flex; flex-direction: column; gap: 16px; height: calc(100vh - 200px); overflow: hidden; }
.prestacion-info-superior { display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center; background: white; border-radius: 14px; padding: 16px 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); flex-shrink: 0; }
.prestacion-cuerpo { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; flex: 1; min-height: 0; }
.prestacion-col-izq, .prestacion-col-der { background: white; border-radius: 14px; padding: 16px 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow-y: auto; }
.prestacion-col-der { display: flex; flex-direction: column; }

.worker-mini-card { display: flex; gap: 12px; align-items: center; }
.worker-mini-card .wm-foto { width: 44px; height: 44px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1e40af; flex-shrink: 0; overflow: hidden; }
.worker-mini-card .wm-foto img { width: 100%; height: 100%; object-fit: cover; }
.worker-mini-card h4 { font-size: 0.95rem; color: #0f172a; margin: 0; }
.worker-mini-card p { font-size: 0.78rem; color: #64748b; margin: 2px 0 0; }

.prestacion-actions-bar { display: flex; gap: 8px; align-items: center; }
.prestacion-actions-bar .badge-st { font-size: 0.7rem; padding: 4px 12px; border-radius: 10px; font-weight: 700; white-space: nowrap; }

.subtitulo-sec { font-size: 0.8rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 10px; }
.subtitulo-sec i { color: #2563eb; }

.fila-sueldo { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
.fila-sueldo label { font-size: 0.75rem; color: #64748b; font-weight: 600; white-space: nowrap; }
.fila-sueldo input { flex: 1; padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; background: #f8fafc; outline: none; max-width: 180px; }
.fila-sueldo input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }

.prima-row { display: grid; grid-template-columns: 1fr 90px 1fr; gap: 8px; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.prima-row:last-child { border-bottom: none; }
.prima-info .prima-nombre { font-size: 0.8rem; font-weight: 600; color: #0f172a; }
.prima-info .prima-codigo { font-size: 0.65rem; color: #94a3b8; font-family: monospace; }
.prima-valor { font-size: 0.75rem; font-weight: 700; color: #475569; text-align: right; }
.prima-valor span { display: block; font-size: 0.6rem; color: #94a3b8; font-weight: 400; }
.prima-auto { font-size: 0.8rem; font-weight: 600; color: #0f172a; background: #f0fdf4; padding: 6px 10px; border-radius: 6px; border: 1px solid #bbf7d0; text-align: center; }
.prima-auto.na { background: #f8fafc; color: #94a3b8; border-color: #e2e8f0; }

.pct-area { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 8px; padding: 10px 14px; margin-top: 10px; border: 1px solid #e2e8f0; gap: 10px; }
.pct-area strong { font-size: 0.78rem; color: #0f172a; white-space: nowrap; }
.pct-toggle { display: flex; gap: 3px; }
.pct-toggle button { padding: 6px 14px; border: 1px solid #e2e8f0; background: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; color: #64748b; }
.pct-toggle button.active { background: #1a365d; color: white; border-color: #1a365d; }
.pct-toggle button span { display: block; font-size: 0.6rem; opacity: 0.7; }

.btn-prestacion { padding: 10px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; justify-content: center; transition: all 0.2s; }
.btn-prestacion-primario { background: #1a365d; color: white; }
.btn-prestacion-primario:hover { background: #1e3a8a; box-shadow: 0 4px 12px rgba(26,54,93,0.25); }
.btn-prestacion-exito { background: #16a34a; color: white; }
.btn-prestacion-exito:hover { background: #15803d; }
.btn-prestacion-info { background: #2563eb; color: white; }
.btn-prestacion-info:hover { background: #1d4ed8; }
.btn-prestacion:disabled { opacity: 0.5; cursor: not-allowed; }

.resumen-primas { overflow-y: auto; }
.resumen-item { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.78rem; }
.resumen-item:last-child { border-bottom: none; }
.resumen-item .r-label { color: #64748b; }
.resumen-item .r-value { font-weight: 700; color: #0f172a; }

.total-prestacion-box { background: linear-gradient(135deg, #1a365d, #1e3a8a); border-radius: 10px; padding: 14px 18px; color: white; margin-top: 10px; flex-shrink: 0; }
.total-prestacion-box p { font-size: 0.65rem; opacity: 0.7; margin: 0; }
.total-prestacion-box h2 { font-size: 1.3rem; margin: 4px 0 0; }

.btn-volver { display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 8px; color: #475569; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-volver:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1a365d; }

.modal-resultado { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 5000; }
.modal-resultado.active { display: flex; }
.modal-resultado-box { background: white; border-radius: 16px; padding: 24px; width: 420px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-resultado-box h3 { font-size: 1rem; color: #0f172a; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
.modal-rfila { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem; }
.modal-rfila:last-child { border-bottom: none; }
.modal-rfila .ml { color: #64748b; }
.modal-rfila .mr { font-weight: 700; color: #0f172a; }
.modal-rfila.total { border-top: 2px solid #1a365d; margin-top: 6px; padding-top: 10px; }
.modal-rfila.total .ml, .modal-rfila.total .mr { color: #1a365d; font-size: 0.95rem; }

.hidden { display: none !important; }

@media (max-width: 1024px) { .prestacion-cuerpo { grid-template-columns: 1fr; } .prestacion-detalle-layout { height: auto; overflow: visible; } }
body.dark-mode .prestacion-card { background: #1e293b; border-color: #334155; }
body.dark-mode .prestacion-card:hover { border-color: #3b82f6; }
body.dark-mode .prestacion-card .pc-foto { background: linear-gradient(135deg, #1a2332, #1e293b); }
body.dark-mode .prestacion-card .pc-foto::after { background: linear-gradient(transparent, #1e293b); }
body.dark-mode .prestacion-card .pc-avatar { background: #334155; color: #64748b; }
body.dark-mode .prestacion-card .pc-info strong { color: #f1f5f9; }
body.dark-mode .prestacion-card .pc-cedula { color: #94a3b8; }
body.dark-mode .prestacion-card .pc-anios { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .prestacion-card .pc-badge { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .prestacion-card .pc-titulo { background: #14532d; color: #86efac; }
body.dark-mode .prestacion-card .pc-monto { background: #14532d; color: #86efac; }
body.dark-mode .prestacion-info-superior { background: #1e293b; border-color: #334155; }
body.dark-mode .worker-mini-card h4 { color: #f1f5f9; }
body.dark-mode .worker-mini-card p { color: #94a3b8; }
body.dark-mode .prestacion-col-izq, body.dark-mode .prestacion-col-der { background: #1e293b; border-color: #334155; }
body.dark-mode .subtitulo-sec { color: #f1f5f9; border-bottom-color: #334155; }
body.dark-mode .prima-row { border-bottom-color: #334155; }
body.dark-mode .prima-info .prima-nombre { color: #f1f5f9; }
body.dark-mode .prima-auto { background: #14532d; border-color: #166534; color: #86efac; }
body.dark-mode .prima-auto.na { background: #1e293b; color: #64748b; border-color: #334155; }
body.dark-mode .pct-area { background: #1a2332; border-color: #334155; }
body.dark-mode .pct-area strong { color: #f1f5f9; }
body.dark-mode .pct-toggle button { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .pct-toggle button.active { background: #1e40af; border-color: #1e40af; color: white; }
body.dark-mode .resumen-item { border-bottom-color: #334155; }
body.dark-mode .resumen-item .r-label { color: #94a3b8; }
body.dark-mode .resumen-item .r-value { color: #f1f5f9; }
body.dark-mode .modal-resultado-box { background: #1e293b; }
body.dark-mode .modal-resultado-box h3 { color: #f1f5f9; }
body.dark-mode .modal-rfila .ml { color: #94a3b8; }
body.dark-mode .modal-rfila .mr { color: #f1f5f9; }
body.dark-mode .modal-rfila.total .ml, body.dark-mode .modal-rfila.total .mr { color: #93c5fd; }
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
    <div class="prestacion-detalle-layout">
        <div class="prestacion-info-superior">
            <button class="btn-volver" id="btnVolverPrestaciones">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
            <div class="worker-mini-card">
                <div class="wm-foto" id="detalleFotoMini"><i class="fas fa-user" style="font-size:18px;"></i></div>
                <div>
                    <h4 id="detalleNombreMini"></h4>
                    <p id="detalleInfoMini"></p>
                </div>
            </div>
            <div class="prestacion-actions-bar">
                <span class="badge-st" id="badgeEdad" style="background:#eff6ff;color:#1e40af;">—</span>
                <span class="badge-st" id="badgeTipo" style="background:#ecfdf5;color:#059669;">—</span>
                <span class="badge-st" id="badgeTitulo" style="background:#f0fdf4;color:#166534;display:none;">
                    <i class="fas fa-graduation-cap"></i> <span id="badgeTituloTexto"></span>
                </span>
                <button class="btn-prestacion btn-prestacion-info hidden" id="btnGenerarComprobante">
                    <i class="fas fa-file-invoice"></i> Comprobante
                </button>
            </div>
        </div>

        <div class="prestacion-cuerpo">
            <div class="prestacion-col-izq">
                <div class="subtitulo-sec"><i class="fas fa-coins"></i> Sueldo Base</div>
                <div class="fila-sueldo">
                    <label>SUELDO BASE MENSUAL (Bs.)</label>
                    <input type="number" step="0.01" min="0" id="inputSueldoBase" value="0.00" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
                </div>

                <div class="subtitulo-sec" style="margin-top:12px;"><i class="fas fa-calculator"></i> Primas Aplicables</div>
                <div id="primasContainer" style="max-height:280px;overflow-y:auto;"><p style="color:#94a3b8;font-size:0.8rem;">Cargando primas...</p></div>

                <div class="pct-area">
                    <strong>Porcentaje de Jubilación</strong>
                    <div class="pct-toggle">
                        <button class="active" type="button" data-pct="100">100% <span>Jubilación</span></button>
                        <button type="button" data-pct="82.5">82.5% <span>Incapacidad</span></button>
                    </div>
                </div>

                <button class="btn-prestacion btn-prestacion-primario" id="btnCalcularPrestacion" style="width:100%;margin-top:12px;">
                    <i class="fas fa-calculator"></i> Calcular Prestaciones
                </button>
            </div>

            <div class="prestacion-col-der">
                <div class="subtitulo-sec"><i class="fas fa-list-check"></i> Resumen de Primas</div>
                <div class="resumen-primas" id="sidebarPrimasResumen">
                    <p style="color:#94a3b8;font-size:0.8rem;">Presione "Calcular Prestaciones" para ver el resumen.</p>
                </div>
                <div class="total-prestacion-box">
                    <p>MONTO TOTAL PRESTACIONES</p>
                    <h2 id="totalPrestacionDisplay">Bs. 0,00</h2>
                </div>
                <div id="tasaInfoPrestacion" style="background:#f8fafc;border-radius:8px;padding:10px 14px;margin-top:10px;border:1px solid #e2e8f0;font-size:0.75rem;">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:4px;"><i class="fas fa-dollar-sign" style="color:#2563eb;"></i> Tasa de Cambio</div>
                    <div id="tasaDetallePrestacion" style="color:#64748b;">Cargando...</div>
                </div>
                <button class="btn-prestacion btn-prestacion-exito" id="btnGuardarPrestacion" style="width:100%;margin-top:10px;">
                    <i class="fas fa-save"></i> Guardar y Generar Nómina
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-resultado" id="modalResultado">
    <div class="modal-resultado-box">
        <h3><i class="fas fa-file-invoice-dollar"></i> Resultado del Cálculo</h3>
        <div id="modalResultadoBody">
            <div class="modal-rfila"><span class="ml">Sueldo Base</span><span class="mr" id="mrSueldoBase">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Total Primas</span><span class="mr" id="mrTotalPrimas">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Sueldo Integral Mensual</span><span class="mr" id="mrSueldoIntegral">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Porcentaje Aplicable</span><span class="mr" id="mrPorcentaje">100%</span></div>
            <div class="modal-rfila"><span class="ml">Años de Servicio</span><span class="mr" id="mrAnios">0</span></div>
            <div class="modal-rfila total"><span class="ml">MONTO TOTAL PRESTACIONES</span><span class="mr" id="mrTotal">Bs. 0,00</span></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button class="btn-prestacion btn-prestacion-exito" id="btnGuardarDesdeModal" style="flex:1;">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button class="btn-prestacion btn-prestacion-primario" id="btnCerrarModal" style="flex:1;background:#64748b;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let trabajadorActualId = null;
    let datosTrabajador = null;
    let primasDisponibles = [];
    let porcentajeActual = 100;
    let ultimoCalculo = null;

    // Escala de profesionalización por nivel académico (se aplica siempre el mayor, sin sumar).
    const NIVELES_PROFESIONALIZACION = [
        { nivel: 25, ids: [7], nombres: ['doctor', 'doctorado', 'phd'] },
        { nivel: 20, ids: [6], nombres: ['magister', 'magister'] },
        { nivel: 15, ids: [5], nombres: ['especialista', 'especializacion'] },
        { nivel: 13, ids: [3, 4], nombres: ['licenciado', 'licenciatura', 'lic', 'ingeniero', 'ingenieria', 'ing'] },
        { nivel: 11, ids: [2], nombres: ['tsu', 'tecnico superior universitario'] },
    ];

    // Devuelve { nivel: porcentaje, etiqueta: string } para el nivel académico del trabajador,
    // o null si no corresponde a ninguno de la escala (p.ej. Bachiller o sin nivel).
    function profesionalizacionDeTrabajador(t) {
        const id = parseInt(t.nivel_instruccion_id, 10);
        const texto = String(t.nivel_educativo_texto || '').toLowerCase();
        const legacy = parseInt(t.nivel_instruccion, 10);
        let mejor = null;
        for (const e of NIVELES_PROFESIONALIZACION) {
            const porId = e.ids.includes(id);
            const porNombre = e.nombres.some(n => texto.includes(n));
            if (porId || porNombre) {
                if (!mejor || e.nivel > mejor.nivel) mejor = e;
            }
        }
        if (!mejor && legacy > 0) {
            // Respaldo con el código legacy (0-5): 1=TSU, 2=Lic/Ing, 3=Esp, 4=Mag, 5=Doc
            const porLegacy = { 1: 11, 2: 13, 3: 15, 4: 20, 5: 25 };
            if (porLegacy[legacy]) mejor = { nivel: porLegacy[legacy], etiqueta: String(porLegacy[legacy]) + '%' };
        }
        if (!mejor) return null;
        return { nivel: mejor.nivel, etiqueta: texto || (mejor.etiqueta || (mejor.nivel + '%')) };
    }

    const PRIMA_CONFIG = {
        PRIMA_HIJO: { workerField: 'numero_hijos', calc: (v, t) => (t.numero_hijos || 0) * v, label: 'Número de hijos' },
        PRIMA_HIJOS_DISCAPACIDAD: { workerField: 'hijos_discapacidad', calc: (v, t) => (t.hijos_discapacidad || 0) * v, label: 'Hijos con discapacidad' },
        PRIMA_ACTIVIDAD_UNIVERSITARIA: { workerField: 'actividad_universitaria', calc: (v, t) => t.actividad_universitaria ? v : 0, label: 'Actividad universitaria' },
        PRIMA_FAMILIAR: { workerField: 'porcentaje_antiguedad', calc: (v, t) => v * (t.total_anos_servicio || 0), label: 'Antigüedad' },
        PRIMA_PROFESIONALIZACION: {
            workerField: 'prima_profesionalizacion',
            calc: (v, t) => {
                const prof = profesionalizacionDeTrabajador(t);
                // Utiliza la base de datos del trabajador ANTES de la prima (sueldo_base del trabajador).
                const base = parseFloat(t.sueldo_base) || 0;
                return prof ? base * (prof.nivel / 100) : 0;
            },
            label: 'Profesionalización'
        },
        PRIMA_RESPONSABILIDAD: { workerField: 'es_jefe_coordinador', calc: (v, t) => t.es_jefe_coordinador ? v : 0, label: 'Responsabilidad' },
        CESTA_TICKET: { workerField: 'cesta_ticket', calc: (v, t) => v, label: 'Cesta Ticket' },
    };

    function fmtBs(v) { return 'Bs. ' + v.toFixed(2).replace('.', ','); }

    document.querySelectorAll('.pct-toggle button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.pct-toggle button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            porcentajeActual = parseFloat(this.dataset.pct);
        });
    });

    async function cargarPrestaciones() {
        const grid = document.getElementById('prestacionesGrid');
        if (!grid) return;
        try {
            const result = await cachedFetch('/prestaciones?v=2', { ttl: 60000 });
            const items = Array.isArray(result.data) ? result.data : (result.data.data || []);
            grid.innerHTML = '';
            if (!items.length) {
                grid.innerHTML = '<p class="empty-state">No hay trabajadores disponibles. Deben tener solicitud aprobada y expediente.</p>';
                return;
            }
            items.forEach(t => {
                const foto = t.foto_carnet ? '/storage/' + t.foto_carnet : '';
                const card = document.createElement('div');
                card.className = 'prestacion-card';
                card.onclick = () => abrirDetalle(t.id);
                let montoHtml = '';
                if (t.tiene_prestacion && t.monto) {
                    montoHtml = '<span class="pc-monto">Bs. ' + parseFloat(t.monto).toFixed(2) + '</span>';
                }
                let titulo = '';
                const profTarjeta = profesionalizacionDeTrabajador(t);
                if (profTarjeta) {
                    const texto = String(t.nivel_educativo_texto || '').trim();
                    const nom = texto ? texto : (profTarjeta.etiqueta !== String(profTarjeta.nivel) ? profTarjeta.etiqueta : 'Profesional');
                    titulo = '<span class="pc-titulo"><i class="fas fa-graduation-cap"></i> ' + escaparHTML(nom) + ' · ' + profTarjeta.nivel + '%</span>';
                }
                card.innerHTML = `
                    <div class="pc-foto">${foto ? '<img src="'+foto+'" alt="">' : '<div class="pc-avatar"><i class="fas fa-user" size="28"></i></div>'}</div>
                    <div class="pc-info">
                        <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                        <span class="pc-cedula">${escaparHTML(t.cedula)} · ${escaparHTML(t.cargo)}</span>
                        ${titulo ? `<div class="pc-row">${titulo}</div>` : ''}
                        <div class="pc-row">
                            <span class="pc-anios">${escaparHTML(t.total_anos_servicio) || 0} años</span>
                            <span class="pc-badge">${escaparHTML(t.tipo_jubilacion)}</span>
                            ${montoHtml}
                        </div>
                    </div>`;
                grid.appendChild(card);
            });
        } catch (err) {
            console.error('Error prestaciones:', err);
            grid.innerHTML = '<p class="empty-state">Error al cargar datos.</p>';
        }
    }

    window.abrirDetalle = async function(id) {
        trabajadorActualId = id;
        ultimoCalculo = null;
        try {
            mostrarCargando('Cargando datos del trabajador...');
            const resp = await fetch('/prestaciones/' + id);
            const data = await resp.json();
            datosTrabajador = data.trabajador;
            primasDisponibles = data.primas || [];

            document.getElementById('prestaciones-lista').classList.add('hidden');
            document.getElementById('prestacion-detalle').classList.remove('hidden');

            const nombreCompleto = datosTrabajador.nombres + ' ' + datosTrabajador.apellidos;
            const foto = data.expediente.foto_carnet ? '/storage/' + data.expediente.foto_carnet : '';

            document.getElementById('detalleNombreMini').textContent = nombreCompleto;
            document.getElementById('detalleInfoMini').textContent = datosTrabajador.cedula + ' · ' + datosTrabajador.cargo;
            document.getElementById('detalleFotoMini').innerHTML = foto
                ? '<img src="'+foto+'" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">'
                : '<i class="fas fa-user" style="font-size:18px;"></i>';

            document.getElementById('badgeEdad').textContent = (datosTrabajador.edad || '—') + ' AÑOS';
            document.getElementById('badgeTipo').textContent = data.solicitud.tipo_jubilacion || '—';

            // Mostrar título académico y su porcentaje de profesionalización
            const badgeTitulo = document.getElementById('badgeTitulo');
            const prof = profesionalizacionDeTrabajador(datosTrabajador);
            const tituloTexto = String(datosTrabajador.nivel_educativo_texto || '').trim();
            if (prof) {
                const tituloFinal = tituloTexto ? tituloTexto : (prof.etiqueta !== String(prof.nivel) ? prof.etiqueta : 'Profesional');
                document.getElementById('badgeTituloTexto').textContent = tituloFinal + ' · ' + prof.nivel + '%';
                badgeTitulo.style.display = 'inline-flex';
            } else {
                badgeTitulo.style.display = 'none';
            }

            // Mostrar botón comprobante solo si ya hay prestación guardada
            const btnComp = document.getElementById('btnGenerarComprobante');
            if (data.prestacion) {
                btnComp.classList.remove('hidden');
                const savedDetalles = data.prestacion.detalles || [];
                const savedPct = parseFloat(data.prestacion.porcentaje_jubilacion) || 100;
                btnComp.onclick = () => generarComprobante(
                    parseFloat(data.prestacion.sueldo_integral || 0) - parseFloat(data.prestacion.total_primas || 0),
                    parseFloat(data.prestacion.total_primas || 0),
                    parseFloat(data.prestacion.sueldo_integral || 0),
                    parseFloat(data.prestacion.monto || 0),
                    savedDetalles,
                    savedPct
                );
                // Restaurar resumen, total y porcentaje desde datos guardados
                if (savedDetalles.length) {
                    const resumenHtml = savedDetalles
                        .filter(d => d.monto > 0)
                        .map(d => '<div class="resumen-item"><span class="r-label">' + escaparHTML(d.nombre) + '</span><span class="r-value">' + fmtBs(d.monto) + '</span></div>')
                        .join('');
                    document.getElementById('sidebarPrimasResumen').innerHTML = resumenHtml;
                }
                const savedMonto = parseFloat(data.prestacion.monto) || 0;
                document.getElementById('totalPrestacionDisplay').textContent = fmtBs(savedMonto);
                porcentajeActual = savedPct;
                document.querySelectorAll('.pct-toggle button').forEach(b => {
                    b.classList.toggle('active', parseFloat(b.dataset.pct) === savedPct);
                });
                const savedSueldoBase = parseFloat(data.prestacion.sueldo_integral || 0) - parseFloat(data.prestacion.total_primas || 0);
                document.getElementById('inputSueldoBase').value = savedSueldoBase.toFixed(2);
                ultimoCalculo = {
                    sueldoBase: savedSueldoBase,
                    totalPrimas: parseFloat(data.prestacion.total_primas || 0),
                    sueldoIntegral: parseFloat(data.prestacion.sueldo_integral || 0),
                    totalPrestaciones: savedMonto,
                    detalles: savedDetalles
                };
            } else {
                btnComp.classList.add('hidden');
                document.getElementById('totalPrestacionDisplay').textContent = 'Bs. 0,00';
                document.getElementById('sidebarPrimasResumen').innerHTML = '<p style="color:#94a3b8;font-size:0.8rem;">Presione "Calcular Prestaciones" para ver el resumen.</p>';
                document.getElementById('inputSueldoBase').value = (datosTrabajador.sueldo_base || 0).toFixed(2);
            }

            renderPrimas(primasDisponibles, datosTrabajador);

            const tasaDetalle = document.getElementById('tasaDetallePrestacion');
            if (data.prestacion && data.prestacion.tasa_utilizada) {
                const tFecha = data.prestacion.fecha_tasa_utilizada ? new Date(data.prestacion.fecha_tasa_utilizada).toLocaleDateString('es-VE') : '—';
                tasaDetalle.innerHTML = '<strong style="color:#0f172a;">' + parseFloat(data.prestacion.tasa_utilizada).toLocaleString('es-VE', {minimumFractionDigits:4}) + ' ' + (data.prestacion.moneda_tasa || 'VES/USD') + '</strong><br><span style="font-size:0.68rem;">Congelada: ' + tFecha + ' · ' + (data.prestacion.fuente_tasa || '—') + '</span>';
            } else if (data.tasa_actual) {
                tasaDetalle.innerHTML = '<strong style="color:#0f172a;">' + parseFloat(data.tasa_actual.tasa).toLocaleString('es-VE', {minimumFractionDigits:4}) + ' VES/USD</strong><br><span style="font-size:0.68rem;">' + data.tasa_actual.fecha + ' · ' + (data.tasa_actual.fuente || '—') + '</span>';
            } else {
                tasaDetalle.innerHTML = '<span style="color:#94a3b8;">Sin tasa registrada</span>';
            }
        } catch (err) {
            console.error('Error detalle:', err);
            mostrarToast('Error al cargar datos del trabajador.', 'error');
        } finally {
            ocultarCargando();
        }
    };

    function renderPrimas(primas, t) {
        const container = document.getElementById('primasContainer');
        container.innerHTML = '';
        primas.forEach(p => {
            const config = PRIMA_CONFIG[p.codigo] || {};
            const row = document.createElement('div');
            row.className = 'prima-row';
            let inputHtml = '';
            if (config.calc) {
                const autoVal = config.calc(p.valor, t);
                const isNa = autoVal === 0 && !t[config.workerField];
                inputHtml = '<div class="prima-auto' + (isNa ? ' na' : '') + '">Bs. ' + autoVal.toFixed(2) + '</div>';
            } else {
                inputHtml = '<div class="prima-auto na">—</div>';
            }
            row.innerHTML = `
                <div class="prima-info">
                    <div class="prima-nombre">${escaparHTML(p.nombre)}</div>
                    <div class="prima-codigo">${escaparHTML(p.codigo)}</div>
                </div>
                <div class="prima-valor">Bs. ${parseFloat(p.valor).toFixed(2)}<span>valor unitario</span></div>
                <div>${inputHtml}</div>`;
            container.appendChild(row);
        });
    }

    document.getElementById('btnCalcularPrestacion').addEventListener('click', function() {
        if (!datosTrabajador || !primasDisponibles.length) return;

        const sueldoBase = parseFloat(document.getElementById('inputSueldoBase').value) || 0;
        let totalPrimas = 0;
        const detalles = [];
        const resumenHtml = [];

        primasDisponibles.forEach(p => {
            const config = PRIMA_CONFIG[p.codigo] || {};
            let monto = 0;
            if (config.calc) {
                monto = config.calc(p.valor, datosTrabajador);
            }
            totalPrimas += monto;
            detalles.push({ codigo: p.codigo, nombre: p.nombre, valor_unitario: p.valor, monto });
            if (p.codigo === 'PRIMA_PROFESIONALIZACION') {
                const prof = profesionalizacionDeTrabajador(datosTrabajador);
                const base = parseFloat(datosTrabajador.sueldo_base) || 0;
                if (monto > 0 && prof) {
                    resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + ' · ' + escaparHTML(prof.etiqueta) + ' (' + prof.nivel + '%) sobre base ' + fmtBs(base) + '</span><span class="r-value">' + fmtBs(monto) + '</span></div>');
                } else {
                    resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + '</span><span class="r-value" style="color:#d97706;">+ ' + fmtBs(monto) + ' · nivel no aplica (0%)</span></div>');
                }
            } else if (monto > 0) {
                resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + '</span><span class="r-value">' + fmtBs(monto) + '</span></div>');
            }
        });

        const sueldoIntegral = sueldoBase + totalPrimas;
        const totalPrestaciones = sueldoIntegral * (porcentajeActual / 100) * (datosTrabajador.total_anos_servicio || 0);

        ultimoCalculo = { sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles };

        // Actualizar resumen en columna derecha
        document.getElementById('sidebarPrimasResumen').innerHTML = resumenHtml.length
            ? resumenHtml.join('')
            : '<p style="color:#94a3b8;font-size:0.8rem;">Sin primas activas</p>';
        document.getElementById('totalPrestacionDisplay').textContent = fmtBs(totalPrestaciones);

        // Mostrar modal con resultado
        document.getElementById('mrSueldoBase').textContent = fmtBs(sueldoBase);
        document.getElementById('mrTotalPrimas').textContent = fmtBs(totalPrimas);
        document.getElementById('mrSueldoIntegral').textContent = fmtBs(sueldoIntegral);
        document.getElementById('mrPorcentaje').textContent = porcentajeActual + '%';
        document.getElementById('mrAnios').textContent = datosTrabajador.total_anos_servicio || 0;
        document.getElementById('mrTotal').textContent = fmtBs(totalPrestaciones);
        document.getElementById('modalResultado').classList.add('active');
    });

    function guardarCalculo() {
        if (!ultimoCalculo || !datosTrabajador) {
            mostrarToast('Debe calcular primero las prestaciones.', 'warning');
            return;
        }
        guardarPrestacion(ultimoCalculo.sueldoBase, ultimoCalculo.totalPrimas, ultimoCalculo.sueldoIntegral, ultimoCalculo.totalPrestaciones, ultimoCalculo.detalles);
    }

    document.getElementById('btnGuardarPrestacion').addEventListener('click', guardarCalculo);
    document.getElementById('btnGuardarDesdeModal').addEventListener('click', function() {
        document.getElementById('modalResultado').classList.remove('active');
        guardarCalculo();
    });
    document.getElementById('btnCerrarModal').addEventListener('click', () => document.getElementById('modalResultado').classList.remove('active'));
    document.getElementById('modalResultado').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    async function guardarPrestacion(sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles) {
        try {
            mostrarCargando('Guardando cálculo...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/prestaciones', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({
                    trabajador_id: trabajadorActualId,
                    sueldo_base: sueldoBase,
                    monto: totalPrestaciones,
                    anios_servicio: datosTrabajador.total_anos_servicio || 0,
                    sueldo_integral: sueldoIntegral,
                    total_primas: totalPrimas,
                    porcentaje_jubilacion: porcentajeActual,
                    detalles: detalles,
                })
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Prestaciones guardadas.', 'success');
            if (window.limpiarCacheSigejub) window.limpiarCacheSigejub();
            abrirDetalle(trabajadorActualId);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al guardar.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    async function generarComprobante(sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles, pct) {
        if (!datosTrabajador || !trabajadorActualId) return;
        try {
            mostrarCargando('Generando comprobante...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/prestaciones/' + trabajadorActualId + '/comprobante', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({
                    sueldo_base: sueldoBase,
                    total_primas: totalPrimas,
                    sueldo_integral: sueldoIntegral,
                    total_prestaciones: totalPrestaciones,
                    porcentaje_jubilacion: pct ?? porcentajeActual,
                    detalles: detalles,
                })
            });
            if (!resp.ok) throw await resp.json();
            const blob = await resp.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Comprobante_Prestaciones_' + datosTrabajador.cedula.replace(/[^a-zA-Z0-9]/g, '') + '.pdf';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            mostrarToast('Comprobante generado correctamente.', 'success');
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al generar comprobante.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    document.getElementById('btnVolverPrestaciones').addEventListener('click', function() {
        document.getElementById('prestacion-detalle').classList.add('hidden');
        document.getElementById('prestaciones-lista').classList.remove('hidden');
        trabajadorActualId = null;
        datosTrabajador = null;
        ultimoCalculo = null;
        cargarPrestaciones();
    });

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
