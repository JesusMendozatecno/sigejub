{{-- vista/blade de gestión de primas oficiales del sistema SIGEJUB.
     Permite listar, crear, editar y eliminar primas (solo superadmin).
     Incluye búsqueda, paginación, modal de formulario con validación
     y confirmación de eliminación. Los datos se cargan vía AJAX
     desde /master/prima (CRUD genérico de tablas maestras). --}}

<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Primas</span></h1>
        <p>Administre los valores oficiales de las primas del sistema.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" id="btnCrearPrima">
            <i class="fas fa-plus" size="20"></i> Nueva Prima
        </button>
    </div>
</header>

<div class="search-filter-bar">
    <div class="search-wrapper">
        <i class="fas fa-search" size="16"></i>
        <input type="text" id="buscadorPrimas" placeholder="Buscar por nombre o código...">
    </div>
    <div class="filter-group-sm">
        <select id="filtroEstadoPrima">
            <option value="">Todas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
        </select>
    </div>
</div>

<div class="data-table-container" style="margin-top: 20px;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>CÓDIGO</th>
                <th>NOMBRE</th>
                <th>VALOR</th>
                <th>EN BS</th>
                <th>FECHA VIGENCIA</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyPrimas">
        </tbody>
    </table>
</div>

<div class="table-footer">
    <span id="primasCounter">Mostrando 0 primas</span>
</div>

<div id="modalPrima" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:left;max-width:520px;">
        <h3 style="text-align:center;" id="tituloModalPrima"><i class="fas fa-coins"></i> Nueva Prima</h3>
        <form id="formPrima">
            @csrf
            <input type="hidden" name="prima_id" id="primaId">
            <div class="input-group">
                <label>CÓDIGO</label>
                <input type="text" name="codigo" id="primaCodigo" placeholder="Ej: PRIMA_FAMILIAR" required pattern="[A-Za-z0-9_\-]+" title="Letras, números, guiones o guiones bajos" onkeypress="if(!/[A-Za-z0-9_\-]/.test(event.key))event.preventDefault()">
            </div>
            <div class="input-group">
                <label>NOMBRE</label>
                <input type="text" name="nombre" id="primaNombre" placeholder="Ej: Prima de Antigüedad" required>
            </div>
            <div class="input-group">
                <label>VALOR ($)</label>
                <input type="number" name="valor" id="primaValor" step="0.01" min="0" inputmode="decimal" placeholder="0.00" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
            </div>
            <div class="input-group">
                <label>FECHA DE VIGENCIA</label>
                <input type="date" name="fecha_vigencia" id="primaFechaVigencia">
            </div>
            <div class="input-group">
                <label>ESTADO</label>
                <select name="activo" id="primaActivo">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </div>
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn-cancel" id="btnCancelarPrima">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitPrima">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalConfirmarEliminarPrima" class="modal-overlay">
    <div class="modal-delete-box" style="text-align:center;">
        <i class="fas fa-triangle-exclamation" style="font-size:2.5rem;color:#f59e0b;margin-bottom:12px;"></i>
        <h3>Eliminar Prima</h3>
        <p style="color:#64748b;font-size:0.85rem;margin-bottom:16px;">¿Está seguro que desea eliminar esta prima? Esta acción no se puede deshacer.</p>
        <input type="hidden" id="primaEliminarId">
        <div style="display:flex;gap:8px;justify-content:center;">
            <button class="btn-cancel" id="btnCancelarEliminarPrima">Cancelar</button>
            <button class="btn-submit" style="background:#dc2626;" id="btnConfirmarEliminarPrima">Eliminar</button>
        </div>
    </div>
</div>

