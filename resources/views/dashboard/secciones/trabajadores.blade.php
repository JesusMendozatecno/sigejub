{{-- trabajadores.blade.php - Sección de directorio de trabajadores: tabla DataTable con filtros, modal de registro/edición con formulario completo y modal de confirmación de baja. --}}
<header class="section-header">
    <div class="header-info">
        <h1>Directorio de Trabajadores</h1>
        <p>Gestione la información laboral y el estatus institucional de los miembros activos y jubilados.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" id="btnRegistrarTrabajador">
            <i class="fas fa-circle-plus" size="20"></i> Registrar Trabajador
        </button>
    </div>
</header>

<section class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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
            <option value="">Todas</option>
            <option value="Docente">Docente</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Obrero">Obrero</option>
        </select>
    </div>
    <div class="filter-group">
        <label>ASIGNACIÓN</label>
        <select id="filtroAsignacion">
            <option value="">Todas</option>
            <option value="Manual">Manual</option>
            <option value="Nomina">Nómina</option>
        </select>
    </div>
    <div class="total-badge-card" style="margin-left: auto;">
        <div>
            <p>TOTAL REGISTRADOS</p>
            <h2 id="totalTrabajadores">0</h2>
        </div>
        <i class="fas fa-users icon-bg"></i>
    </div>
</section>

<div class="data-table-container" style="margin-top: 20px;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>NOMBRE COMPLETO</th>
                <th>CÉDULA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>ASIGNACIÓN</th>
                <th>ESTATUS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyTrabajadores">
            </tbody>
    </table>
    
    <div class="table-footer">
        <span>Mostrando registros en tiempo real</span>
        <div class="pagination">
            <button type="button">&lt;</button>
            <button type="button" class="active">1</button>
            <button type="button">&gt;</button>
        </div>
    </div>
</div>

<div class="content-layout" style="margin-top: 20px;">
    <div class="promo-card-blue">
        <div class="promo-content">
            <h3>Próximas Jubilaciones</h3>
            <div id="proximasJubilacionesList">
                <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">Cargando...</p>
            </div>
            <button class="btn-white" type="button" style="margin-top:12px;">Ver Calendario</button>
        </div>
        <div class="promo-icon-watermark">
            <i class="fas fa-scroll"></i>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-header">
            <i class="fas fa-circle-check"></i> AUDITORÍA AL DÍA
        </div>
        <h3>Estatus de Datos</h3>
        <p id="estatusDatosTexto">Cargando estadísticas...</p>
        <div class="progress-container">
            <div class="progress-bar" id="estatusDatosBarra" style="width: 0%;"></div>
        </div>
        <span class="progress-val" id="estatusDatosPorcentaje">0%</span>
    </div>
</div>

