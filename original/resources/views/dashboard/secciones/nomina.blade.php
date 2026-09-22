{{-- nomina.blade.php - Rediseñado con formato oficial de 3 secciones (Azul/Celeste/Verde) y 3 hojas (ADM/DOC/OBREROS) --}}
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
.nomina-anios-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 16px; }
.nomina-anio-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 12px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.nomina-anio-card:hover { transform: translateY(-3px); border-color: #bfdbfe; box-shadow: 0 8px 20px rgba(37,99,235,0.12); }
.nomina-anio-card .na-folder { width: 46px; height: 46px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
.nomina-anio-card .na-anio { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.nomina-anio-card .na-count { font-size: 0.75rem; color: #64748b; font-weight: 600; }
.nomina-anio-header { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; }
.nomina-anio-header h2 { font-size: 1.1rem; color: #0f172a; margin: 0; }
.nomina-anio-header .btn-back { border: none; background: #f1f5f9; color: #1e3a8a; border-radius: 8px; padding: 8px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.nomina-anio-header .btn-back:hover { background: #e2e8f0; }
body.dark-mode .nomina-anio-card { background: #1e293b; border-color: #334155; }
body.dark-mode .nomina-anio-card:hover { border-color: #3b82f6; box-shadow: 0 8px 20px rgba(59,130,246,0.2); }
body.dark-mode .nomina-anio-card .na-folder { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .nomina-anio-card .na-anio { color: #f1f5f9; }
body.dark-mode .nomina-anio-card .na-count { color: #94a3b8; }
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
.modal-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Nómina</h1>
        <p>Gestión de planilla de nómina institucional organizada por años.</p>
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

<div id="vistaAniosNomina">
    <div class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <div class="filter-group">
            <label>AÑOS DISPONIBLES</label>
            <p style="margin:2px 0 0;font-size:0.75rem;color:#64748b;">Seleccione un año para ver su nómina.</p>
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

<div id="vistaTablaNomina" style="display:none;">
    <div class="nomina-anio-header">
        <h2><i class="fas fa-folder-open"></i> NÓMINA &gt; <span id="anioTitulo">2026</span></h2>
        <button type="button" class="btn-back" onclick="volverAnios()">←</button>
    </div>

    <div class="nomina-tabs" id="nominaTabs">
        <button class="nomina-tab active" data-tipo="">Todas</button>
        <button class="nomina-tab" data-tipo="ADM">Administrativos</button>
        <button class="nomina-tab" data-tipo="DOC">Docentes</button>
        <button class="nomina-tab" data-tipo="OBREROS">Obreros</button>
    </div>

    <div class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <div class="filter-group">
            <label>AÑO</label>
            <select id="filtroAnioNomina" onchange="cambiarAnioNomina(this.value)">
                <option value=""></option>
            </select>
        </div>
        <div class="total-badge-card" style="margin-left: auto;">
            <div>
                <p>TOTAL TRABAJADORES</p>
                <h2 id="totalNomina">0</h2>
            </div>
            <i class="fas fa-users icon-bg"></i>
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

<div id="modalImportarNomina" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:560px;">
        <h3 style="text-align:center;"><i class="fas fa-file-import"></i> Importar Nómina</h3>
        <p style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:16px;">
            Suba el archivo Excel con formato oficial (hojas ADM / DOC / OBREROS) y seleccione el año de la nómina.
        </p>
        <form id="formImportarNomina">
            @csrf
            <div class="input-group">
                <label>AÑO DE LA NÓMINA</label>
                <select name="anio" required>
                    <option value="">Seleccione el año</option>
                </select>
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
    </div>
</div>

<div id="modalEditarNomina" class="modal-overlay">
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

<div id="modalEliminarNomina" class="modal-overlay">
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
let filasNomina = [];
let indiceEliminarNomina = null;

document.querySelectorAll('.nomina-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.nomina-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        tipoNominaActual = this.dataset.tipo;
        cargarNomina();
    });
});

window.exportarNomina = function() {
    const overlay = document.getElementById('loading-overlay');
    const texto = document.getElementById('loadingText');
    if (overlay) overlay.classList.add('active');
    if (texto) texto.textContent = 'Generando planilla de nómina...';
    const params = new URLSearchParams();
    if (tipoNominaActual) params.set('tipo_nomina', tipoNominaActual);
    if (anioNominaActual) params.set('anio', anioNominaActual);
    const qs = params.toString();
    fetch('/exportar/nomina' + (qs ? '?' + qs : ''))
        .then(r => { if (!r.ok) throw Error(); return r.blob(); })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'NOMINA_' + (anioNominaActual || new Date().getFullYear()) + '.xlsx';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (overlay) overlay.classList.remove('active');
        })
        .catch(() => { if (overlay) overlay.classList.remove('active'); mostrarToast('Error al generar la planilla','error'); });
};

window.abrirModalImportarNomina = function() {
    const sel = document.getElementById('modalImportarNomina').querySelector('select[name="anio"]');
    if (sel && sel.options.length <= 1) poblarSelectorAnios(sel);
    document.getElementById('modalImportarNomina').style.display = 'flex';
    document.getElementById('formImportarNomina').reset();
    if (sel) sel.value = '';
    document.getElementById('resultadoImportacion').style.display = 'none';
};

function fmt(v) { return (v || 0).toFixed(2).replace('.', ','); }

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
    document.getElementById('eliminarNominaInfo').innerHTML =
        '¿Seguro que desea eliminar a <strong>' + (t.nombre_completo || '') + '</strong><br>(C.I. ' + (t.cedula || '') + ') de la nómina correspondiente al año <strong>' + anioNominaActual + '</strong>?<br><br>Esta acción no se puede deshacer.';
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
            const anios = (resp && resp.anios) || [];
            const grid = document.getElementById('nominaAniosGrid');
            const select = document.getElementById('filtroAnioNomina');
            if (grid) {
                let html = '';
                let total = 0;
                anios.forEach(a => {
                    total += parseInt(a.total_trabajadores || 0, 10);
                    html += '<div class="nomina-anio-card" onclick="abrirAnioNomina(\'' + a.anio + '\')">' +
                        '<div class="na-folder"><i class="fas fa-folder"></i></div>' +
                        '<div class="na-anio">' + a.anio + '</div>' +
                        '<div class="na-count">' + a.total_trabajadores + ' trabajadores</div>' +
                        '</div>';
                });
                if (!html) {
                    html = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px 20px;border:1px dashed #cbd5e1;border-radius:12px;">' +
                        'Aún no hay nóminas registradas. Presione <strong>Importar Nómina</strong> para comenzar.</div>';
                }
                grid.innerHTML = html;
                const badge = document.getElementById('totalTrabajadoresAnios');
                if (badge) badge.textContent = total;
            }
            if (select) {
                let optHtml = '<option value="">Seleccione el año</option>';
                anios.forEach(a => {
                    optHtml += '<option value="' + a.anio + '">' + a.anio + '</option>';
                });
                select.innerHTML = optHtml;
                if (anioNominaActual) select.value = anioNominaActual;
            }
        })
        .catch(err => console.error(err));
};

function poblarSelectorAnios(sel) {
    // Rango completo: 1900 hasta el año actual (se actualiza solo cada año).
    const anioActual = new Date().getFullYear();
    const anios = [];
    for (let anio = 1900; anio <= anioActual; anio++) {
        anios.push(anio);
    }
    anios.sort((a, b) => b - a);
    let html = '<option value="">Seleccione el año</option>';
    anios.forEach(a => { html += '<option value="' + a + '">' + a + '</option>'; });
    sel.innerHTML = html;
}

window.abrirAnioNomina = function(anio) {
    anioNominaActual = anio;
    document.getElementById('anioTitulo').textContent = anio;
    document.getElementById('vistaAniosNomina').style.display = 'none';
    document.getElementById('vistaTablaNomina').style.display = 'block';
    const select = document.getElementById('filtroAnioNomina');
    if (select) select.value = anio;
    tipoNominaActual = '';
    document.querySelectorAll('.nomina-tab').forEach(t => {
        t.classList.remove('active');
        if (!t.dataset.tipo) t.classList.add('active');
    });
    cargarNomina();
};

window.volverAnios = function() {
    anioNominaActual = '';
    document.getElementById('vistaTablaNomina').style.display = 'none';
    document.getElementById('vistaAniosNomina').style.display = 'block';
    cargarAniosNomina();
};

window.cambiarAnioNomina = function(anio) {
    if (anio) abrirAnioNomina(anio);
};

function cargarNomina() {
    let url = '/nomina';
    const params = new URLSearchParams();
    if (anioNominaActual) params.set('anio', anioNominaActual);
    if (tipoNominaActual) params.set('tipo_nomina', tipoNominaActual);
    const qs = params.toString();
    if (qs) url += '?' + qs;
    fetch(url)
        .then(r => r.json())
        .then(resp => {
            const rows = resp.trabajadores || resp;
            const tbody = document.getElementById('tbodyNomina');
            if (!tbody) return;
            filasNomina = rows;
            let html = '';
            for (let i = 0; i < rows.length; i++) {
                const t = rows[i];
                const tiene = t.tiene_nomina;
                const cls = tiene ? '' : ' style="opacity:0.55;"';
                const acciones = tiene
                    ? '<button type="button" class="btn-accion btn-accion-edit" data-editar="' + i + '" title="Editar registro"><i class="fas fa-pen"></i></button>' +
                      '<button type="button" class="btn-accion btn-accion-del" data-eliminar="' + i + '" title="Eliminar de la nómina"><i class="fas fa-trash"></i></button>'
                    : '<span style="color:#cbd5e1;">—</span>';
                html += '<tr' + cls + '>' +
                    '<td>' + acciones + '</td>' +
                    // AZUL
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + (t.cedula || '') + '</td>' +
                    '<td>' + (t.nombre_completo || '') + '</td>' +
                    '<td>' + (t.genero || '') + '</td>' +
                    '<td>' + t.numero_hijos + '</td>' +
                    '<td>' + t.hijos_discapacidad + '</td>' +
                    '<td>' + (t.nivel_educativo_texto || '') + '</td>' +
                    '<td class="sep-col">' + (t.nivel_instruccion || '') + '</td>' +
                    // CELESTE
                    '<td>' + (t.fecha_ingreso || '') + '</td>' +
                    '<td>' + t.anos_servicio_inst + '</td>' +
                    '<td>' + t.anos_servicio_externo + '</td>' +
                    '<td>' + t.total_anos_servicio + '</td>' +
                    '<td>' + fmt(t.porcentaje_antiguedad) + '</td>' +
                    '<td>' + (t.codigo_prima_resp || '') + '</td>' +
                    '<td>' + (t.cargo || '') + '</td>' +
                    '<td class="sep-col">' + (t.grado_cargo || '') + '</td>' +
                    // VERDE
                    '<td>' + (tiene ? fmt(t.sueldo_base) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_familiar) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_hijo) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_hijos_discapacidad) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_actividad_universitaria) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_profesionalizacion) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_responsabilidad) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.complemento_prima_responsabilidad) : '-') + '</td>' +
                    '<td>' + (tiene ? fmt(t.prima_antiguedad) : '-') + '</td>' +
                    '<td><strong>' + (tiene ? fmt(t.total_asignacion) : '-') + '</strong></td>' +
                    '</tr>';
            }
            tbody.innerHTML = html;
            document.getElementById('totalNomina').textContent = rows.length;
        })
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', function() { cargarAniosNomina(); });

