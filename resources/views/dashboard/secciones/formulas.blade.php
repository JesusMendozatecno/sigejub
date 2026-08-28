<style>
.formula-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;}
.formula-card{background:white;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);overflow:hidden;transition:all 0.25s ease;cursor:pointer;}
.formula-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,35,102,0.1);border-color:#dbeafe;}
.formula-card-header{padding:16px 18px 12px;display:flex;align-items:flex-start;justify-content:space-between;border-bottom:1px solid #f1f5f9;}
.formula-card-header .fc-icon{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center;color:#2563eb;font-size:1rem;}
.formula-card-header .fc-titulo{flex:1;margin-left:12px;}
.formula-card-header .fc-titulo h4{font-size:0.88rem;color:#0f172a;margin:0;line-height:1.3;}
.formula-card-header .fc-titulo span{font-size:0.68rem;color:#94a3b8;font-family:monospace;}
.formula-card-body{padding:14px 18px;}
.formula-card-body .fc-formula{background:#f8fafc;border-radius:8px;padding:10px 14px;font-family:'Courier New',monospace;font-size:0.8rem;color:#1e3a8a;font-weight:600;margin-bottom:10px;border:1px solid #e2e8f0;line-height:1.5;}
.formula-card-body .fc-desc{font-size:0.78rem;color:#64748b;line-height:1.5;margin:0;}
.formula-card-footer{padding:10px 18px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;}
.formula-card-footer .fc-badge{font-size:0.68rem;font-weight:700;padding:3px 10px;border-radius:10px;}
.fc-badge-activa{background:#dcfce7;color:#166534;}
.fc-badge-inactiva{background:#fef2f2;color:#991b1b;}

.formula-detalle-layout{display:flex;flex-direction:column;gap:16px;}
.formula-detalle-card{background:white;border-radius:14px;padding:20px 24px;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
.formula-detalle-card h3{font-size:0.9rem;font-weight:700;color:#0f172a;margin:0 0 12px;display:flex;align-items:center;gap:8px;}
.formula-detalle-card h3 i{color:#2563eb;}
.formula-var-row{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;}
.formula-var-row:last-child{border-bottom:none;}
.formula-var-row .fv-var{font-size:0.82rem;font-weight:700;color:#1e3a8a;font-family:monospace;min-width:180px;background:#eff6ff;padding:4px 10px;border-radius:6px;height:fit-content;}
.formula-var-row .fv-desc{font-size:0.8rem;color:#475569;line-height:1.5;}

.formula-btn{padding:8px 14px;border-radius:8px;font-size:0.78rem;font-weight:600;border:none;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:5px;}
.formula-btn-edit{background:#eff6ff;color:#1e40af;}
.formula-btn-edit:hover{background:#dbeafe;}
.formula-btn-delete{background:#fef2f2;color:#dc2626;}
.formula-btn-delete:hover{background:#fee2e2;}
.formula-btn-toggle{background:#f0fdf4;color:#166534;}
.formula-btn-toggle:hover{background:#dcfce7;}
.formula-btn-toggle.off{background:#fef2f2;color:#991b1b;}

body.dark-mode .formula-card{background:#1e293b;border-color:#334155;}
body.dark-mode .formula-card:hover{border-color:#3b82f6;}
body.dark-mode .formula-card-header{border-bottom-color:#334155;}
body.dark-mode .formula-card-header .fc-titulo h4{color:#f1f5f9;}
body.dark-mode .formula-card-body .fc-formula{background:#0f172a;border-color:#334155;color:#93c5fd;}
body.dark-mode .formula-card-body .fc-desc{color:#94a3b8;}
body.dark-mode .formula-card-footer{border-top-color:#334155;}
body.dark-mode .formula-detalle-card{background:#1e293b;border-color:#334155;}
body.dark-mode .formula-detalle-card h3{color:#f1f5f9;}
body.dark-mode .formula-var-row{border-bottom-color:#334155;}
body.dark-mode .formula-var-row .fv-desc{color:#94a3b8;}
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Fórmulas de <span class="text-blue-accent">Cálculo</span></h1>
        <p>Consulte y administre las fórmulas utilizadas para el cálculo de prestaciones sociales.</p>
    </div>
    <div class="header-actions" id="formulasHeaderActions" style="display:none;">
        <button type="button" class="btn-primary-dark" id="btnCrearFormula">
            <i class="fas fa-plus" size="20"></i> Nueva Fórmula
        </button>
    </div>
</header>

<div class="search-filter-bar">
    <div class="search-wrapper">
        <i class="fas fa-search" size="16"></i>
        <input type="text" id="buscadorFormulas" placeholder="Buscar por nombre o código...">
    </div>
    <div class="filter-group-sm">
        <select id="filtroEstadoFormula">
            <option value="">Todas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
        </select>
    </div>
</div>

<div id="formulasVistaCards">
    <div class="formula-card-grid" id="formulasGrid">
        <p class="empty-state">Cargando fórmulas...</p>
    </div>
</div>

<div id="formulaDetalleVista" class="hidden">
    <div style="margin-bottom:14px;">
        <button class="btn-volver" id="btnVolverFormulas"><i class="fas fa-arrow-left"></i> Volver</button>
    </div>
    <div class="formula-detalle-layout" id="formulaDetalleContent"></div>
</div>

<div class="table-footer">
    <span id="formulasCounter">Mostrando 0 fórmulas</span>
</div>

<div id="modalFormula" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:600px;max-height:90vh;overflow-y:auto;">
        <h3 style="text-align:center;" id="tituloModalFormula"><i class="fas fa-function"></i> Nueva Fórmula</h3>
        <form id="formFormula">
            @csrf
            <input type="hidden" name="formula_id" id="formulaId">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>NOMBRE *</label>
                    <input type="text" name="nombre" id="formulaNombre" placeholder="Ej: Prestación de Antigüedad" required>
                </div>
                <div class="input-group">
                    <label>CÓDIGO *</label>
                    <input type="text" name="codigo" id="formulaCodigo" placeholder="Ej: PRESTACION_ANTIGUEDAD" required pattern="[A-Za-z0-9_\-]+" onkeypress="if(!/[A-Za-z0-9_\-]/.test(event.key))event.preventDefault()">
                </div>
            </div>
            <div class="input-group">
                <label>DESCRIPCIÓN</label>
                <textarea name="descripcion" id="formulaDescripcion" rows="2" placeholder="Descripción de la fórmula..." maxlength="1000"></textarea>
            </div>
            <div class="input-group">
                <label>FÓRMULA MATEMÁTICA</label>
                <input type="text" name="formula_matematica" id="formulaMatematica" placeholder="Ej: (Sueldo Base + Total Primas) × % × Años" style="font-family:monospace;">
            </div>
            <div class="input-group">
                <label>VARIABLES (formato: variable = descripción, una por línea)</label>
                <textarea name="variables_text" id="formulaVariables" rows="4" placeholder="sueldo_base = Salario base mensual&#10;anios_servicio = Total años de servicio"></textarea>
            </div>
            <div class="input-group">
                <label>CONCEPTOS (uno por línea)</label>
                <textarea name="conceptos_text" id="formulaConceptos" rows="3" placeholder="Sueldo Base&#10;Primas salariales&#10;Años de servicio"></textarea>
            </div>
            <div class="input-group">
                <label>EJEMPLO DE CÁLCULO</label>
                <textarea name="ejemplo_calculo" id="formulaEjemplo" rows="3" placeholder="Ejemplo paso a paso del cálculo..."></textarea>
            </div>
            <div class="input-group">
                <label>OBSERVACIONES</label>
                <textarea name="observaciones" id="formulaObservaciones" rows="2" placeholder="Notas adicionales..."></textarea>
            </div>
            <div class="input-group">
                <label>ESTADO</label>
                <select name="activo" id="formulaActivo">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarFormula">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitFormula">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalConfirmarEliminarFormula" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:center;">
        <i class="fas fa-triangle-exclamation" style="font-size:2.5rem;color:#f59e0b;margin-bottom:12px;"></i>
        <h3>Eliminar Fórmula</h3>
        <p style="color:#64748b;font-size:0.85rem;margin-bottom:16px;">¿Está seguro que desea eliminar esta fórmula?</p>
        <input type="hidden" id="formulaEliminarId">
        <div style="display:flex;gap:8px;justify-content:center;">
            <button class="btn-cancel" id="btnCancelarEliminarFormula">Cancelar</button>
            <button class="btn-submit" style="background:#dc2626;" id="btnConfirmarEliminarFormula">Eliminar</button>
        </div>
    </div>
</div>

<script>
(function(){
    let currentPage = 1;
    let vistaDetalle = false;

    async function cargarFormulas(page) {
        page = page || 1;
        const search = document.getElementById('buscadorFormulas')?.value || '';
        const estado = document.getElementById('filtroEstadoFormula')?.value || '';
        let url = '/formulas-prestaciones?page=' + page;
        if (search) url += '&search=' + encodeURIComponent(search);
        if (estado !== '') url += '&solo_activos=' + estado;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            const grid = document.getElementById('formulasGrid');
            if (!data.data || data.data.length === 0) {
                grid.innerHTML = '<p class="empty-state">No hay fórmulas registradas.</p>';
                document.getElementById('formulasCounter').textContent = 'Mostrando 0 fórmulas';
                return;
            }
            grid.innerHTML = data.data.map(function(f) {
                const badge = f.activo
                    ? '<span class="fc-badge fc-badge-activa">Activa</span>'
                    : '<span class="fc-badge fc-badge-inactiva">Inactiva</span>';
                const formula = f.formula_matematica
                    ? '<div class="fc-formula">' + escaparHTML(f.formula_matematica) + '</div>'
                    : '';
                const desc = f.descripcion
                    ? '<p class="fc-desc">' + escaparHTML(f.descripcion).substring(0, 120) + (f.descripcion.length > 120 ? '...' : '') + '</p>'
                    : '';
                return '<div class="formula-card" onclick="verDetalleFormula(' + f.id + ')">' +
                    '<div class="formula-card-header">' +
                    '<div class="fc-icon"><i class="fas fa-square-root-variable"></i></div>' +
                    '<div class="fc-titulo"><h4>' + escaparHTML(f.nombre) + '</h4><span>' + escaparHTML(f.codigo) + '</span></div>' +
                    '</div>' +
                    '<div class="formula-card-body">' + formula + desc + '</div>' +
                    '<div class="formula-card-footer">' + badge +
                    '<div style="display:flex;gap:4px;">' +
                    '<button class="formula-btn formula-btn-edit" onclick="event.stopPropagation();editarFormula(' + f.id + ')"><i class="fas fa-pen"></i></button>' +
                    '<button class="formula-btn formula-btn-delete" onclick="event.stopPropagation();confirmarEliminarFormula(' + f.id + ')"><i class="fas fa-trash"></i></button>' +
                    '</div></div></div>';
            }).join('');
            document.getElementById('formulasCounter').textContent = 'Mostrando ' + data.data.length + ' de ' + data.total + ' fórmulas';
            currentPage = data.current_page;
        } catch(e) {
            console.error('Error al cargar fórmulas:', e);
        }
    }

    window.verDetalleFormula = async function(id) {
        try {
            mostrarCargando('Cargando fórmula...');
            const resp = await fetch('/formulas-prestaciones/' + id);
            const f = await resp.json();
            let html = '';

            html += '<div class="formula-detalle-card">';
            html += '<h3><i class="fas fa-square-root-variable"></i> ' + escaparHTML(f.nombre) + ' <span style="font-size:0.7rem;color:#94a3b8;font-family:monospace;margin-left:8px;">' + escaparHTML(f.codigo) + '</span></h3>';
            if (f.descripcion) html += '<p style="font-size:0.82rem;color:#475569;margin:0 0 12px;line-height:1.5;">' + escaparHTML(f.descripcion) + '</p>';
            if (f.formula_matematica) {
                html += '<div style="background:#eff6ff;border-radius:10px;padding:14px 18px;border:1px solid #bfdbfe;margin-bottom:14px;">';
                html += '<div style="font-size:0.7rem;font-weight:700;color:#1e40af;text-transform:uppercase;margin-bottom:6px;">Fórmula Matemática</div>';
                html += '<div style="font-family:monospace;font-size:0.95rem;color:#1e3a8a;font-weight:700;">' + escaparHTML(f.formula_matematica) + '</div>';
                html += '</div>';
            }
            html += '</div>';

            if (f.explicacion_variables && Array.isArray(f.explicacion_variables) && f.explicacion_variables.length > 0) {
                html += '<div class="formula-detalle-card">';
                html += '<h3><i class="fas fa-code"></i> Variables y Explicación</h3>';
                f.explicacion_variables.forEach(function(v) {
                    html += '<div class="formula-var-row">';
                    html += '<div class="fv-var">' + escaparHTML(v.variable || '') + '</div>';
                    html += '<div class="fv-desc">' + escaparHTML(v.explicacion || '') + '</div>';
                    html += '</div>';
                });
                html += '</div>';
            }

            if (f.ejemplo_calculo) {
                html += '<div class="formula-detalle-card">';
                html += '<h3><i class="fas fa-lightbulb"></i> Ejemplo de Cálculo</h3>';
                html += '<pre style="background:#f8fafc;border-radius:8px;padding:14px;font-size:0.82rem;color:#334155;line-height:1.6;border:1px solid #e2e8f0;white-space:pre-wrap;margin:0;">' + escaparHTML(f.ejemplo_calculo) + '</pre>';
                html += '</div>';
            }

            if (f.observaciones) {
                html += '<div class="formula-detalle-card">';
                html += '<h3><i class="fas fa-info-circle"></i> Observaciones</h3>';
                html += '<p style="font-size:0.82rem;color:#475569;margin:0;line-height:1.5;">' + escaparHTML(f.observaciones) + '</p>';
                html += '</div>';
            }

            document.getElementById('formulaDetalleContent').innerHTML = html;
            document.getElementById('formulasVistaCards').classList.add('hidden');
            document.getElementById('formulaDetalleVista').classList.remove('hidden');
            vistaDetalle = true;
        } catch(e) {
            mostrarToast('Error al cargar la fórmula.', 'error');
        } finally {
            ocultarCargando();
        }
    };

    document.getElementById('btnVolverFormulas')?.addEventListener('click', function() {
        document.getElementById('formulasVistaCards').classList.remove('hidden');
        document.getElementById('formulaDetalleVista').classList.add('hidden');
        vistaDetalle = false;
    });

    document.getElementById('btnCrearFormula')?.addEventListener('click', function() {
        document.getElementById('tituloModalFormula').innerHTML = '<i class="fas fa-square-root-variable"></i> Nueva Fórmula';
        document.getElementById('formFormula').reset();
        document.getElementById('formulaId').value = '';
        document.getElementById('modalFormula').style.display = 'flex';
    });

    document.getElementById('btnCancelarFormula')?.addEventListener('click', function() {
        document.getElementById('modalFormula').style.display = 'none';
    });

    window.editarFormula = async function(id) {
        try {
            const resp = await fetch('/formulas-prestaciones/' + id);
            const f = await resp.json();
            document.getElementById('tituloModalFormula').innerHTML = '<i class="fas fa-square-root-variable"></i> Editar Fórmula';
            document.getElementById('formulaId').value = f.id;
            document.getElementById('formulaNombre').value = f.nombre;
            document.getElementById('formulaCodigo').value = f.codigo;
            document.getElementById('formulaDescripcion').value = f.descripcion || '';
            document.getElementById('formulaMatematica').value = f.formula_matematica || '';
            document.getElementById('formulaEjemplo').value = f.ejemplo_calculo || '';
            document.getElementById('formulaObservaciones').value = f.observaciones || '';
            document.getElementById('formulaActivo').value = f.activo ? '1' : '0';

            if (f.variables && typeof f.variables === 'object') {
                let vars = '';
                Object.keys(f.variables).forEach(function(k) {
                    vars += k + ' = ' + f.variables[k] + '\n';
                });
                document.getElementById('formulaVariables').value = vars.trim();
            } else {
                document.getElementById('formulaVariables').value = '';
            }

            if (f.conceptos && Array.isArray(f.conceptos)) {
                document.getElementById('formulaConceptos').value = f.conceptos.join('\n');
            } else {
                document.getElementById('formulaConceptos').value = '';
            }

            document.getElementById('modalFormula').style.display = 'flex';
        } catch(e) {
            mostrarToast('Error al cargar la fórmula.', 'error');
        }
    };

    document.getElementById('formFormula')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('formulaId').value;
        const btn = document.getElementById('btnSubmitFormula');
        btn.disabled = true; btn.textContent = 'Guardando...';

        try {
            const formData = new FormData(e.target);
            const data = {};

            data.nombre = formData.get('nombre');
            data.codigo = formData.get('codigo');
            data.descripcion = formData.get('descripcion');
            data.formula_matematica = formData.get('formula_matematica');
            data.ejemplo_calculo = formData.get('ejemplo_calculo');
            data.observaciones = formData.get('observaciones');
            data.activo = formData.get('activo') === '1';

            const varsText = formData.get('variables_text') || '';
            const variables = {};
            varsText.split('\n').forEach(function(line) {
                const parts = line.split('=');
                if (parts.length >= 2) {
                    variables[parts[0].trim()] = parts.slice(1).join('=').trim();
                }
            });
            if (Object.keys(variables).length > 0) data.variables = variables;

            const conceptosText = formData.get('conceptos_text') || '';
            const conceptos = conceptosText.split('\n').map(function(l){return l.trim();}).filter(function(l){return l.length > 0;});
            if (conceptos.length > 0) data.conceptos = conceptos;

            const url = id ? '/formulas-prestaciones/' + id : '/formulas-prestaciones';
            const method = id ? 'PUT' : 'POST';
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const resp = await fetch(url, {
                method: method,
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token},
                body: JSON.stringify(data)
            });
            const result = await resp.json();
            if (!resp.ok) throw result;
            mostrarToast(result.mensaje || 'Fórmula guardada.', 'success');
            document.getElementById('modalFormula').style.display = 'none';
            cargarFormulas(currentPage);
        } catch(err) {
            if (err.errors) {
                mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
            } else {
                mostrarToast(err.mensaje || 'Error al guardar.', 'error');
            }
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar';
        }
    });

    window.confirmarEliminarFormula = function(id) {
        document.getElementById('formulaEliminarId').value = id;
        document.getElementById('modalConfirmarEliminarFormula').style.display = 'flex';
    };

    document.getElementById('btnCancelarEliminarFormula')?.addEventListener('click', function() {
        document.getElementById('modalConfirmarEliminarFormula').style.display = 'none';
    });

    document.getElementById('btnConfirmarEliminarFormula')?.addEventListener('click', async function() {
        const id = document.getElementById('formulaEliminarId').value;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/formulas-prestaciones/' + id, {
                method: 'DELETE',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':token}
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Fórmula eliminada.', 'success');
            document.getElementById('modalConfirmarEliminarFormula').style.display = 'none';
            cargarFormulas(currentPage);
        } catch(err) {
            mostrarToast(err.mensaje || 'Error al eliminar.', 'error');
        }
    });

    document.getElementById('buscadorFormulas')?.addEventListener('input', function(){cargarFormulas(1);});
    document.getElementById('filtroEstadoFormula')?.addEventListener('change', function(){cargarFormulas(1);});

    document.addEventListener('DOMContentLoaded', function() {
        var sec = document.getElementById('formulas');
        if (sec && sec.classList.contains('active') && !vistaDetalle) {
            cargarFormulas();
            var rol = window.SIGEJUB_ROL || '';
            if (rol === 'admin' || rol === 'superadmin') {
                document.getElementById('formulasHeaderActions').style.display = 'flex';
            }
        }
    });

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.target.id === 'formulas' && m.target.classList.contains('active')) {
                if (!vistaDetalle) cargarFormulas();
                var rol = window.SIGEJUB_ROL || '';
                if (rol === 'admin' || rol === 'superadmin') {
                    document.getElementById('formulasHeaderActions').style.display = 'flex';
                }
            }
        });
    });
    var sec = document.getElementById('formulas');
    if (sec) observer.observe(sec, {attributes:true, attributeFilter:['class']});
})();
</script>