<div id="modalTrabajador" class="modal-overlay" style="z-index: 2000;">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">Sigejub v1.0</span>
            <h1 id="modalTitle">Registrar<br>Nuevo<br>Trabajador</h1>
            <p id="modalDescription">Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.</p>
            <div class="sidebar-actions">
                <button type="button" class="btn-sidebar-edit" id="btnHabilitarEdicion" style="display: none;">
                    <i class="fas fa-pen"></i> Editar Expediente
                </button>
                <button type="button" class="btn-sidebar-cancel" id="btnCancelar">Descartar</button>
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModal" type="button">&times;</button>
            
            <form id="formTrabajador">
                @csrf
                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CÉDULA DE IDENTIDAD</label>
                            <div style="display:flex;gap:0;">
                                <select name="tipo_documento" id="selectTipoDocumento" required style="width:80px;min-width:80px;border-radius:8px 0 0 8px;border-right:none;">
                                    <option value="V" selected>V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                    <option value="P">P</option>
                                    <option value="G">G</option>
                                </select>
                                <input type="text" name="cedula" id="inputCedula" required placeholder="00000000" pattern="\d{5,10}" title="Solo números (5 a 10 dígitos)" oninput="this.value=this.value.replace(/[^0-9]/g,'')" onkeypress="if(!/[0-9]/.test(event.key))event.preventDefault()" style="border-radius:0 8px 8px 0;flex:1;">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>GÉNERO</label>
                            <select name="genero" id="selectGenero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRES</label>
                            <input type="text" name="nombres" id="inputNombres" required oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')" onkeypress="if(!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key))event.preventDefault()">
                        </div>
                        <div class="input-group">
                            <label>APELLIDOS</label>
                            <input type="text" name="apellidos" id="inputApellidos" required oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')" onkeypress="if(!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key))event.preventDefault()">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="date" name="fecha_nacimiento" id="inputFechaNacimiento" required>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-building"></i> Datos Institucionales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CARGO ACTUAL</label>
                            <select name="cargo" id="selectCargo" required>
                                <option value="" disabled selected>Cargando...</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>UNIDAD O DEPARTAMENTO</label>
                            <input type="text" name="unidad_departamento" id="inputUnidadDepartamento" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>GRADO / NIVEL</label>
                            <select name="grado_nivel" id="selectGradoNivel" required>
                                <option value="" disabled selected>Cargando...</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>FECHA DE INGRESO</label>
                            <input type="date" name="fecha_ingreso" id="inputFechaIngreso" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>AÑOS ADM. PÚBLICA (EXTERNO)</label>
                            <input type="number" inputmode="numeric" name="anos_servicio_externo" id="inputAnosExterno" value="0" min="0" max="60" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <div class="input-group">
                            <label>% ANTIGÜEDAD (OPCIONAL)</label>
                            <input type="number" inputmode="decimal" step="0.01" name="porcentaje_antiguedad" id="inputPorcentajeAntiguedad" value="0" min="0" max="100">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-graduation-cap"></i> Información Socio-Económica</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NIVEL DE INSTRUCCIÓN</label>
                            <select name="nivel_instruccion" id="selectNivelInstruccion">
                                <option value="1">TSU</option>
                                <option value="2">Licenciado / Ingeniero</option>
                                <option value="3">Especialista</option>
                                <option value="4">Magíster</option>
                                <option value="5">Doctorado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>NÚMERO DE CUENTA (BDV)</label>
                            <input type="tel" inputmode="numeric" name="cuenta_bancaria" id="inputCuentaBancaria" placeholder="0102..." pattern="\d{20}" maxlength="20" title="Solo 20 dígitos numéricos" oninput="this.value=this.value.replace(/[^0-9]/g,'')" onkeypress="if(!/[0-9]/.test(event.key))event.preventDefault()">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;padding:14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:8px 12px;background:white;border-radius:8px;border:1px solid #e2e8f0;transition:all 0.2s;" id="labelTieneHijos">
                                <input type="checkbox" id="checkTieneHijos" onchange="toggleHijosFields()" style="width:18px;height:18px;accent-color:#2563eb;">
                                <span style="font-size:0.85rem;font-weight:600;color:#0f172a;">¿Tiene hijos?</span>
                            </label>
                            <div id="inputGroupHijos" style="display:none;padding:8px 12px 8px 40px;">
                                <label style="font-size:0.75rem;color:#64748b;font-weight:600;">NÚMERO DE HIJOS</label>
                                <input type="number" name="numero_hijos" id="inputNumeroHijos" min="0" max="30" value="0" style="width:90px;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;margin-top:4px;" oninput="validarHijosDiscapacidad()">
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:8px 12px;background:white;border-radius:8px;border:1px solid #e2e8f0;transition:all 0.2s;" id="labelHijosDiscapacidad">
                                <input type="checkbox" id="checkHijosDiscapacidad" onchange="toggleHijosDiscapacidadFields()" style="width:18px;height:18px;accent-color:#2563eb;">
                                <span style="font-size:0.85rem;font-weight:600;color:#0f172a;">¿Hijos con discapacidad?</span>
                            </label>
                            <div id="inputGroupHijosDiscapacidad" style="display:none;padding:8px 12px 8px 40px;">
                                <label style="font-size:0.75rem;color:#64748b;font-weight:600;">CANTIDAD</label>
                                <input type="number" name="hijos_discapacidad" id="inputHijosDiscapacidad" min="0" max="30" value="0" style="width:90px;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;margin-top:4px;">
                            </div>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:8px 12px;background:white;border-radius:8px;border:1px solid #e2e8f0;transition:all 0.2s;">
                                <input type="checkbox" name="actividad_universitaria" id="checkActividadUniversitaria" value="1" style="width:18px;height:18px;accent-color:#2563eb;">
                                <span style="font-size:0.85rem;font-weight:600;color:#0f172a;">¿Realiza actividad universitaria?</span>
                            </label>
                        </div>
                    </div>
                </section>

        

                <div class="modal-actions">
                    <button type="submit" class="btn-submit" id="btnSubmitTrabajador">Registrar Trabajador</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalAgregarCatalogo" class="modal-overlay" style="z-index: 3000;">
    <div class="modal-delete-box" style="max-width:400px;">
        <h3 id="tituloModalCatalogo"><i class="fas fa-plus-circle"></i> Agregar Nuevo Cargo</h3>
        <div style="margin-top:16px;">
            <div class="input-group" style="margin-bottom:12px;">
                <label>NOMBRE</label>
                <input type="text" id="inputNombreCatalogo" placeholder="Ej: Coordinador" required>
            </div>
            <div class="input-group" style="margin-bottom:12px;">
                <label>CÓDIGO</label>
                <input type="text" id="inputCodigoCatalogo" placeholder="Ej: COORD" required pattern="[A-Za-z0-9_\-]+" oninput="this.value=this.value.replace(/[^A-Za-z0-9_\-]/g,'')">
            </div>
        </div>
        <div class="delete-actions">
            <button type="button" class="btn-delete-cancel" id="btnCancelarCatalogo">Cancelar</button>
            <button type="button" class="btn-delete-confirm" id="btnGuardarCatalogo">Guardar</button>
        </div>
    </div>
