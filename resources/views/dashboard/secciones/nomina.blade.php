{{-- nomina.blade.php - Rediseñado con formato oficial de 3 secciones (Azul/Celeste/Verde) y 3 hojas (ADM/DOC/OBREROS) --}}
<style>
.nomina-tabs { display: flex; gap: 4px; margin-bottom: 16px; background: #f1f5f9; border-radius: 10px; padding: 3px; overflow-x: auto; }
.nomina-tab { padding: 8px 20px; border: none; background: transparent; border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.nomina-tab.active { background: white; color: #1a365d; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.nomina-tab:hover:not(.active) { color: #1e3a8a; }
.table-wrapper-nomina { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; background: white; }
.table-wrapper-nomina table { min-width: 2800px; border-collapse: collapse; font-size: 0.72rem; }
.table-wrapper-nomina th { position: sticky; top: 0; z-index: 2; padding: 6px 5px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.15); }
.table-wrapper-nomina td { padding: 5px 5px; white-space: nowrap; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f8fafc; }
.section-azul th { background: #1e3a8a; color: white; }
.section-celeste th { background: #0284c7; color: white; }
.section-verde th { background: #15803d; color: white; }
.sep-col { border-left: 3px solid #e2e8f0 !important; }
.sec-label { font-size: 0.6rem; font-weight: 700; text-align: center; letter-spacing: 0.5px; padding: 3px 5px !important; }
.sec-label-azul { background: #dbeafe; color: #1e3a8a; }
.sec-label-celeste { background: #e0f2fe; color: #0284c7; }
.sec-label-verde { background: #dcfce7; color: #15803d; }
body.dark-mode .table-wrapper-nomina { border-color: #334155; }
body.dark-mode .table-wrapper-nomina td { border-bottom-color: #334155; border-right-color: #1e293b; }
body.dark-mode .sep-col { border-left-color: #334155 !important; }
body.dark-mode .sec-label-azul { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .sec-label-celeste { background: #0c4a6e; color: #7dd3fc; }
body.dark-mode .sec-label-verde { background: #14532d; color: #86efac; }
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Nómina Mensual</h1>
        <p>Gestión de planilla de nómina institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-outline" onclick="abrirModalImportarNomina()">
            <i class="fas fa-file-import"></i> Importar
        </button>
        <button type="button" class="btn-primary-dark" onclick="exportarNomina()">
            <i class="fas fa-file-excel"></i> Exportar Planilla
        </button>
    </div>
</header>

<div class="nomina-tabs" id="nominaTabs">
    <button class="nomina-tab active" data-tipo="">Todas</button>
    <button class="nomina-tab" data-tipo="ADM">Administrativos</button>
    <button class="nomina-tab" data-tipo="DOC">Docentes</button>
    <button class="nomina-tab" data-tipo="OBREROS">Obreros</button>
</div>

<div class="filters-bar-card">
    <div class="filter-group">
        <label>PERÍODO</label>
        <select id="filtroPeriodoNomina" onchange="cargarNomina()">
            <option value="">Mes actual</option>
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
                <th class="section-azul" colspan="8">DATOS PERSONALES BÁSICOS</th>
                <th class="section-celeste" colspan="8">DATOS LABORALES Y ANTIGÜEDAD</th>
                <th class="section-verde" colspan="10">REMUNERACIÓN Y PRIMAS / ASIGNACIONES</th>
            </tr>
            <tr>
                <!-- AZUL -->
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

<div id="modalImportarNomina" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:560px;">
        <h3 style="text-align:center;"><i class="fas fa-file-import"></i> Importar Nómina</h3>
        <p style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:16px;">
            Suba el archivo Excel con formato oficial (hojas ADM / DOC / OBREROS). Los trabajadores serán registrados o actualizados automáticamente.
        </p>
        <form id="formImportarNomina">
            @csrf
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

<script>
let tipoNominaActual = '';

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
    const tipo = tipoNominaActual || '';
    fetch('/exportar/nomina' + (tipo ? '?tipo_nomina=' + tipo : ''))
        .then(r => { if (!r.ok) throw Error(); return r.blob(); })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'NOMINA_' + new Date().toISOString().slice(0,7) + '.xlsx';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (overlay) overlay.classList.remove('active');
        })
        .catch(() => { if (overlay) overlay.classList.remove('active'); mostrarToast('Error al generar la planilla','error'); });
};

window.abrirModalImportarNomina = function() {
    document.getElementById('modalImportarNomina').style.display = 'flex';
    document.getElementById('formImportarNomina').reset();
    document.getElementById('resultadoImportacion').style.display = 'none';
};

function fmt(v) { return (v || 0).toFixed(2).replace('.', ','); }

function cargarNomina() {
    const periodo = document.getElementById('filtroPeriodoNomina')?.value || '';
    let url = '/nomina?periodo=' + periodo;
    if (tipoNominaActual) url += '&tipo_nomina=' + tipoNominaActual;
    fetch(url)
        .then(r => r.json())
        .then(resp => {
            const rows = resp.trabajadores || resp;
            const tbody = document.getElementById('tbodyNomina');
            if (!tbody) return;
            let html = '';
            for (let i = 0; i < rows.length; i++) {
                const t = rows[i];
                const tiene = t.tiene_nomina;
                const cls = tiene ? '' : ' style="opacity:0.55;"';
                html += '<tr' + cls + '>' +
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

function poblarPeriodos() {
    const sel = document.getElementById('filtroPeriodoNomina');
    if (!sel) return;
    const hoy = new Date();
    for (let m = 0; m < 12; m++) {
        const d = new Date(hoy.getFullYear(), hoy.getMonth() - m, 1);
        const val = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
        const lbl = d.toLocaleString('es', { month: 'long', year: 'numeric' });
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = lbl.charAt(0).toUpperCase() + lbl.slice(1);
        sel.appendChild(opt);
    }
}

document.addEventListener('DOMContentLoaded', function() { poblarPeriodos(); cargarNomina(); });

const observerNomina = new MutationObserver(function(mutations) {
    mutations.forEach(function(m) {
        if (m.target.id === 'nomina' && m.target.classList.contains('active')) cargarNomina();
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
        let html = '<strong>' + data.mensaje + '</strong>';
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
        mostrarToast(data.mensaje, 'success');
        cargarNomina();
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
