<header class="section-header">
    <div class="header-info">
        <h1>Directorio de Trabajadores</h1>
        <p>Gestione la información laboral y el estatus institucional de los miembros activos y jubilados.</p>
    </div>
</div>

{{-- ======================================================
     SCRIPTS — Eventos de tabla y formulario trabajador
====================================================== --}}
<script>
// ── EDITAR: cargar datos en el modal ──────────────────
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-editar');
    if (!btn) return;

    const f = document.getElementById('formTrabajador');
    f.setAttribute('data-action', `/trabajadores/${btn.dataset.id}`);
    f.setAttribute('data-method', 'PUT');
    document.getElementById('btnSubmitTrabajador').textContent = 'Guardar Cambios';

    f.querySelector('[name="cedula"]').value                   = btn.dataset.cedula;
    f.querySelector('[name="nombres"]').value                  = btn.dataset.nombres;
    f.querySelector('[name="apellidos"]').value                 = btn.dataset.apellidos;
    f.querySelector('[name="cargo"]').value                     = btn.dataset.cargo;
    f.querySelector('[name="unidad_departamento"]').value       = btn.dataset.unidad;
    f.querySelector('[name="grado_nivel"]').value               = btn.dataset.grado;
    f.querySelector('[name="fecha_ingreso"]').value             = btn.dataset['fecha-ingreso'];
    f.querySelector('[name="fecha_nacimiento"]').value          = btn.dataset['fecha-nacimiento'];
    f.querySelector('[name="genero"]').value                    = btn.dataset.genero;
    f.querySelector('[name="nivel_instruccion"]').value         = btn.dataset.nivel;
    f.querySelector('[name="anos_servicio_externo"]').value     = btn.dataset.externo;
    f.querySelector('[name="porcentaje_antiguedad"]').value     = btn.dataset.porcAntig;
    f.querySelector('[name="cuenta_bancaria"]').value           = btn.dataset.cuenta;

    document.getElementById('modalTrabajador').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