</div>

<script>
function toggleHijosFields() {
    const checked = document.getElementById('checkTieneHijos').checked;
    document.getElementById('inputGroupHijos').style.display = checked ? 'block' : 'none';
    if (!checked) {
        document.getElementById('inputNumeroHijos').value = 0;
        document.getElementById('checkHijosDiscapacidad').checked = false;
        toggleHijosDiscapacidadFields();
    }
}

function toggleHijosDiscapacidadFields() {
    const checked = document.getElementById('checkHijosDiscapacidad').checked;
    document.getElementById('inputGroupHijosDiscapacidad').style.display = checked ? 'block' : 'none';
    if (!checked) {
        document.getElementById('inputHijosDiscapacidad').value = 0;
    }
}

function validarHijosDiscapacidad() {
    const maxHijos = parseInt(document.getElementById('inputNumeroHijos').value) || 0;
    const discInput = document.getElementById('inputHijosDiscapacidad');
    if (parseInt(discInput.value) > maxHijos) {
        discInput.value = maxHijos;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('inputHijosDiscapacidad').addEventListener('input', function() {
        const maxHijos = parseInt(document.getElementById('inputNumeroHijos').value) || 0;
        if (parseInt(this.value) > maxHijos) {
            this.value = maxHijos;
            mostrarToast('La cantidad de hijos con discapacidad no puede superar la cantidad de hijos.', 'warning');
        }
    });
});
</script>

<div id="modalEliminarConfirm" class="modal-overlay" style="z-index: 2500;">
    <div class="modal-delete-box">
        <div class="delete-icon-warn">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3>¿Confirmar baja del trabajador?</h3>
        <p>Esta acción ejecutará un soft-delete institucional sobre <strong id="deleteWorkerName"></strong>. ¿Desea continuar?</p>
        <div class="delete-actions">
            <button type="button" class="btn-delete-cancel" id="btnNoEliminar">No, cancelar</button>
            <button type="button" class="btn-delete-confirm" id="btnSiEliminar">Sí, eliminar</button>
        </div>
    </div>
</div>