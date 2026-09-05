<style>
.cg-tabs{display:flex;gap:4px;margin-bottom:20px;background:#f1f5f9;border-radius:10px;padding:4px;}
.cg-tab{flex:1;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;color:#64748b;background:transparent;transition:all 0.2s;text-align:center;}
.cg-tab.active{background:white;color:#0f172a;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.cg-tab:hover:not(.active){color:#334155;}
body.dark-mode .cg-tabs{background:#1e293b;}
body.dark-mode .cg-tab.active{background:#0f172a;color:#f1f5f9;}
body.dark-mode .cg-tab:hover:not(.active){color:#e2e8f0;}
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
            @csrf
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
    const MAP = { cargo: 'Cargo', grado: 'Grado' };
    const ICONS = { cargo: 'fa-briefcase', grado: 'fa-graduation-cap' };

    function cambiarTipo(tipo) {
        tipoActivo = tipo;
        document.getElementById('tabCargos').classList.toggle('active', tipo === 'cargo');
        document.getElementById('tabGrados').classList.toggle('active', tipo === 'grado');
        document.getElementById('buscadorCG').value = '';
        document.getElementById('filtroEstadoCG').value = '';
        currentPage = 1;
        cargarDatos();
    }

    document.getElementById('tabCargos')?.addEventListener('click', () => cambiarTipo('cargo'));
    document.getElementById('tabGrados')?.addEventListener('click', () => cambiarTipo('grado'));

    async function cargarDatos(page) {
        page = page || 1;
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
            const resp = await fetch('/master/' + tipoActivo + '/' + id, {
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