const observerNomina = new MutationObserver(function(mutations) {
    mutations.forEach(function(m) {
        if (m.target.id === 'nomina' && m.target.classList.contains('active')) {
            if (anioNominaActual) cargarNomina();
            else cargarAniosNomina();
        }
    });
});
const secNomina = document.getElementById('nomina');
if (secNomina) observerNomina.observe(secNomina, { attributes: true, attributeFilter: ['class'] });

document.getElementById('btnCancelarImportar')?.addEventListener('click', () => document.getElementById('modalImportarNomina').style.display = 'none');

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
        mostrarToast('Importación completada correctamente', 'success');
        cargarAniosNomina();
        const anioImport = data.datos && data.datos.anio ? String(data.datos.anio) : null;
        if (anioImport) abrirAnioNomina(anioImport);
    } catch (err) {
        resultado.style.display = 'block';
        resultado.style.background = '#fef2f2';
        resultado.style.borderColor = '#fecaca';
        resultado.innerHTML = '<strong style="color:#dc2626;">' + (err.mensaje || err.message || 'Error al importar') + '</strong>';
        mostrarToast(err.mensaje || 'Error al importar', 'error');
    } finally {
        ocultarCargando();
        btn.disabled = false; btn.textContent = 'Importar';
    }
});
</script>
