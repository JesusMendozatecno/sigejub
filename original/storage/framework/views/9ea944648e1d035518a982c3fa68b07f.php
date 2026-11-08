<style>
.cg-tabs{display:flex;gap:4px;margin-bottom:20px;background:#f1f5f9;border-radius:10px;padding:4px;}
.cg-tab{flex:1;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;color:#64748b;background:transparent;transition:all 0.2s;text-align:center;}
.cg-tab.active{background:white;color:#0f172a;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.cg-tab:hover:not(.active){color:#334155;}
body.dark-mode .cg-tabs{background:#1e293b;}
body.dark-mode .cg-tab.active{background:#0f172a;color:#f1f5f9;}
body.dark-mode .cg-tab:hover:not(.active){color:#e2e8f0;}
.cg-tabs{flex-wrap:wrap;}
.cg-tab{flex:1 1 auto;min-width:130px;}
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Cargos y Grados</span></h1>
        <p>Administre los cargos y grados disponibles para asignar a los trabajadores.</p>
    </div>
    <div class="header-actions" id="cgHeaderActions" style="display:none;">
        <button type="button" class="btn-primary-dark" id="btnCrearCG">
            <i class="fas fa-plus" size="20"></i> Nuevo
        </button>
    </div>
</header>

<div class="cg-tabs">
    <button class="cg-tab active" data-tab="cargos" id="tabCargos"><i class="fas fa-briefcase"></i> Cargos</button>
    <button class="cg-tab" data-tab="grados" id="tabGrados"><i class="fas fa-graduation-cap"></i> Grados</button>
    <button class="cg-tab" data-tab="unidad-departamento" id="tabUnidades"><i class="fas fa-building"></i> Unidad / Depto</button>
    <button class="cg-tab" data-tab="nivel-instruccion" id="tabNiveles"><i class="fas fa-user-graduate"></i> Nivel de Instrucción</button>
    <button class="cg-tab" data-tab="tipo-contrato" id="tabCondicion"><i class="fas fa-file-signature"></i> Categoría</button>
    <button class="cg-tab" data-tab="dedicacion" id="tabDedicacion"><i class="fas fa-clock"></i> Dedicación</button>
    <button class="cg-tab" data-tab="clausulas" id="tabClausulas"><i class="fas fa-file-contract"></i> Cláusulas de Convención</button>
</div>

<div class="search-filter-bar">
    <div class="search-wrapper">
        <i class="fas fa-search" size="16"></i>
        <input type="text" id="buscadorCG" placeholder="Buscar por nombre o código...">
    </div>
    <div class="filter-group-sm">
        <select id="filtroEstadoCG">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </div>
</div>

<div class="data-table-container" style="margin-top:20px;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>CÓDIGO</th>
                <th>NOMBRE</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyCG"></tbody>
    </table>
</div>

<div class="table-footer">
    <span id="cgCounter">Mostrando 0 registros</span>
</div>

<div id="modalCG" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:520px;">
        <h3 style="text-align:center;" id="tituloModalCG"><i class="fas fa-briefcase"></i> Nuevo Registro</h3>
        <form id="formCG">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="cg_id" id="cgId">
            <input type="hidden" name="cg_tipo" id="cgTipo" value="cargo">
            <div class="input-group">
                <label>CÓDIGO *</label>
                <input type="text" name="codigo" id="cgCodigo" placeholder="Ej: COORDINADOR" required pattern="[A-Za-z0-9_\-]+" onkeypress="if(!/[A-Za-z0-9_\-]/.test(event.key))event.preventDefault()">
            </div>
            <div class="input-group">
                <label>NOMBRE *</label>
                <input type="text" name="nombre" id="cgNombre" placeholder="Ej: Coordinador de Área" required>
            </div>
            <div class="input-group">
                <label>ESTADO</label>
                <select name="activo" id="cgActivo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarCG">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitCG">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalClausula" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:600px;">
        <h3 style="text-align:center;" id="tituloModalClausula"><i class="fas fa-file-contract"></i> Nueva Cláusula</h3>
        <p style="text-align:center;color:#64748b;font-size:0.82rem;margin-bottom:14px;">
            El beneficio de la cláusula se suma al cálculo de prestaciones del expediente que la tenga asignada.
        </p>
        <form id="formClausula">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="clausula_id" id="clausulaId">
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;">
                <div class="input-group">
                    <label>NÚMERO *</label>
                    <input type="text" name="numero" id="clausulaNumero" placeholder="Ej: 55" maxlength="30" required>
                </div>
                <div class="input-group">
                    <label>NOMBRE *</label>
                    <input type="text" name="nombre" id="clausulaNombre" placeholder="Ej: Pensión de jubilación" maxlength="150" required>
                </div>
            </div>
            <div class="input-group">
                <label>DESCRIPCIÓN</label>
                <textarea name="descripcion" id="clausulaDescripcion" rows="3" maxlength="2000" placeholder="Texto o resumen de la cláusula"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>TIPO DE CÁLCULO *</label>
                    <select name="tipo_calculo" id="clausulaTipo" required>
                        <option value="informativa">Informativa (sin monto)</option>
                        <option value="porcentaje_sueldo_base">% del sueldo base</option>
                        <option value="porcentaje_sueldo_integral">% del sueldo integral</option>
                        <option value="dias_sueldo_integral">Días de salario integral</option>
                        <option value="monto_fijo">Monto fijo (Bs.)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>VALOR</label>
                    <input type="number" step="0.01" min="0" name="valor" id="clausulaValor" value="0">
                </div>
                <div class="input-group">
                    <label>ESTADO</label>
                    <select name="activo" id="clausulaActivo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#0f172a;font-weight:600;margin:4px 0 14px;">
                <input type="checkbox" name="por_anio" id="clausulaPorAnio" value="1" style="width:16px;height:16px;accent-color:#2563eb;">
                El monto se multiplica por cada año de servicio
            </label>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarClausula">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitClausula">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalConfirmarEliminarCG" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:center;">
        <i class="fas fa-triangle-exclamation" style="font-size:2.5rem;color:#f59e0b;margin-bottom:12px;"></i>
        <h3>Eliminar Registro</h3>
        <p style="color:#64748b;font-size:0.85rem;margin-bottom:16px;">¿Está seguro que desea eliminar este registro? Esta acción no se puede deshacer.</p>
        <input type="hidden" id="cgEliminarId">
        <div style="display:flex;gap:8px;justify-content:center;">
            <button class="btn-cancel" id="btnCancelarEliminarCG">Cancelar</button>
            <button class="btn-submit" style="background:#dc2626;" id="btnConfirmarEliminarCG">Eliminar</button>
        </div>
    </div>
</div>

<script>
(function(){
    let currentPage = 1;
    let tipoActivo = 'cargo';
    const MAP = { cargo: 'Cargo', grado: 'Grado', 'unidad-departamento': 'Unidad / Departamento', 'nivel-instruccion': 'Nivel de Instrucción', 'tipo-contrato': 'Categoría', dedicacion: 'Dedicación', clausulas: 'Cláusulas de Convención' };
    const ICONS = { cargo: 'fa-briefcase', grado: 'fa-graduation-cap', 'unidad-departamento': 'fa-building', 'nivel-instruccion': 'fa-user-graduate', 'tipo-contrato': 'fa-file-signature', dedicacion: 'fa-clock' };

    function cambiarTipo(tipo) {
        tipoActivo = tipo;
        document.getElementById('tabCargos').classList.toggle('active', tipo === 'cargo');
        document.getElementById('tabGrados').classList.toggle('active', tipo === 'grado');
        document.getElementById('tabUnidades').classList.toggle('active', tipo === 'unidad-departamento');
        document.getElementById('tabNiveles').classList.toggle('active', tipo === 'nivel-instruccion');
        document.getElementById('tabCondicion').classList.toggle('active', tipo === 'tipo-contrato');
        document.getElementById('tabDedicacion').classList.toggle('active', tipo === 'dedicacion');
        document.getElementById('tabClausulas').classList.toggle('active', tipo === 'clausulas');
        document.getElementById('buscadorCG').value = '';
        document.getElementById('filtroEstadoCG').value = '';
        currentPage = 1;
        cargarDatos();
    }

    document.getElementById('tabCargos')?.addEventListener('click', () => cambiarTipo('cargo'));
    document.getElementById('tabGrados')?.addEventListener('click', () => cambiarTipo('grado'));
    document.getElementById('tabUnidades')?.addEventListener('click', () => cambiarTipo('unidad-departamento'));
    document.getElementById('tabNiveles')?.addEventListener('click', () => cambiarTipo('nivel-instruccion'));
    document.getElementById('tabCondicion')?.addEventListener('click', () => cambiarTipo('tipo-contrato'));
    document.getElementById('tabDedicacion')?.addEventListener('click', () => cambiarTipo('dedicacion'));
    document.getElementById('tabClausulas')?.addEventListener('click', () => cambiarTipo('clausulas'));

    // El catálogo de cláusulas de convención tiene su propio controlador.
    const TIPOS_CALCULO = {
        informativa: 'Informativa (sin monto)',
        porcentaje_sueldo_base: '% del sueldo base',
        porcentaje_sueldo_integral: '% del sueldo integral',
        dias_sueldo_integral: 'Días de salario integral',
        monto_fijo: 'Monto fijo'
    };

    async function cargarClausulasCG(page) {
        page = page || 1;
        const search = document.getElementById('buscadorCG')?.value || '';
        const estado = document.getElementById('filtroEstadoCG')?.value || '';
        let url = `/clausulas-convencion?page=${page}&per_page=50`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (estado !== '') url += `&solo_activos=${estado}`;
        const tbody = document.getElementById('tbodyCG');
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (!tbody) return;
            if (!data.data || !data.data.length) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">No hay cláusulas registradas.</td></tr>';
                document.getElementById('cgCounter').textContent = 'Mostrando 0 registros';
                return;
            }
            tbody.innerHTML = data.data.map(function(item, i) {
                const badge = item.activo
                    ? '<span class="badge-status approved">Activa</span>'
                    : '<span class="badge-status rejected">Inactiva</span>';
                const valor = parseFloat(item.valor) || 0;
                const detalle = (TIPOS_CALCULO[item.tipo_calculo] || item.tipo_calculo) +
                    (item.tipo_calculo === 'informativa' ? '' : ' · ' + valor + (item.por_anio ? ' por año' : ''));
                return '<tr>' +
                    '<td>' + ((i + 1) + ((data.current_page - 1) * data.per_page)) + '</td>' +
                    '<td><code class="codigo-tag">' + escaparHTML(item.numero) + '</code></td>' +
                    '<td>' + escaparHTML(item.nombre) +
                        '<div style="font-size:0.72rem;color:#64748b;">' + escaparHTML(detalle) + '</div></td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="actions">' +
                    '<i class="fas fa-pen btn-icon btn-editar-clausula" title="Editar" data-id="' + item.id + '"></i>' +
                    '<i class="fas fa-trash btn-icon" title="Eliminar" style="color:#dc2626;" onclick="confirmarEliminarCG(' + item.id + ')"></i>' +
                    '</td></tr>';
            }).join('');
            document.getElementById('cgCounter').textContent = 'Mostrando ' + data.data.length + ' de ' + data.total + ' registros';
            currentPage = data.current_page;
        } catch (e) {
            console.error('Error cargando cláusulas:', e);
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#dc2626;">Error al cargar las cláusulas.</td></tr>';
        }
    }

    async function cargarDatos(page) {
        page = page || 1;
        if (tipoActivo === 'clausulas') { return cargarClausulasCG(page); }
        const search = document.getElementById('buscadorCG')?.value || '';
        const estado = document.getElementById('filtroEstadoCG')?.value || '';
        let url = `/master/${tipoActivo}?page=${page}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (estado !== '') url += `&solo_activos=${estado}`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            const tbody = document.getElementById('tbodyCG');
            if (!tbody) return;

            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">No hay ' + MAP[tipoActivo].toLowerCase() + ' registrados.</td></tr>';
                document.getElementById('cgCounter').textContent = 'Mostrando 0 registros';
                return;
            }

            tbody.innerHTML = data.data.map(function(item, i) {
                const badge = item.activo
                    ? '<span class="badge-status approved">Activo</span>'
                    : '<span class="badge-status rejected">Inactivo</span>';
                return '<tr>' +
                    '<td>' + ((i + 1) + ((data.current_page - 1) * data.per_page)) + '</td>' +
                    '<td><code class="codigo-tag">' + escaparHTML(item.codigo) + '</code></td>' +
                    '<td>' + escaparHTML(item.nombre) + '</td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="actions">' +
                    '<i class="fas fa-pen btn-icon btn-editar-cg" title="Editar" data-id="' + item.id + '"></i>' +
                    '<i class="fas fa-trash btn-icon" title="Eliminar" style="color:#dc2626;" onclick="confirmarEliminarCG(' + item.id + ')"></i>' +
                    '</td></tr>';
            }).join('');

            document.getElementById('cgCounter').textContent = 'Mostrando ' + data.data.length + ' de ' + data.total + ' registros';
            currentPage = data.current_page;
        } catch(e) {
            console.error('Error cargando datos:', e);
        }
    }

    document.getElementById('btnCrearCG')?.addEventListener('click', function() {
        if (tipoActivo === 'clausulas') {
            document.getElementById('tituloModalClausula').innerHTML = '<i class="fas fa-file-contract"></i> Nueva Cláusula';
            document.getElementById('formClausula').reset();
            document.getElementById('clausulaId').value = '';
            document.getElementById('clausulaActivo').value = '1';
            document.getElementById('clausulaTipo').value = 'informativa';
            document.getElementById('modalClausula').style.display = 'flex';
            return;
        }
        const label = MAP[tipoActivo];
        document.getElementById('tituloModalCG').innerHTML = '<i class="fas ' + ICONS[tipoActivo] + '"></i> Nuevo ' + label;
        document.getElementById('formCG').reset();
        document.getElementById('cgId').value = '';
        document.getElementById('cgTipo').value = tipoActivo;
        document.getElementById('modalCG').style.display = 'flex';
    });

    document.getElementById('btnCancelarCG')?.addEventListener('click', function() {
        document.getElementById('modalCG').style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        const btnClausula = e.target.closest('.btn-editar-clausula');
        if (btnClausula) {
            const id = btnClausula.dataset.id;
            fetch('/clausulas-convencion?per_page=200')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    const item = (res.data || []).find(function(c) { return String(c.id) === String(id); });
                    if (!item) { mostrarToast('No se encontró la cláusula.', 'error'); return; }
                    document.getElementById('tituloModalClausula').innerHTML = '<i class="fas fa-file-contract"></i> Editar Cláusula';
                    document.getElementById('clausulaId').value = item.id;
                    document.getElementById('clausulaNumero').value = item.numero;
                    document.getElementById('clausulaNombre').value = item.nombre;
                    document.getElementById('clausulaDescripcion').value = item.descripcion || '';
                    document.getElementById('clausulaTipo').value = item.tipo_calculo;
                    document.getElementById('clausulaValor').value = item.valor || 0;
                    document.getElementById('clausulaPorAnio').checked = !!item.por_anio;
                    document.getElementById('clausulaActivo').value = item.activo ? '1' : '0';
                    document.getElementById('modalClausula').style.display = 'flex';
                });
            return;
        }
        const btn = e.target.closest('.btn-editar-cg');
        if (!btn) return;
        const id = btn.dataset.id;
        const label = MAP[tipoActivo];
        fetch('/master/' + tipoActivo + '/' + id)
            .then(function(r){ return r.json(); })
            .then(function(item) {
                document.getElementById('tituloModalCG').innerHTML = '<i class="fas ' + ICONS[tipoActivo] + '"></i> Editar ' + label;
                document.getElementById('cgId').value = item.id;
                document.getElementById('cgTipo').value = tipoActivo;
                document.getElementById('cgCodigo').value = item.codigo;
                document.getElementById('cgNombre').value = item.nombre;
                document.getElementById('cgActivo').value = item.activo ? '1' : '0';
                document.getElementById('modalCG').style.display = 'flex';
            });
    });

    document.getElementById('formCG')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('cgId').value;
        const tipo = document.getElementById('cgTipo').value;
        const btn = document.getElementById('btnSubmitCG');
        btn.disabled = true; btn.textContent = 'Guardando...';

        try {
            const formData = new FormData(e.target);
            const url = id ? '/master/' + tipo + '/' + id : '/master/' + tipo;
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
            if (id) formData.append('_method', 'PUT');
            const resp = await fetch(url, { method: 'POST', body: formData, headers: headers });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Guardado exitosamente.', 'success');
            document.getElementById('modalCG').style.display = 'none';
            cargarDatos(currentPage);
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

    document.getElementById('btnCancelarClausula')?.addEventListener('click', function() {
        document.getElementById('modalClausula').style.display = 'none';
    });

    document.getElementById('formClausula')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('clausulaId').value;
        const btn = document.getElementById('btnSubmitClausula');
        btn.disabled = true; btn.textContent = 'Guardando...';
        try {
            const payload = {
                numero: document.getElementById('clausulaNumero').value.trim(),
                nombre: document.getElementById('clausulaNombre').value.trim(),
                descripcion: document.getElementById('clausulaDescripcion').value.trim() || null,
                tipo_calculo: document.getElementById('clausulaTipo').value,
                valor: parseFloat(document.getElementById('clausulaValor').value) || 0,
                por_anio: document.getElementById('clausulaPorAnio').checked ? 1 : 0,
                activo: parseInt(document.getElementById('clausulaActivo').value, 10)
            };
            const resp = await fetch(id ? '/clausulas-convencion/' + id : '/clausulas-convencion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Cláusula guardada.', 'success');
            document.getElementById('modalClausula').style.display = 'none';
            cargarDatos(currentPage);
        } catch (err) {
            if (err.errors) {
                mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
            } else {
                mostrarToast(err.mensaje || 'Error al guardar la cláusula.', 'error');
            }
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar';
        }
    });

    window.confirmarEliminarCG = function(id) {
        document.getElementById('cgEliminarId').value = id;
        document.getElementById('modalConfirmarEliminarCG').style.display = 'flex';
    };

    document.getElementById('btnCancelarEliminarCG')?.addEventListener('click', function() {
        document.getElementById('modalConfirmarEliminarCG').style.display = 'none';
    });

    document.getElementById('btnConfirmarEliminarCG')?.addEventListener('click', async function() {
        const id = document.getElementById('cgEliminarId').value;
        try {
            const urlEliminar = tipoActivo === 'clausulas' ? '/clausulas-convencion/' + id : '/master/' + tipoActivo + '/' + id;
            const resp = await fetch(urlEliminar, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Eliminado.', 'success');
            document.getElementById('modalConfirmarEliminarCG').style.display = 'none';
            cargarDatos(currentPage);
        } catch(err) {
            mostrarToast(err.mensaje || 'Error al eliminar.', 'error');
        }
    });

    document.getElementById('buscadorCG')?.addEventListener('input', function(){ cargarDatos(1); });
    document.getElementById('filtroEstadoCG')?.addEventListener('change', function(){ cargarDatos(1); });

    document.addEventListener('DOMContentLoaded', function() {
        var sec = document.getElementById('cargos-grados');
        if (sec && sec.classList.contains('active')) {
            cargarDatos();
            var rol = window.SIGEJUB_ROL || '';
            if (rol === 'admin' || rol === 'superadmin') {
                document.getElementById('cgHeaderActions').style.display = 'flex';
            }
        }
    });

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.target.id === 'cargos-grados' && m.target.classList.contains('active')) {
                cargarDatos();
                var rol = window.SIGEJUB_ROL || '';
                if (rol === 'admin' || rol === 'superadmin') {
                    document.getElementById('cgHeaderActions').style.display = 'flex';
                }
            }
        });
    });
    var sec = document.getElementById('cargos-grados');
    if (sec) observer.observe(sec, { attributes: true, attributeFilter: ['class'] });
})();
</script>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\dashboard\secciones\cargos-grados.blade.php ENDPATH**/ ?>