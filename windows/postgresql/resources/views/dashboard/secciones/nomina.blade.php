{{-- nomina.blade.php - Nómina institucional organizada por año y mes.
     La institución carga la planilla cada mes: años → meses → planilla del mes. --}}
<style>
.nomina-tabs { display: flex; gap: 4px; margin-bottom: 16px; background: #f1f5f9; border-radius: 10px; padding: 3px; overflow-x: auto; }
.nomina-tab { padding: 8px 20px; border: none; background: transparent; border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.nomina-tab.active { background: white; color: #1a365d; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.nomina-tab:hover:not(.active) { color: #1e3a8a; }
.table-wrapper-nomina { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; background: white; }
.table-wrapper-nomina table { min-width: 2920px; border-collapse: collapse; font-size: 0.72rem; }
.table-wrapper-nomina th { position: sticky; top: 0; z-index: 2; padding: 6px 5px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.15); text-align: center; }
.table-wrapper-nomina td { padding: 5px 5px; white-space: nowrap; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f8fafc; text-align: center; }
.section-azul th { background: #1e3a8a; color: white; }
.section-celeste th { background: #0284c7; color: white; }
.section-verde th { background: #15803d; color: white; }
.sep-col { border-left: 3px solid #e2e8f0 !important; }
.sec-label { font-size: 0.6rem; font-weight: 700; text-align: center; letter-spacing: 0.5px; padding: 3px 5px !important; }
.sec-label-azul { background: #dbeafe; color: #1e3a8a; }
.sec-label-celeste { background: #e0f2fe; color: #0284c7; }
.sec-label-verde { background: #dcfce7; color: #15803d; }
body.dark-mode .table-wrapper-nomina { border-color: #334155; }
body.dark-mode .table-wrapper-nomina td { border-bottom-color: #334155; border-right-color: #1e293b; color: #f1f5f9; }
body.dark-mode .sep-col { border-left-color: #334155 !important; }
body.dark-mode .sec-label-azul { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .sec-label-celeste { background: #0c4a6e; color: #7dd3fc; }
body.dark-mode .sec-label-verde { background: #14532d; color: #86efac; }

/* Rejilla de períodos (años y meses) */
.nomina-anios-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 16px; }
.nomina-anio-card { position: relative; background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.nomina-anio-card::after { content: ''; position: absolute; inset: auto 0 0 0; height: 4px; border-radius: 0 0 14px 14px; background: linear-gradient(90deg, #2563eb, #38bdf8); opacity: 0; transition: opacity .2s; }
.nomina-anio-card:hover { transform: translateY(-3px); border-color: #bfdbfe; box-shadow: 0 8px 20px rgba(37,99,235,0.12); }
.nomina-anio-card:hover::after { opacity: 1; }
.nomina-anio-card .na-folder { width: 46px; height: 46px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
.nomina-anio-card .na-anio { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.nomina-anio-card .na-count { font-size: 0.75rem; color: #64748b; font-weight: 600; }
.nomina-anio-card.vacia { background: #fbfdff; border-style: dashed; }
.nomina-anio-card.vacia .na-folder { background: #f1f5f9; color: #94a3b8; }
.nomina-anio-card.vacia .na-anio { color: #64748b; }
.nomina-anio-card .na-tag { position: absolute; top: 12px; right: 12px; font-size: 0.62rem; font-weight: 800; letter-spacing: .4px; padding: 3px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; text-transform: uppercase; }
.nomina-anio-card.vacia .na-tag { background: #f1f5f9; color: #94a3b8; }
.nomina-mes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; margin-top: 16px; }
.nomina-mes-card { position: relative; background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; cursor: pointer; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.04); text-align: left; }
.nomina-mes-card:hover { transform: translateY(-2px); border-color: #bfdbfe; box-shadow: 0 8px 18px rgba(37,99,235,.12); }
.nomina-mes-card .nm-mes { font-size: 1rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.nomina-mes-card .nm-meta { margin-top: 6px; font-size: 0.75rem; color: #64748b; font-weight: 600; }
.nomina-mes-card .nm-total { margin-top: 8px; font-size: 0.78rem; color: #047857; font-weight: 800; }
.nomina-mes-card.sin-datos { background: #fbfdff; border-style: dashed; }
.nomina-mes-card.sin-datos .nm-mes { color: #94a3b8; }
.nomina-mes-card.sin-datos .nm-meta { color: #a8b6c8; font-weight: 500; }
.nomina-anio-header { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap; }
.nomina-anio-header h2 { font-size: 1.1rem; color: #0f172a; margin: 0; }
.nomina-anio-header .btn-back { border: none; background: #f1f5f9; color: #1e3a8a; border-radius: 8px; padding: 8px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.nomina-anio-header .btn-back:hover { background: #e2e8f0; }
.nomina-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #64748b; font-weight: 600; }
.nomina-breadcrumb strong { color: #1e3a8a; }
body.dark-mode .nomina-anio-card, body.dark-mode .nomina-mes-card { background: #1e293b; border-color: #334155; }
body.dark-mode .nomina-anio-card:hover, body.dark-mode .nomina-mes-card:hover { border-color: #3b82f6; box-shadow: 0 8px 20px rgba(59,130,246,0.2); }
body.dark-mode .nomina-anio-card .na-folder { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .nomina-anio-card .na-anio, body.dark-mode .nomina-mes-card .nm-mes { color: #f1f5f9; }
body.dark-mode .nomina-anio-card .na-count, body.dark-mode .nomina-mes-card .nm-meta { color: #94a3b8; }
body.dark-mode .nomina-anio-card.vacia, body.dark-mode .nomina-mes-card.sin-datos { background: #172033; }
body.dark-mode .nomina-anio-header h2 { color: #f1f5f9; }
body.dark-mode .nomina-anio-header .btn-back { background: #334155; color: #93c5fd; }
body.dark-mode .nomina-anio-header .btn-back:hover { background: #1e293b; }
.btn-accion { width: 26px; height: 26px; border: none; border-radius: 7px; font-size: 0.7rem; cursor: pointer; margin: 0 2px; transition: all 0.15s; vertical-align: middle; }
.btn-accion-edit { background: #eff6ff; color: #1d4ed8; }
.btn-accion-edit:hover { background: #dbeafe; }
.btn-accion-del { background: #fef2f2; color: #dc2626; }
.btn-accion-del:hover { background: #fee2e2; }
body.dark-mode .btn-accion-edit { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .btn-accion-edit:hover { background: #0f2a4a; }
body.dark-mode .btn-accion-del { background: #450a0a; color: #fca5a5; }
body.dark-mode .btn-accion-del:hover { background: #5f0f0f; }
.nomina-total-asis { margin-top: 14px; padding: 12px; border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; text-align: center; font-weight: 800; color: #15803d; font-size: 0.9rem; }
body.dark-mode .nomina-total-asis { background: #14532d; border-color: #166534; color: #86efac; }
/* La rejilla .modal-grid de la ventana de edición vive en css/dashboard/secciones/modal.css */
</style>

<header class="section-header">
    <div class="header-info">
        <h1><span class="text-blue-accent">Nómina</span></h1>
        <p>Gestión de la planilla institucional organizada por año y mes (una planilla por mes).</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-outline" onclick="abrirModalImportarNomina()">
            <i class="fas fa-file-import"></i> Importar Nómina
        </button>
        <button type="button" class="btn-primary-dark" onclick="exportarNomina()">
            <i class="fas fa-file-excel"></i> Exportar Planilla
        </button>
    </div>
</header>

{{-- VISTA 1: AÑOS --}}
<div id="vistaAniosNomina">
    <div class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <div class="filter-group">
            <label>AÑOS DISPONIBLES</label>
            <p style="margin:2px 0 0;font-size:0.75rem;color:#64748b;">Seleccione un año para ver sus meses cargados.</p>
        </div>
        <div class="total-badge-card" style="margin-left: auto;">
            <div>
                <p>TOTAL TRABAJADORES REGISTRADOS</p>
                <h2 id="totalTrabajadoresAnios">0</h2>
            </div>
            <i class="fas fa-users icon-bg"></i>
        </div>
    </div>
    <div class="nomina-anios-grid" id="nominaAniosGrid"></div>
</div>

{{-- VISTA 2: MESES DEL AÑO --}}
<div id="vistaMesesNomina" style="display:none;">
    <div class="nomina-anio-header" style="margin-top:20px;">
        <button type="button" class="btn-back" onclick="volverAniosNomina()"><i class="fas fa-arrow-left"></i> Años</button>
        <div>
            <h2><i class="fas fa-calendar-days"></i> NÓMINA <span id="anioMesesTitulo">2026</span></h2>
            <div class="nomina-breadcrumb">Nómina &gt; <strong id="anioMesesBreadcrumb">2026</strong> · Seleccione el mes</div>
        </div>
        <div class="total-badge-card" style="margin-left:auto;">
            <div>
                <p>PLANILLAS DEL AÑO</p>
                <h2 id="totalPlanillasAnio">0</h2>
            </div>
            <i class="fas fa-folder-open icon-bg"></i>
        </div>
    </div>
    <div class="nomina-mes-grid" id="nominaMesesGrid"></div>
</div>

{{-- VISTA 3: PLANILLA DEL MES --}}
<div id="vistaTablaNomina" style="display:none;">
    <div class="nomina-anio-header" style="margin-top:20px;">
        <button type="button" class="btn-back" onclick="volverMesesNomina()"><i class="fas fa-arrow-left"></i> Meses</button>
        <div>
            <h2><i class="fas fa-folder-open"></i> NÓMINA &gt; <span id="anioTitulo">2026</span> &gt; <span id="mesTitulo">—</span></h2>
            <div class="nomina-breadcrumb">Nómina &gt; <strong id="breadcrumbAnio">2026</strong> &gt; <strong id="breadcrumbMes">—</strong></div>
        </div>
    </div>

    <div class="nomina-tabs" id="nominaTabs">
        <button class="nomina-tab active" data-tipo="">Todas</button>
        <button class="nomina-tab" data-tipo="ADM">Administrativos</button>
        <button class="nomina-tab" data-tipo="DOC">Docentes</button>
        <button class="nomina-tab" data-tipo="OBREROS">Obreros</button>
    </div>

    <div class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <div class="filter-group">
            <label>MES</label>
            <select id="filtroMesNomina" onchange="cambiarMesDesdeSelector(this.value)"></select>
        </div>
        <div class="filter-group">
            <label>ESTADO DE LA PLANILLA</label>
            <p id="estadoPlanilla" style="margin:6px 0 0;font-size:0.8rem;font-weight:700;color:#15803d;">—</p>
        </div>
        <div class="total-badge-card" style="margin-left: auto;">
            <div>
                <p>TOTAL TRABAJADORES</p>
                <h2 id="totalNomina">0</h2>
            </div>
            <i class="fas fa-users icon-bg"></i>
        </div>
        <div class="total-badge-card">
            <div>
                <p>TOTAL ASIGNACIONES</p>
                <h2 id="totalGeneralNomina">Bs. 0,00</h2>
            </div>
            <i class="fas fa-coins icon-bg"></i>
        </div>
    </div>

    <div class="table-wrapper-nomina" style="margin-top:16px;">
        <table>
            <thead>
                <tr>
                    <th class="section-azul" colspan="9">DATOS PERSONALES BÁSICOS</th>
                    <th class="section-celeste" colspan="8">DATOS LABORALES Y ANTIGÜEDAD</th>
                    <th class="section-verde" colspan="10">REMUNERACIÓN Y PRIMAS / ASIGNACIONES</th>
                </tr>
                <tr>
                    <!-- AZUL -->
                    <th class="section-azul" style="min-width:80px;">ACCIONES</th>
                    <th class="section-azul">N°</th>
                    <th class="section-azul">CÉDULA</th>
                    <th class="section-azul">APELLIDOS Y NOMBRES</th>
                    <th class="section-azul">GÉN</th>
                    <th class="section-azul">N° HIJ</th>
                    <th class="section-azul">N° H-DISC</th>
                    <th class="section-azul">GRADO INSTR.</th>
                    <th class="section-azul sep-col">CÓD GRADO</th>
                    <!-- CELESTE -->
                    <th class="section-celeste">F.INGRESO</th>
                    <th class="section-celeste">AÑOS SERV</th>
                    <th class="section-celeste">AÑOS PREV</th>
                    <th class="section-celeste">TOTAL AÑOS</th>
                    <th class="section-celeste">% ANTIG</th>
                    <th class="section-celeste">CÓD PRIMA R</th>
                    <th class="section-celeste">CARGO</th>
                    <th class="section-celeste sep-col">GRADO CGO</th>
                    <!-- VERDE -->
                    <th class="section-verde">SUELDO BASE</th>
                    <th class="section-verde">P.FAMILIAR</th>
                    <th class="section-verde">P.HIJOS</th>
                    <th class="section-verde">P.H-DISC</th>
                    <th class="section-verde">P.A-UNIV</th>
                    <th class="section-verde">P.PROFES</th>
                    <th class="section-verde">P.RESP</th>
                    <th class="section-verde">COMP RESP</th>
                    <th class="section-verde">P.ANTIG</th>
                    <th class="section-verde">TOTAL ASIG</th>
                </tr>
            </thead>
            <tbody id="tbodyNomina"></tbody>
        </table>
    </div>
</div>

<div id="modalImportarNomina" class="modal-overlay" data-no-close-outside="true">
    <div class="modal-delete-box" style="text-align:left;max-width:560px;">
        <h3 style="text-align:center;"><i class="fas fa-file-import"></i> Importar Nómina del Mes</h3>
        <p style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:16px;">
            Suba el archivo Excel con formato oficial (hojas ADM / DOC / OBREROS) y seleccione el mes y el año de la planilla.
        </p>
        <form id="formImportarNomina">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>MES DE LA NÓMINA</label>
                    <select name="mes" id="importarMesNomina" required>
                        <option value="">Seleccione el mes</option>
                        @foreach (\App\Models\Nomina::MESES as $num => $nombre)
                            <option value="{{ $num }}" @if ($num === (int) now()->month) selected @endif>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <label>AÑO DE LA NÓMINA</label>
                    <select name="anio" id="importarAnioNomina" required>
                        <option value="">Seleccione el año</option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label>ARCHIVO EXCEL</label>
                <input type="file" name="archivo" accept=".xlsx,.xls" required>
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarImportar">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitImportar">Importar</button>
            </div>
        </form>
        <div id="resultadoImportacion" style="display:none;margin-top:16px;padding:12px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;font-size:0.85rem;"></div>
        <div style="text-align:center;margin-top:12px;">
            <button type="button" class="btn-submit" id="btnCerrarImportacion" style="display:none;background:#64748b;width:auto;padding:10px 28px;">Cerrar</button>
        </div>
    </div>
</div>

<div id="modalEditarNomina" class="modal-overlay" data-no-close-outside="true">
    <div class="modal-delete-box" style="text-align:left;max-width:620px;">
        <h3 style="text-align:center;"><i class="fas fa-pen"></i> Editar Nómina</h3>
        <p id="editarNominaInfo" style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:16px;"></p>
        <form id="formEditarNomina">
            @csrf
            <input type="hidden" name="nomina_trabajador_id" id="editarNominaPivotId">
            <div class="modal-grid">
                <div class="input-group">
                    <label>SUELDO BASE (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="sueldo_base" class="input-monto-nomina" required>
                </div>
                <div class="input-group">
                    <label>P. FAMILIAR (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_familiar" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. HIJOS (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_hijo" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. H-DISC (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_hijos_discapacidad" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. A-UNIV (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_actividad_universitaria" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. PROFES (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_profesionalizacion" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. RESP (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_responsabilidad" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>COMP. RESP (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="complemento_prima_responsabilidad" class="input-monto-nomina">
                </div>
                <div class="input-group">
                    <label>P. ANTIG (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="prima_antiguedad" class="input-monto-nomina">
                </div>
            </div>
            <div class="nomina-total-asis">TOTAL ASIGNACIÓN: <span id="editarTotalAsignacion">0,00</span> Bs.</div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarEditarNomina">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitEditarNomina">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEliminarNomina" class="modal-overlay" data-no-close-outside="true">
    <div class="modal-delete-box">
        <h3 style="text-align:center;"><i class="fas fa-trash"></i> Eliminar de la Nómina</h3>
        <p id="eliminarNominaInfo" style="text-align:center;color:#64748b;font-size:0.85rem;margin:16px 0;"></p>
        <div class="modal-actions" style="justify-content:center;">
            <button type="button" class="btn-cancel" id="btnCancelarEliminarNomina">Cancelar</button>
            <button type="button" class="btn-submit" id="btnConfirmarEliminarNomina">Sí, eliminar</button>
        </div>
    </div>
</div>

<script>
let tipoNominaActual = '';
let anioNominaActual = '';
let mesNominaActual = '';
let filasNomina = [];
let indiceEliminarNomina = null;
let aniosNominaCache = [];
const MESES_NOMINA = @json(\App\Models\Nomina::MESES);

document.querySelectorAll('.nomina-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.nomina-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        tipoNominaActual = this.dataset.tipo;
        cargarNomina();
    });
});

window.exportarNomina = function() {
    if (!anioNominaActual || !mesNominaActual) {
        mostrarToast('Seleccione primero un mes de la nómina.', 'warning');
        return;
    }
    const overlay = document.getElementById('loading-overlay');
    const texto = document.getElementById('loadingText');
    if (overlay) overlay.classList.add('active');
    if (texto) texto.textContent = 'Generando planilla de nómina...';
    const params = new URLSearchParams({ anio: anioNominaActual, mes: mesNominaActual });
    if (tipoNominaActual) params.set('tipo_nomina', tipoNominaActual);
    fetch('/exportar/nomina?' + params.toString())
        .then(r => { if (!r.ok) throw Error(); return r.blob(); })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const nombreMes = MESES_NOMINA[String(mesNominaActual)] || mesNominaActual;
            a.download = 'NOMINA_' + nombreMes + '_' + anioNominaActual + '.xlsx';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (overlay) overlay.classList.remove('active');
        })
        .catch(() => { if (overlay) overlay.classList.remove('active'); mostrarToast('Error al generar la planilla', 'error'); });
};

window.abrirModalImportarNomina = function() {
    const selAnio = document.getElementById('importarAnioNomina');
    if (selAnio && selAnio.options.length <= 1) poblarSelectorAnios(selAnio);
    if (selAnio && anioNominaActual) selAnio.value = anioNominaActual;
    const selMes = document.getElementById('importarMesNomina');
    if (selMes && mesNominaActual) selMes.value = mesNominaActual;
    document.getElementById('modalImportarNomina').style.display = 'flex';
    document.getElementById('resultadoImportacion').style.display = 'none';
    document.getElementById('btnCerrarImportacion').style.display = 'none';
};

function fmt(v) { return (v || 0).toFixed(2).replace('.', ','); }
function fmtBsNom(v) { return 'Bs. ' + fmt(v); }

function recalcularTotalAsignacion() {
    const form = document.getElementById('formEditarNomina');
    let total = 0;
    form.querySelectorAll('.input-monto-nomina').forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById('editarTotalAsignacion').textContent = fmt(total);
    return total;
}

window.abrirModalEditarNomina = function(index) {
    const t = filasNomina[index];
    if (!t || !t.nomina_trabajador_id) { mostrarToast('Este trabajador no tiene datos de nómina', 'error'); return; }
    document.getElementById('editarNominaPivotId').value = t.nomina_trabajador_id;
    document.getElementById('editarNominaInfo').textContent = (t.cedula || '') + ' - ' + (t.nombre_completo || '');
    const form = document.getElementById('formEditarNomina');
    ['sueldo_base','prima_familiar','prima_hijo','prima_hijos_discapacidad','prima_actividad_universitaria',
     'prima_profesionalizacion','prima_responsabilidad','complemento_prima_responsabilidad','prima_antiguedad'].forEach(campo => {
        form.querySelector('[name="' + campo + '"]').value = Number(t[campo] || 0);
    });
    recalcularTotalAsignacion();
    document.getElementById('modalEditarNomina').style.display = 'flex';
};

window.abrirModalEliminarNomina = function(index) {
    const t = filasNomina[index];
    if (!t || !t.nomina_trabajador_id) { mostrarToast('Este trabajador no tiene datos de nómina', 'error'); return; }
    indiceEliminarNomina = index;
    const nombreMes = MESES_NOMINA[String(mesNominaActual)] || '';
    document.getElementById('eliminarNominaInfo').innerHTML =
        '¿Seguro que desea eliminar a <strong>' + (t.nombre_completo || '') + '</strong><br>(C.I. ' + (t.cedula || '') +
        ') de la nómina de <strong>' + nombreMes + ' ' + anioNominaActual + '</strong>?<br><br>Esta acción no se puede deshacer.';
    document.getElementById('modalEliminarNomina').style.display = 'flex';
};

const tbodyNomina = document.getElementById('tbodyNomina');
if (tbodyNomina) {
    tbodyNomina.addEventListener('click', function(ev) {
        const btnEdit = ev.target.closest('[data-editar]');
        const btnDel = ev.target.closest('[data-eliminar]');
        if (btnEdit) { abrirModalEditarNomina(parseInt(btnEdit.dataset.editar, 10)); return; }
        if (btnDel) { abrirModalEliminarNomina(parseInt(btnDel.dataset.eliminar, 10)); }
    });
}

document.getElementById('formEditarNomina')?.addEventListener('input', recalcularTotalAsignacion);
document.getElementById('btnCancelarEditarNomina')?.addEventListener('click', () => document.getElementById('modalEditarNomina').style.display = 'none');
document.getElementById('btnCancelarEliminarNomina')?.addEventListener('click', () => document.getElementById('modalEliminarNomina').style.display = 'none');

document.getElementById('formEditarNomina')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('editarNominaPivotId').value;
    const btn = document.getElementById('btnSubmitEditarNomina');
    btn.disabled = true; btn.textContent = 'Guardando...';
    try {
        const resp = await fetch('/nomina/trabajador/' + id + '/actualizar', {
            method: 'POST', body: new FormData(this),
            headers: { 'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        const data = await resp.json();
        if (!resp.ok) throw data;
        document.getElementById('modalEditarNomina').style.display = 'none';
        mostrarToast('Cambios guardados correctamente', 'success');
        cargarNomina();
    } catch (err) {
        mostrarToast(err.mensaje || 'Error al guardar los cambios', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Guardar cambios';
    }
});

document.getElementById('btnConfirmarEliminarNomina')?.addEventListener('click', async function() {
    const t = filasNomina[indiceEliminarNomina];
    if (!t || !t.nomina_trabajador_id) return;
    const btn = this;
    btn.disabled = true;
    try {
        const resp = await fetch('/nomina/trabajador/' + t.nomina_trabajador_id + '/eliminar', {
            method: 'POST',
            headers: { 'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: ''
        });
        const data = await resp.json();
        if (!resp.ok) throw data;
        document.getElementById('modalEliminarNomina').style.display = 'none';
        mostrarToast('Trabajador eliminado de la nómina', 'success');
        cargarNomina();
        cargarAniosNomina();
    } catch (err) {
        mostrarToast(err.mensaje || 'Error al eliminar', 'error');
    } finally {
        btn.disabled = false;
    }
});

window.cargarAniosNomina = function() {
    fetch('/nomina/anios')
        .then(r => r.json())
        .then(resp => {
            aniosNominaCache = (resp && resp.anios) || [];
            const grid = document.getElementById('nominaAniosGrid');
            if (grid) {
                let html = '';
                aniosNominaCache.forEach(a => {
                    const sinDatos = !a.planillas;
                    html += '<div class="nomina-anio-card' + (sinDatos ? ' vacia' : '') + '" onclick="abrirAnioNomina(\'' + a.anio + '\')">' +
                        '<span class="na-tag">' + (sinDatos ? 'Sin planillas' : a.planillas + ' planilla' + (a.planillas === 1 ? '' : 's')) + '</span>' +
                        '<div class="na-folder"><i class="fas fa-folder' + (sinDatos ? '' : '-open') + '"></i></div>' +
                        '<div class="na-anio">' + a.anio + '</div>' +
                        '<div class="na-count">' + a.total_trabajadores + ' trabajador' + (a.total_trabajadores === 1 ? '' : 'es') + ' vigentes</div>' +
                        '</div>';
                });
                if (!html) {
                    html = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px 20px;border:1px dashed #cbd5e1;border-radius:12px;">' +
                        'Aún no hay nóminas registradas. Presione <strong>Importar Nómina</strong> para cargar la primera.</div>';
                }
                grid.innerHTML = html;
            }
            const badge = document.getElementById('totalTrabajadoresAnios');
            if (badge && resp) badge.textContent = resp.total_registrados ?? 0;
        })
        .catch(err => console.error(err));
};

function poblarSelectorAnios(sel) {
    const anioActual = new Date().getFullYear();
    let html = '<option value="">Seleccione el año</option>';
    for (let anio = anioActual + 1; anio >= 1900; anio--) {
        html += '<option value="' + anio + '">' + anio + '</option>';
    }
    sel.innerHTML = html;
}

// AÑOS → MESES
window.abrirAnioNomina = function(anio) {
    anioNominaActual = String(anio);
    mesNominaActual = '';
    const datos = aniosNominaCache.find(a => String(a.anio) === String(anio)) || { meses: [], planillas: 0 };
    document.getElementById('anioMesesTitulo').textContent = anio;
    document.getElementById('anioMesesBreadcrumb').textContent = anio;
    document.getElementById('totalPlanillasAnio').textContent = datos.planillas || 0;

    const cargados = {};
    (datos.meses || []).forEach(m => { if (m.mes) cargados[m.mes] = m; });

    let html = '';
    for (let mes = 1; mes <= 12; mes++) {
        const info = cargados[mes];
        if (info) {
            html += '<button type="button" class="nomina-mes-card" onclick="abrirMesNomina(\'' + anio + '\',' + mes + ')">' +
                '<div class="nm-mes"><i class="fas fa-folder-open" style="color:#1d4ed8;"></i> ' + MESES_NOMINA[String(mes)] + '</div>' +
                '<div class="nm-meta">' + info.total_trabajadores + ' trabajador' + (info.total_trabajadores === 1 ? '' : 'es') + ' · ' + (info.estado || 'borrador') + '</div>' +
                '<div class="nm-total">' + fmtBsNom(info.total_general) + '</div>' +
                '</button>';
        } else {
            html += '<button type="button" class="nomina-mes-card sin-datos" onclick="importarMes(\'' + anio + '\',' + mes + ')">' +
                '<div class="nm-mes"><i class="fas fa-folder"></i> ' + MESES_NOMINA[String(mes)] + '</div>' +
                '<div class="nm-meta">Sin cargar · clic para importar</div>' +
                '</button>';
        }
    }
    document.getElementById('nominaMesesGrid').innerHTML = html;

    document.getElementById('vistaAniosNomina').style.display = 'none';
    document.getElementById('vistaTablaNomina').style.display = 'none';
    document.getElementById('vistaMesesNomina').style.display = 'block';
};

window.abrirMesNomina = function(anio, mes) {
    anioNominaActual = String(anio);
    mesNominaActual = String(mes);
    document.getElementById('anioTitulo').textContent = anio;
    document.getElementById('mesTitulo').textContent = MESES_NOMINA[String(mes)] || mes;
    document.getElementById('breadcrumbAnio').textContent = anio;
    document.getElementById('breadcrumbMes').textContent = MESES_NOMINA[String(mes)] || mes;
    document.getElementById('vistaAniosNomina').style.display = 'none';
    document.getElementById('vistaMesesNomina').style.display = 'none';
    document.getElementById('vistaTablaNomina').style.display = 'block';

    const sel = document.getElementById('filtroMesNomina');
    if (sel) {
        let opts = '';
        for (let m = 1; m <= 12; m++) {
            opts += '<option value="' + m + '"' + (String(m) === String(mes) ? ' selected' : '') + '>' + MESES_NOMINA[String(m)] + '</option>';
        }
        sel.innerHTML = opts;
    }

    tipoNominaActual = '';
    document.querySelectorAll('.nomina-tab').forEach(t => {
        t.classList.remove('active');
        if (!t.dataset.tipo) t.classList.add('active');
    });
    cargarNomina();
};

window.importarMes = function(anio, mes) {
    abrirModalImportarNomina();
    const selAnio = document.getElementById('importarAnioNomina');
    const selMes = document.getElementById('importarMesNomina');
    if (selAnio) selAnio.value = anio;
    if (selMes) selMes.value = mes;
};

window.volverAniosNomina = function() {
    anioNominaActual = '';
    mesNominaActual = '';
    document.getElementById('vistaMesesNomina').style.display = 'none';
    document.getElementById('vistaTablaNomina').style.display = 'none';
    document.getElementById('vistaAniosNomina').style.display = 'block';
    cargarAniosNomina();
};

window.volverMesesNomina = function() {
    const anio = anioNominaActual;
    mesNominaActual = '';
    document.getElementById('vistaTablaNomina').style.display = 'none';
    if (anio) abrirAnioNomina(anio);
    else volverAniosNomina();
};

window.cambiarMesDesdeSelector = function(mes) {
    if (!mes || !anioNominaActual) return;
    abrirMesNomina(anioNominaActual, mes);
};

function cargarNomina() {
    if (!anioNominaActual || !mesNominaActual) return;
    const params = new URLSearchParams({ anio: anioNominaActual, mes: mesNominaActual });
    if (tipoNominaActual) params.set('tipo_nomina', tipoNominaActual);
    const tbody = document.getElementById('tbodyNomina');
    if (tbody) tbody.innerHTML = '<tr><td colspan="27" style="padding:28px;color:#94a3b8;">Cargando planilla...</td></tr>';

    fetch('/nomina?' + params.toString())
        .then(r => r.json())
        .then(resp => {
            const rows = resp.trabajadores || [];
            if (!tbody) return;
            filasNomina = rows;
            const totalGeneral = rows.reduce((acc, r) => acc + (parseFloat(r.total_asignacion) || 0), 0);

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="27" style="padding:36px;color:#94a3b8;">' +
                    'No hay trabajadores vigentes en esta planilla. Importe la nómina del mes o verifique los trabajadores registrados.</td></tr>';
            } else {
                let html = '';
                for (let i = 0; i < rows.length; i++) {
                    const t = rows[i];
                    const acciones =
                        '<button type="button" class="btn-accion btn-accion-edit" data-editar="' + i + '" title="Editar registro"><i class="fas fa-pen"></i></button>' +
                        '<button type="button" class="btn-accion btn-accion-del" data-eliminar="' + i + '" title="Eliminar de la nómina"><i class="fas fa-trash"></i></button>';
                    html += '<tr>' +
                        '<td>' + acciones + '</td>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + (t.cedula || '') + '</td>' +
                        '<td style="text-align:left;font-weight:600;">' + (t.nombre_completo || '') + '</td>' +
                        '<td>' + (t.genero || '') + '</td>' +
                        '<td>' + t.numero_hijos + '</td>' +
                        '<td>' + t.hijos_discapacidad + '</td>' +
                        '<td>' + (t.nivel_educativo_texto || '') + '</td>' +
                        '<td class="sep-col">' + (t.nivel_instruccion || '') + '</td>' +
                        '<td>' + (t.fecha_ingreso || '') + '</td>' +
                        '<td>' + t.anos_servicio_inst + '</td>' +
                        '<td>' + t.anos_servicio_externo + '</td>' +
                        '<td>' + t.total_anos_servicio + '</td>' +
                        '<td>' + fmt(t.porcentaje_antiguedad) + '</td>' +
                        '<td>' + (t.codigo_prima_resp || '') + '</td>' +
                        '<td>' + (t.cargo || '') + '</td>' +
                        '<td class="sep-col">' + (t.grado_cargo || '') + '</td>' +
                        '<td>' + fmt(t.sueldo_base) + '</td>' +
                        '<td>' + fmt(t.prima_familiar) + '</td>' +
                        '<td>' + fmt(t.prima_hijo) + '</td>' +
                        '<td>' + fmt(t.prima_hijos_discapacidad) + '</td>' +
                        '<td>' + fmt(t.prima_actividad_universitaria) + '</td>' +
                        '<td>' + fmt(t.prima_profesionalizacion) + '</td>' +
                        '<td>' + fmt(t.prima_responsabilidad) + '</td>' +
                        '<td>' + fmt(t.complemento_prima_responsabilidad) + '</td>' +
                        '<td>' + fmt(t.prima_antiguedad) + '</td>' +
                        '<td><strong>' + fmt(t.total_asignacion) + '</strong></td>' +
                        '</tr>';
                }
                tbody.innerHTML = html;
            }

            document.getElementById('totalNomina').textContent = rows.length;
            document.getElementById('totalGeneralNomina').textContent = fmtBsNom(totalGeneral);
            document.getElementById('estadoPlanilla').textContent = resp.nomina_id
                ? 'Cargada (' + (resp.nomina_codigo || '') + ') · ' + (resp.nomina_estado || 'borrador')
                : 'Sin planilla cargada para este mes';
            document.getElementById('estadoPlanilla').style.color = resp.nomina_id ? '#15803d' : '#b45309';
        })
        .catch(err => {
            console.error(err);
            if (tbody) tbody.innerHTML = '<tr><td colspan="27" style="padding:28px;color:#dc2626;">Error al cargar la planilla.</td></tr>';
        });
}

document.addEventListener('DOMContentLoaded', function() { cargarAniosNomina(); });

const observerNomina = new MutationObserver(function(mutations) {
    mutations.forEach(function(m) {
        if (m.target.id === 'nomina' && m.target.classList.contains('active')) {
            if (anioNominaActual && mesNominaActual) cargarNomina();
            else if (anioNominaActual) abrirAnioNomina(anioNominaActual);
            else cargarAniosNomina();
        }
    });
});
const secNomina = document.getElementById('nomina');
if (secNomina) observerNomina.observe(secNomina, { attributes: true, attributeFilter: ['class'] });

document.getElementById('btnCancelarImportar')?.addEventListener('click', () => document.getElementById('modalImportarNomina').style.display = 'none');
document.getElementById('btnCerrarImportacion')?.addEventListener('click', () => {
    document.getElementById('modalImportarNomina').style.display = 'none';
    document.getElementById('formImportarNomina').reset();
    document.getElementById('resultadoImportacion').style.display = 'none';
});

document.getElementById('formImportarNomina')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = document.getElementById('btnSubmitImportar');
    const resultado = document.getElementById('resultadoImportacion');
    btn.disabled = true; btn.textContent = 'Importando...';
    try {
        mostrarCargando('Importando nómina...');
        const resp = await fetch('/importar/nomina', {
            method: 'POST', body: formData,
            headers: { 'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        const data = await resp.json();
        if (!resp.ok) throw data;
        resultado.style.display = 'block';
        resultado.style.background = '#f0fdf4';
        resultado.style.borderColor = '#bbf7d0';
        let html = '<strong>Importación completada correctamente.</strong><br><br>' + (data.mensaje || '');
        if (data.datos && data.datos.errores && data.datos.errores.length) {
            html += '<ul style="margin:8px 0 0 0;padding-left:20px;font-size:0.8rem;color:#b91c1c;">';
            data.datos.errores.forEach(function(e) {
                html += '<li>' + (e || '').replace(/[<>&"']/g, function(m) {
                    return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;',"'":'&#39;'}[m] || m;
                }) + '</li>';
            });
            html += '</ul>';
        }
        resultado.innerHTML = html;
        document.getElementById('btnCerrarImportacion').style.display = 'inline-block';
        mostrarToast('Importación completada correctamente', 'success');
        await cargarAniosNomina();
        if (data.datos && data.datos.anio) {
            abrirMesNomina(String(data.datos.anio), data.datos.mes || '');
            if (!data.datos.mes) abrirAnioNomina(String(data.datos.anio));
        }
    } catch (err) {
        resultado.style.display = 'block';
        resultado.style.background = '#fef2f2';
        resultado.style.borderColor = '#fecaca';
        resultado.innerHTML = '<strong style="color:#dc2626;">' + (err.mensaje || err.message || 'Error al importar') + '</strong>';
        document.getElementById('btnCerrarImportacion').style.display = 'inline-block';
        mostrarToast(err.mensaje || 'Error al importar', 'error');
    } finally {
        ocultarCargando();
        btn.disabled = false; btn.textContent = 'Importar';
    }
});
</script>