<script>
(function() {
    let currentPage = 1;
    let primaTasaDia = 0;

    // Obtiene la tasa del día (USD -> VES) desde /tasas-cambio/estado.
    async function cargarTasaDia() {
        try {
            const resp = await fetch('/tasas-cambio/estado');
            const data = await resp.json();
            primaTasaDia = (data.disponible && data.tasa && data.tasa.valor) ? parseFloat(data.tasa.valor) : 0;
        } catch (e) {
            primaTasaDia = 0;
        }
    }

    async function cargarPrimas(page = 1) {
        const search = document.getElementById('buscadorPrimas')?.value || '';
        const estado = document.getElementById('filtroEstadoPrima')?.value || '';
        let url = `/master/prima?page=${page}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (estado !== '') url += `&solo_activos=${estado}`;

        try {
            await cargarTasaDia();
            const resp = await fetch(url);
            const data = await resp.json();
            const tbody = document.getElementById('tbodyPrimas');
            if (!tbody) return;

            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:#94a3b8;">No hay primas registradas.</td></tr>';
                document.getElementById('primasCounter').textContent = 'Mostrando 0 primas';
                return;
            }

            tbody.innerHTML = data.data.map((p, i) => {
                const badge = p.activo
                    ? '<span class="badge-status approved">Activa</span>'
                    : '<span class="badge-status rejected">Inactiva</span>';
                const fecha = p.fecha_vigencia ? new Date(p.fecha_vigencia).toLocaleDateString('es-ES') : '—';
                const valorNum = parseFloat(p.valor || 0);
                const enBs = primaTasaDia > 0 ? (valorNum * primaTasaDia) : 0;
                const valorTxt = valorNum.toLocaleString('es-VE', { minimumFractionDigits: 2 });
                const enBsTxt = primaTasaDia > 0 ? enBs.toLocaleString('es-VE', { minimumFractionDigits: 2 }) : '—';
                return `<tr>
                    <td>${(i + 1) + ((data.current_page - 1) * data.per_page)}</td>
                    <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.8rem;">${escaparHTML(p.codigo)}</code></td>
                    <td>${escaparHTML(p.nombre)}</td>
                    <td><strong>$ ${valorTxt}</strong></td>
                    <td><strong class="celdaEnBs" data-usd="${valorNum}">Bs. ${enBsTxt}</strong></td>
                    <td>${fecha}</td>
                    <td>${badge}</td>
                    <td class="actions">
                        <i class="fas fa-pen btn-icon btn-editar-prima" title="Editar" data-id="${p.id}"></i>
                        <i class="fas fa-trash btn-icon" title="Eliminar" style="color:#dc2626;" data-id="${p.id}" onclick="confirmarEliminarPrima(${p.id})"></i>
                    </td>
                </tr>`;
            }).join('');

            document.getElementById('primasCounter').textContent = `Mostrando ${data.data.length} de ${data.total} primas`;
            currentPage = data.current_page;
        } catch (err) {
            console.error('Error al cargar primas:', err);
        }
    }

    document.getElementById('btnCrearPrima')?.addEventListener('click', () => {
        document.getElementById('tituloModalPrima').innerHTML = '<i class="fas fa-coins"></i> Nueva Prima';
        document.getElementById('formPrima').reset();
        document.getElementById('primaId').value = '';
        document.getElementById('modalPrima').style.display = 'flex';
    });

    document.getElementById('btnCancelarPrima')?.addEventListener('click', () => {
        document.getElementById('modalPrima').style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-editar-prima');
        if (!btn) return;
        const id = btn.dataset.id;
        fetch(`/master/prima/${id}`)
            .then(r => r.json())
            .then(p => {
                document.getElementById('tituloModalPrima').innerHTML = '<i class="fas fa-coins"></i> Editar Prima';
                document.getElementById('primaId').value = p.id;
                document.getElementById('primaCodigo').value = p.codigo;
                document.getElementById('primaNombre').value = p.nombre;
                document.getElementById('primaValor').value = p.valor || '';
                document.getElementById('primaFechaVigencia').value = p.fecha_vigencia ? p.fecha_vigencia.split('T')[0] : '';
                document.getElementById('primaActivo').value = p.activo ? '1' : '0';
                document.getElementById('modalPrima').style.display = 'flex';
            });
    });

    document.getElementById('formPrima')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('primaId').value;
        const formData = new FormData(e.target);
        const btn = document.getElementById('btnSubmitPrima');
        btn.disabled = true; btn.textContent = 'Guardando...';

        try {
            const url = id ? `/master/prima/${id}` : '/master/prima';
            const method = id ? 'POST' : 'POST';
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
            if (id) {
                formData.append('_method', 'PUT');
            }
            const resp = await fetch(url, { method, body: formData, headers });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Prima guardada exitosamente.', 'success');
            document.getElementById('modalPrima').style.display = 'none';
            cargarPrimas(currentPage);
        } catch (err) {
            if (err.errors) {
                mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
            } else {
                mostrarToast(err.mensaje || 'Error al guardar', 'error');
            }
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar';
        }
    });

    window.confirmarEliminarPrima = function(id) {
        document.getElementById('primaEliminarId').value = id;
        document.getElementById('modalConfirmarEliminarPrima').style.display = 'flex';
    };

    document.getElementById('btnCancelarEliminarPrima')?.addEventListener('click', () => {
        document.getElementById('modalConfirmarEliminarPrima').style.display = 'none';
    });

    document.getElementById('btnConfirmarEliminarPrima')?.addEventListener('click', async () => {
        const id = document.getElementById('primaEliminarId').value;
        try {
            const resp = await fetch(`/master/prima/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Prima eliminada.', 'success');
            document.getElementById('modalConfirmarEliminarPrima').style.display = 'none';
            cargarPrimas(currentPage);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al eliminar', 'error');
        }
    });

    document.getElementById('buscadorPrimas')?.addEventListener('input', () => cargarPrimas(1));
    document.getElementById('filtroEstadoPrima')?.addEventListener('change', () => cargarPrimas(1));

    document.addEventListener('DOMContentLoaded', function() {
        var sec = document.getElementById('primas');
        if (sec && sec.classList.contains('active')) {
            cargarPrimas();
        }
    });

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'primas' && m.target.classList.contains('active')) {
                cargarPrimas();
            }
        });
    });
    const seccion = document.getElementById('primas');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });

    // Actualiza la columna EN BS con la tasa del día cada 30s mientras la sección esté activa.
    setInterval(async () => {
        const sec = document.getElementById('primas');
        if (!sec || !sec.classList.contains('active')) return;
        const anterior = primaTasaDia;
        await cargarTasaDia();
        if (primaTasaDia === anterior) return;
        document.querySelectorAll('.celdaEnBs').forEach(el => {
            const usd = parseFloat(el.dataset.usd || 0);
            el.textContent = primaTasaDia > 0 ? 'Bs. ' + (usd * primaTasaDia).toLocaleString('es-VE', { minimumFractionDigits: 2 }) : '—';
        });
    }, 30000);
})();
</script>
