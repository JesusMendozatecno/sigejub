<style>
.tasa-header-row{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.tasa-actual-card{background:linear-gradient(135deg,#1a365d,#1e3a8a);border-radius:14px;padding:24px;color:white;display:flex;align-items:center;gap:20px;min-width:320px;}
.tasa-actual-card .tasa-icon{font-size:2.5rem;opacity:0.7;}
.tasa-actual-card .tasa-info h3{font-size:0.8rem;opacity:0.7;margin:0 0 4px;}
.tasa-actual-card .tasa-info .tasa-valor{font-size:2rem;font-weight:800;margin:0;line-height:1;}
.tasa-actual-card .tasa-info .tasa-meta{font-size:0.72rem;opacity:0.6;margin-top:6px;}
.tasa-actions{display:flex;gap:8px;flex-wrap:wrap;}
.tasa-btn{padding:10px 18px;border-radius:8px;font-size:0.82rem;font-weight:600;border:none;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all 0.2s;}
.tasa-btn-primary{background:white;color:#1a365d;}
.tasa-btn-primary:hover{background:#f1f5f9;}
.tasa-btn-success{background:#16a34a;color:white;}
.tasa-btn-success:hover{background:#15803d;}
.tasa-btn-secondary{background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);}
.tasa-btn-secondary:hover{background:rgba(255,255,255,0.3);}
.historial-table{width:100%;}
.historial-table th{text-align:left;padding:10px 12px;font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;}
.historial-table td{padding:10px 12px;font-size:0.82rem;color:#0f172a;border-bottom:1px solid #f1f5f9;}
.historial-table tr:hover td{background:#f8fafc;}
.badge-tipo{font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:10px;}
.badge-auto{background:#dbeafe;color:#1e40af;}
.badge-manual{background:#fef3c7;color:#92400e;}
.hist-pag-btn{min-width:32px;height:32px;padding:0 10px;border:1px solid #e2e8f0;background:white;border-radius:8px;font-size:0.78rem;font-weight:600;color:#64748b;cursor:pointer;transition:all 0.15s;}
.hist-pag-btn:hover{background:#f1f5f9;color:#1a365d;}
.hist-pag-btn.active{background:#1a365d;color:white;border-color:#1a365d;}
.hist-pag-dots{min-width:28px;height:32px;display:inline-flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:700;}
body.dark-mode .hist-pag-btn{background:#1e293b;border-color:#334155;color:#94a3b8;}
body.dark-mode .hist-pag-btn:hover{background:#334155;color:#f1f5f9;}
body.dark-mode .hist-pag-btn.active{background:#1e40af;border-color:#1e40af;color:white;}
body.dark-mode .hist-pag-dots{color:#64748b;}
.modal-tasa{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:5000;}
.modal-tasa.active{display:flex;}
.modal-tasa-box{background:white;border-radius:16px;padding:28px;width:480px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.modal-tasa-box h3{font-size:1rem;color:#0f172a;margin:0 0 18px;display:flex;align-items:center;gap:8px;}
.modal-tasa-box .input-group{margin-bottom:14px;}
.modal-tasa-box .input-group label{display:block;font-size:0.75rem;font-weight:700;color:#64748b;margin-bottom:4px;}
.modal-tasa-box .input-group input,.modal-tasa-box .input-group textarea,.modal-tasa-box .input-group select{width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.85rem;}
.modal-tasa-box .input-group input:focus,.modal-tasa-box .input-group textarea:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.08);}
.modal-tasa-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:18px;}
body.dark-mode .tasa-actual-card{background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid #334155;}
body.dark-mode .historial-table th{color:#94a3b8;border-bottom-color:#334155;}
body.dark-mode .historial-table td{color:#e2e8f0;border-bottom-color:#334155;}
body.dark-mode .historial-table tr:hover td{background:#1e293b;}
body.dark-mode .modal-tasa-box{background:#1e293b;}
body.dark-mode .modal-tasa-box h3{color:#f1f5f9;}
body.dark-mode .modal-tasa-box input,body.dark-mode .modal-tasa-box textarea,body.dark-mode .modal-tasa-box select{background:#0f172a;border-color:#334155;color:#e2e8f0;}
body.dark-mode #tasaEstadoBadge{background:#1e293b;color:#e2e8f0;}
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Tasa de <span class="text-blue-accent">Cambio</span></h1>
        <p>Consulte y administre la tasa de cambio USD/VES utilizada por el sistema.</p>
    </div>
    <div class="header-actions" id="tasaHeaderActions" style="display:none;">
        <button type="button" class="btn-primary-dark" id="btnRegistrarTasa">
            <i class="fas fa-plus" size="20"></i> Registrar Tasa
        </button>
    </div>
</header>

<div id="tasaActualSection">
    <div class="tasa-header-row">
        <div class="tasa-actual-card" id="tasaActualCard">
            <div class="tasa-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="tasa-info">
                <h3>TASA ACTUAL</h3>
                <p class="tasa-valor" id="tasaValorActual">Cargando...</p>
                <p class="tasa-meta" id="tasaMetaActual"></p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div id="tasaEstadoBadge" style="display:none;align-items:center;gap:8px;padding:6px 14px;border-radius:20px;font-size:0.75rem;font-weight:700;background:#f1f5f9;color:#475569;">
                <span id="tasaEstadoDot" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#94a3b8;"></span>
                <span id="tasaEstadoLabel">—</span>
            </div>
            <div class="tasa-actions" id="tasaAdminActions" style="display:none;">
                <button class="tasa-btn tasa-btn-success" id="btnSincronizarTasa">
                    <i class="fas fa-sync-alt"></i> Sincronizar API
                </button>
                <button class="tasa-btn tasa-btn-primary" id="btnRegistrarTasa2">
                    <i class="fas fa-edit"></i> Registrar Manual
                </button>
            </div>
        </div>
    </div>
</div>

<div style="background:white;border-radius:14px;padding:20px 24px;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <h3 style="font-size:0.9rem;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-history" style="color:#2563eb;"></i> Historial de Tasas de Cambio
    </h3>
    <div style="overflow-x:auto;">
        <table class="historial-table">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>TASA</th>
                    <th>MONEDA</th>
                    <th>FUENTE</th>
                    <th>TIPO</th>
                    <th>USUARIO</th>
                </tr>
            </thead>
            <tbody id="tbodyTasas">
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">Cargando historial...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="table-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <span id="tasasCounter">Mostrando 0 registros</span>
        <div class="hist-pagination" id="tasasPagination" style="display:flex;gap:4px;flex-wrap:wrap;"></div>
    </div>
</div>

<div class="modal-tasa" id="modalRegistrarTasa">
    <div class="modal-tasa-box">
        <h3><i class="fas fa-dollar-sign"></i> <span id="tituloModalTasa">Registrar Tasa de Cambio</span></h3>
        <form id="formTasa">
            @csrf
            <div class="input-group">
                <label>TASA USD/VES *</label>
                <input type="number" name="tasa" id="tasaInput" step="0.01" min="0.01" max="999999.99" inputmode="decimal" required placeholder="Ej: 150.00" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>MONEDA ORIGEN</label>
                    <input type="text" name="moneda_origen" value="USD" readonly style="background:#f8fafc;">
                </div>
                <div class="input-group">
                    <label>MONEDA DESTINO</label>
                    <input type="text" name="moneda_destino" value="VES" readonly style="background:#f8fafc;">
                </div>
            </div>
            <div class="input-group">
                <label>FUENTE</label>
                <input type="text" name="fuente" id="tasaFuenteInput" placeholder="Ej: Manual, BCV, API" maxlength="100">
            </div>
            <div class="input-group">
                <label>OBSERVACIÓN</label>
                <textarea name="observacion" id="tasaObsInput" rows="2" placeholder="Detalles adicionales..." maxlength="500" style="resize:vertical;"></textarea>
            </div>
            <div class="modal-tasa-actions">
                <button type="button" class="btn-cancel" id="btnCancelarTasa" style="padding:10px 18px;border-radius:8px;font-size:0.82rem;font-weight:600;border:1px solid #e2e8f0;background:white;color:#64748b;cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitTasa" style="padding:10px 18px;border-radius:8px;font-size:0.82rem;font-weight:600;border:none;background:#1a365d;color:white;cursor:pointer;">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    let currentPage = 1;

    function aplicarIndicadorEstado(data) {
        const badge = document.getElementById('tasaEstadoBadge');
        const dot = document.getElementById('tasaEstadoDot');
        const label = document.getElementById('tasaEstadoLabel');
        if (!badge || !dot || !label) return;

        badge.style.display = 'inline-flex';
        const estados = {
            actualizada: { color: '#4ade80', bg: 'rgba(74,222,128,0.15)', txt: '#166534', label: '🟢 Actualizada' },
            disponible: { color: '#facc15', bg: 'rgba(250,204,21,0.15)', txt: '#92400e', label: '🟡 Última disponible' },
            desactualizada: { color: '#f87171', bg: 'rgba(248,113,113,0.15)', txt: '#991b1b', label: '🔴 Desactualizada' },
            no_disponible: { color: '#f87171', bg: 'rgba(248,113,113,0.15)', txt: '#991b1b', label: '🔴 No disponible' },
        };
        const cfg = estados[data.estado] || { color: '#94a3b8', bg: 'rgba(148,163,184,0.15)', txt: '#475569', label: data.etiqueta || '—' };
        dot.style.background = cfg.color;
        badge.style.background = cfg.bg;
        badge.style.color = cfg.txt;
        label.textContent = cfg.label;
    }

    async function cargarTasaActual() {
        try {
            const resp = await fetch('/tasas-cambio/estado');
            const data = await resp.json();
            const valorEl = document.getElementById('tasaValorActual');
            const metaEl = document.getElementById('tasaMetaActual');

            if (data.disponible && data.tasa) {
                const t = data.tasa;
                valorEl.textContent = '1 USD = ' + parseFloat(t.valor).toLocaleString('es-VE', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' ' + t.moneda_destino;
                metaEl.textContent = t.fecha + ' · ' + (t.fuente || 'BCV/Monitor') + ' · Tipo: ' + t.tipo;
            } else {
                valorEl.textContent = 'Sin tasa registrada';
                metaEl.textContent = data.mensaje || 'Registre una tasa para comenzar.';
            }
            aplicarIndicadorEstado(data);
        } catch(e) {
            document.getElementById('tasaValorActual').textContent = 'Error al cargar';
        }
    }

    async function cargarHistorial(page) {
        page = page || currentPage || 1;
        try {
            const resp = await fetch('/tasas-cambio/historial?page=' + page);
            const data = await resp.json();
            const tbody = document.getElementById('tbodyTasas');
            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">No hay registros de tasas.</td></tr>';
                document.getElementById('tasasCounter').textContent = 'Mostrando 0 registros';
                document.getElementById('tasasPagination').innerHTML = '';
                return;
            }
            tbody.innerHTML = data.data.map(function(t) {
                const fecha = new Date(t.created_at).toLocaleDateString('es-VE', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
                const tipoBadge = t.tipo === 'automatica'
                    ? '<span class="badge-tipo badge-auto"><i class="fas fa-robot"></i> Automática</span>'
                    : '<span class="badge-tipo badge-manual"><i class="fas fa-user"></i> Manual</span>';
                const usuario = t.usuario ? (t.usuario.nombre + ' ' + t.usuario.apellido) : '—';
                return '<tr>' +
                    '<td>' + escaparHTML(fecha) + '</td>' +
                    '<td><strong>' + parseFloat(t.tasa).toLocaleString('es-VE', {minimumFractionDigits:2,maximumFractionDigits:2}) + '</strong></td>' +
                    '<td>' + escaparHTML(t.moneda_origen) + '/' + escaparHTML(t.moneda_destino) + '</td>' +
                    '<td>' + escaparHTML(t.fuente || '—') + '</td>' +
                    '<td>' + tipoBadge + '</td>' +
                    '<td>' + escaparHTML(usuario) + '</td>' +
                    '</tr>';
            }).join('');
            const perPage = data.per_page || data.data.length;
            const desde = ((data.current_page - 1) * perPage) + 1;
            const hasta = Math.min(data.current_page * perPage, data.total);
            document.getElementById('tasasCounter').textContent = 'Mostrando ' + desde + '–' + hasta + ' de ' + data.total + ' registros';
            currentPage = data.current_page;
            renderHistPagination(data);
        } catch(e) {
            console.error('Error historial tasas:', e);
        }
    }

    function renderHistPagination(data) {
        const cont = document.getElementById('tasasPagination');
        if (!cont) return;
        if (data.last_page <= 1) { cont.innerHTML = ''; return; }
        const cur = data.current_page;
        const last = data.last_page;
        let html = '';
        if (cur > 1) html += '<button data-p="' + (cur - 1) + '" class="hist-pag-btn" title="Anterior">‹</button>';
        for (let i = 1; i <= last; i++) {
            if (i === 1 || i === last || (i >= cur - 2 && i <= cur + 2)) {
                html += '<button data-p="' + i + '" class="hist-pag-btn' + (i === cur ? ' active' : '') + '">' + i + '</button>';
            } else if (i === cur - 3 || i === cur + 3) {
                html += '<span class="hist-pag-dots">…</span>';
            }
        }
        if (cur < last) html += '<button data-p="' + (cur + 1) + '" class="hist-pag-btn" title="Siguiente">›</button>';
        cont.innerHTML = html;
        cont.querySelectorAll('button').forEach(function(b) {
            b.addEventListener('click', function() { cargarHistorial(parseInt(this.dataset.p, 10)); });
        });
    }

    function abrirModal() {
        document.getElementById('formTasa').reset();
        document.getElementById('tituloModalTasa').textContent = 'Registrar Tasa de Cambio';
        document.getElementById('modalRegistrarTasa').classList.add('active');
    }

    function cerrarModal() {
        document.getElementById('modalRegistrarTasa').classList.remove('active');
    }

    document.getElementById('btnRegistrarTasa')?.addEventListener('click', abrirModal);
    document.getElementById('btnRegistrarTasa2')?.addEventListener('click', abrirModal);
    document.getElementById('btnCancelarTasa')?.addEventListener('click', cerrarModal);
    document.getElementById('modalRegistrarTasa')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });

    document.getElementById('btnSincronizarTasa')?.addEventListener('click', async function() {
        try {
            mostrarCargando('Sincronizando tasa de cambio...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/tasas-cambio/sincronizar', {
                method: 'POST',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':token}
            });
            const data = await resp.json();
            mostrarToast(data.mensaje || 'Proceso completado.', data.estado === 'success' ? 'success' : 'warning');
            cargarTasaActual();
            cargarHistorial();
        } catch(e) {
            mostrarToast('Error al sincronizar.', 'error');
        } finally {
            ocultarCargando();
        }
    });

    document.getElementById('formTasa')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitTasa');
        btn.disabled = true; btn.textContent = 'Guardando...';
        try {
            const formData = new FormData(e.target);
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/tasas-cambio', {
                method: 'POST',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':token},
                body: formData
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Tasa registrada.', 'success');
            cerrarModal();
            cargarTasaActual();
            cargarHistorial();
        } catch(err) {
            if (err.errors) {
                mostrarToast(Object.values(err.errors).flat().join('\n'), 'error');
            } else {
                mostrarToast(err.mensaje || 'Error al guardar.', 'error');
            }
        } finally {
            btn.disabled = false; btn.textContent = 'Registrar';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var sec = document.getElementById('tasas-cambio');
        if (sec && sec.classList.contains('active')) {
            cargarTasaActual();
            cargarHistorial();
            var rol = window.SIGEJUB_ROL || '';
            if (rol === 'admin' || rol === 'superadmin') {
                document.getElementById('tasaHeaderActions').style.display = 'flex';
                document.getElementById('tasaAdminActions').style.display = 'flex';
            }
        }
    });

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.target.id === 'tasas-cambio' && m.target.classList.contains('active')) {
                cargarTasaActual();
                cargarHistorial();
                var rol = window.SIGEJUB_ROL || '';
                if (rol === 'admin' || rol === 'superadmin') {
                    document.getElementById('tasaHeaderActions').style.display = 'flex';
                    document.getElementById('tasaAdminActions').style.display = 'flex';
                }
            }
        });
    });
    var sec = document.getElementById('tasas-cambio');
    if (sec) observer.observe(sec, {attributes:true, attributeFilter:['class']});

    window.addEventListener('sigejub:tasa-actualizada', function() {
        cargarTasaActual();
    });
})();
</script>