// ── ELIMINAR: confirmar y borrar ──────────────────────
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-eliminar');
    if (!btn) return;

    const nombre = btn.dataset.nombre;
    const id     = btn.dataset.id;

    if (!confirm(`¿Eliminar a "${nombre}"?\nEsta acción se puede deshacer (soft delete).`)) return;

    fetch(`/trabajadores/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            const fila = btn.closest('tr');
            fila.style.transition = 'opacity 0.3s';
            fila.style.opacity = '0';
            setTimeout(() => fila.remove(), 300);
            document.getElementById('totalTrabajadores').textContent =
                parseInt(document.getElementById('totalTrabajadores').textContent || '0') - 1;
            alert(data.message);
        } else {
            alert(data.message || 'Error al eliminar');
        }
    })
    .catch(err => alert('Error de conexión.'));
});

// ── ENVÍO FORMULARIO: crear o editar según method ───────
const formTrabajador = document.getElementById('formTrabajador');
if (formTrabajador) {
    formTrabajador.addEventListener('submit', async function(e) {
        e.preventDefault();

        const url        = this.getAttribute('data-action');
        const method     = this.getAttribute('data-method') || 'POST';
        const formData   = new FormData(this);
        const btnSubmit  = this.querySelector('.btn-submit');

        if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Guardando...'; }

        try {
            const resp = await fetch(url, {
                method : method,
                body   : formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            });
            const data = await resp.json();
            if (!resp.ok) throw data;

            alert(data.message || 'Operación exitosa.');
            formTrabajador.reset();
            document.getElementById('closeModal')?.click();

            // Reiniciar modal a modo CREAR
            formTrabajador.setAttribute('data-action',  '/trabajadores');
            formTrabajador.setAttribute('data-method',  'POST');
            btnSubmit.textContent = 'Registrar Trabajador';

            // Recargar tabla
            location.reload();

        } catch (err) {
            if (err.errors) {
                alert(Object.values(err.errors).flat().join('\n'));
            } else {
                alert(err.message || 'Error interno.');
            }
        } finally {
            if (btnSubmit) { btnSubmit.disabled = false; }
        }
    });
}

// ── FILTRO POR ESTATUS ────────────────────────────────
const selEstatus = document.getElementById('filtroEstatus');
if (selEstatus) {
    selEstatus.addEventListener('change', () => {
        cargarTrabajadores(selEstatus.value);
    });
}

// ── LIMPIAR ESTADO AL CERRAR MODAL ────────────────────
const btnCancelar = document.getElementById('btnCancelar');
if (btnCancelar) {
    btnCancelar.addEventListener('click', () => {
        const f = document.getElementById('formTrabajador');
        setTimeout(() => {
            f.reset();
            f.setAttribute('data-action', '/trabajadores');
            f.setAttribute('data-method', 'POST');
            const btn = document.getElementById('btnSubmitTrabajador');
            if (btn) btn.textContent = 'Registrar Trabajador';
        }, 100);
    });
}
</script>

    <div class="filter-group">
        <label>FILTRAR POR ESTATUS</label>
        <select id="filtroEstatus">
            <option value="">Cualquier estatus</option>
            <option value="activo">Activo</option>
            <option value="jubilado">Jubilado</option>
        </select>
    </div>
    <div class="filter-group">
        <label>TIPO DE NÓMINA</label>
        <select id="filtroNomina">
            <option>Todas</option>
            <option>Docente</option>
            <option>Administrativo</option>
            <option>Obrero</option>
        </select>
    </div>
    <div class="total-badge-card">
        <div>
            <p>TOTAL REGISTRADOS</p>
            <h2 id="totalTrabajadores">0</h2>
        </div>
        <i data-lucide="users" class="icon-bg"></i>
    </div>
</section>

{{-- Botón de apertura del modal — onclick nativo para delegar a JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnAbrirModal = document.querySelector('.btn-primary-dark');
    const modal = document.getElementById('modalTrabajador');
    const btnCerrar  = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');

    function abrirModal() {
        modal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function cerrarModal() {
        modal.style.display = 'none';
    }

    if (btnAbrirModal) btnAbrirModal.addEventListener('click', (e) => { e.preventDefault(); abrirModal(); });
    if (btnCerrar)    btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar)  btnCancelar.addEventListener('click', cerrarModal);
    window.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });
});
</script>

<div class="data-table-container">
    <table class="custom-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOMBRE COMPLETO</th>
                <th>CÉDULA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>ESTATUS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyTrabajadores">
            <!-- Las filas se cargan dinámicamente por JS desde la BD -->
        </tbody>
    </table>
    
    <div class="table-footer">
        <span>Mostrando 1 - 10 de 1,248 trabajadores</span>
        <div class="pagination">
            <button>&lt;</button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <span>...</span>
            <button>125</button>
            <button>&gt;</button>
        </div>
    </div>
</div>

<div class="content-layout">
    <div class="promo-card-blue">
        <div class="promo-content">
            <h3>Próximas Jubilaciones</h3>
            <p>Hay 14 docentes que cumplen los requisitos de años de servicio este trimestre. Inicie el proceso de revisión de expedientes.</p>
            <button class="btn-white">Ver Calendario</button>
        </div>
        <div class="promo-icon-watermark">
            <i data-lucide="scroll"></i>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-header">
            <i data-lucide="check-circle-2"></i> AUDITORÍA AL DÍA
        </div>
        <h3>Estatus de Datos</h3>
        <p>El 98.4% de los expedientes cuentan con documentación digitalizada completa.</p>
        <div class="progress-container">
            <div class="progress-bar" style="width: 98%;"></div>
        </div>
        <span class="progress-val">98%</span>
    </div>
</div>

<div id="modalTrabajador" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">Sigejub v1.0</span>
            <h1>Registrar<br>Nuevo<br>Trabajador</h1>
            <p>Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.</p>
            <div style="margin-top: auto; font-size: 0.75rem; color: #64748b;">
                <i data-lucide="info" style="width: 14px; vertical-align: middle;"></i> 
                Asegúrese de que la cédula sea exacta para evitar duplicados.
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModal" type="button">&times;</button>
            
            <form id="formTrabajador" data-action="{{ route('trabajador') }}">
                @csrf 
                <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CÉDULA DE IDENTIDAD</label>
                            <input type="text" name="cedula" required placeholder="V-00000000">
                        </div>
                        <div class="input-group">
                            <label>GÉNERO</label>
                            <select name="genero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRES</label>
                            <input type="text" name="nombres" required>
                        </div>
                        <div class="input-group">
                            <label>APELLIDOS</label>
                            <input type="text" name="apellidos" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="date" name="fecha_nacimiento" required title="Necesario para calcular la edad automáticamente">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="building"></i> Datos Institucionales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CARGO ACTUAL</label>
                            <input type="text" name="cargo" required>
                        </div>
                        <div class="input-group">
                            <label>UNIDAD O DEPARTAMENTO</label>
                            <input type="text" name="unidad_departamento" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>GRADO / NIVEL</label>
                            <input type="text" name="grado_nivel" placeholder="Ej: P1, B1..." required>
                        </div>
                        <div class="input-group">
                            <label>FECHA DE INGRESO</label>
                            <input type="date" name="fecha_ingreso" required title="Necesario para calcular antigüedad institucional">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>AÑOS ADM. PÚBLICA (EXTERNO)</label>
                            <input type="number" name="anos_servicio_externo" value="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>% ANTIGÜEDAD (OPCIONAL)</label>
                            <input type="number" step="0.01" name="porcentaje_antiguedad" value="0">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="graduation-cap"></i> Información Socio-Económica</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NIVEL DE INSTRUCCIÓN</label>
                            <select name="nivel_instruccion">
                                <option value="1">TSU</option>
                                <option value="2">Licenciado / Ingeniero</option>
                                <option value="3">Especialista</option>
                                <option value="4">Magíster</option>
                                <option value="5">Doctorado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>NÚMERO DE CUENTA (BDV)</label>
                            <input type="text" name="cuenta_bancaria" placeholder="0102..." pattern="\d{20}" title="Deben ser 20 dígitos">
                        </div>
                    </div>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelar">Descartar</button>
                    <button type="submit" class="btn-submit" id="btnSubmitTrabajador">Registrar Trabajador</button>
                </div>
            </form>
        </main>
    </div>
</div>

{{-- ======================================================
     SCRIPTS — Lógica de Trabajadores (BD en vivo)
====================================================== --}}
<script>
async function cargarTrabajadores(estatus = '') {
    const params = estatus ? `?estatus=${estatus}` : '';
    try {
        const resp = await fetch('/trabajadores' + params);
        const data = await resp.json();
        const tbody = document.getElementById('tbodyTrabajadores');

        if (!tbody) return;

        tbody.innerHTML = '';

        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem; color: #888;">No hay trabajadores registrados</td></tr>';
            document.getElementById('totalTrabajadores').textContent = '0';
            return;
        }

        data.data.forEach(t => {
            const esJubilado = (t.total_anos_servicio >= 25 || t.edad >= 60);
            const estatusTxt = esJubilado ? 'jubilado' : 'activo';
            const iniciales = t.nombres.charAt(0) + t.apellidos.charAt(0);
            const colores = ['blue', 'purple', 'green', 'orange', 'red', 'teal', 'indigo', 'pink', 'amber', 'cyan'];
            const color = colores[t.id % colores.length];

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>TR-${String(t.id).padStart(4, '0')}</td>
                <td>
                    <div class="user-cell">
                        <span class="avatar ${color}">${iniciales.toUpperCase()}</span>
                        <strong>${t.nombres} ${t.apellidos}</strong>
                    </div>
                </td>
                <td>${t.cedula}</td>
                <td>${t.cargo}</td>
                <td><span class="badge-type doc">INSTITUCIONAL</span></td>
                <td><span class="dot ${estatusTxt}"></span> ${estatusTxt.charAt(0).toUpperCase() + estatusTxt.slice(1)}</td>
                <td class="actions">
                    <i data-lucide="folder-open" class="btn-icon btn-ver" title="Ver Expediente" data-id="${t.id}"></i>
                    <i data-lucide="edit-3"   class="btn-icon btn-editar" title="Editar" data-id="${t.id}" data-nombres="${t.nombres}" data-apellidos="${t.apellidos}" data-cedula="${t.cedula}" data-cargo="${t.cargo}" data-unidad="${t.unidad_departamento}" data-grado="${t.grado_nivel}" data-fecha-ingreso="${t.fecha_ingreso}" data-fecha-nacimiento="${t.fecha_nacimiento}" data-genero="${t.genero}" data-nivel="${t.nivel_instruccion}" data-externo="${t.anos_servicio_externo}" data-porc-antig="${t.porcentaje_antiguedad}" data-cuenta="${t.cuenta_bancaria || ''}"></i>
                    <i data-lucide="trash-2"   class="btn-icon btn-eliminar" title="Eliminar" data-id="${t.id}" data-nombre="${t.nombres} ${t.apellidos}"></i>
                </td>
            `;
            tbody.appendChild(fila);
        });

        document.getElementById('totalTrabajadores').textContent = data.total || data.data.length;
        if (typeof lucide !== 'undefined') lucide.createIcons();

    } catch (err) {
        console.error('Error al cargar trabajadores:', err);
    }
}

// Cargar al entrar en la sección de trabajadores
const observer = new MutationObserver((mutations) => {
    mutations.forEach(m => {
        if (m.target.id === 'trabajadores' && m.target.classList.contains('active')) {
            cargarTrabajadores();
        }
    });
});
const seccion = document.getElementById('trabajadores');
if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
</script>
